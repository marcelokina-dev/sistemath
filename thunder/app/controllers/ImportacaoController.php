<?php

require_once __DIR__ . '/BaseController.php';

/**
 * IMPORTAÇÃO EM MASSA
 * ---------------------------------------------------------
 * Permite colar, de uma vez, o roster de atletas de um evento
 * e o card completo de lutas (com resultado) em vez de usar
 * o formulário padrão luta por luta / atleta por atleta.
 *
 * Não substitui os cadastros normais: usa os MESMOS métodos
 * das models (AtletaModel::salvarCompleto, LutaModel::salvar,
 * LutaModel::registrarResultado), só que em lote e com
 * casamento automático de atletas já cadastrados (por nome).
 */
class ImportacaoController extends BaseController
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * TELA PRINCIPAL
     */
    public function index()
    {
        $eventos = $this->db->query(
            "SELECT id_evento, nome, slugs, data_evento, id_modalidade 
             FROM eventos ORDER BY data_evento DESC"
        )->fetchAll(PDO::FETCH_ASSOC);

        $equipes = $this->db->query("SELECT id_equipe, nome FROM equipes ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
        $categorias = $this->db->query("SELECT id_categoria_peso, nome, id_modalidade, sexo FROM categoria_peso ORDER BY id_modalidade, sexo, peso_max")->fetchAll(PDO::FETCH_ASSOC);
        $metodos = $this->db->query("SELECT id_metodo, nome, sigla FROM metodos_vitoria")->fetchAll(PDO::FETCH_ASSOC);

        $this->render('importacao/index', [
            'titulo'     => 'Importação em Massa',
            'eventos'    => $eventos,
            'equipes'    => $equipes,
            'categorias' => $categorias,
            'metodos'    => $metodos,
            'relatorio'  => $_SESSION['import_relatorio'] ?? null
        ]);

        unset($_SESSION['import_relatorio']);
    }

    /**
     * PROCESSA O ENVIO DO FORMULÁRIO
     */
    public function processar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->redirect('/importacao');
        }

        if (session_status() === PHP_SESSION_NONE) session_start();

        $log = [];
        $idEvento = !empty($_POST['id_evento']) ? (int) $_POST['id_evento'] : null;
        $rosterTexto = trim($_POST['roster'] ?? '');
        $cardTexto   = trim($_POST['card'] ?? '');

        try {
            $this->db->beginTransaction();

            // 1) ROSTER (opcional) — cria ou atualiza atletas
            if ($rosterTexto !== '') {
                $log[] = "== ATLETAS (ROSTER) ==";
                foreach ($this->linhas($rosterTexto) as $i => $linha) {
                    $log[] = $this->processarLinhaRoster($linha, $i + 1);
                }
            }

            // 2) CARD DE LUTAS (opcional, mas normalmente é o principal)
            if ($cardTexto !== '') {
                if (!$idEvento) {
                    throw new Exception("Selecione o evento antes de importar o card de lutas.");
                }
                $evento = $this->buscarEvento($idEvento);
                if (!$evento) throw new Exception("Evento não encontrado.");

                $log[] = "== CARD DE LUTAS: {$evento['nome']} ==";
                foreach ($this->linhas($cardTexto) as $i => $linha) {
                    $log[] = $this->processarLinhaLuta($linha, $i + 1, $evento);
                }
            }

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            $log[] = "❌ IMPORTAÇÃO CANCELADA (nada foi salvo): " . $e->getMessage();
        }

        $_SESSION['import_relatorio'] = $log;
        return $this->redirect('/importacao');
    }

    // =========================================================
    // HELPERS
    // =========================================================

    private function linhas($texto)
    {
        $linhas = preg_split('/\r\n|\r|\n/', trim($texto));
        $out = [];
        foreach ($linhas as $l) {
            $l = trim($l);
            if ($l === '' || strpos($l, '#') === 0) continue; // ignora vazias/comentários
            $out[] = $l;
        }
        return $out;
    }

    private function normalizar($str)
    {
        $str = trim((string) $str);
        if ($str === '') return '';
        $str = mb_strtolower($str, 'UTF-8');
        $str = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $str);
        $str = preg_replace('/[^a-z0-9 ]/', '', $str);
        $str = preg_replace('/\s+/', ' ', $str);
        return trim($str);
    }

    private function buscarEvento($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM eventos WHERE id_evento = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Roster: nome|sobrenome|apelido|sexo(M/F)|nacionalidade|modalidade(P/A)|categoria|peso|altura|equipe|vitorias|derrotas|empates|tapology|sherdog
     * Só "nome" é obrigatório. Campos em branco não sobrescrevem dados existentes na atualização.
     * modalidade: P = MMA Profissional (id 1), A = MMA Amador (id 2)
     */
    private function processarLinhaRoster($linha, $numero)
    {
        $c = array_pad(array_map('trim', explode('|', $linha)), 15, '');
        [$nome, $sobrenome, $apelido, $sexo, $nacionalidade, $modalidadeFlag, $categoriaTxt, $peso, $altura,
            $equipe, $vitorias, $derrotas, $empates, $tapology, $sherdog] = $c;

        if ($nome === '') return "  L{$numero}: ⚠️ ignorada (sem nome)";

        $idEquipe = $equipe !== '' ? $this->buscarOuCriarEquipe($equipe) : null;
        $sexo = strtoupper($sexo) === 'F' ? 'F' : (strtoupper($sexo) === 'M' ? 'M' : null);
        $idModalidade = $this->resolverModalidadeFlag($modalidadeFlag);
        $idCategoria = ($categoriaTxt !== '' && $idModalidade && $sexo) ? $this->resolverCategoria($categoriaTxt, $idModalidade, $sexo) : null;

        $existente = $this->buscarAtletaPorNome($nome, $sobrenome, $apelido);

        if ($existente) {
            // Atualiza só os campos preenchidos, sem apagar o que já existe
            $sets = [];
            $params = [':id' => $existente['id_atleta']];
            $map = [
                'apelido' => $apelido, 'nacionalidade' => $nacionalidade, 'sexo' => $sexo,
                'id_equipe' => $idEquipe, 'tapology' => $tapology, 'sherdog' => $sherdog,
                'id_modalidade' => $idModalidade, 'id_categoria_peso' => $idCategoria,
            ];
            foreach ($map as $col => $val) {
                if ($val !== '' && $val !== null) {
                    $sets[] = "$col = :$col";
                    $params[":$col"] = $val;
                }
            }
            if ($peso !== '') { $sets[] = "peso = :peso"; $params[':peso'] = (float) str_replace(',', '.', $peso); }
            if ($altura !== '') { $sets[] = "altura = :altura"; $params[':altura'] = (float) str_replace(',', '.', $altura); }
            if ($vitorias !== '') { $sets[] = "vitorias = :vitorias"; $params[':vitorias'] = (int) $vitorias; }
            if ($derrotas !== '') { $sets[] = "derrotas = :derrotas"; $params[':derrotas'] = (int) $derrotas; }
            if ($empates !== '') { $sets[] = "empates = :empates"; $params[':empates'] = (int) $empates; }

            if ($sets) {
                $sql = "UPDATE atletas SET " . implode(', ', $sets) . " WHERE id_atleta = :id";
                $this->db->prepare($sql)->execute($params);
            }
            return "  L{$numero}: 🔄 atleta já existia (#{$existente['id_atleta']} {$nome} {$sobrenome}) — dados atualizados";
        }

        $sqlEnd = "INSERT INTO endereco (logradouro, bairro, cidade, estado, cep, numero, complemento, pais) VALUES ('','','','','','','', 'Brasil')";
        $this->db->exec($sqlEnd);
        $idEndereco = $this->db->lastInsertId();

        $sqlCont = "INSERT INTO contato (celular, email, instagram, facebook, whatsapp) VALUES (NULL,NULL,NULL,NULL,NULL)";
        $this->db->exec($sqlCont);
        $idContato = $this->db->lastInsertId();

        $stmt = $this->db->prepare(
            "INSERT INTO atletas (nome, sobrenome, apelido, foto, sexo, nacionalidade, peso, altura,
                id_modalidade, id_categoria_peso, vitorias, derrotas, empates,
                id_equipe, id_endereco, id_contato, tapology, sherdog, status)
             VALUES (:nome, :sobrenome, :apelido, 'sem-foto.png', :sexo, :nacionalidade, :peso, :altura,
                :id_modalidade, :id_categoria, :vitorias, :derrotas, :empates,
                :id_equipe, :id_endereco, :id_contato, :tapology, :sherdog, 'ativo')"
        );
        $stmt->execute([
            ':nome' => $nome,
            ':sobrenome' => $sobrenome ?: null,
            ':apelido' => $apelido ?: null,
            ':sexo' => $sexo ?: 'M',
            ':nacionalidade' => $nacionalidade ?: 'Brasileira',
            ':peso' => $peso !== '' ? (float) str_replace(',', '.', $peso) : null,
            ':altura' => $altura !== '' ? (float) str_replace(',', '.', $altura) : null,
            ':id_modalidade' => $idModalidade,
            ':id_categoria' => $idCategoria,
            ':vitorias' => $vitorias !== '' ? (int) $vitorias : 0,
            ':derrotas' => $derrotas !== '' ? (int) $derrotas : 0,
            ':empates' => $empates !== '' ? (int) $empates : 0,
            ':id_equipe' => $idEquipe,
            ':id_endereco' => $idEndereco,
            ':id_contato' => $idContato,
            ':tapology' => $tapology ?: null,
            ':sherdog' => $sherdog ?: null,
        ]);
        $novoId = $this->db->lastInsertId();
        return "  L{$numero}: ✅ atleta criado (#{$novoId} {$nome} {$sobrenome})";
    }

    // (resolverModalidadeFlag definido mais abaixo, com suporte a valor padrão)

    /**
     * Card: ordem|vermelho(id_ou_nome,apelido,sexo)|azul(id_ou_nome,apelido,sexo)|vencedor(V/A/E)|metodo(sigla)|round|tempo(mm:ss)|rounds_totais|modalidade(P/A/ID)|categoria(nome_ou_kg)|arbitro|cinturao(0/1)
     * O lutador pode ser um ID de atleta já cadastrado (ex: "1809") OU "nome,apelido,sexo".
     * modalidade: P=MMA Profissional(1), A=MMA Amador(2), ou o ID numérico direto (ex: 5=Kickboxing).
     *   Se em branco, usa a modalidade padrão do evento selecionado.
     * categoria: nome (ex: "Peso Leve"), ID numérico de categoria_peso, ou peso em kg (ex: "61kg") — nesse caso
     *   o sistema acha automaticamente a categoria certa pela faixa de peso da modalidade/sexo.
     */
    private function processarLinhaLuta($linha, $numero, $evento)
    {
        $c = array_pad(array_map('trim', explode('|', $linha)), 12, '');
        [$ordem, $vermelhoRaw, $azulRaw, $vencedorFlag, $metodoSigla, $round, $tempo, $numRounds, $modalidadeFlag, $categoriaTxt, $arbitroNome, $cinturao] = $c;

        if ($vermelhoRaw === '' || $azulRaw === '') {
            return "  L{$numero}: ⚠️ ignorada (faltam os dois lutadores)";
        }

        $vermelho = $this->resolverAtleta($vermelhoRaw);
        $azul     = $this->resolverAtleta($azulRaw);

        if (!$vermelho['id'] || !$azul['id']) {
            return "  L{$numero}: ❌ ignorada — {$vermelho['label']} x {$azul['label']} (ID de atleta não encontrado no banco)";
        }

        $idModalidade = $this->resolverModalidadeFlag($modalidadeFlag, $evento['id_modalidade']);
        $idCategoria = $categoriaTxt !== '' ? $this->resolverCategoria($categoriaTxt, $idModalidade, $vermelho['sexo']) : null;
        $idMetodo = $metodoSigla !== '' ? $this->resolverMetodo($metodoSigla) : null;
        $idArbitro = $arbitroNome !== '' ? $this->buscarOuCriarArbitro($arbitroNome) : null;

        $vencedorId = null;
        if (strtoupper($vencedorFlag) === 'V') $vencedorId = $vermelho['id'];
        elseif (strtoupper($vencedorFlag) === 'A') $vencedorId = $azul['id'];
        // 'E' ou vazio = empate/sem vencedor

        // Evita duplicar a mesma luta se a linha for reimportada
        $dup = $this->db->prepare(
            "SELECT id_luta FROM lutas WHERE id_evento = ? AND
             ((id_atleta_azul = ? AND id_atleta_vermelho = ?) OR (id_atleta_azul = ? AND id_atleta_vermelho = ?))"
        );
        $dup->execute([$evento['id_evento'], $azul['id'], $vermelho['id'], $vermelho['id'], $azul['id']]);
        if ($dup->fetch()) {
            return "  L{$numero}: ⏭️ luta {$vermelho['label']} x {$azul['label']} já existia neste evento — pulada";
        }

        // Regra: decisão (UD/SD/MD) no MMA Amador (id 2) sem tempo informado = 3:00
        $siglaUpper = strtoupper(trim($metodoSigla));
        if ($tempo === '' && $idModalidade == 2 && in_array($siglaUpper, ['UD', 'SD', 'MD'])) {
            $tempo = '3:00';
        }

        $tempoSql = null;
        if ($tempo !== '') {
            $tempoSql = (substr_count($tempo, ':') === 1) ? "00:$tempo" : $tempo;
        }

        $stmt = $this->db->prepare(
            "INSERT INTO lutas (id_evento, ordem_card, id_atleta_azul, id_atleta_vermelho, vencedor_id,
                id_metodo, round_final, tempo_final, id_modalidade, id_categoria_peso, id_arbitro, status, num_rounds, vale_cinturao)
             VALUES (:id_evento, :ordem, :azul, :vermelho, :vencedor, :metodo, :round, :tempo, :modalidade, :categoria, :arbitro, 'realizada', :num_rounds, :cinturao)"
        );
        $stmt->execute([
            ':id_evento' => $evento['id_evento'],
            ':ordem' => $ordem !== '' ? (int) $ordem : 0,
            ':azul' => $azul['id'],
            ':vermelho' => $vermelho['id'],
            ':vencedor' => $vencedorId,
            ':metodo' => $idMetodo,
            ':round' => $round !== '' ? (int) $round : null,
            ':tempo' => $tempoSql,
            ':modalidade' => $idModalidade,
            ':categoria' => $idCategoria,
            ':arbitro' => $idArbitro,
            ':num_rounds' => $numRounds !== '' ? (int) $numRounds : 3,
            ':cinturao' => ($cinturao === '1') ? 1 : 0,
        ]);

        $prefixoAtletas = ($vermelho['novo'] ? '🆕' : '') . ($azul['novo'] ? '🆕' : '');
        return "  L{$numero}: ✅ luta criada — {$vermelho['label']} x {$azul['label']} " .
               ($vencedorId ? "(vencedor: " . ($vencedorId == $vermelho['id'] ? $vermelho['label'] : $azul['label']) . ")" : "(empate/NC)") .
               ($prefixoAtletas ? " {$prefixoAtletas} atleta(s) novo(s) criado(s) automaticamente" : "");
    }

    private function buscarOuCriarArbitro($nome)
    {
        $nome = trim($nome);
        if ($nome === '' || strtoupper($nome) === 'N/A') return null;
        $alvo = $this->normalizar($nome);
        $stmt = $this->db->query("SELECT id_arbitro, nome FROM arbitros");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($this->normalizar($row['nome']) === $alvo) return $row['id_arbitro'];
        }
        $ins = $this->db->prepare("INSERT INTO arbitros (nome, status) VALUES (?, 'ativo')");
        $ins->execute([$nome]);
        return $this->db->lastInsertId();
    }

    /**
     * Recebe "Nome Sobrenome,Apelido,Sexo" (apelido/sexo opcionais) e retorna
     * o atleta existente (casado por nome) ou cria um cadastro mínimo na hora.
     * TAMBÉM aceita um ID de atleta já cadastrado direto, ex: "1809" ou "#1809".
     */
    private function resolverAtleta($raw)
    {
        $raw = trim($raw);

        // --- ID de atleta já existente (ex: "1809" ou "#1809") ---
        $possivelId = ltrim($raw, '#');
        if ($possivelId !== '' && ctype_digit($possivelId)) {
            $stmt = $this->db->prepare("SELECT id_atleta, nome, sobrenome, sexo FROM atletas WHERE id_atleta = ?");
            $stmt->execute([(int) $possivelId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                return [
                    'id' => $row['id_atleta'],
                    'sexo' => $row['sexo'] ?: 'M',
                    'label' => trim($row['nome'] . ' ' . $row['sobrenome']),
                    'novo' => false
                ];
            }
            // ID informado mas não existe no banco — não dá pra inventar, sinaliza erro
            return ['id' => null, 'sexo' => 'M', 'label' => "ID {$possivelId} (NÃO ENCONTRADO)", 'novo' => false];
        }

        $partes = array_map('trim', explode(',', $raw));
        $nomeCompleto = $partes[0] ?? '';
        $apelido = $partes[1] ?? '';
        $sexo = isset($partes[2]) && strtoupper($partes[2]) === 'F' ? 'F' : 'M';

        $tokens = preg_split('/\s+/', trim($nomeCompleto));
        $nome = array_shift($tokens);
        $sobrenome = implode(' ', $tokens);

        $existente = $this->buscarAtletaPorNome($nome, $sobrenome, $apelido);
        if ($existente) {
            return [
                'id' => $existente['id_atleta'],
                'sexo' => $existente['sexo'] ?: $sexo,
                'label' => trim($existente['nome'] . ' ' . $existente['sobrenome']),
                'novo' => false
            ];
        }

        // Cria cadastro mínimo (pode ser completado depois em Atletas > Editar, ou via roster)
        $this->db->exec("INSERT INTO endereco (logradouro, bairro, cidade, estado, cep, numero, complemento, pais) VALUES ('','','','','','','', 'Brasil')");
        $idEndereco = $this->db->lastInsertId();
        $this->db->exec("INSERT INTO contato (celular, email, instagram, facebook, whatsapp) VALUES (NULL,NULL,NULL,NULL,NULL)");
        $idContato = $this->db->lastInsertId();

        $stmt = $this->db->prepare(
            "INSERT INTO atletas (nome, sobrenome, apelido, foto, sexo, nacionalidade, id_endereco, id_contato, status)
             VALUES (:nome, :sobrenome, :apelido, 'sem-foto.png', :sexo, 'Brasileira', :id_endereco, :id_contato, 'ativo')"
        );
        $stmt->execute([
            ':nome' => $nome ?: $nomeCompleto,
            ':sobrenome' => $sobrenome ?: null,
            ':apelido' => $apelido ?: null,
            ':sexo' => $sexo,
            ':id_endereco' => $idEndereco,
            ':id_contato' => $idContato,
        ]);
        $novoId = $this->db->lastInsertId();

        return ['id' => $novoId, 'sexo' => $sexo, 'label' => trim("$nome $sobrenome"), 'novo' => true];
    }

    private function buscarAtletaPorNome($nome, $sobrenome, $apelido)
    {
        $alvoNome = $this->normalizar($nome . ' ' . $sobrenome);
        $alvoApelido = $this->normalizar($apelido);

        $stmt = $this->db->query("SELECT id_atleta, nome, sobrenome, apelido, sexo FROM atletas");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $nomeAtual = $this->normalizar($row['nome'] . ' ' . $row['sobrenome']);
            if ($nomeAtual !== '' && $nomeAtual === $alvoNome) return $row;
            if ($alvoApelido !== '' && $this->normalizar($row['apelido']) === $alvoApelido) return $row;
        }
        return null;
    }

    private function buscarOuCriarEquipe($nome)
    {
        $alvo = $this->normalizar($nome);
        $stmt = $this->db->query("SELECT id_equipe, nome FROM equipes");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($this->normalizar($row['nome']) === $alvo) return $row['id_equipe'];
        }
        $ins = $this->db->prepare("INSERT INTO equipes (nome, status) VALUES (?, 'Ativo')");
        $ins->execute([$nome]);
        return $this->db->lastInsertId();
    }

    private function resolverCategoria($texto, $idModalidade, $sexo)
    {
        $texto = trim($texto);
        if ($texto === '' || !$idModalidade) return null;

        // Peso em kg (ex: "61kg", "61.5 kg") — acha a categoria pela faixa de peso
        if (preg_match('/^([0-9]+(?:[.,][0-9]+)?)\s*kg$/i', $texto, $m)) {
            $kg = (float) str_replace(',', '.', $m[1]);

            $stmt = $this->db->prepare(
                "SELECT id_categoria_peso FROM categoria_peso
                 WHERE id_modalidade = ? AND sexo = ? AND ? BETWEEN peso_min AND peso_max LIMIT 1"
            );
            $stmt->execute([$idModalidade, $sexo, $kg]);
            $r = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($r) return $r['id_categoria_peso'];

            // Não caiu exatamente numa faixa: pega a categoria mais próxima acima
            $stmt2 = $this->db->prepare(
                "SELECT id_categoria_peso FROM categoria_peso
                 WHERE id_modalidade = ? AND sexo = ? AND peso_max >= ?
                 ORDER BY peso_max ASC LIMIT 1"
            );
            $stmt2->execute([$idModalidade, $sexo, $kg]);
            $r2 = $stmt2->fetch(PDO::FETCH_ASSOC);
            return $r2 ? $r2['id_categoria_peso'] : null;
        }

        // ID numérico direto de categoria_peso
        if (is_numeric($texto)) return (int) $texto;

        // Nome da categoria (ex: "Peso Leve")
        $alvo = $this->normalizar($texto);
        $stmt = $this->db->prepare("SELECT id_categoria_peso, nome FROM categoria_peso WHERE id_modalidade = ? AND sexo = ?");
        $stmt->execute([$idModalidade, $sexo]);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (strpos($this->normalizar($row['nome']), $alvo) !== false || strpos($alvo, $this->normalizar($row['nome'])) !== false) {
                return $row['id_categoria_peso'];
            }
        }
        return null;
    }

    /**
     * P = MMA Profissional (id 1) | A = MMA Amador (id 2) | número = usa o ID direto (ex: 5 = Kickboxing)
     * Em branco = usa $default (normalmente a modalidade do evento selecionado).
     */
    private function resolverModalidadeFlag($flag, $default = null)
    {
        $flag = strtoupper(trim($flag));
        if ($flag === '') return $default;
        if (ctype_digit($flag)) return (int) $flag;
        if ($flag === 'P') return 1;
        if ($flag === 'A') return 2;
        return $default;
    }

    private function resolverMetodo($sigla)
    {
        $stmt = $this->db->prepare("SELECT id_metodo FROM metodos_vitoria WHERE UPPER(sigla) = UPPER(?) LIMIT 1");
        $stmt->execute([trim($sigla)]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return $r ? $r['id_metodo'] : null;
    }
}
