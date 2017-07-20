<?php

class PaybrasDadosPedido {
    private $id;
    private $valor_total_original;
    private $descricao;
    private $meio_pagamento;
    private $moeda;
    private $url_redirecionamento;

    // Inicializa nova instância da classe PaybrasDadosPedido
    public function __construct (Array $dados = null) {
        if ($dados) {
            if (isset($dados['id'])) {
                $this->id = $dados['id'];
            }
            if (isset($dados['valor_total_original'])) {
                $this->valor_total_original = $dados['valor_total_original'];
            }
            if (isset($dados['descricao'])) {
                $this->descricao = $dados['descricao'];
            }
            if (isset($dados['meio_pagamento'])) {
                $this->meio_pagamento = $dados['meio_pagamento'];
            }
            if (isset($dados['moeda'])) {
                $this->moeda = $dados['moeda'];
            }
            if (isset($dados['url_redirecionamento'])) {
                $this->url_redirecionamento = $dados['url_redirecionamento'];
            }
        } else {
            throw new Exception("Dados do pedido não setados.");
        }
    }

    // Retorna id
    public function getID() {
        return !empty($this->id) ? $this->id : null;
    }

    // Seta id
    public function setID($id) {
        $this->id = $id;
    }

    // Retorna valor total da compra
    public function getValorTotalOriginal() {
        return !empty($this->valor_total_original) ? $this->valor_total_original : null;
    }

    // Seta valor total da compra
    public function setValorTotalOriginal($valor_total_original) {
        $this->nome = $valor_total_original;
    }

    // Retorna descrição
    public function getDescricao() {
        return !empty($this->descricao) ? $this->descricao : null;
    }

    // Seta descrição
    public function setDescricao($descricao) {
        $this->descricao = $descricao;
    }

    // Retorna meio de pagamento
    public function getMeioPagamento() {
        return !empty($this->meio_pagamento) ? $this->meio_pagamento : null;
    }

    // Seta meio de pagamento
    public function setMeioPagamento($meio_pagamento) {
        $this->meio_pagamento = $meio_pagamento;
    }

    // Retorna moeda
    public function getMoeda() {
        return !empty($this->moeda) ? $this->moeda : null;
    }

    // Seta moeda
    public function setMoeda($moeda) {
        $this->moeda = $moeda;
    }

    // Retorna url de redirecionamento
    public function getURLRedirecionamento() {
        return !empty($this->url_redirecionamento) ? $this->url_redirecionamento : null;
    }

    // Seta url de redirecionamento
    public function setURLRedirecionamento($url_redirecionamento) {
        $this->url_redirecionamento = $url_redirecionamento;
    }
}

?>