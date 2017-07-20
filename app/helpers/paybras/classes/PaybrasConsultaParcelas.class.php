<?php

class PaybrasConsultaParcelas {
    private $lojista;
    private $pedido_valor_total;
    private $conexao;

    // Inicializa nova instÃ¢ncia da classe PaybrasConsultaParcelas
    public function __construct(Array $dados = null) {
        if ($dados) {
            if (isset($dados['lojista']) && $dados['lojista'] instanceof PaybrasDadosLojista) {
                $this->lojista = $dados['lojista'];
            }
            if (isset($dados['conexao']) && $dados['conexao'] instanceof PaybrasDadosConexao) {
                $this->conexao = $dados['conexao'];
            }
            if (isset($dados['pedido_valor_total'])) {
                $this->pedido_valor_total = $dados['pedido_valor_total'];
            }
        } else {
            throw new Exception("Dados de consulta de parcelas não setados.");
        }
    }

    // Retorna dados do lojista
    public function getLojista() {
        return !empty($this->recebedor_email) ? $this->recebedor_email : null;
    }

    // Seta dados do lojista
    public function setLojista(PaybrasDadosLojista $lojista) {
        $this->lojista = $lojista;
    }

    // Retorna valor total do pedido
    public function getPedidoValor() {
        return !empty($this->pedido_valor_total) ? $this->pedido_valor_total : null;
    }

    // Seta valor total do pedido
    public function setPedidoValor($pedido_valor_total) {
        $this->pedido_valor_total = $pedido_valor_total;
    }

    // Retorna dados da conexão
    public function getConexao() {
        return !empty($this->conexao) ? $this->conexao : null;
    }

    // Seta dados da conexão
    public function setConexao(PaybrasDadosConexao $conexao) {
        $this->conexao = $conexao;
    }

    // Gera mensagem para ser enviada
    public function getArrayParcelas(){
        if(isset($this->lojista) && !empty($this->lojista)){
            $mensagem['recebedor_email'] = $this->lojista->getEmail();
            $mensagem['recebedor_api_token'] = $this->lojista->getToken();
        } else {
            throw new Exception("Dados do lojista não setados.");
        }

        if(isset($this->conexao) && !empty($this->conexao)){
            $urlEnvio = $this->conexao->getURL();
        } else {
            throw new Exception("Dados da conexão não setados.");
        }

        if(isset($this->pedido_valor_total)){
            $mensagem['pedido_valor_total'] = $this->pedido_valor_total;
        } else {
            throw new Exception("Falta valor total do pedido.");
        }


        PaybrasConfig::utf8_encode_deep($mensagem);
        $retorno = PaybrasConfig::curl($urlEnvio, $mensagem);

        return json_decode($retorno, true);
    }



}

?>