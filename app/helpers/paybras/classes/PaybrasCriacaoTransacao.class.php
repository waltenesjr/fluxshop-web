<?php

class PaybrasCriacaoTransacao {
    private $conexao;
    private $lojista;

    private $cartao;
    private $pagador;
    private $pedido;
    private $endereco_entrega;
    private $produto = array();
    private $recebedor_secundario;

    // Inicializa nova instância da classe PaybrasCriacaoTransacao
    public function __construct(Array $dados = null) {
        if ($dados) {
            if (isset($dados['conexao']) && $dados['conexao'] instanceof PaybrasDadosConexao) {
                $this->conexao = $dados['conexao'];
            }
            if (isset($dados['lojista']) && $dados['lojista'] instanceof PaybrasDadosLojista) {
                $this->lojista = $dados['lojista'];
            }

            if (isset($dados['cartao']) && $dados['cartao'] instanceof PaybrasDadosCartao) {
                $this->cartao = $dados['cartao'];
            }
            if (isset($dados['pagador']) && $dados['pagador'] instanceof PaybrasDadosPagador) {
                $this->pagador = $dados['pagador'];
            }
            if (isset($dados['pedido']) && $dados['pedido'] instanceof PaybrasDadosPedido) {
                $this->pedido = $dados['pedido'];
            }
            if (isset($dados['endereco_entrega']) && $dados['endereco_entrega'] instanceof PaybrasDadosEntrega) {
                $this->endereco_entrega = $dados['endereco_entrega'];
            }
            if (isset($dados['produto'])) {
                foreach ($dados['produto'] as $value) {
                    $this->produto[] = $value;
                }
            }
        } else {
            throw new Exception("Dados de criação de transação não setados.");
        }
    }

    // Retorna dados da conexão
    public function getConexao() {
        return !empty($this->conexao) ? $this->conexao : null;
    }

    // Seta dados da conexão
    public function setConexao(PaybrasDadosConexao $conexao) {
        $this->conexao = $conexao;
    }

    ///////////////////////////////////////////////////////////////

    // Retorna dados do lojista
    public function getLojista() {
        return !empty($this->recebedor_email) ? $this->recebedor_email : null;
    }

    // Seta dados do lojista
    public function setLojista(PaybrasDadosLojista $lojista) {
        $this->lojista = $lojista;
    }

    ///////////////////////////////////////////////////////////////

    // Retorna dados do cartao
    public function getCartao() {
        return !empty($this->cartao) ? $this->cartao : null;
    }

    // Seta dados do cartao
    public function setCartao(PaybrasDadosCartao $cartao) {
        $this->cartao = $cartao;
    }

    ///////////////////////////////////////////////////////////////

    // Retorna dados do pagador
    public function getPagador() {
        return !empty($this->pagador) ? $this->pagador : null;
    }

    // Seta dados do pagador
    public function setPagador(PaybrasDadosPagador $pagador) {
        $this->pagador = $pagador;
    }

    ///////////////////////////////////////////////////////////////

    // Retorna dados do pedido
    public function getPedido() {
        return !empty($this->pedido) ? $this->pedido : null;
    }

    // Seta dados do pedido
    public function setPedido(PaybrasDadosPedido $pedido) {
        $this->pedido = $pedido;
    }

    ///////////////////////////////////////////////////////////////

    // Retorna endereço de entrega
    public function getEnderecoEntrega() {
        return !empty($this->endereco_entrega) ? $this->endereco_entrega : null;
    }

    // Seta endereço de entrega
    public function setEnderecoEntrega(PaybrasDadosEntrega $endereco_entrega) {
        $this->endereco_entrega = $endereco_entrega;
    }

    ///////////////////////////////////////////////////////////////

    // Retorna dados do produto
    public function getProduto() {
        return !empty($this->produto) ? $this->produto : null;
    }

    // Seta dados do produto
    public function setProduto(PaybrasDadosProduto $produto) {
        $this->produto = $produto;
    }

    // Gera mensagem para ser enviada
    public function getArrayCriacaoTransacao(){

        if(isset($this->lojista) && !empty($this->lojista)){
            $mensagem['recebedor_email'] = $this->lojista->getEmail();
            $mensagem['recebedor_api_token'] = $this->lojista->getToken();
        } else {
            throw new Exception("Dados do lojista não setados.");
        }

        if(isset($this->cartao) && !empty($this->cartao)){
            $mensagem['cartao_numero'] = $this->cartao->getNumero();
            $mensagem['cartao_parcelas'] = $this->cartao->getParcelas();
            $mensagem['cartao_codigo_de_seguranca'] = $this->cartao->getCodigoSeguranca();
            $mensagem['cartao_bandeira'] = $this->cartao->getBandeira();
            $mensagem['cartao_portador_nome'] = $this->cartao->getPortadorNome();
            $mensagem['cartao_validade_mes'] = $this->cartao->getValidadeMes();
            $mensagem['cartao_validade_ano'] = $this->cartao->getValidadeAno();
            $mensagem['cartao_portador_cpf'] = $this->cartao->getPortadorCPF();
            $mensagem['cartao_portador_data_de_nascimento'] = $this->cartao->getPortadorNascimento();
            $mensagem['cartao_portador_telefone_ddd'] = $this->cartao->getPortadorDDD();
            $mensagem['cartao_portador_telefone'] = $this->cartao->getPortadorTelefone();
        }

        if(isset($this->pagador) && !empty($this->pagador)){
            $mensagem['pagador_logradouro'] = $this->pagador->getEndereco()->getLogradouro();
            $mensagem['pagador_numero'] = $this->pagador->getEndereco()->getNumero();
            $mensagem['pagador_complemento'] = $this->pagador->getEndereco()->getComplemento();
            $mensagem['pagador_bairro'] = $this->pagador->getEndereco()->getBairro();
            $mensagem['pagador_cep'] = $this->pagador->getEndereco()->getCEP();
            $mensagem['pagador_cidade'] = $this->pagador->getEndereco()->getCidade();
            $mensagem['pagador_estado'] = $this->pagador->getEndereco()->getEstado();
            $mensagem['pagador_pais'] = $this->pagador->getEndereco()->getPais();

            $mensagem['pagador_nome'] = $this->pagador->getNome();
            $mensagem['pagador_email'] = $this->pagador->getEmail();
            $mensagem['pagador_cpf'] = $this->pagador->getCPF();
            $mensagem['pagador_rg'] = $this->pagador->getRG();
            $mensagem['pagador_telefone_ddd'] = $this->pagador->getDDDTelefone();
            $mensagem['pagador_telefone'] = $this->pagador->getTelefone();
            $mensagem['pagador_celular_ddd'] = $this->pagador->getDDDCelular();
            $mensagem['pagador_celular'] = $this->pagador->getCelular();
            $mensagem['pagador_sexo'] = $this->pagador->getSexo();
            $mensagem['pagador_data_nascimento'] = $this->pagador->getNascimento();
            $mensagem['pagador_ip'] = $this->pagador->getIP();
        } else {
            throw new Exception("Dados do pagador não setados.");
        }

        if(isset($this->pedido) && !empty($this->pedido)){
            $mensagem['pedido_id'] = $this->pedido->getID();
            $mensagem['pedido_descricao'] = $this->pedido->getDescricao();
            $mensagem['pedido_meio_pagamento'] = $this->pedido->getMeioPagamento();
            $mensagem['pedido_moeda'] = $this->pedido->getMoeda();
            $mensagem['pedido_valor_total_original'] = $this->pedido->getValorTotalOriginal();
            $mensagem['pedido_url_redirecionamento'] = $this->pedido->getURLRedirecionamento();
        } else {
            throw new Exception("Dados do pedido não setados.");
        }

        if(isset($this->endereco_entrega) && !empty($this->endereco_entrega)){
            $mensagem['entrega_logradouro'] = $this->endereco_entrega->getEndereco()->getLogradouro();
            $mensagem['entrega_numero'] = $this->endereco_entrega->getEndereco()->getNumero();
            $mensagem['entrega_complemento'] = $this->endereco_entrega->getEndereco()->getComplemento();
            $mensagem['entrega_bairro'] = $this->endereco_entrega->getEndereco()->getBairro();
            $mensagem['entrega_cep'] = $this->endereco_entrega->getEndereco()->getCEP();
            $mensagem['entrega_cidade'] = $this->endereco_entrega->getEndereco()->getCidade();
            $mensagem['entrega_estado'] = $this->endereco_entrega->getEndereco()->getEstado();
            $mensagem['entrega_pais'] = $this->endereco_entrega->getEndereco()->getPais();
            $mensagem['entrega_nome'] = $this->endereco_entrega->getNome();

        } else {
            throw new Exception("Dados do entrega não setados.");
        }

        if(isset($this->produto) && !empty($this->produto)){
            $i=0;
            foreach ($this->produto as $value) {
                $mensagem['produtos'][$i]['produto_nome'] = $value->getNome();
                $mensagem['produtos'][$i]['produto_codigo'] = $value->getCodigo();
                $mensagem['produtos'][$i]['produto_categoria'] = $value->getCategoria();
                $mensagem['produtos'][$i]['produto_qtd'] = $value->getQuantidade();
                $mensagem['produtos'][$i]['produto_valor'] = $value->getValor();
                $mensagem['produtos'][$i]['produto_peso'] = $value->getPeso();
                $i++;
            }

        }

        if(isset($this->recebedor_secundario) && !empty($this->recebedor_secundario)){
            $i=0;
            foreach ($this->recebedor_secundario as $value) {
                $mensagem['recebedor_secundario'][$i]['produto_nome'] = $value->getEmail();
                $mensagem['recebedor_secundario'][$i]['produto_codigo'] = $value->getValor();
                $i++;
            }
        }
        
        if(isset($this->conexao) && !empty($this->conexao)){
            $urlEnvio = $this->conexao->getURL();
        } else {
            throw new Exception("Dados da conexão não setados.");
        }

        $get_erro = $this->validaMensagem($mensagem);

        if($get_erro['sucesso']){
            PaybrasConfig::utf8_encode_deep($mensagem);
            $retorno = PaybrasConfig::curl($urlEnvio, $mensagem);
            return json_decode($retorno, true);
        } else {
            return $get_erro;
        }
    }

    public function validaMensagem($mensagem){
        $erro = array();
        $retorno['sucesso'] = 1;
        
        if($mensagem['pedido_meio_pagamento'] == 'cartao'){
            $mensagem['cartao_numero'] ? null : $erro[] = 'Número do cartão é campo obrigatório!';
            $mensagem['cartao_parcelas'] ? null : $erro[] = 'Parcela é campo obrigatório!';
            $this->validaCodSeguranca($mensagem['cartao_codigo_de_seguranca'], $mensagem['cartao_bandeira']) ? null : $erro[] = 'Código de segurança é campo obrigatório!';
            $this->validaBandeira($mensagem['cartao_bandeira']) ? null : $erro[] = 'Bandeira é campo obrigatório!';
            $mensagem['cartao_portador_nome'] ? null : $erro[] = 'Nome do titular do cartão é campo obrigatório!';
            $this->validaMes($mensagem['cartao_validade_mes']) ? null : $erro[] = 'Mês de validade do cartão é campo obrigatório!';
            $this->validaAno($mensagem['cartao_validade_ano']) ? null : $erro[] = 'Ano de validade do cartão é campo obrigatório!';

            $this->validaCPF($mensagem['cartao_portador_cpf'], false) ? null : $erro[] = 'CPF informado é inválido!';
            $this->validaData($mensagem['cartao_portador_data_de_nascimento'], false) ? null : $erro[] = 'Data de nascimento do portador do cartão é inválida!';
            $this->validaTelefone($mensagem['cartao_portador_telefone_ddd'].$mensagem['cartao_portador_telefone'], false) ? null : $erro[] = 'Telefone do portador do cartão informado é inválido!';        
        } else {
            $mensagem['pedido_url_redirecionamento'] ? null : $erro[] = ' é campo obrigatório!';
        }

        $mensagem['pagador_nome'] ? null : $erro[] = 'Nome do cliente é campo obrigatório!';
        $this->validaEmail($mensagem['pagador_email']) ? null : $erro[] = 'E-mail informado é inválido!';
        $this->validaCPF($mensagem['pagador_cpf'], true) ? null : $erro[] = 'CPF informado é inválido!';
        $this->validaTelefone($mensagem['pagador_telefone_ddd'].$mensagem['pagador_telefone'], true) ? null : $erro[] = 'Telefone informado é inválido!';
        $this->validaTelefone($mensagem['pagador_celular_ddd'].$mensagem['pagador_celular'], false) ? null : $erro[] = 'Celular informado é inválido!';
        $mensagem['entrega_logradouro'] ? null : $erro[] = 'Endereço de Entrega é campo obrigatório!';
        $mensagem['entrega_bairro'] ? null : $erro[] = 'Bairro do endereço de entrega é campo obrigatório!';
        $this->validaCEP($mensagem['entrega_cep'], true) ? null : $erro[] = 'CEP informado é inválido!';
        $mensagem['entrega_cidade'] ? null : $erro[] = 'Cidade do endereço de entrega é campo obrigatório!';
        $this->validaEstado($mensagem['entrega_estado'], true) ? null : $erro[] = 'Estado informado é inválido. Formato: XX';
        $mensagem['entrega_pais'] ? null : $erro[] = 'País do endereço de entrega é campo obrigatório!';

        $this->validaData($mensagem['pagador_data_nascimento'], false) ? null : $erro[] = 'Data de nascimento inválida!';
        $this->validaCEP($mensagem['pagador_cep'], false) ? null : $erro[] = 'CEP informado é inválido!';
        $this->validaEstado($mensagem['pagador_estado'], false) ? null : $erro[] = 'Estado informado é inválido. Formato: XX';

        if(!empty($erro)){
            $retorno['sucesso'] = 0;
            $retorno['mensagem_erro'] = $erro;
        }

        return $retorno;
    }

    public function validaData($data, $obrigatorio){
        if(empty($data) && !$obrigatorio) {
            return true;
        }

        $data=explode("/",$data);
        if (($data['0']<=31 && $data['0']!=0)&&($data['1']<=12 && $data['1']!=0)&&($data['2']>=1900 && $data['2']<=date("Y")+20)) {
            return true;
        }
        return false;
    }

    function validaMes($mes) {
        if($mes >= 1 && $mes <= 12){
            return true;
        }
        return false;
    }

    function validaAno($ano) {
        if($ano >= 00 && $ano <= date("y")+20){
            return true;
        }
        return false;
    }

    function validaCodSeguranca($codseg = null, $bandeira) {
        if($bandeira == 'amex'){
            if(strlen($codseg) == 4){
                return true;
            } else {
                return false;
            }
        } else {
            if(strlen($codseg) == 3){
                return true;
            } else {
                return false;
            }
        }
        return false;
    }

    function validaBandeira($bandeira) {
        $bandeiras = 'amex,mastercard,visa,diners,elo,simulacao';
        $bandeiras = explode(',', $bandeiras);

        if ($bandeira){
            if (in_array($bandeira, $bandeiras)){
                return true;
            }
        }
        return false;
    }

    function validaTelefone($tel = null, $obrigatorio) {
        // Verifica se um número foi informado
        if(empty($tel) && !$obrigatorio) {
            return true;
        }

        $formatado = $this->formataTelefone($tel);
        if(strlen($formatado) >= 10 && strlen($formatado) <= 11){
            return true;
        }
        return false;
    }

    function validaCEP($cep = null, $obrigatorio) {
        if(empty($cep) && !$obrigatorio) {
            return true;
        }

        $formatado = $this->apenasNumeros($cep);
        if(strlen($formatado) == 8){
            return true;
        }

        return false;


    }

    function validaEstado($estado = null, $obrigatorio) {
        if(empty($estado) && !$obrigatorio) {
            return true;
        }

        //Valida estados Brasileiros
        $estados = "AC,AL,AP,AM,BA,CE,DF,ES,GO,MA,MT,MS,MG,PA,PB,PR,PE,PI,RJ,RN,RS,RO,RR,SC,SP,SE,TO";
        $estados = explode(',', $estados);
        
        if ($estado){
            if (in_array($estado, $estados)){
                return true;
            }
        }
        return false;
    }

    function validaCPF($cpf = null, $obrigatorio) {
 
        // Verifica se um número foi informado
        if(empty($cpf) && !$obrigatorio) {
            return true;
        }
     
        // Elimina possivel mascara
        $cpf = ereg_replace('[^0-9]', '', $cpf);
        $cpf = str_pad($cpf, 11, '0', STR_PAD_LEFT);
         
        // Verifica se o numero de digitos informados é igual a 11 
        if (strlen($cpf) != 11) {
            return false;
        }
        // Verifica se nenhuma das sequências invalidas abaixo 
        // foi digitada. Caso afirmativo, retorna falso
        else if ($cpf == '00000000000' || 
            $cpf == '11111111111' || 
            $cpf == '22222222222' || 
            $cpf == '33333333333' || 
            $cpf == '44444444444' || 
            $cpf == '55555555555' || 
            $cpf == '66666666666' || 
            $cpf == '77777777777' || 
            $cpf == '88888888888' || 
            $cpf == '99999999999') {
            return false;
         // Calcula os digitos verificadores para verificar se o
         // CPF é válido
         } else {   
             
            for ($t = 9; $t < 11; $t++) {
                 
                for ($d = 0, $c = 0; $c < $t; $c++) {
                    $d += $cpf{$c} * (($t + 1) - $c);
                }
                $d = ((10 * $d) % 11) % 10;
                if ($cpf{$c} != $d) {
                    return false;
                }
            }
     
            return true;
        }
    }

    public function validaEmail($email){
        $conta = "^[a-zA-Z0-9\._-]+@";
        $domino = "[a-zA-Z0-9\._-]+.";
        $extensao = "([a-zA-Z]{2,4})$";

        $pattern = $conta.$domino.$extensao;
        if (ereg($pattern, $email))
            return true;
        else
            return false;
    }

    public function apenasNumeros($string){
        return preg_replace("/[^0-9\s]/", "", $string);
    }

    public function formataTelefone($string){
        $telefone = preg_replace("/[^0-9\s]/", "", $string);
        if(substr($telefone, 0,1) == 0){
            return substr($telefone, 1);
        }

        return $telefone;
    }
}

?>