<?php
    require_once "servicos/PaybrasCriaTransacao.php";

    $retorno = PaybrasCriaTransacao::main($_POST);
    if(isset($retorno['sucesso'])){
        $sucesso = $retorno['sucesso']; // 0: erro; 1: sucesso
    } else {
        print_r($retorno);
        die;
    }
    if(!$sucesso){ 
        //Erro na criação de Transação
        $mensagem_erro = $retorno['mensagem_erro'];
        foreach ($mensagem_erro as $key => $value) {
            echo "<br/>" .$value;
        }
    } else {
        $pedido_id = $_POST['pedido_id']; //ID do pedido
        $transacao_id = $retorno['transacao_id']; //ID da transacoo
        // Código de status da transação:
        //  1: Ag. Pgto
        //  2: Em Análise
        //  3: Não Autorizado
        //  4: Aprovado
        //  5: Recusado
        $status_codigo = $retorno['status_codigo'];
        $status_nome = $retorno['status_nome']; // Nome do status da transação

        $url_pagamento = isset($retorno['url_pagamento']) ? $retorno['url_pagamento'] : null; // URL para pagamento de boleto ou TEF
        $nao_autorizado_codigo = isset($retorno['nao_autorizado_codigo']) ? $retorno['nao_autorizado_codigo'] : null; // CÃ³digo de nÃ£o autorização de transação com cartÃ£o
        $nao_autorizado_mensagem = isset($retorno['nao_autorizado_mensagem']) ? $retorno['nao_autorizado_mensagem'] : null; // Mensagem de nÃ£o autorização de transação com cartÃ£o

        echo "ID do pedido: " .$pedido_id. "<br>";
        echo "ID da transação: " .$transacao_id. "<br>";
        echo "Status: " .$status_codigo. " (" .$status_nome. ")<br>";
        if($url_pagamento) { echo "URL pagamento: " .$url_pagamento. "<br>"; }
        if($nao_autorizado_codigo) { echo "Cod. não autorização: " .$nao_autorizado_codigo. "<br>"; }
        if($nao_autorizado_mensagem) { echo "Msg. não autorização: " .$nao_autorizado_mensagem. "<br>"; }
        
        if($status_codigo=="1"){
            //Atualiza tabela de pedidos com novo status
        }elseif($status_codigo=="2"){
            //Atualiza tabela de pedidos com novo status
        }elseif($status_codigo=="3"){
            //Atualiza tabela de pedidos com novo status
        }elseif($status_codigo=="4"){
            //Atualiza tabela de pedidos com novo status
        }elseif($status_codigo=="5"){
            //Atualiza tabela de pedidos com novo status
        }
        //Se transação for TEF ou Boleto imprime na tela
        if($url_pagamento){
            echo "URL de Pgto: " .$url_pagamento;
        }
    }
?>
