<?php
require_once __DIR__.'/BaseController.php';
require_once __DIR__.'/../services/TapologyService.php';

class TapologyController extends BaseController
{
    private $db;
    private $service;

    public function __construct($db)
    {
        $this->db = $db;
        $this->service = new TapologyService();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function index()
    {
        $eventos = $this->db->query(
            "SELECT id_evento,nome,data_evento,slugs,id_modalidade
             FROM eventos ORDER BY data_evento DESC"
        )->fetchAll(PDO::FETCH_ASSOC);

        $preview = $_SESSION['tapology_preview'] ?? null;
        $erro = $_SESSION['tapology_erro'] ?? null;
        $ok = $_SESSION['tapology_ok'] ?? null;

        unset(
            $_SESSION['tapology_preview'],
            $_SESSION['tapology_erro'],
            $_SESSION['tapology_ok']
        );

        $this->render('tapology/index', [
            'titulo' => 'Importar Tapology',
            'eventos' => $eventos,
            'preview' => $preview,
            'erro' => $erro,
            'ok' => $ok
        ]);
    }

    public function analisar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->redirect('/tapology');
        }

        $texto = trim($_POST['tapology_texto'] ?? '');
        $id = (int)($_POST['id_evento'] ?? 0);

        if ($texto === '' || !$id) {
            $_SESSION['tapology_erro'] =
                'Selecione o evento e cole o conteúdo do card.';
            return $this->redirect('/tapology');
        }

        $evento = $this->buscarEvento($id);

        if (!$evento) {
            $_SESSION['tapology_erro'] = 'Evento não encontrado.';
            return $this->redirect('/tapology');
        }

        $a = $this->service->analisar($texto);

        $p = [
            'id_evento' => $id,
            'evento' => $evento,
            'total' => $a['total'],
            'lutas' => []
        ];

        foreach ($a['lutas'] as $l) {
            $l['vermelho_match'] = $this->resolverAtleta(
                $l['vermelho'],
                $l['vermelho_url'] ?? '',
                $l['categoria'] ?? '',
                $evento['id_modalidade'] ?? 1
            );

            $l['azul_match'] = $this->resolverAtleta(
                $l['azul'],
                $l['azul_url'] ?? '',
                $l['categoria'] ?? '',
                $evento['id_modalidade'] ?? 1
            );

            $p['lutas'][] = $l;
        }

        $_SESSION['tapology_preview'] = $p;

        return $this->redirect('/tapology');
    }

    public function importar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->redirect('/tapology');
        }

        $p = $_SESSION['tapology_preview'] ?? null;

        if (!$p) {
            $_SESSION['tapology_erro'] =
                'A prévia expirou. Faça a análise novamente.';
            return $this->redirect('/tapology');
        }

        $ids = $_POST['atleta'] ?? [];

        try {
            $this->db->beginTransaction();

            $ok = 0;
            $skip = 0;
            $criadas = 0;

            foreach ($p['lutas'] as $i => $l) {
                $vr = (int)($ids[$i . '.vermelho'] ?? ($l['vermelho_match']['id'] ?? 0));
                $az = (int)($ids[$i . '.azul'] ?? ($l['azul_match']['id'] ?? 0));

                // Novo atleta: se o Tapology foi encontrado, cria automaticamente.
                if (!$vr && !empty($l['vermelho_match']['tapology_profile']['ok'])) {
                    $vr = $this->criarAtletaTapology(
                        $l['vermelho_match']['tapology_profile'],
                        $l['categoria'] ?? '',
                        $p['evento']['id_modalidade'] ?? 1
                    );
                    if ($vr) {
                        $criadas++;
                    }
                }

                if (!$az && !empty($l['azul_match']['tapology_profile']['ok'])) {
                    $az = $this->criarAtletaTapology(
                        $l['azul_match']['tapology_profile'],
                        $l['categoria'] ?? '',
                        $p['evento']['id_modalidade'] ?? 1
                    );
                    if ($az) {
                        $criadas++;
                    }
                }

                if (!$vr || !$az || $vr === $az) {
                    $skip++;
                    continue;
                }

                // Não duplica a mesma luta no mesmo evento.
                $d = $this->db->prepare(
                    "SELECT id_luta FROM lutas
                     WHERE id_evento=?
                     AND (
                         (id_atleta_azul=? AND id_atleta_vermelho=?)
                         OR
                         (id_atleta_azul=? AND id_atleta_vermelho=?)
                     )
                     LIMIT 1"
                );
                $d->execute([
                    $p['id_evento'],
                    $az,
                    $vr,
                    $vr,
                    $az
                ]);

                if ($d->fetch()) {
                    $skip++;
                    continue;
                }

                $mod = (int)$p['evento']['id_modalidade'];
                $cat = $this->resolverCategoria(
                    $l['categoria'],
                    $mod,
                    $this->sexoAtleta($vr)
                );

                $met = $this->resolverMetodo($l['metodo']);

                $v = null;
                if ($l['vencedor'] === 'V') {
                    $v = $vr;
                } elseif ($l['vencedor'] === 'A') {
                    $v = $az;
                }

                $s = $this->db->prepare(
                    "INSERT INTO lutas
                    (
                        id_evento,
                        ordem_card,
                        id_atleta_azul,
                        id_atleta_vermelho,
                        vencedor_id,
                        id_metodo,
                        round_final,
                        tempo_final,
                        id_modalidade,
                        id_categoria_peso,
                        status,
                        num_rounds,
                        vale_cinturao
                    )
                    VALUES (?,?,?,?,?,?,?,?,?,?, 'realizada',?,0)"
                );

                $s->execute([
                    $p['id_evento'],
                    $l['ordem'],
                    $az,
                    $vr,
                    $v,
                    $met,
                    $l['round'] ?: null,
                    $this->tempoSql($l['tempo']),
                    $mod,
                    $cat,
                    $l['num_rounds'] ?: 3
                ]);

                $ok++;
            }

            $this->db->commit();

            unset($_SESSION['tapology_preview']);

            $_SESSION['tapology_ok'] =
                "Importação concluída: {$ok} luta(s) criada(s), " .
                "{$criadas} atleta(s) novo(s), {$skip} ignorada(s).";

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            $_SESSION['tapology_erro'] =
                'Importação cancelada: ' . $e->getMessage();
        }

        return $this->redirect('/tapology');
    }

    private function buscarEvento($id)
    {
        $s = $this->db->prepare(
            "SELECT * FROM eventos WHERE id_evento=?"
        );
        $s->execute([$id]);

        return $s->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Primeiro tenta o URL exato do Tapology.
     * Depois nome completo.
     * Depois slug do URL já gravado no banco.
     *
     * Se não encontrar, consulta o perfil individual do Tapology.
     */
    private function resolverAtleta($nome, $tapologyUrl = '', $categoria = '', $idModalidade = 1)
    {
        $tapologyUrl = $this->normalizarUrlTapology($tapologyUrl);

        if ($tapologyUrl !== '') {
            $s = $this->db->prepare(
                "SELECT id_atleta,nome,sobrenome,apelido,sexo,tapology
                 FROM atletas
                 WHERE tapology=?
                 LIMIT 1"
            );
            $s->execute([$tapologyUrl]);

            if ($r = $s->fetch(PDO::FETCH_ASSOC)) {
                return [
                    'id' => (int)$r['id_atleta'],
                    'nome' => $this->label($r),
                    'status' => 'encontrado',
                    'opcoes' => [$r],
                    'tapology_url' => $tapologyUrl
                ];
            }
        }

        $alvo = $this->normalizar($nome);

        if ($alvo !== '') {
            $s = $this->db->query(
                "SELECT id_atleta,nome,sobrenome,apelido,sexo,tapology
                 FROM atletas"
            );

            $m = [];

            while ($r = $s->fetch(PDO::FETCH_ASSOC)) {
                $full = $this->normalizar(
                    trim($r['nome'] . ' ' . $r['sobrenome'])
                );

                $slug = $r['tapology']
                    ? $this->slugNome($r['tapology'])
                    : '';

                if (
                    $full === $alvo ||
                    ($slug && $slug === $alvo)
                ) {
                    $m[] = $r;
                }
            }

            if (count($m) === 1) {
                return [
                    'id' => (int)$m[0]['id_atleta'],
                    'nome' => $this->label($m[0]),
                    'status' => 'encontrado',
                    'opcoes' => $m,
                    'tapology_url' => $tapologyUrl
                ];
            }

            if (count($m) > 1) {
                return [
                    'id' => null,
                    'nome' => $nome,
                    'status' => 'ambiguo',
                    'opcoes' => $m,
                    'tapology_url' => $tapologyUrl
                ];
            }
        }

        // Não encontrado no banco: consulta o perfil individual.
        if ($tapologyUrl !== '') {
            $perfil = $this->service->buscarPerfil($tapologyUrl);

            if (!empty($perfil['ok'])) {
                return [
                    'id' => null,
                    'nome' => $perfil['nome_completo'] ?: $nome,
                    'status' => 'novo_tapology',
                    'opcoes' => [],
                    'tapology_url' => $tapologyUrl,
                    'tapology_profile' => $perfil
                ];
            }

            return [
                'id' => null,
                'nome' => $nome,
                'status' => 'novo_erro_tapology',
                'opcoes' => [],
                'tapology_url' => $tapologyUrl,
                'tapology_profile' => $perfil
            ];
        }

        return [
            'id' => null,
            'nome' => $nome,
            'status' => 'novo',
            'opcoes' => [],
            'tapology_url' => ''
        ];
    }

    /**
     * Cria o atleta a partir do perfil Tapology.
     * Antes de inserir, verifica novamente pelo URL para evitar duplicação.
     */
    private function criarAtletaTapology($perfil, $categoria = '', $idModalidade = 1)
    {
        if (empty($perfil['ok']) || empty($perfil['url'])) {
            return 0;
        }

        $url = $this->normalizarUrlTapology($perfil['url']);

        $q = $this->db->prepare(
            "SELECT id_atleta FROM atletas WHERE tapology=? LIMIT 1"
        );
        $q->execute([$url]);

        if ($id = $q->fetchColumn()) {
            return (int)$id;
        }

        $nomeCompleto = trim((string)($perfil['nome_completo'] ?? ''));

        if ($nomeCompleto === '') {
            return 0;
        }

        // Remove eventuais aspas que possam ter vindo do H1.
        $nomeCompleto = trim($nomeCompleto, " \t\n\r\"'");

        $partes = preg_split('/\s+/u', $nomeCompleto);
        $nome = array_shift($partes);
        $sobrenome = trim(implode(' ', $partes));

        $sexo = $perfil['sexo'] ?? 'M';
        if (!in_array($sexo, ['M', 'F'], true)) {
            $sexo = 'M';
        }

        $cat = $this->resolverCategoria(
            $categoria,
            (int)$idModalidade,
            $sexo
        );

        // Se a luta não trouxe categoria, tentamos a categoria pelo peso
        // do perfil somente quando existir.
        if (!$cat && !empty($perfil['peso'])) {
            $cat = $this->resolverCategoria(
                $perfil['peso'] . 'kg',
                (int)$idModalidade,
                $sexo
            );
        }

        $idEquipe = $this->resolverEquipe($perfil['equipe'] ?? '');

        $slug = $this->slugNome($url);

        $ins = $this->db->prepare(
            "INSERT INTO atletas
            (
                nome,
                sobrenome,
                apelido,
                foto,
                sexo,
                nacionalidade,
                peso,
                altura,
                envergadura,
                id_modalidade,
                id_categoria_peso,
                vitorias,
                derrotas,
                empates,
                id_equipe,
                tapology,
                slug,
                status
            )
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?, 'ativo')"
        );

        $ins->execute([
            $nome,
            $sobrenome,
            $perfil['apelido'] ?? '',
            'default.jpg',
            $sexo,
            $perfil['nacionalidade'] ?? '',
            $perfil['peso'] !== null ? $perfil['peso'] : null,
            $perfil['altura'] !== null ? $perfil['altura'] : null,
            $perfil['envergadura'] !== null ? $perfil['envergadura'] : null,
            (int)$idModalidade,
            $cat ?: null,
            $perfil['vitorias'] !== null ? $perfil['vitorias'] : 0,
            $perfil['derrotas'] !== null ? $perfil['derrotas'] : 0,
            $perfil['empates'] !== null ? $perfil['empates'] : 0,
            $idEquipe ?: null,
            $url,
            $slug
        ]);

        return (int)$this->db->lastInsertId();
    }

    private function resolverEquipe($nome)
    {
        $nome = trim((string)$nome);

        if ($nome === '' || preg_match('/^(N\/A|None|N\/D)$/i', $nome)) {
            return null;
        }

        $alvo = $this->normalizar($nome);

        $s = $this->db->query(
            "SELECT id_equipe,nome FROM equipes WHERE status='Ativo'"
        );

        while ($r = $s->fetch(PDO::FETCH_ASSOC)) {
            if ($this->normalizar($r['nome']) === $alvo) {
                return (int)$r['id_equipe'];
            }
        }

        // Não criamos equipe automaticamente. Isso evita poluir a tabela
        // com variações de nomes do Tapology.
        return null;
    }

    public function podeImportar($p)
    {
        foreach ($p['lutas'] as $l) {
            $vrOk =
                ($l['vermelho_match']['id'] ?? 0) > 0 ||
                ($l['vermelho_match']['status'] ?? '') === 'novo_tapology';

            $azOk =
                ($l['azul_match']['id'] ?? 0) > 0 ||
                ($l['azul_match']['status'] ?? '') === 'novo_tapology';

            if (!$vrOk || !$azOk) {
                return false;
            }
        }

        return !empty($p['lutas']);
    }

    public function label($r)
    {
        return trim($r['nome'] . ' ' . $r['sobrenome']) .
            ($r['apelido'] ? ' (' . $r['apelido'] . ')' : '');
    }

    private function slugNome($u)
    {
        $s = basename(parse_url($u, PHP_URL_PATH));
        $s = preg_replace('/^\d+-/', '', $s);

        return $this->normalizar(
            str_replace('-', ' ', $s)
        );
    }

    private function normalizarUrlTapology($url)
    {
        $url = trim((string)$url);

        if ($url === '') {
            return '';
        }

        $url = preg_replace('/[\]\[\(\)>,;]+$/', '', $url);
        $url = preg_replace('/\/$/', '', $url);

        if (!preg_match(
            '~^https?://(?:www\.)?tapology\.com/fightcenter/fighters/~i',
            $url
        )) {
            return '';
        }

        // Padroniza domínio e esquema para facilitar o match no banco.
        $path = parse_url($url, PHP_URL_PATH);

        return 'https://www.tapology.com' . rtrim($path, '/');
    }

    private function normalizar($s)
    {
        $s = mb_strtolower(trim((string)$s), 'UTF-8');
        $s = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);

        return preg_replace(
            '/\s+/',
            ' ',
            preg_replace('/[^a-z0-9 ]/', '', $s)
        );
    }

    private function sexoAtleta($id)
    {
        $s = $this->db->prepare(
            "SELECT sexo FROM atletas WHERE id_atleta=?"
        );
        $s->execute([$id]);

        return $s->fetchColumn() ?: 'M';
    }

    private function resolverCategoria($t, $mod, $sexo)
    {
        if (!$t) {
            return null;
        }

        if (ctype_digit((string)$t)) {
            $s = $this->db->prepare(
                "SELECT id_categoria_peso
                 FROM categoria_peso
                 WHERE id_categoria_peso=?"
            );
            $s->execute([(int)$t]);

            return $s->fetchColumn() ?: null;
        }

        $a = $this->normalizar($t);

        $s = $this->db->prepare(
            "SELECT id_categoria_peso,nome,peso_max,peso_min
             FROM categoria_peso
             WHERE id_modalidade=?
             AND (sexo=? OR sexo IS NULL)"
        );
        $s->execute([$mod, $sexo]);

        $rows = $s->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $r) {
            if ($this->normalizar($r['nome']) === $a) {
                return $r['id_categoria_peso'];
            }
        }

        if (preg_match('/([0-9]+(?:\.[0-9]+)?)\s*kg/i', $t, $m)) {
            foreach ($rows as $r) {
                if (
                    (float)$m[1] <= (float)$r['peso_max'] &&
                    (
                        $r['peso_min'] === null ||
                        (float)$m[1] > (float)$r['peso_min']
                    )
                ) {
                    return $r['id_categoria_peso'];
                }
            }
        }

        return null;
    }

    private function resolverMetodo($s)
    {
        if (!$s) {
            return null;
        }

        $q = $this->db->prepare(
            "SELECT id_metodo
             FROM metodos_vitoria
             WHERE UPPER(sigla)=UPPER(?)
             OR UPPER(nome)=UPPER(?)
             LIMIT 1"
        );
        $q->execute([$s, $s]);

        return $q->fetchColumn() ?: null;
    }

    private function tempoSql($t)
    {
        if (!$t) {
            return null;
        }

        if (substr_count($t, ':') === 1) {
            [$m, $s] = array_map('intval', explode(':', $t));
            return sprintf('00:%02d:%02d', $m, $s);
        }

        if (preg_match('/^(\d{1,2}):(\d{1,2}):(\d{1,2})$/', $t, $m)) {
            return sprintf(
                '%02d:%02d:%02d',
                (int)$m[1],
                (int)$m[2],
                (int)$m[3]
            );
        }

        return null;
    }
}
