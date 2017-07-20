<?php

class PaybrasDadosRecebedorSecundario {
    private $email;
    private $valor;

    // Inicializa nova instância da classe PaybrasDadosRecebedorSecundario
    public function __construct (Array $dados = null) {
        if ($dados) {
            if (isset($dados['email'])) {
                $this->email = $dados['email'];
            }
            if (isset($dados['valor'])) {
                $this->valor = $dados['valor'];
            }
        } else {
            throw new Exception("Dados do recebedor secundário não setados.");
        }
    }


    // Retorna email
    public function getEmail() {
        return !empty($this->email) ? $this->email : null;
    }

    // Seta email
    public function setEmail($email) {
        $this->email = $email;
    }

    // Retorna valor
    public function getValor() {
        return !empty($this->valor) ? $this->valor : null;
    }

    // Seta valor
    public function setValor($valor) {
        $this->valor = $valor;
    }

}

?>