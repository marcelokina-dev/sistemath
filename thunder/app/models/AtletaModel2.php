<?php

class AtletaModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * CONTA TOTAL DE REGISTROS PARA PAGINAÇÃO
     */
    public function contarTotal($filtros = [])
    {
        $where = " WHERE 1=1 ";
        $params = [];

        if (!empty($filtros['busca'])) {
            $where .= " AND (a.nome LIKE :busca1 OR a.apelido LIKE :busca2 OR a.sobrenome LIKE :busca3)";
            $termo = '%' . $filtros['busca'] . '%';
            $params[':busca1'] = $termo;
            $params[':busca2'] = $termo;
            $params[':busca3'] = $termo;
        }
        if (!empty($filtros['sexo'])) {
            $where .= " AND a.sexo = :sexo";
            $params[':sexo'] = $filtros['sexo'];
        }
        if (!empty($filtros['modalidade'])) {
            $where .= " AND a.id_modalidade = :modalidade";
            $params[':modalidade'] = $filtros['modalidade'];
        }
        if (!empty($filtros['categoria'])) {
            $where .= " AND a.id_categoria_peso = :categoria";
            $params[':categoria'] = $filtros['categoria'];
        }

        $idEquipe = $filtros['id_equipe'] ?? $filtros['equipe'] ?? null;
        if (!empty($idEquipe)) {
            $where .= " AND a.id_equipe = :equipe";
            $params[':equipe'] = $idEquipe;
        }

        $sql = "SELECT COUNT(*) as total FROM atletas a $where";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($result['total'] ?? 0);
    }

    /**
     * LISTAR ATLETAS COM FILTROS E PAGINAÇÃO
     */
    public function listar($filtros = [], $limite = 15, $offset = 0)
    {
        $where = " WHERE 1=1 ";
        $params = [];

        if (!empty($filtros['busca'])) {
            $where .= " AND (a.nome LIKE :busca1 OR a.apelido LIKE :busca2 OR a.sobrenome LIKE :busca3)";
            $termo = '%' . $filtros['busca'] . '%';
            $params[':busca1'] = $termo;
            $params[':busca2'] = $termo;
            $params[':busca3'] = $termo;
        }
        if (!empty($filtros['sexo'])) {
            $where .= " AND a.sexo = :sexo";
            $params[':sexo'] = $filtros['sexo'];
        }
        if (!empty($filtros['modalidade'])) {
            $where .= " AND a.id_modalidade = :modalidade";
            $params[':modalidade'] = $filtros['modalidade'];
        }
        if (!empty($filtros['categoria'])) {
            $where .= " AND a.id_categoria_peso = :categoria";
            $params[':categoria'] = $filtros['categoria'];
        }

        $idEquipe = $filtros['id_equipe'] ?? $filtros['equipe'] ?? null;
        if (!empty($idEquipe)) {
            $where .= " AND a.id_equipe = :equipe";
            $params[':equipe'] = $idEquipe;
        }

        $sql = "SELECT a.*, m.nome as modalidade_nome, cp.nome as categoria_nome, eq.nome as equipe_nome, e.cidade, e.estado
                FROM atletas a 
                LEFT JOIN modalidade m ON m.id_modalidade = a.id_modalidade 
                LEFT JOIN categoria_peso cp ON cp.id_categoria_peso = a.id_categoria_peso 
                LEFT JOIN equipes eq ON eq.id_equipe = a.id_equipe 
                LEFT JOIN endereco e ON e.id_endereco = a.id_endereco 
                $where ORDER BY a.nome ASC LIMIT :limite OFFSET :offset";

        $stmt = $this->db->prepare($sql);

        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }

        $stmt->bindValue(':limite', (int) $limite, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarParaCombate()
    {
        $sql = "SELECT a.id_atleta, a.nome, a.sobrenome, a.apelido, a.sexo, a.peso,
                cp.nome as nome_categoria, cp.peso_max 
            FROM atletas a
            LEFT JOIN categoria_peso cp ON a.id_categoria_peso = cp.id_categoria_peso
            WHERE a.status = 'ativo' 
            ORDER BY a.nome ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarTodasAtivas()
    {
        return $this->db->query("SELECT * FROM modalidade ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarCategoriasPorModalidade($id_modalidade, $sexo)
    {
        $sql = "SELECT id_categoria_peso, nome, peso_max 
            FROM categoria_peso 
            WHERE id_modalidade = :mod AND sexo = :sexo AND status = 'ativo'
            ORDER BY peso_max ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':mod' => $id_modalidade, ':sexo' => $sexo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function salvarCompleto($dados, $foto, $graduacoes = [])
    {
        try {
            $this->db->beginTransaction();

            $sqlEnd = "INSERT INTO endereco (logradouro, bairro, cidade, estado, cep, numero, complemento, pais) 
                       VALUES (:logradouro, :bairro, :cidade, :estado, :cep, :numero, :complemento, :pais)";
            $stmtEnd = $this->db->prepare($sqlEnd);
            $stmtEnd->execute([
                ':logradouro' => $dados['logradouro'] ?? '',
                ':bairro' => $dados['bairro'] ?? '',
                ':cidade' => $dados['cidade'] ?? '',
                ':estado' => $dados['estado'] ?? '',
                ':cep' => $dados['cep'] ?? '',
                ':numero' => $dados['numero'] ?? '',
                ':complemento' => $dados['complemento'] ?? '',
                ':pais' => $dados['pais'] ?? 'Brasil'
            ]);
            $idEndereco = $this->db->lastInsertId();

            $sqlCont = "INSERT INTO contato (celular, email, instagram, facebook, whatsapp) 
                        VALUES (:celular, :email, :instagram, :facebook, :whatsapp)";
            $stmtCont = $this->db->prepare($sqlCont);
            $stmtCont->execute([
                ':celular' => $dados['celular'] ?? null,
                ':email' => $dados['email'] ?? null,
                ':instagram' => $dados['instagram'] ?? null,
                ':facebook' => $dados['facebook'] ?? null,
                ':whatsapp' => $dados['whatsapp'] ?? null
            ]);
            $idContato = $this->db->lastInsertId();

            $sqlAtleta = "INSERT INTO atletas (nome, sobrenome, apelido, slug, foto, sexo, cpf, rg, nacionalidade, 
                        data_nascimento, peso, altura, envergadura, vitorias, derrotas, empates,
                        id_modalidade, id_categoria_peso, id_equipe, id_endereco, id_contato, 
                        status, sherdog, tapology) VALUES (:nome, :sobrenome, :apelido, :slug, :foto, :sexo, :cpf, :rg, :nacionalidade, 
                        :data_nascimento, :peso, :altura, :envergadura, :vitorias, :derrotas, :empates,
                        :id_modalidade, :id_categoria_peso, :id_equipe, :id_endereco, :id_contato, 
                        :status, :sherdog, :tapology)";

            $stmtAtleta = $this->db->prepare($sqlAtleta);
            $stmtAtleta->execute([
                ':nome' => $dados['nome'] ?? 'Sem Nome',
                ':sobrenome' => $dados['sobrenome'] ?? null,
                ':apelido' => $dados['apelido'] ?? null,
                ':slug' => $dados['slug'] ?? null,
                ':foto' => $foto ?? 'sem-foto.png',
                ':sexo' => $dados['sexo'] ?? 'M',
                ':cpf' => $dados['cpf'] ?? null,
                ':rg' => $dados['rg'] ?? null,
                ':nacionalidade' => $dados['nacionalidade'] ?? 'Brasileira',
                ':data_nascimento' => !empty($dados['data_nascimento']) ? $dados['data_nascimento'] : null,
                ':peso' => !empty($dados['peso']) ? (float) str_replace(',', '.', $dados['peso']) : null,
                ':altura' => !empty($dados['altura']) ? (float) str_replace(',', '.', $dados['altura']) : null,
                ':envergadura' => !empty($dados['envergadura']) ? (float) str_replace(',', '.', $dados['envergadura']) : null,
                ':vitorias' => (int) ($dados['vitorias'] ?? 0),
                ':derrotas' => (int) ($dados['derrotas'] ?? 0),
                ':empates' => (int) ($dados['empates'] ?? 0),
                ':id_modalidade' => !empty($dados['id_modalidade']) ? (int) $dados['id_modalidade'] : null,
                ':id_categoria_peso' => !empty($dados['id_categoria_peso']) ? (int) $dados['id_categoria_peso'] : null,
                ':id_equipe' => !empty($dados['id_equipe']) ? (int) $dados['id_equipe'] : null,
                ':id_endereco' => $idEndereco,
                ':id_contato' => $idContato,
                ':status' => $dados['status'] ?? 'ativo',
                ':sherdog' => $dados['link_sherdog'] ?? $dados['sherdog'] ?? null,
                ':tapology' => $dados['link_tapology'] ?? $dados['tapology'] ?? null
            ]);
            $idAtleta = $this->db->lastInsertId();

            if (!empty($graduacoes) && $idAtleta) {
                $stmtGrad = $this->db->prepare("INSERT INTO atleta_graduacoes (id_atleta, id_modalidade, id_graduacao) VALUES (?, ?, ?)");
                foreach ($graduacoes as $g) {
                    if (!empty($g['id_modalidade']) && !empty($g['id_graduacao'])) {
                        $stmtGrad->execute([$idAtleta, $g['id_modalidade'], $g['id_graduacao']]);
                    }
                }
            }

            $this->db->commit();
            return $idAtleta;
        } catch (Exception $e) {
            if ($this->db->inTransaction())
                $this->db->rollBack();
            error_log("Erro ao salvar atleta: " . $e->getMessage());
            return false;
        }
    }

    public function atualizar($id_atleta, $dados, $graduacoes = [], $foto = null)
    {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("SELECT id_endereco, id_contato, foto FROM atletas WHERE id_atleta = ?");
            $stmt->execute([$id_atleta]);
            $atletaAtual = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$atletaAtual)
                throw new Exception("Atleta não encontrado.");
            $fotoFinal = $foto ?: $atletaAtual['foto'];

            // 1. Endereço
            $sqlEnd = "UPDATE endereco SET logradouro=?, bairro=?, cidade=?, estado=?, cep=?, numero=?, complemento=?, pais=? WHERE id_endereco=?";
            $this->db->prepare($sqlEnd)->execute([
                $dados['logradouro'] ?? '',
                $dados['bairro'] ?? '',
                $dados['cidade'] ?? '',
                $dados['estado'] ?? '',
                $dados['cep'] ?? '',
                $dados['numero'] ?? '',
                $dados['complemento'] ?? '',
                $dados['pais'] ?? 'Brasil',
                $atletaAtual['id_endereco']
            ]);

            // 2. Contato
            $sqlCont = "UPDATE contato SET celular=?, email=?, instagram=?, facebook=?, whatsapp=? WHERE id_contato=?";
            $this->db->prepare($sqlCont)->execute([
                $dados['celular'] ?? null,
                $dados['email'] ?? null,
                $dados['instagram'] ?? null,
                $dados['facebook'] ?? null,
                $dados['whatsapp'] ?? null,
                $atletaAtual['id_contato']
            ]);

            // 3. Atleta
            $sqlAtleta = "UPDATE atletas SET nome=?, sobrenome=?, apelido=?, slug=?, foto=?, sexo=?, cpf=?, rg=?, 
                          nacionalidade=?, data_nascimento=?, peso=?, altura=?, envergadura=?, vitorias=?, 
                          derrotas=?, empates=?, id_modalidade=?, id_categoria_peso=?, id_equipe=?, status=?, 
                          sherdog=?, tapology=? WHERE id_atleta=?";

            $this->db->prepare($sqlAtleta)->execute([
                $dados['nome'],
                $dados['sobrenome'] ?? null,
                $dados['apelido'] ?? null,
                $dados['slug'] ?? null,
                $fotoFinal,
                $dados['sexo'] ?? 'M',
                $dados['cpf'] ?? null,
                $dados['rg'] ?? null,
                $dados['nacionalidade'] ?? 'Brasileira',
                !empty($dados['data_nascimento']) ? $dados['data_nascimento'] : null,
                !empty($dados['peso']) ? (float) str_replace(',', '.', $dados['peso']) : null,
                !empty($dados['altura']) ? (float) str_replace(',', '.', $dados['altura']) : null,
                !empty($dados['envergadura']) ? (float) str_replace(',', '.', $dados['envergadura']) : null,
                (int) ($dados['vitorias'] ?? 0),
                (int) ($dados['derrotas'] ?? 0),
                (int) ($dados['empates'] ?? 0),
                (int) $dados['id_modalidade'],
                (int) $dados['id_categoria_peso'],
                !empty($dados['id_equipe']) ? (int) $dados['id_equipe'] : null,
                $dados['status'] ?? 'ativo',
                $dados['link_sherdog'] ?? $dados['sherdog'] ?? null,
                $dados['link_tapology'] ?? $dados['tapology'] ?? null,
                $id_atleta
            ]);

            // 4. Graduações
            $this->db->prepare("DELETE FROM atleta_graduacoes WHERE id_atleta = ?")->execute([$id_atleta]);
            if (!empty($graduacoes)) {
                $stmtGrad = $this->db->prepare("INSERT INTO atleta_graduacoes (id_atleta, id_modalidade, id_graduacao) VALUES (?, ?, ?)");
                foreach ($graduacoes as $g) {
                    if (!empty($g['id_modalidade']) && !empty($g['id_graduacao'])) {
                        $stmtGrad->execute([$id_atleta, $g['id_modalidade'], $g['id_graduacao']]);
                    }
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction())
                $this->db->rollBack();
            error_log("Erro ao atualizar: " . $e->getMessage());
            return false;
        }
    }

    public function buscarPorId($id)
    {
        $sql = "SELECT a.*, e.*, c.*, a.sherdog as link_sherdog, a.tapology as link_tapology,
                m.nome as modalidade_nome, cp.nome as categoria_nome, eq.nome as equipe_nome
                FROM atletas a 
                LEFT JOIN endereco e ON a.id_endereco = e.id_endereco 
                LEFT JOIN contato c ON a.id_contato = c.id_contato 
                LEFT JOIN modalidade m ON a.id_modalidade = m.id_modalidade
                LEFT JOIN categoria_peso cp ON a.id_categoria_peso = cp.id_categoria_peso
                LEFT JOIN equipes eq ON a.id_equipe = eq.id_equipe WHERE a.id_atleta = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function listarEquipesCompleto()
    {
        return $this->db->query("SELECT id_equipe, nome FROM equipes ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarGraduacoesPorAtleta($id_atleta)
    {
        $sql = "SELECT ag.*, m.nome as modalidade_nome, g.nome as graduacao_nome 
                FROM atleta_graduacoes ag
                JOIN modalidade m ON ag.id_modalidade = m.id_modalidade
                JOIN graduacao g ON ag.id_graduacao = g.id_graduacao
                WHERE ag.id_atleta = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_atleta]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function excluir($id)
    {
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("SELECT id_endereco, id_contato FROM atletas WHERE id_atleta = ?");
            $stmt->execute([$id]);
            $vinculos = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($vinculos) {
                $this->db->prepare("DELETE FROM atleta_graduacoes WHERE id_atleta = ?")->execute([$id]);
                $this->db->prepare("DELETE FROM atletas WHERE id_atleta = ?")->execute([$id]);
                if ($vinculos['id_endereco'])
                    $this->db->prepare("DELETE FROM endereco WHERE id_endereco = ?")->execute([$vinculos['id_endereco']]);
                if ($vinculos['id_contato'])
                    $this->db->prepare("DELETE FROM contato WHERE id_contato = ?")->execute([$vinculos['id_contato']]);
            }
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction())
                $this->db->rollBack();
            return false;
        }
    }

    /**
     * BUSCA O HISTÓRICO DE LUTAS DO ATLETA NO EVENTO
     */
    public function buscarHistoricoLutas($idAtleta)
    {
        // Verificamos dinamicamente qual coluna existe na tabela 'lutas' para evitar o erro Fatal
        $colunaModalidade = 'id_modalidade';
        try {
            $q = $this->db->query("SHOW COLUMNS FROM lutas LIKE 'id_modalidade'");
            if ($q->rowCount() == 0) {
                // Se não estiver na tabela lutas, provavelmente está na tabela eventos
                $colunaModalidade = 'e.id_modalidade';
            } else {
                $colunaModalidade = 'l.id_modalidade';
            }
        } catch (Exception $e) {
            $colunaModalidade = 'l.id_modalidade';
        }

        $sql = "SELECT 
                l.*, 
                e.nome AS evento_nome, 
                e.data_evento,
                m.sigla AS metodo_sigla,
                m.nome AS metodo_nome,
                
                -- Busca o nome da modalidade dinamicamente usando uma subquery segura
                (SELECT nome FROM modalidade WHERE id_modalidade = {$colunaModalidade} LIMIT 1) AS modalidade_nome,

                -- Nome do Oponente
                IF(l.id_atleta_azul = :id1, 
                    (SELECT CONCAT(nome, ' ', sobrenome) FROM atletas WHERE id_atleta = l.id_atleta_vermelho),
                    (SELECT CONCAT(nome, ' ', sobrenome) FROM atletas WHERE id_atleta = l.id_atleta_azul)
                ) AS oponente_nome,
                
                -- Foto do Oponente
                IF(l.id_atleta_azul = :id2, 
                    (SELECT foto FROM atletas WHERE id_atleta = l.id_atleta_vermelho),
                    (SELECT foto FROM atletas WHERE id_atleta = l.id_atleta_azul)
                ) AS oponente_foto,
                
                -- Resultado Ajustado para reconhecer No Contest e Empate perfeitamente
                CASE 
                    WHEN m.nome LIKE '%No Contest%' OR m.sigla LIKE '%NC%' THEN 'No Contest'
                    WHEN l.vencedor_id = :id3 THEN 'Vitória'
                    WHEN l.vencedor_id IS NULL OR l.vencedor_id = 0 THEN 'Empate'
                    ELSE 'Derrota'
                 END AS resultado
            FROM lutas l
            JOIN eventos e ON l.id_evento = e.id_evento
            LEFT JOIN metodos_vitoria m ON l.id_metodo = m.id_metodo
            WHERE l.id_atleta_azul = :id4 OR l.id_atleta_vermelho = :id5
            ORDER BY e.data_evento DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id1' => $idAtleta,
            ':id2' => $idAtleta,
            ':id3' => $idAtleta,
            ':id4' => $idAtleta,
            ':id5' => $idAtleta
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}