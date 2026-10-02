<?php

class EquipeModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Busca apenas ID e Nome de todas as equipes ativas para dropdowns.
     */
    public function listarTodasParaSelect()
    {
        try {
            $sql = "SELECT id_equipe, nome FROM equipes WHERE status = 'Ativo' ORDER BY nome ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erro ao listar equipes para select: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca os detalhes completos de uma equipe para edição (JOIN com Endereço e Contato)
     */
public function find($id)
{
    $sql = "SELECT 
                e.*, 
                IFNULL(en.logradouro, '') as logradouro, 
                IFNULL(en.bairro, '') as bairro, 
                IFNULL(en.cidade, '') as cidade, 
                IFNULL(en.estado, '') as estado, 
                IFNULL(en.pais, 'Brasil') as pais, 
                IFNULL(en.cep, '') as cep, 
                IFNULL(en.numero, '') as numero, 
                IFNULL(en.complemento, '') as complemento,
                IFNULL(c.email, '') as email, 
                IFNULL(c.instagram, '') as instagram, 
                IFNULL(c.facebook, '') as facebook, 
                IFNULL(c.whatsapp, '') as whatsapp
            FROM equipes e
            LEFT JOIN endereco en ON e.id_endereco = en.id_endereco
            LEFT JOIN contato c ON e.id_contato = c.id_contato
            WHERE e.id_equipe = :id";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

    /**
     * Salva nova equipe criando registros nas tabelas vinculadas
     */
    public function store($data, $foto)
    {
        try {
            $this->db->beginTransaction();

            // 1. Inserir Endereço
            $sqlEnd = "INSERT INTO endereco (logradouro, bairro, cidade, estado, pais, cep, numero, complemento) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $this->db->prepare($sqlEnd)->execute([
                $data['logradouro'] ?? null,
                $data['bairro'] ?? null,
                $data['cidade'] ?? null,
                $data['estado'] ?? null,
                $data['pais'] ?? 'Brasil',
                $data['cep'] ?? null,
                $data['numero'] ?? null,
                $data['complemento'] ?? null
            ]);
            $idEndereco = $this->db->lastInsertId();

            // 2. Inserir Contato
            $sqlCont = "INSERT INTO contato (email, instagram, facebook, whatsapp, celular) 
                        VALUES (?, ?, ?, ?, ?)";
            $this->db->prepare($sqlCont)->execute([
                $data['email'] ?? null,
                str_replace('@', '', $data['instagram'] ?? ''),
                $data['facebook'] ?? null,
                $data['whatsapp'] ?? null,
                $data['whatsapp'] ?? null // Celular recebe o mesmo que whatsapp por padrão
            ]);
            $idContato = $this->db->lastInsertId();

            // 3. Inserir Equipe
            $sqlEquipe = "INSERT INTO equipes (nome, foto, id_endereco, id_contato, responsavel, status) 
                          VALUES (?, ?, ?, ?, ?, ?)";
            $this->db->prepare($sqlEquipe)->execute([
                $data['nome'] ?? 'Nova Equipe',
                $foto,
                $idEndereco,
                $idContato,
                $data['responsavel'] ?? null,
                $data['status'] ?? 'Ativo'
            ]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log("Erro Store Equipe: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza os dados da equipe e corrige vínculos NULL
     */
    public function update($id, $data, $foto)
    {
        try {
            $this->db->beginTransaction();

            $equipe = $this->find($id);
            if (!$equipe) return false;

            // Mantém a foto atual se nenhuma nova for enviada
            if ($foto === 'sem-foto png' || empty($foto)) {
                $foto = $equipe['foto'] ?? 'sem-foto.png';
            }

            // Garante que o ID de Endereço exista (Cria se for NULL no banco)
            $idEndereco = $equipe['id_endereco'];
            if (empty($idEndereco)) {
                $this->db->prepare("INSERT INTO endereco (logradouro) VALUES ('')")->execute();
                $idEndereco = $this->db->lastInsertId();
                $this->db->prepare("UPDATE equipes SET id_endereco = ? WHERE id_equipe = ?")->execute([$idEndereco, $id]);
            }

            // Garante que o ID de Contato exista (Cria se for NULL no banco)
            $idContato = $equipe['id_contato'];
            if (empty($idContato)) {
                $this->db->prepare("INSERT INTO contato (email) VALUES ('')")->execute();
                $idContato = $this->db->lastInsertId();
                $this->db->prepare("UPDATE equipes SET id_contato = ? WHERE id_equipe = ?")->execute([$idContato, $id]);
            }

            // 1. Atualiza Endereço
            $sqlEnd = "UPDATE endereco SET logradouro = ?, bairro = ?, cidade = ?, estado = ?, pais = ?, cep = ?, numero = ?, complemento = ? 
                       WHERE id_endereco = ?";
            $this->db->prepare($sqlEnd)->execute([
                $data['logradouro'] ?? null,
                $data['bairro'] ?? null,
                $data['cidade'] ?? null,
                $data['estado'] ?? null,
                $data['pais'] ?? 'Brasil',
                $data['cep'] ?? null,
                $data['numero'] ?? null,
                $data['complemento'] ?? null,
                $idEndereco
            ]);

            // 2. Atualiza Contato
            $sqlCont = "UPDATE contato SET email = ?, instagram = ?, facebook = ?, whatsapp = ?, celular = ? 
                        WHERE id_contato = ?";
            $this->db->prepare($sqlCont)->execute([
                $data['email'] ?? null,
                str_replace('@', '', $data['instagram'] ?? ''),
                $data['facebook'] ?? null,
                $data['whatsapp'] ?? null,
                $data['whatsapp'] ?? null,
                $idContato
            ]);

            // 3. Atualiza Equipe
            $sqlEquipe = "UPDATE equipes SET nome = ?, foto = ?, responsavel = ?, status = ? WHERE id_equipe = ?";
            $this->db->prepare($sqlEquipe)->execute([
                $data['nome'] ?? $equipe['nome'],
                $foto,
                $data['responsavel'] ?? null,
                $data['status'] ?? 'Ativo',
                $id
            ]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log("Erro Update Equipe: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Listagem paginada para a tela principal
     */
    public function getEquipes($filtros = [], $limite = 15, $offset = 0)
    {
        $sql = "SELECT e.id_equipe, e.nome, e.foto, e.responsavel, e.status,
                       en.cidade, en.estado, c.whatsapp, c.email 
                FROM equipes e
                LEFT JOIN endereco en ON e.id_endereco = en.id_endereco
                LEFT JOIN contato c ON e.id_contato = c.id_contato
                WHERE 1=1";

        if (!empty($filtros['nome'])) $sql .= " AND e.nome LIKE :nome";
        if (!empty($filtros['cidade'])) $sql .= " AND en.cidade LIKE :cidade";
        if (!empty($filtros['estado'])) $sql .= " AND en.estado = :estado";

        $sql .= " ORDER BY e.nome ASC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        if (!empty($filtros['nome'])) $stmt->bindValue(':nome', '%' . $filtros['nome'] . '%');
        if (!empty($filtros['cidade'])) $stmt->bindValue(':cidade', '%' . $filtros['cidade'] . '%');
        if (!empty($filtros['estado'])) $stmt->bindValue(':estado', $filtros['estado']);

        $stmt->bindValue(':limit', (int) $limite, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Conta o total de equipes para a paginação
     */
    public function countEquipes($filtros = [])
    {
        $sql = "SELECT COUNT(*) FROM equipes e 
                LEFT JOIN endereco en ON e.id_endereco = en.id_endereco 
                WHERE 1=1";

        if (!empty($filtros['nome'])) $sql .= " AND e.nome LIKE :nome";
        if (!empty($filtros['cidade'])) $sql .= " AND en.cidade LIKE :cidade";

        $stmt = $this->db->prepare($sql);
        if (!empty($filtros['nome'])) $stmt->bindValue(':nome', '%' . $filtros['nome'] . '%');
        if (!empty($filtros['cidade'])) $stmt->bindValue(':cidade', '%' . $filtros['cidade'] . '%');

        $stmt->execute();
        return $stmt->fetchColumn();
    }

    /**
     * Exclui uma equipe
     */
    public function delete($id)
    {
        try {
            $sql = "DELETE FROM equipes WHERE id_equipe = :id";
            return $this->db->prepare($sql)->execute([':id' => $id]);
        } catch (Exception $e) {
            error_log("Erro Delete Equipe: " . $e->getMessage());
            return false;
        }
    }
}