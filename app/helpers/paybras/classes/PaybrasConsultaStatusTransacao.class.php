<?php

class PaybrasConsultaStatusTransacao {
    private $lojista;
    private $transacao_id;
    private $pedido_id;

    // Inicializa nova instância da classe PaybrasConsultaParcelas
    public function __construct(Array $dados = null) {
        if ($dados) {
            if (isset($dados['lojista']) && $dados['lojista'] instanceof PaybrasDadosLojista) {
                $this->lojista = $dados['lojista'];
            }
            if (isset($dados['conexao']) && $dados['conexao'] instanceof PaybrasDadosConexao) {
                $this->conexao = $dados['conexao'];
            }
            if (isset($dados['transacao_id'])) {
                $this->transacao_id = $dados['transacao_id'];
            }
            if (isset($dados['pedido_id'])) {
                $this->pedido_id = $dados['pedido_id'];
            }
        } else {
            throw new Exception("Dados de status de transação não setados.");
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

    // Retorna ID da transação
    public function getTransacaoID() {
        return !empty($this->transacao_id) ? $this->transacao_id : null;
    }

    // Seta ID da transação
    public function setTransacaoID($transacao_id) {
        $this->transacao_id = $transacao_id;
    }

    // Retorna ID do pedido
    public function getPedidoID() {
        return !empty($this->pedido_id) ? $this->pedido_id : null;
    }

    // Seta ID do pedido
    public function setPedidoID($pedido_id) {
        $this->pedido_id = $pedido_id;
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
    public function getArrayStatusTransacao(){

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

        if(!empty($this->transacao_id))
            $mensagem['transacao_id'] = $this->transacao_id;
        
        if(!empty($this->pedido_id))
            $mensagem['pedido_id'] = $this->pedido_id;

        PaybrasConfig::utf8_encode_deep($mensagem);
        $retorno = PaybrasConfig::curl($urlEnvio, $mensagem);

        return json_decode($retorno, true);
    }



}

?>