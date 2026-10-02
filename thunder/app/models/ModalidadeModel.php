<?php

class ModalidadeModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function listarTodasAtivas()
    {
        // Busca apenas as modalidades que podem receber atletas
        $sql = "SELECT * FROM modalidade ORDER BY nome ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Mantenha o listarTodas() se já existir, ou use este:
    public function listarTodas()
    {
        $sql = "SELECT * FROM modalidade ORDER BY nome ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarPaginado($limite, $offset, $nome = '')
    {
        $sql = "SELECT * FROM modalidade WHERE nome LIKE :nome ORDER BY nome ASC LIMIT :limite OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':nome', '%' . $nome . '%', PDO::PARAM_STR);
        $stmt->bindValue(':limite', (int) $limite, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contarTotal($nome = '')
    {
        $sql = "SELECT COUNT(*) as total FROM modalidade WHERE nome LIKE ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['%' . $nome . '%']);
        return (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function buscarPorId($id)
    {
        $sql = "SELECT * FROM modalidade WHERE id_modalidade = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function salvar($dados)
    {
        $sql = "INSERT INTO modalidade (nome, status) VALUES (?, ?)";
        return $this->db->prepare($sql)->execute([
            $dados['nome'],
            $dados['status'] ?? 'ativo'
        ]);
    }

    public function atualizar($id, $dados)
    {
        $sql = "UPDATE modalidade SET nome = ?, status = ? WHERE id_modalidade = ?";
        return $this->db->prepare($sql)->execute([
            $dados['nome'],
            $dados['status'],
            $id
        ]);
    }

    public function excluir($id)
    {
        try {
            $sql = "DELETE FROM modalidade WHERE id_modalidade = ?";
            return $this->db->prepare($sql)->execute([$id]);
        } catch (PDOException $e) {
            return false; // Retorna falso se houver vínculo com categorias
        }
    }
}