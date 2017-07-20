<?php

class PaybrasDadosEntrega {
    private $nome;
    private $endereco;

    // Inicializa nova instância da classe PaybrasDadosEntrega
    public function __construct(Array $dados = null) {
        if ($dados) {
            if (isset($dados['nome'])) {
                $this->nome = $dados['nome'];
            }
            if (isset($dados['endereco']) && $dados['endereco'] instanceof PaybrasDadosEndereco) {
                $this->endereco = $dados['endereco'];
            }
        } else {
            throw new Exception("Dados de entrega não setados.");
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

    // Retorna endereco do pagador
    public function getEndereco() {
        return !empty($this->endereco) ? $this->endereco : null;
    }

    // Seta enderecodo pagador
    public function setEndereco(PaybrasDadosEndereco $endereco) {
        $this->endereco = $endereco;
    }
}
