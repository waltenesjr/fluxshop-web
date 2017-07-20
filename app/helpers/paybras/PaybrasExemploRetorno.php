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
        //Erro na cria��o de Transa��o
        $mensagem_erro = $retorno['mensagem_erro'];
        foreach ($mensagem_erro as $key => $value) {
            echo "<br/>" .$value;
        }
    } else {
        $pedido_id = $_POST['pedido_id']; //ID do pedido
        $transacao_id = $retorno['transacao_id']; //ID da transacoo
        // C�digo de status da transa��o:
        //  1: Ag. Pgto
        //  2: Em An�lise
        //  3: N�o Autorizado
        //  4: Aprovado
        //  5: Recusado
        $status_codigo = $retorno['status_codigo'];
        $status_nome = $retorno['status_nome']; // Nome do status da transa��o

        $url_pagamento = isset($retorno['url_pagamento']) ? $retorno['url_pagamento'] : null; // URL para pagamento de boleto ou TEF
        $nao_autorizado_codigo = isset($retorno['nao_autorizado_codigo']) ? $retorno['nao_autorizado_codigo'] : null; // Código de não autoriza��o de transa��o com cartão
        $nao_autorizado_mensagem = isset($retorno['nao_autorizado_mensagem']) ? $retorno['nao_autorizado_mensagem'] : null; // Mensagem de não autoriza��o de transa��o com cartão

        echo "ID do pedido: " .$pedido_id. "<br>";
        echo "ID da transa��o: " .$transacao_id. "<br>";
        echo "Status: " .$status_codigo. " (" .$status_nome. ")<br>";
        if($url_pagamento) { echo "URL pagamento: " .$url_pagamento. "<br>"; }
        if($nao_autorizado_codigo) { echo "Cod. n�o autoriza��o: " .$nao_autorizado_codigo. "<br>"; }
        if($nao_autorizado_mensagem) { echo "Msg. n�o autoriza��o: " .$nao_autorizado_mensagem. "<br>"; }
        
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
        //Se transa��o for TEF ou Boleto imprime na tela
        if($url_pagamento){
            echo "URL de Pgto: " .$url_pagamento;
        }
    }
?>
