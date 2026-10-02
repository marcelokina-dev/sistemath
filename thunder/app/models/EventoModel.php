<?php

require_once __DIR__ . '/../../core/Database.php';

class EventoModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function listarComFiltros($filtros = [], $itensPorPagina = 10, $paginaAtual = 1)
    {
        $itensPorPagina = (int) $itensPorPagina > 0 ? (int) $itensPorPagina : 10;
        $paginaAtual = (int) $paginaAtual > 0 ? (int) $paginaAtual : 1;
        $offset = ($paginaAtual - 1) * $itensPorPagina;

        $params = [];
        $where = " WHERE 1=1 ";

        if (!empty($filtros['nome'])) {
            $where .= " AND e.nome LIKE ? ";
            $params[] = "%" . $filtros['nome'] . "%";
        }

        if (!empty($filtros['ano']) && $filtros['ano'] !== 'Todos') {
            $where .= " AND YEAR(e.data_evento) = ? ";
            $params[] = $filtros['ano'];
        }

        if (!empty($filtros['status'])) {
            $where .= " AND e.status = ? ";
            $params[] = $filtros['status'];
        }

        $sqlTotal = "SELECT COUNT(*) as total FROM eventos e" . $where;
        $stmtTotal = $this->db->prepare($sqlTotal);
        $stmtTotal->execute($params);
        $totalRegistros = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];

        // Ajustado para incluir o JOIN com contato se necessário no futuro
        $sql = "SELECT e.*, ed.cidade, ed.estado, m.nome as modalidade_nome 
        FROM eventos e 
        LEFT JOIN endereco ed ON e.id_endereco = ed.id_endereco 
        LEFT JOIN modalidade m ON e.id_modalidade = m.id_modalidade 
        " . $where . " 
        ORDER BY e.data_evento DESC 
        LIMIT $itensPorPagina OFFSET $offset";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return [
            'dados' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'totalPaginas' => ceil($totalRegistros / $itensPorPagina),
            'totalRegistros' => (int) $totalRegistros
        ];
    }

    /*
    public function buscarPorId($id)
    {
        // Removido o JOIN com contato
        $sql = "SELECT e.*, ed.logradouro, ed.numero, ed.bairro, ed.cidade, ed.estado, ed.pais 
                FROM eventos e
                LEFT JOIN endereco ed ON e.id_endereco = ed.id_endereco
                WHERE e.id_evento = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
        */

    public function buscarPorId($id)
    {
        // Seleção explícita de colunas para evitar o erro de 'redes_sociais'
        $sql = "SELECT e.id_evento, e.nome, e.foto, e.data_evento, e.local_nome, 
                   e.id_endereco, e.slugs, e.status, e.id_modalidade,
                   ed.logradouro, ed.numero, ed.bairro, ed.cidade, ed.estado, ed.pais 
            FROM eventos e
            LEFT JOIN endereco ed ON e.id_endereco = ed.id_endereco
            WHERE e.id_evento = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }



    public function salvar($dados)
    {
        try {
            $this->db->beginTransaction();

            // 1. Insere apenas o endereço
            $sqlEnd = "INSERT INTO endereco (logradouro, numero, bairro, cidade, estado, pais) VALUES (?, ?, ?, ?, ?, ?)";
            $stmtEnd = $this->db->prepare($sqlEnd);
            $stmtEnd->execute([
                $dados['logradouro'] ?? null,
                $dados['numero'] ?? null,
                $dados['bairro'] ?? null,
                $dados['cidade'] ?? null,
                $dados['estado'] ?? null,
                $dados['pais'] ?? 'Brasil'
            ]);
            $idEndereco = $this->db->lastInsertId();

            // 2. Insere o evento sem id_contato
            $sqlEvt = "INSERT INTO eventos (nome, foto, data_evento, local_nome, id_endereco, id_modalidade, slugs, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmtEvt = $this->db->prepare($sqlEvt);
            $slug = $this->gerarSlug($dados['nome']);

            $stmtEvt->execute([
                $dados['nome'],
                $dados['foto'] ?? null,
                $dados['data_evento'],
                $dados['local_nome'],
                $idEndereco,
                $dados['id_modalidade'], // Novo campo
                $slug,
                $dados['status'] ?? 'Planejamento'
            ]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function atualizar($id, $dados)
    {
        try {
            $this->db->beginTransaction();

            $slug = $this->gerarSlug($dados['nome']);

            // Atualização do Evento
            $sqlEvt = "UPDATE eventos SET nome = ?, foto = ?, data_evento = ?, local_nome = ?, id_modalidade = ?, slugs = ?, status = ? WHERE id_evento = ?";
            $stmtEvt = $this->db->prepare($sqlEvt);
            $stmtEvt->execute([
                $dados['nome'],
                $dados['foto'],
                $dados['data_evento'],
                $dados['local_nome'],
                $dados['id_modalidade'], // Novo campo
                $slug,
                $dados['status'],
                $id
            ]);

            // Atualização do Endereço
            if (!empty($dados['id_endereco'])) {
                $sqlEnd = "UPDATE endereco SET logradouro = ?, numero = ?, bairro = ?, cidade = ?, estado = ?, pais = ? WHERE id_endereco = ?";
                $stmtEnd = $this->db->prepare($sqlEnd);
                $stmtEnd->execute([
                    $dados['logradouro'] ?? null,
                    $dados['numero'] ?? null,
                    $dados['bairro'] ?? null,
                    $dados['cidade'] ?? null,
                    $dados['estado'] ?? null,
                    $dados['pais'] ?? 'Brasil',
                    $dados['id_endereco']
                ]);
            }

            // Atualização do Contato (Opcional se você tiver os campos na view)
            if (!empty($dados['id_contato'])) {
                $sqlCont = "UPDATE contato SET email = ?, celular = ? WHERE id_contato = ?";
                $stmtCont = $this->db->prepare($sqlCont);
                $stmtCont->execute([
                    $dados['email'] ?? null,
                    $dados['celular'] ?? null,
                    $dados['id_contato']
                ]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Erro ao atualizar evento: " . $e->getMessage());
            return false;
        }
    }

    public function excluir($id)
    {
        try {
            $this->db->beginTransaction();

            $stmtBusca = $this->db->prepare("SELECT id_endereco, id_contato FROM eventos WHERE id_evento = ?");
            $stmtBusca->execute([$id]);
            $evento = $stmtBusca->fetch(PDO::FETCH_ASSOC);

            // 1. Excluir Lutas (IMPORTANTE para evitar erro de FK)
            $stmtLutas = $this->db->prepare("DELETE FROM lutas WHERE id_evento = ?");
            $stmtLutas->execute([$id]);

            // 2. Excluir Evento
            $stmtDelEvt = $this->db->prepare("DELETE FROM eventos WHERE id_evento = ?");
            $stmtDelEvt->execute([$id]);

            // 3. Limpar tabelas auxiliares
            if ($evento) {
                if (!empty($evento['id_endereco'])) {
                    $this->db->prepare("DELETE FROM endereco WHERE id_endereco = ?")->execute([$evento['id_endereco']]);
                }
                if (!empty($evento['id_contato'])) {
                    $this->db->prepare("DELETE FROM contato WHERE id_contato = ?")->execute([$evento['id_contato']]);
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Erro ao excluir evento: " . $e->getMessage());
            return false;
        }
    }

    private function gerarSlug($text)
    {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = @iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);

        return empty($text) ? 'evento-' . time() : $text;
    }

    public function buscarCategoriasPorModalidade($id_modalidade, $sexo)
    {
        // Força limpeza total dos dados
        $sexo = strtoupper(trim($sexo));
        $id_modalidade = intval($id_modalidade);

        // Query simplificada para garantir o retorno
        $sql = "SELECT id_categoria_peso, nome, peso_max 
            FROM categoria_peso 
            WHERE id_modalidade = :mod 
            AND (sexo = :sexo OR sexo = 'U')
            ORDER BY peso_max ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':mod', $id_modalidade, PDO::PARAM_INT);
        $stmt->bindValue(':sexo', $sexo, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function buscarProximaOrdemCard($id_evento)
    {
        $sql = "SELECT MAX(ordem_card) as ultima_ordem FROM lutas WHERE id_evento = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_evento]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        // Se não houver lutas, começa com 1. Se houver, soma +1.
        return ($resultado['ultima_ordem'] ?? 0) + 1;
    }
}