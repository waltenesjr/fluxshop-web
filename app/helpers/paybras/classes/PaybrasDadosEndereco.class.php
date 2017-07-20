<?php

class PaybrasDadosEndereco {
    private $logradouro;
    private $numero;
    private $complemento;
    private $bairro;
    private $cep;
    private $cidade;
    private $estado;
    private $pais;

    // Inicializa nova instância da classe PaybrasDadosEndereco
    public function __construct (Array $dados = null) {
        if ($dados) {
            if (isset($dados['logradouro'])) {
                $this->logradouro = $dados['logradouro'];
            }
            if (isset($dados['numero'])) {
                $this->numero = $dados['numero'];
            }
            if (isset($dados['complemento'])) {
                $this->complemento = $dados['complemento'];
            }
            if (isset($dados['bairro'])) {
                $this->bairro = $dados['bairro'];
            }
            if (isset($dados['cep'])) {
                $this->cep = $dados['cep'];
            }
            if (isset($dados['cidade'])) {
                $this->cidade = $dados['cidade'];
            }
            if (isset($dados['estado'])) {
                $this->estado = $dados['estado'];
            }
            if (isset($dados['pais'])) {
                $this->pais = $dados['pais'];
            }
        } else {
            throw new Exception("Dados de endereço não setados.");
        }
    }

    // Retorna logradouro
    public function getLogradouro() {
        return !empty($this->logradouro) ? $this->logradouro : null;
    }

    // Seta logradouro
    public function setLogradouro($logradouro) {
        $this->logradouro = $logradouro;
    }

    // Retorna número da rua
    public function getNumero() {
        return !empty($this->numero) ? $this->numero : null;
    }

    // Seta número de parcelas
    public function setNumero($numero) {
        $this->numero = $numero;
    }

    // Retorna complemento do endereço
    public function getComplemento() {
        return !empty($this->complemento) ? $this->complemento : null;
    }

    // Seta complemento do endereço
    public function setComplemento($complemento) {
        $this->complemento = $complemento;
    }

    // Retorna bairro
    public function getBairro() {
        return !empty($this->bairro) ? $this->bairro : null;
    }

    // Seta bairro
    public function setBairro($bairro) {
        $this->bairro = $bairro;
    }

    // Retorna CEP
    public function getCEP() {
        return !empty($this->cep) ? $this->cep : null;
    }

    // Seta CEP
    public function setCEP($cep) {
        $this->cep = $cep;
    }

    // Retorna cidade
    public function getCidade() {
        return !empty($this->cidade) ? $this->cidade : null;
    }

    // Seta cidade
    public function setCidade($cidade) {
        $this->cidade = $cidade;
    }

    // Retorna estado
    public function getEstado() {
        return !empty($this->estado) ? $this->estado : null;
    }

    // Seta estado
    public function setEstado($estado) {
        $this->estado = $estado;
    }

    // Retorna país
    public function getPais() {
        return !empty($this->pais) ? $this->pais : null;
    }

    // Seta país
    public function setPais($pais) {
        $this->pais = $pais;
    }
}

?>