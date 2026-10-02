<?php

class ArbitroModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Lista árbitros com filtros e paginação
     */
    public function getArbitros($filtros = [], $itensPorPagina = 15, $offset = 0)
    {
        $params = [];
        $where = " WHERE 1=1";

        if (!empty($filtros['nome'])) {
            $where .= " AND (a.nome LIKE :nome_busca OR a.apelido LIKE :apelido_busca)";
            $params[':nome_busca'] = "%" . $filtros['nome'] . "%";
            $params[':apelido_busca'] = "%" . $filtros['nome'] . "%";
        }

        if (!empty($filtros['sexo'])) {
            $where .= " AND a.sexo = :sexo";
            $params[':sexo'] = $filtros['sexo'];
        }

        // Filtra apenas se status não for vazio e não for 'Todos'. 
        // Se estiver vazio, a query ignora o filtro e traz Ativos + Inativos.
        if (isset($filtros['status']) && $filtros['status'] !== '' && $filtros['status'] !== 'Todos') {
            $where .= " AND a.status = :status";
            $params[':status'] = $filtros['status'];
        }

        if (!empty($filtros['modalidade'])) {
            $where .= " AND a.id_arbitro IN (SELECT id_arbitro FROM arbitro_modalidade WHERE id_modalidade = :mod)";
            $params[':mod'] = $filtros['modalidade'];
        }

        $sql = "SELECT a.*, e.cidade, e.estado 
                FROM arbitros a
                LEFT JOIN endereco e ON a.id_endereco = e.id_endereco
                $where 
                ORDER BY a.nome ASC 
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        
        // Fazendo bind manual para garantir que LIMIT e OFFSET sejam tratados como inteiros
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', (int) $itensPorPagina, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        $stmt->execute();

        $arbitros = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($arbitros as &$arb) {
            $arb['modalidades'] = $this->getModalidadesNomesByArbitro($arb['id_arbitro']);
        }
        return $arbitros;
    }

    /**
     * Conta o total de árbitros conforme os filtros (essencial para a paginação)
     */
    public function countArbitros($filtros = [])
    {
        $params = [];
        $where = " WHERE 1=1";

        if (!empty($filtros['nome'])) {
            $where .= " AND (a.nome LIKE :nome_busca OR a.apelido LIKE :apelido_busca)";
            $params[':nome_busca'] = "%" . $filtros['nome'] . "%";
            $params[':apelido_busca'] = "%" . $filtros['nome'] . "%";
        }

        if (!empty($filtros['sexo'])) {
            $where .= " AND a.sexo = :sexo";
            $params[':sexo'] = $filtros['sexo'];
        }

        // AJUSTE: Aplicada a mesma lógica do getArbitros para consistência
        if (isset($filtros['status']) && $filtros['status'] !== '' && $filtros['status'] !== 'Todos') {
            $where .= " AND a.status = :status";
            $params[':status'] = $filtros['status'];
        }

        if (!empty($filtros['modalidade'])) {
            $where .= " AND a.id_arbitro IN (SELECT id_arbitro FROM arbitro_modalidade WHERE id_modalidade = :mod)";
            $params[':mod'] = $filtros['modalidade'];
        }

        $sql = "SELECT COUNT(*) FROM arbitros a $where";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    private function getModalidadesNomesByArbitro($id)
    {
        $sql = "SELECT m.nome FROM modalidade m
                INNER JOIN arbitro_modalidade am ON am.id_modalidade = m.id_modalidade
                WHERE am.id_arbitro = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function find($id)
    {
        $sql = "SELECT a.*, e.cep, e.logradouro, e.numero, e.bairro, e.cidade, e.estado, 
                       c.email, c.celular
                FROM arbitros a
                LEFT JOIN endereco e ON a.id_endereco = e.id_endereco
                LEFT JOIN contato c ON a.id_contato = c.id_contato
                WHERE a.id_arbitro = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $arbitro = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($arbitro) {
            $stmtMod = $this->db->prepare("SELECT id_modalidade FROM arbitro_modalidade WHERE id_arbitro = :id");
            $stmtMod->execute([':id' => $id]);
            $arbitro['modalidades_ids'] = $stmtMod->fetchAll(PDO::FETCH_COLUMN);
        }

        return $arbitro;
    }

    public function syncModalidades($idArbitro, $modalidades)
    {
        $stmtDel = $this->db->prepare("DELETE FROM arbitro_modalidade WHERE id_arbitro = :id");
        $stmtDel->execute([':id' => $idArbitro]);

        if (!empty($modalidades)) {
            $sql = "INSERT INTO arbitro_modalidade (id_arbitro, id_modalidade) VALUES (:arb, :mod)";
            $stmtIns = $this->db->prepare($sql);
            foreach ($modalidades as $idMod) {
                $stmtIns->execute([':arb' => $idArbitro, ':mod' => $idMod]);
            }
        }
    }

    public function listar()
    {
        $sql = "SELECT id_arbitro, nome FROM arbitros WHERE status = 'Ativo' ORDER BY nome ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}