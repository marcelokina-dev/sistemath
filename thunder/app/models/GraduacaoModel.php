<?php

class GraduacaoModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }


    /**
     * Retorna todas as graduações sem paginação. 
     * Usado para preencher o select no formulário de Atletas.
     */
    public function listar()
    {
        $sql = "SELECT g.*, m.nome as modalidade_nome 
            FROM graduacao g
            LEFT JOIN modalidade m ON g.id_modalidade = m.id_modalidade
            ORDER BY m.nome ASC, g.nome ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    

    /**
     * Lista as graduações com paginação e filtro por modalidade
     */
    public function listarPaginado($pagina, $itensPorPagina, $id_modalidade = null)
    {
        // Garante que os valores sejam inteiros para o SQL
        $pagina = (int) $pagina;
        $itensPorPagina = (int) $itensPorPagina;
        $offset = ($pagina - 1) * $itensPorPagina;
        $params = [];

        $sql = "SELECT g.id_graduacao, g.nome as graduacao_nome, m.nome as modalidade_nome 
                FROM graduacao g 
                LEFT JOIN modalidade m ON g.id_modalidade = m.id_modalidade";

        if ($id_modalidade) {
            $sql .= " WHERE g.id_modalidade = ?";
            $params[] = $id_modalidade;
        }

        $sql .= " ORDER BY m.nome ASC, g.nome ASC LIMIT $itensPorPagina OFFSET $offset";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Conta o total de registros para a paginação
     */
    public function contarTotal($id_modalidade = null)
    {
        $sql = "SELECT COUNT(*) FROM graduacao";
        $params = [];

        if ($id_modalidade) {
            $sql .= " WHERE id_modalidade = ?";
            $params[] = $id_modalidade;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    /**
     * Busca uma graduação específica por ID
     */
    public function buscarPorId($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM graduacao WHERE id_graduacao = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Insere uma nova graduação
     */
    public function salvar($dados)
    {
        $sql = "INSERT INTO graduacao (nome, id_modalidade) VALUES (?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $dados['nome'],
            $dados['id_modalidade']
        ]);
    }

    /**
     * Atualiza uma graduação existente
     */
    public function atualizar($id, $dados)
    {
        $sql = "UPDATE graduacao SET nome = ?, id_modalidade = ? WHERE id_graduacao = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $dados['nome'],
            $dados['id_modalidade'],
            $id
        ]);
    }

    /**
     * Exclui uma graduação
     */
    public function excluir($id)
    {
        $stmt = $this->db->prepare("DELETE FROM graduacao WHERE id_graduacao = ?");
        return $stmt->execute([$id]);
    }
}