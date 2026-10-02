<?php

class RankingModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function processarRankingGlobal()
    {
        try {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $this->db->beginTransaction();

            // 1. Mapeia atletas que o administrador ocultou manualmente
            $ocultos = $this->db->query("SELECT id_atleta, id_categoria_peso FROM ranking WHERE visivel_ranking = 0")
                ->fetchAll(PDO::FETCH_ASSOC);
            $mapaOcultos = [];
            foreach ($ocultos as $oc) {
                $mapaOcultos[$oc['id_atleta'] . "_" . $oc['id_categoria_peso']] = true;
            }

            // 2. Limpa o ranking para reconstrução
            $this->db->query("DELETE FROM ranking");

            // 3. Regras de pontuação
            $regras = $this->db->query("SELECT id_metodo, resultado, pontos FROM regras_pontuacao")->fetchAll(PDO::FETCH_ASSOC);
            $pontosMap = [];
            foreach ($regras as $r) {
                $chave = $r['id_metodo'] . "_" . trim(strtolower($r['resultado']));
                $pontosMap[$chave] = (int) $r['pontos'];
            }

            // --- NOVIDADE: 3.1 Garantir que Campeões existam no array de Stats ---
            $stats = [];
            $campeoesAtuais = $this->db->query("SELECT id_atleta, id_modalidade, id_categoria_peso FROM historico_campeoes WHERE status = 'campeao'")
                ->fetchAll(PDO::FETCH_ASSOC);

            foreach ($campeoesAtuais as $c) {
                $chave = $c['id_atleta'] . "_" . $c['id_categoria_peso'] . "_" . $c['id_modalidade'];
                $stats[$chave] = [
                    'id_atleta' => (int) $c['id_atleta'],
                    'id_mod' => (int) $c['id_modalidade'],
                    'id_cat' => (int) $c['id_categoria_peso'],
                    'v' => 0,
                    'd' => 0,
                    'e' => 0,
                    'pts' => 0,
                    'data' => null // Será preenchido se houver lutas
                ];
            }

            // 4. Busca lutas realizadas
            $sql = "SELECT l.*, e.data_evento 
                FROM lutas l
                INNER JOIN eventos e ON l.id_evento = e.id_evento
                WHERE l.vencedor_id IS NOT NULL AND l.status = 'realizada'";

            $lutas = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

            foreach ($lutas as $luta) {
                $idCat = (int) $luta['id_categoria_peso'];
                $idMod = (int) $luta['id_modalidade'];
                $idMetodo = (int) $luta['id_metodo'];
                $idVencedor = (int) $luta['vencedor_id'];

                $atletas = [
                    'vermelho' => (int) $luta['id_atleta_vermelho'],
                    'azul' => (int) $luta['id_atleta_azul']
                ];

                foreach ($atletas as $cor => $idA) {
                    if ($idA <= 0)
                        continue;

                    $chaveAtleta = $idA . "_" . $idCat . "_" . $idMod;

                    if (!isset($stats[$chaveAtleta])) {
                        $stats[$chaveAtleta] = [
                            'id_atleta' => $idA,
                            'id_mod' => $idMod,
                            'id_cat' => $idCat,
                            'v' => 0,
                            'd' => 0,
                            'e' => 0,
                            'pts' => 0,
                            'data' => $luta['data_evento']
                        ];
                    }

                    // Atualiza data da última luta
                    if (!$stats[$chaveAtleta]['data'] || strtotime($luta['data_evento']) > strtotime($stats[$chaveAtleta]['data'])) {
                        $stats[$chaveAtleta]['data'] = $luta['data_evento'];
                    }

                    // Lógica de Pontuação e Stats
                    if ($idVencedor === $idA) {
                        $stats[$chaveAtleta]['v']++;
                        $stats[$chaveAtleta]['pts'] += $pontosMap[$idMetodo . "_vitoria"] ?? 0;
                    } elseif ($idVencedor === 0) { // EMPATE (Considerando que 0 ou NULL no vencedor_id com status realizada seja empate)
                        $stats[$chaveAtleta]['e']++;
                        $stats[$chaveAtleta]['pts'] += $pontosMap[$idMetodo . "_empate"] ?? 0;
                    } else {
                        $stats[$chaveAtleta]['d']++;
                        $stats[$chaveAtleta]['pts'] += $pontosMap[$idMetodo . "_derrota"] ?? 0;
                    }
                }
            }

            // 5. Inserção no Banco
            $stmt = $this->db->prepare("INSERT INTO ranking (id_atleta, id_modalidade, id_categoria_peso, pontos, vitorias, derrotas, empates, ultima_luta, visivel_ranking, campeao) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0)");

            foreach ($stats as $s) {
                $visivel = isset($mapaOcultos[$s['id_atleta'] . "_" . $s['id_cat']]) ? 0 : 1;
                $stmt->execute([
                    $s['id_atleta'],
                    $s['id_mod'],
                    $s['id_cat'],
                    $s['pts'],
                    $s['v'],
                    $s['d'],
                    $s['e'],
                    $s['data'],
                    $visivel
                ]);
            }

            // 6. Sincronizar Coroa do Campeão
            $sqlSyncCampeao = "UPDATE ranking r
                           INNER JOIN historico_campeoes h ON 
                                r.id_atleta = h.id_atleta AND 
                                r.id_modalidade = h.id_modalidade AND 
                                r.id_categoria_peso = h.id_categoria_peso
                           SET r.campeao = 1
                           WHERE h.status = 'campeao'";

            $this->db->query($sqlSyncCampeao);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction())
                $this->db->rollBack();
            error_log($e->getMessage());
            return false;
        }
    }
    public function obterRanking($id_modalidade, $id_categoria, $sexo = 'M', $pagina = 1)
    {
        $itensPorPagina = 15;
        $offset = (max(1, (int) $pagina) - 1) * $itensPorPagina;

        $sql = "SELECT r.*, a.nome as atleta_nome, a.foto, cp.nome as categoria_nome 
            FROM ranking r
            JOIN atletas a ON r.id_atleta = a.id_atleta
            JOIN categoria_peso cp ON r.id_categoria_peso = cp.id_categoria_peso
            WHERE r.id_modalidade = :mod 
              AND r.id_categoria_peso = :cat 
              AND a.sexo = :sexo 
              AND r.visivel_ranking = 1
            ORDER BY r.campeao DESC, r.pontos DESC, r.vitorias DESC, r.ultima_luta DESC
            LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':mod', (int) $id_modalidade, PDO::PARAM_INT);
        $stmt->bindValue(':cat', (int) $id_categoria, PDO::PARAM_INT);
        $stmt->bindValue(':sexo', $sexo, PDO::PARAM_STR);
        $stmt->bindValue(':limit', (int) $itensPorPagina, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contarTotalRanking($id_modalidade, $id_categoria, $sexo)
    {
        $sql = "SELECT COUNT(*) as total 
            FROM ranking r
            JOIN atletas a ON r.id_atleta = a.id_atleta
            WHERE r.id_modalidade = ? 
              AND r.id_categoria_peso = ? 
              AND a.sexo = ? 
              AND r.visivel_ranking = 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([(int) $id_modalidade, (int) $id_categoria, $sexo]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    }

    public function removerAtleta($id_atleta, $id_categoria, $id_modalidade)
    {
        $sql = "UPDATE ranking SET visivel_ranking = 0 
            WHERE id_atleta = ? AND id_categoria_peso = ? AND id_modalidade = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([(int) $id_atleta, (int) $id_categoria, (int) $id_modalidade]);
    }
}