<?php

class PaybrasDadosLojista {
    private $email;
    private $token;

    // Inicializa nova instÃ¢ncia da classe PaybrasDadosLojista
    public function __construct ($email, $token) {
        if ($email !== null && $token !== null) {
            $this->email = $email;
            $this->token = $token;
        } else {
            throw new Exception("Dados do lojista não setados.");
        }
    }

    // Retorna e-mail do lojista
    public function getEmail() {
        return !empty($this->email) ? $this->email : null;
    }

    // Seta e-mail do lojista
    public function setEmail($email) {
        $this->email = $email;
    }

    // Retorna token do lojista
    public function getToken() {
        return !empty($this->token) ? $this->token : null;
    }

    // Seta token do lojista
    public function setToken($token) {
        $this->token = $token;
    }
}

?>