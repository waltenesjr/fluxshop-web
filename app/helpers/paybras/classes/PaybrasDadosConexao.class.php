<?php

class PaybrasDadosConexao {
    private $URL;

    // Inicializa nova instância da classe PaybrasDadosConexao
    public function __construct($URL) {
        if(isset($URL) && !empty($URL)){
            $this->URL = $URL;
        } else {
            throw new Exception("Dados de conex�o n�o setados.");
        }
    }

    // Retorna URL
    public function getURL() {
        return !empty($this->URL) ? $this->URL : null;
    }

    // Seta URL
    public function setURL($URL) {
        $this->URL = $URL;
    }
}

?>