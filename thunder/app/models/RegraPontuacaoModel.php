<?php

class RegraPontuacaoModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getModalidades()
    {
        // Tabela: modalidade
        return $this->db->query("SELECT id_modalidade, nome FROM modalidade ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMetodos()
    {
        return $this->db->query("SELECT id_metodo, nome, sigla FROM metodos_vitoria ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contarTotal($busca = '')
    {
        $sql = "SELECT COUNT(*) as total FROM regras_pontuacao r 
                JOIN modalidade m ON r.id_modalidade = m.id_modalidade 
                WHERE m.nome LIKE ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['%' . $busca . '%']);
        return (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function listarPaginado($limite, $offset, $busca = '')
    {
        $sql = "SELECT r.*, m.nome as modalidade_nome, mv.nome as metodo_nome, mv.sigla 
                FROM regras_pontuacao r
                JOIN modalidade m ON r.id_modalidade = m.id_modalidade
                JOIN metodos_vitoria mv ON r.id_metodo = mv.id_metodo
                WHERE m.nome LIKE :busca 
                ORDER BY m.nome ASC, r.pontos DESC 
                LIMIT :limite OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':busca', '%' . $busca . '%', PDO::PARAM_STR);
        $stmt->bindValue(':limite', (int) $limite, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM regras_pontuacao WHERE id_regra = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function salvar($dados)
    {
        $sql = "INSERT INTO regras_pontuacao (id_modalidade, id_metodo, resultado, pontos) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $dados['id_modalidade'] ?? null,
            $dados['id_metodo'] ?? null,
            $dados['resultado'] ?? null,
            $dados['pontos'] ?? 0
        ]);
    }

public function atualizar($id, $dados) {
    $sql = "UPDATE regras_pontuacao SET 
            id_modalidade = :id_modalidade, 
            resultado = :resultado, 
            id_metodo = :id_metodo, 
            pontos = :pontos 
            WHERE id_regra = :id";
            
    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':id_modalidade', $dados['id_modalidade']);
    $stmt->bindValue(':resultado', $dados['resultado']);
    $stmt->bindValue(':id_metodo', $dados['id_metodo']);
    $stmt->bindValue(':pontos', $dados['pontos']);
    $stmt->bindValue(':id', $id);
    
    return $stmt->execute();
}

    public function excluir($id)
    {
        $stmt = $this->db->prepare("DELETE FROM regras_pontuacao WHERE id_regra = ?");
        return $stmt->execute([$id]);
    }


    public function getMetodosVitoria()
    {
        $sql = "SELECT id_metodo, nome, sigla FROM metodos_vitoria ORDER BY id_metodo ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Processa a pontuação de uma luta específica e atualiza o ranking
     */
    public function processarPontuacaoLuta($id_luta)
    {
        try {
            // 1. Busca os detalhes da luta
            $sql = "SELECT vencedor_id, id_atleta_azul, id_atleta_vermelho, id_modalidade, id_categoria_peso, id_metodo 
                FROM lutas WHERE id_luta = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id_luta]);
            $luta = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$luta || !$luta['vencedor_id'])
                return false;

            // 2. Busca a regra de pontuação para o método de vitória (Ex: KO = 5 pts, Decisão = 3 pts)
            $sqlRegra = "SELECT pontos_vitoria, pontos_derrota FROM regras_pontuacao 
                     WHERE id_modalidade = ? AND id_metodo = ? LIMIT 1";
            $stmtRegra = $this->db->prepare($sqlRegra);
            $stmtRegra->execute([$luta['id_modalidade'], $luta['id_metodo']]);
            $regra = $stmtRegra->fetch(PDO::FETCH_ASSOC);

            // Valores padrão caso não haja regra específica cadastrada
            $ptsVence = $regra['pontos_vitoria'] ?? 3;
            $ptsPerde = $regra['pontos_derrota'] ?? 0;

            // 3. Atualiza o Ranking do Vencedor
            $this->atualizarPontosAtleta($luta['vencedor_id'], $luta['id_modalidade'], $luta['id_categoria_peso'], $ptsVence);

            // 4. Atualiza o Ranking do Perdedor
            $perdedor_id = ($luta['vencedor_id'] == $luta['id_atleta_azul']) ? $luta['id_atleta_vermelho'] : $luta['id_atleta_azul'];
            $this->atualizarPontosAtleta($perdedor_id, $luta['id_modalidade'], $luta['id_categoria_peso'], $ptsPerde);

            return true;
        } catch (PDOException $e) {
            error_log("Erro ao processar pontuação: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Auxiliar para somar pontos na tabela de ranking
     */
    private function atualizarPontosAtleta($id_atleta, $id_modalidade, $id_categoria, $pontos)
    {
        // Verifica se o atleta já tem registro nesse ranking, se não, cria
        $sql = "INSERT INTO ranking (id_atleta, id_modalidade, id_categoria_peso, pontos) 
            VALUES (:a, :m, :c, :p) 
            ON DUPLICATE KEY UPDATE pontos = pontos + :p";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':a' => $id_atleta,
            ':m' => $id_modalidade,
            ':c' => $id_categoria,
            ':p' => $pontos
        ]);
    }
}