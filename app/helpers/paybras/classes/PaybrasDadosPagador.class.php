<?php

class PaybrasDadosPagador {
    private $nome;
    private $email;
    private $cpf;
    private $rg;
    private $telefone_ddd;
    private $telefone;
    private $celular_ddd;
    private $celular;
    private $sexo;
    private $data_nascimento;
    private $ip;
    private $endereco;

    // Inicializa nova instância da classe PaybrasDadosPagador
    public function __construct (Array $dados = null) {
        if ($dados) {
            if (isset($dados['nome'])) {
                $this->nome = $dados['nome'];
            }
            if (isset($dados['email'])) {
                $this->email = $dados['email'];
            }
            if (isset($dados['cpf'])) {
                $this->cpf = $dados['cpf'];
            }
            if (isset($dados['rg'])) {
                $this->rg = $dados['rg'];
            }
            if (isset($dados['telefone_ddd'])) {
                $this->telefone_ddd = $dados['telefone_ddd'];
            }
            if (isset($dados['telefone'])) {
                $this->telefone = $dados['telefone'];
            }
            if (isset($dados['celular_ddd'])) {
                $this->celular_ddd = $dados['celular_ddd'];
            }
            if (isset($dados['celular'])) {
                $this->celular = $dados['celular'];
            }
            if (isset($dados['sexo'])) {
                $this->sexo = $dados['sexo'];
            }
            if (isset($dados['data_nascimento'])) {
                $this->data_nascimento = $dados['data_nascimento'];
            }
            if (isset($dados['ip'])) {
                $this->ip = $dados['ip'];
            }
            if (isset($dados['endereco']) && $dados['endereco'] instanceof PaybrasDadosEndereco) {
                $this->endereco = $dados['endereco'];
            }
        } else {
            throw new Exception("Dados do pagador não setados.");
        }
    }

    // Retorna nome
    public function getNome() {
        return !empty($this->nome) ? $this->nome : null;
    }

    // Seta nome
    public function setNome($nome) {
        $this->nome = $nome;
    }

    // Retorna email
    public function getEmail() {
        return !empty($this->email) ? $this->email : null;
    }

    // Seta email
    public function setEmail($email) {
        $this->nome = $email;
    }

    // Retorna CPF
    public function getCPF() {
        return !empty($this->cpf) ? $this->cpf : null;
    }

    // Seta CPF
    public function setCPF($cpf) {
        $this->cpf = $cpf;
    }

    // Retorna RG
    public function getRG() {
        return !empty($this->rg) ? $this->rg : null;
    }

    // Seta RG
    public function setRG($rg) {
        $this->rg = $rg;
    }

    // Retorna DDD do telefone
    public function getDDDTelefone() {
        return !empty($this->telefone_ddd) ? $this->telefone_ddd : null;
    }

    // Seta DDD do telefone
    public function setDDDTelefone($telefone_ddd) {
        $this->telefone_ddd = $telefone_ddd;
    }

    // Retorna telefone
    public function getTelefone() {
        return !empty($this->telefone) ? $this->telefone : null;
    }

    // Seta telefone
    public function setTelefone($telefone) {
        $this->telefone = $telefone;
    }

    // Retorna cidade
    public function getDDDCelular() {
        return !empty($this->celular_ddd) ? $this->celular_ddd : null;
    }

    // Seta DDD do celular
    public function setDDDCelular($celular_ddd) {
        $this->celular_ddd = $celular_ddd;
    }

    // Retorna celular
    public function getCelular() {
        return !empty($this->celular) ? $this->celular : null;
    }

    // Seta celular
    public function setCelular($celular) {
        $this->celular = $celular;
    }

    // Retorna sexo do pagador
    public function getSexo() {
        return !empty($this->sexo) ? $this->sexo : null;
    }

    // Seta sexo do pagador
    public function setSexo($sexo) {
        $this->sexo = $sexo;
    }

    // Retorna data de nascimento
    public function getNascimento() {
        return !empty($this->data_nascimento) ? $this->data_nascimento : null;
    }

    // Seta data de nascimento
    public function setNascimento($data_nascimento) {
        $this->data_nascimento = $data_nascimento;
    }

    // Retorna IP
    public function getIP() {
        return !empty($this->ip) ? $this->ip : null;
    }

    // Seta IP
    public function setIP($ip) {
        $this->ip = $ip;
    }

    // Retorna endereco do pagador
    public function getEndereco() {
        return !empty($this->endereco) ? $this->endereco : null;
    }

    // Seta enderecodo pagador
    public function setEndereco(PaybrasDadosEndereco $endereco) {
        $this->endereco = $endereco;
    }
}

?>