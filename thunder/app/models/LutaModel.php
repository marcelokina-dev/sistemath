<?php

require_once __DIR__ . '/../../core/Database.php';

class LutaModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Lista todas as lutas de um evento com nomes completos, apelidos, categorias e modalidades
     */
    public function listarPorEvento($id_evento)
    {
        $sql = "SELECT l.*, 
                       -- Dados Atleta Azul
                       a1.nome as atleta_azul_nome, 
                       a1.sobrenome as atleta_azul_sobrenome, 
                       a1.apelido as atleta_azul_apelido, 
                       a1.foto as atleta_azul_foto,
                       -- Dados Atleta Vermelho
                       a2.nome as atleta_vermelho_nome, 
                       a2.sobrenome as atleta_vermelho_sobrenome, 
                       a2.apelido as atleta_vermelho_apelido, 
                       a2.foto as atleta_vermelho_foto,
                       -- Outros Joins
                       m.nome as modalidade_nome,
                       mv.nome as metodo_nome,
                       arb.nome as arbitro_nome,
                       cp.nome as categoria_nome,
                       cp.peso_max
                FROM lutas l
                LEFT JOIN atletas a1 ON l.id_atleta_azul = a1.id_atleta
                LEFT JOIN atletas a2 ON l.id_atleta_vermelho = a2.id_atleta
                LEFT JOIN modalidade m ON l.id_modalidade = m.id_modalidade
                LEFT JOIN metodos_vitoria mv ON l.id_metodo = mv.id_metodo
                LEFT JOIN arbitros arb ON l.id_arbitro = arb.id_arbitro
                LEFT JOIN categoria_peso cp ON l.id_categoria_peso = cp.id_categoria_peso
                WHERE l.id_evento = :id_evento
                ORDER BY l.ordem_card DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_evento', $id_evento);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca dados completos para a view lancar_resultado.php
     */
    public function buscarPorIdCompleto($id_luta)
    {
        $sql = "SELECT l.*, 
                       l.vencedor_id as id_vencedor, 
                       l.id_metodo as metodo,
                       l.round_final,
                       l.tempo_final,
                       -- Atletas
                       a1.nome as atleta_azul_nome, 
                       a1.sobrenome as atleta_azul_sobrenome,
                       a1.apelido as atleta_azul_apelido,
                       a2.nome as atleta_vermelho_nome,
                       a2.sobrenome as atleta_vermelho_sobrenome,
                       a2.apelido as atleta_vermelho_apelido,
                       c.nome as categoria_nome,
                       m.nome as modalidade_nome
                FROM lutas l
                LEFT JOIN atletas a1 ON l.id_atleta_azul = a1.id_atleta
                LEFT JOIN atletas a2 ON l.id_atleta_vermelho = a2.id_atleta
                LEFT JOIN categoria_peso c ON l.id_categoria_peso = c.id_categoria_peso
                LEFT JOIN modalidade m ON l.id_modalidade = m.id_modalidade
                WHERE l.id_luta = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_luta]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function buscarPorId($id)
    {
        $sql = "SELECT * FROM lutas WHERE id_luta = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Registra o resultado e atualiza automaticamente o Campeão e Histórico se valer cinturão
     */
    public function registrarResultado($dados)
    {
        try {
            $this->db->beginTransaction();

            $id_luta = $dados['id_luta'];
            $id_venc_raw = $dados['id_vencedor'] ?? null;
            $vencedor_id = ($id_venc_raw === 'empate' || empty($id_venc_raw)) ? null : $id_venc_raw;
            $vale_cinturao = isset($dados['vale_cinturao']) ? 1 : 0;

            // 1. Atualiza os dados da luta (Agora incluindo a Ordem do Card)
            $sql = "UPDATE lutas SET 
        vencedor_id = :v_id, id_metodo = :met, id_arbitro = :arb,
        round_final = :rd, tempo_final = :tm, vale_cinturao = :vc, 
        ordem_card = :ordem, status = 'realizada' 
        WHERE id_luta = :id_luta";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':v_id' => $vencedor_id,
                ':met' => $dados['id_metodo'],
                ':arb' => $dados['id_arbitro'],
                ':rd' => $dados['round_final'],
                ':tm' => $dados['tempo_final'],
                ':vc' => $vale_cinturao,
                ':ordem' => $dados['ordem_card'] ?? 0, // Pega o valor do formulário
                ':id_luta' => $id_luta
            ]);

            // 2. Se houver um vencedor e a luta valia cinturão, processamos a troca
            if ($vencedor_id && $vale_cinturao) {
                // Buscamos os detalhes da luta para saber Categoria e Modalidade
                $luta = $this->buscarPorIdCompleto($id_luta);
                $id_mod = $luta['id_modalidade'];
                $id_cat = $luta['id_categoria_peso'];
                $id_evt = $luta['id_evento'];

                // A. Remove o cinturão de qualquer outro atleta nesta categoria/modalidade
                $this->db->prepare("UPDATE ranking SET campeao = 0 WHERE id_modalidade = ? AND id_categoria_peso = ?")
                    ->execute([$id_mod, $id_cat]);

                // B. Fecha o histórico do campeão anterior (se existir)
                $this->db->prepare("UPDATE historico_campeoes SET data_fim = CURDATE(), status = 'vago' 
                                    WHERE id_modalidade = ? AND id_categoria_peso = ? AND status = 'campeao'")
                    ->execute([$id_mod, $id_cat]);

                // C. Define o novo campeão na tabela Ranking
                $this->db->prepare("UPDATE ranking SET campeao = 1 WHERE id_atleta = ? AND id_modalidade = ? AND id_categoria_peso = ?")
                    ->execute([$vencedor_id, $id_mod, $id_cat]);

                // D. Insere o novo registro no Histórico de Campeões
                $sqlHist = "INSERT INTO historico_campeoes (id_modalidade, id_categoria_peso, id_atleta, id_evento, id_luta, data_inicio, status) 
                            VALUES (?, ?, ?, ?, ?, CURDATE(), 'campeao')";
                $this->db->prepare($sqlHist)->execute([$id_mod, $id_cat, $vencedor_id, $id_evt, $id_luta]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Erro ao registrar resultado e cinturão: " . $e->getMessage());
            return false;
        }
    }

    public function salvar($dados)
    {
        try {
            $sql = "INSERT INTO lutas (id_evento, id_atleta_azul, id_atleta_vermelho, id_modalidade, 
                                     id_categoria_peso, id_arbitro, num_rounds, duracao_round, ordem_card, vale_cinturao) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $this->db->prepare($sql);
            $duracao = $this->formatarDuracao($dados['duracao_round'] ?? '05:00');

            return $stmt->execute([
                $dados['id_evento'],
                $dados['id_atleta_azul'],
                $dados['id_atleta_vermelho'],
                $dados['id_modalidade'],
                $dados['id_categoria_peso'],
                $dados['id_arbitro'] ?: null,
                $dados['num_rounds'] ?: 3,
                $duracao,
                $dados['ordem_card'] ?: 0,
                (!empty($dados['vale_cinturao']) ? 1 : 0)
            ]);
        } catch (PDOException $e) {
            error_log("Erro ao salvar luta: " . $e->getMessage());
            return false;
        }
    }

    public function atualizar($id, $dados)
    {
        try {
            $sql = "UPDATE lutas SET 
                    id_atleta_azul = ?, 
                    id_atleta_vermelho = ?, 
                    id_modalidade = ?, 
                    id_categoria_peso = ?, 
                    id_arbitro = ?, 
                    num_rounds = ?, 
                    duracao_round = ?, 
                    ordem_card = ?, 
                    vale_cinturao = ?
                    WHERE id_luta = ?";

            $stmt = $this->db->prepare($sql);
            $duracao = $this->formatarDuracao($dados['duracao_round'] ?? '05:00');

            return $stmt->execute([
                $dados['id_atleta_azul'],
                $dados['id_atleta_vermelho'],
                $dados['id_modalidade'],
                $dados['id_categoria_peso'],
                $dados['id_arbitro'] ?: null,
                $dados['num_rounds'] ?: 3,
                $duracao,
                $dados['ordem_card'] ?: 0,
                (!empty($dados['vale_cinturao']) ? 1 : 0),
                $id
            ]);
        } catch (PDOException $e) {
            error_log("Erro ao atualizar luta: " . $e->getMessage());
            return false;
        }
    }

    public function excluir($id)
    {
        try {
            $sql = "DELETE FROM lutas WHERE id_luta = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Erro ao excluir luta: " . $e->getMessage());
            return false;
        }
    }

    private function formatarDuracao($tempo)
    {
        if (empty($tempo) || $tempo == '00:00')
            return '00:00:00';

        $partes = explode(':', $tempo);
        $count = count($partes);

        if ($count == 3)
            return $tempo; // HH:MM:SS
        if ($count == 2)
            return "00:" . $partes[0] . ":" . $partes[1]; // MM:SS

        return "00:00:" . str_pad($tempo, 2, "0", STR_PAD_LEFT); // SS
    }
}