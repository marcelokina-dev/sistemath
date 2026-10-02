<?php

class HistoricoModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

public function obterCampeoesAtuais()
{
    try {
        // Adicionado: e.nome como evento_nome e e.data_evento como data_real_evento
        // Adicionado: LEFT JOIN eventos e
        $sql = "SELECT h.*, 
                       a.nome as atleta_nome, a.sobrenome, a.apelido, a.foto, 
                       m.nome as modalidade_nome, 
                       cp.nome as categoria_nome, cp.peso_max,
                       e.nome as evento_nome,
                       e.data_evento as data_real_evento
                FROM historico_campeoes h
                LEFT JOIN atletas a ON h.id_atleta = a.id_atleta
                LEFT JOIN modalidade m ON h.id_modalidade = m.id_modalidade
                LEFT JOIN categoria_peso cp ON h.id_categoria_peso = cp.id_categoria_peso
                LEFT JOIN eventos e ON h.id_evento = e.id_evento
                WHERE h.status = 'campeao' AND h.data_fim IS NULL";

        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

public function obterHallDaFama()
{
    try {
        $sql = "SELECT h.*, 
                       a.nome as atleta_nome, a.sobrenome, a.apelido, 
                       m.nome as modalidade_nome, 
                       cp.nome as categoria_nome, cp.peso_max,
                       e.nome as evento_nome,
                       e.data_evento as data_real_evento -- Buscando a data da tabela eventos
                FROM historico_campeoes h
                LEFT JOIN atletas a ON h.id_atleta = a.id_atleta
                LEFT JOIN modalidade m ON h.id_modalidade = m.id_modalidade
                LEFT JOIN categoria_peso cp ON h.id_categoria_peso = cp.id_categoria_peso
                LEFT JOIN eventos e ON h.id_evento = e.id_evento
                WHERE h.status != 'campeao' OR h.data_fim IS NOT NULL
                ORDER BY e.data_evento DESC"; // Ordenar pela data do evento faz mais sentido aqui

        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

    public function tornarVacante($id)
    {
        try {
            $this->db->beginTransaction();

            // 1. Pega os dados para atualizar o ranking depois
            $stmt = $this->db->prepare("SELECT id_atleta, id_modalidade, id_categoria_peso FROM historico_campeoes WHERE id_historico_campeao = ?");
            $stmt->execute([$id]);
            $dados = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($dados) {
                // 2. Atualiza o histórico para vago
                $sqlH = "UPDATE historico_campeoes SET data_fim = CURDATE(), status = 'vago' WHERE id_historico_campeao = ?";
                $this->db->prepare($sqlH)->execute([$id]);

                // 3. Remove a coroa no ranking
                $sqlR = "UPDATE ranking SET campeao = 0 WHERE id_atleta = ? AND id_modalidade = ? AND id_categoria_peso = ?";
                $this->db->prepare($sqlR)->execute([$dados['id_atleta'], $dados['id_modalidade'], $dados['id_categoria_peso']]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function registrarNovaConquista($id_atleta, $id_mod, $id_cat)
    {
        try {
            $this->db->beginTransaction();

            // 1. Remove o status de campeão atual de qualquer outro atleta nesta categoria
            $stmt = $this->db->prepare("UPDATE historico_campeoes 
                                   SET status = 'vago', data_fim = CURDATE() 
                                   WHERE id_modalidade = ? AND id_categoria_peso = ? AND status = 'campeao'");
            $stmt->execute([$id_mod, $id_cat]);

            // 2. Insere o novo campeão
            $stmt = $this->db->prepare("INSERT INTO historico_campeoes 
                                   (id_atleta, id_modalidade, id_categoria_peso, data_inicio, status) 
                                   VALUES (?, ?, ?, CURDATE(), 'campeao')");
            $stmt->execute([$id_atleta, $id_mod, $id_cat]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}