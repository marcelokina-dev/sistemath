<?php

class EnderecoModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }
    // 1. PAÍS (Tabela: pais)
    public function getOuCriarPais($nome, $sigla = 'BR')
    {
        $stmt = $this->db->prepare("SELECT id_pais FROM pais WHERE nome = ? OR sigla = ?");
        $stmt->execute([$nome, $sigla]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($res)
            return $res['id_pais'];

        $ins = $this->db->prepare("INSERT INTO pais (nome, sigla) VALUES (?, ?)");
        $ins->execute([$nome, $sigla]);
        return $this->db->lastInsertId();
    }

    // 2. ESTADO (Tabela: estado)
    public function getOuCriarEstado($nome, $id_pais)
    {
        // No seu SQL, estados têm 'nome', 'sigla' e 'id_pais'
        $stmt = $this->db->prepare("SELECT id_estado FROM estado WHERE (nome = ? OR sigla = ?) AND id_pais = ?");
        $stmt->execute([$nome, $nome, $id_pais]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($res)
            return $res['id_estado'];

        // Se estiver criando, o ideal é passar nome e sigla separadamente. 
        // Se o formulário enviar apenas um, usamos para ambos por enquanto.
        $ins = $this->db->prepare("INSERT INTO estado (nome, sigla, id_pais) VALUES (?, ?, ?)");
        $ins->execute([$nome, $nome, $id_pais]);
        return $this->db->lastInsertId();
    }

    // 3. CIDADE (Tabela: cidade)
    public function getOuCriarCidade($nome, $id_estado)
    {
        $stmt = $this->db->prepare("SELECT id_cidade FROM cidade WHERE nome = ? AND id_estado = ?");
        $stmt->execute([$nome, $id_estado]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($res)
            return $res['id_cidade'];

        $ins = $this->db->prepare("INSERT INTO cidade (nome, id_estado) VALUES (?, ?)");
        $ins->execute([$nome, $id_estado]);
        return $this->db->lastInsertId();
    }

    // 4. BAIRRO (Tabela: bairro)
    public function getOuCriarBairro($nome, $id_cidade)
    {
        $stmt = $this->db->prepare("SELECT id_bairro FROM bairro WHERE nome = ? AND id_cidade = ?");
        $stmt->execute([$nome, $id_cidade]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($res)
            return $res['id_bairro'];

        $ins = $this->db->prepare("INSERT INTO bairro (nome, id_cidade) VALUES (?, ?)");
        $ins->execute([$nome, $id_cidade]);
        return $this->db->lastInsertId();
    }

    // 5. LOGRADOURO (Tabela: logradouro)
    public function getOuCriarLogradouro($nome, $cep, $id_bairro)
    {
        $cep = preg_replace('/[^0-9]/', '', $cep);

        $stmt = $this->db->prepare("SELECT id_logradouro FROM logradouro WHERE cep = ? AND id_bairro = ?");
        $stmt->execute([$cep, $id_bairro]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($res)
            return $res['id_logradouro'];

        $ins = $this->db->prepare("INSERT INTO logradouro (nome, cep, id_bairro) VALUES (?, ?, ?)");
        $ins->execute([$nome, $cep, $id_bairro]);
        return $this->db->lastInsertId();
    }

    // 6. ENDEREÇO (Tabela: endereco)
    public function criarEndereco($numero, $complemento, $id_logradouro)
    {
        // Verificação para garantir que o id_logradouro não seja nulo (causa erro de FK)
        if (!$id_logradouro)
            return null;

        $ins = $this->db->prepare("INSERT INTO endereco (numero, complemento, id_logradouro) VALUES (?, ?, ?)");
        $ins->execute([$numero, $complemento, $id_logradouro]);
        return $this->db->lastInsertId();
    }
}