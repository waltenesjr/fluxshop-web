<?php

class PaybrasDadosProduto {
    private $codigo;
    private $nome;
    private $categoria;
    private $qtd;
    private $valor;
    private $peso;

    // Inicializa nova instância da classe PaybrasDadosProduto
    public function __construct (Array $dados = null) {
        if ($dados) {
            if (isset($dados['codigo'])) {
                $this->codigo = $dados['codigo'];
            }
            if (isset($dados['nome'])) {
                $this->nome = $dados['nome'];
            }
            if (isset($dados['categoria'])) {
                $this->categoria = $dados['categoria'];
            }
            if (isset($dados['qtd'])) {
                $this->qtd = $dados['qtd'];
            }
            if (isset($dados['valor'])) {
                $this->valor = $dados['valor'];
            }
            if (isset($dados['peso'])) {
                $this->peso = $dados['peso'];
            }
        } else {
            throw new Exception("Dados do produto não setados.");
        }
    }

    // Retorna codigo
    public function getCodigo() {
        return !empty($this->codigo) ? $this->codigo : null;
    }

    // Seta codigo
    public function setCodigo($codigo) {
        $this->codigo = $codigo;
    }

    // Retorna nome do produto
    public function getNome() {
        return !empty($this->nome) ? $this->nome : null;
    }

    // Seta nome do produto
    public function setNome($nome) {
        $this->nome = $nome;
    }

    // Retorna categoria
    public function getCategoria() {
        return !empty($this->categoria) ? $this->categoria : null;
    }

    // Seta categoria
    public function setCategoria($categoria) {
        $this->categoria = $categoria;
    }

    // Retorna quantidade do produto
    public function getQuantidade() {
        return !empty($this->qtd) ? $this->qtd : null;
    }

    // Seta quantidade do produto
    public function setQuantidade($qtd) {
        $this->qtd = $qtd;
    }

    // Retorna valor do produto
    public function getValor() {
        return !empty($this->valor) ? $this->valor : null;
    }

    // Seta valor do produto
    public function setValor($valor) {
        $this->valor = $valor;
    }

    // Retorna peso do produto
    public function getPeso() {
        return !empty($this->peso) ? $this->peso : null;
    }

    // Seta peso do produto
    public function setPeso($peso) {
        $this->peso = $peso;
    }
}

?>