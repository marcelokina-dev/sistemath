<?php

class CategoriaPesoModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * LISTAR PAGINADO COM FILTROS (Ajustado para incluir Sexo se necessário)
     */
    public function listarPaginado($limite = 15, $offset = 0, $filtros = [])
    {
        $condicoes = ["1=1"];
        $params = [];

        if (!empty($filtros['nome'])) {
            $condicoes[] = "cp.nome LIKE :nome";
            $params[':nome'] = '%' . $filtros['nome'] . '%';
        }

        if (!empty($filtros['modalidade'])) {
            $condicoes[] = "cp.id_modalidade = :modalidade";
            $params[':modalidade'] = $filtros['modalidade'];
        }

        // NOVO: Filtro por sexo na listagem geral
        if (!empty($filtros['sexo'])) {
            $condicoes[] = "cp.sexo = :sexo";
            $params[':sexo'] = $filtros['sexo'];
        }

        $sql = "SELECT cp.*, m.nome as modalidade_nome 
                FROM categoria_peso cp
                LEFT JOIN modalidade m ON cp.id_modalidade = m.id_modalidade
                WHERE " . implode(" AND ", $condicoes) . "
                ORDER BY m.nome ASC, cp.sexo ASC, cp.peso_max ASC
                LIMIT :limite OFFSET :offset";

        $stmt = $this->db->prepare($sql);

        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }

        $stmt->bindValue(':limite', (int) $limite, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * BUSCAR POR MODALIDADE E SEXO (O mais importante para o seu formulário)
     * Este método garante que o atleta só veja categorias que condizem com o gênero dele
     */
    public function buscarPorModalidadeESexo($id_mod, $sexo)
    {
        // A query agora busca o sexo selecionado OU categorias marcadas como Unissex
        $sql = "SELECT id_categoria_peso, nome, peso_max 
            FROM categoria_peso 
            WHERE id_modalidade = ? 
            AND (sexo = ? OR sexo = 'Unissex') 
            ORDER BY peso_max ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_mod, $sexo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    /**
     * SALVAR (Ajustado para incluir a coluna 'sexo')
     */
    public function salvar($dados)
    {
        $sql = "INSERT INTO categoria_peso (nome, peso_min, peso_max, id_modalidade, sexo) 
                VALUES (:nome, :peso_min, :peso_max, :id_modalidade, :sexo)";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':nome' => $dados['nome'],
            ':peso_min' => !empty($dados['peso_min']) ? (float) $dados['peso_min'] : 0,
            ':peso_max' => (float) $dados['peso_max'],
            ':id_modalidade' => (int) $dados['id_modalidade'],
            ':sexo' => $dados['sexo'] // M ou F
        ]);
    }

    /**
     * ATUALIZAR (Ajustado para incluir 'sexo')
     */
    public function atualizar($id, $dados)
    {
        $sql = "UPDATE categoria_peso SET 
                    nome = ?, 
                    peso_min = ?, 
                    peso_max = ?, 
                    id_modalidade = ?,
                    sexo = ?
                WHERE id_categoria_peso = ?";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $dados['nome'],
            !empty($dados['peso_min']) ? (float) $dados['peso_min'] : 0,
            (float) $dados['peso_max'],
            (int) $dados['id_modalidade'],
            $dados['sexo'],
            $id
        ]);
    }

    // --- MÉTODOS DE COMPATIBILIDADE ---

    public function listarTodas()
    {
        $sql = "SELECT cp.*, m.nome as modalidade_nome 
                FROM categoria_peso cp
                LEFT JOIN modalidade m ON cp.id_modalidade = m.id_modalidade
                ORDER BY m.nome ASC, cp.peso_max ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarPorModalidade($id_modalidade, $sexo = null)
    {
        if ($sexo) {
            return $this->buscarPorModalidadeESexo($id_modalidade, $sexo);
        }

        $sql = "SELECT * FROM categoria_peso WHERE id_modalidade = ? ORDER BY peso_max ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_modalidade]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM categoria_peso WHERE id_categoria_peso = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function excluir($id)
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM categoria_peso WHERE id_categoria_peso = ?");
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            error_log("Erro ao excluir categoria: " . $e->getMessage());
            return false;
        }
    }

    /**
     * CONTAGEM PARA PAGINAÇÃO (Adicione este método que faltava no seu Model)
     */
    /**
     * CONTAGEM PARA PAGINAÇÃO
     */
    public function contarTotal($filtros = [])
    {
        $condicoes = ["1=1"];
        $params = [];

        if (!empty($filtros['nome'])) {
            $condicoes[] = "nome LIKE :nome";
            $params[':nome'] = '%' . $filtros['nome'] . '%';
        }

        if (!empty($filtros['modalidade'])) {
            $condicoes[] = "id_modalidade = :modalidade";
            $params[':modalidade'] = $filtros['modalidade'];
        }

        // ADICIONADO: Filtro de sexo para a contagem ficar correta
        if (!empty($filtros['sexo'])) {
            $condicoes[] = "sexo = :sexo";
            $params[':sexo'] = $filtros['sexo'];
        }

        $sql = "SELECT COUNT(*) as total FROM categoria_peso WHERE " . implode(" AND ", $condicoes);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($row['total'] ?? 0);
    }
}