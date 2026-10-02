<?php

class DashboardModel {
    private $db;

    public function __construct($db) { $this->db = $db; }

    public function getTotalAtletas() { return $this->db->query("SELECT COUNT(*) FROM atletas")->fetchColumn(); }
    public function getTotalEquipes() { return $this->db->query("SELECT COUNT(*) FROM equipes")->fetchColumn(); }
    public function getTotalEventos() { return $this->db->query("SELECT COUNT(*) FROM eventos")->fetchColumn(); }
    
    // Novo método para contar apenas os eventos que já possuem lutas finalizadas
// Altere este método para contar por data (igual à listagem oficial)
    public function getEventosRealizados() {
        return $this->db->query("
            SELECT COUNT(*) 
            FROM eventos 
            WHERE data_evento <= NOW()
        ")->fetchColumn();
    }
    
    // BÔNUS: Ajuste também o total de lutas finalizadas para incluir Empates e No Contests
    public function getTotalLutasFinalizadas() { 
        // Conta todas as lutas de eventos que já aconteceram
        return $this->db->query("
            SELECT COUNT(*) 
            FROM lutas l
            JOIN eventos e ON l.id_evento = e.id_evento
            WHERE e.data_evento <= NOW()
        ")->fetchColumn(); 
    }

    public function getLutasAgendadas() {
        return $this->db->query("SELECT COUNT(*) FROM lutas l JOIN eventos e ON l.id_evento = e.id_evento WHERE e.data_evento >= CURDATE() AND (l.vencedor_id IS NULL OR l.vencedor_id = 0)")->fetchColumn();
    }

    public function getAtletasRecentes($limit = 5) {
        return $this->db->query("SELECT a.*, m.nome as modalidade_nome FROM atletas a LEFT JOIN modalidade m ON a.id_modalidade = m.id_modalidade ORDER BY a.id_atleta DESC LIMIT $limit")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRegrasResumo() {
        return $this->db->query("SELECT mv.nome as metodo_nome, rp.pontos FROM regras_pontuacao rp JOIN metodos_vitoria mv ON rp.id_metodo = mv.id_metodo WHERE rp.resultado = 'vitoria' LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCrescimentoAtletasPorMes() {
        try {
            return $this->db->query("SELECT DATE_FORMAT(data_cadastro, '%Y-%m') as mes_ano, COUNT(*) as total_atletas FROM atletas GROUP BY mes_ano ORDER BY mes_ano ASC LIMIT 12")->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) { return []; }
    }
}