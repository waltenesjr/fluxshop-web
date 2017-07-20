<?php

if($_POST){
	$data_post = json_decode($_POST['data']);
} else {
	$retorno['retorno'] = 'NOK';
	return json_encode($retorno);
}

$pedido_id = $data_post['pedido_id']; //ID do pedido, armazenado em sua loja;
$transacao_id = $data_post['transacao_id']; // ID da transação, criado pelo Paybras;
$valor_original = $data_post['valor_original']; // Valor original da transação (valor a ser pago pelo pedido com frete);
$status_codigo = $data_post['status_codigo']; // Status atual do pedido (pode ser Aprovado, Recusado ou Devolvido).

if($status_codigo=="4"){ // Aprovado
    //Atualiza tabela de pedidos com novo status
} elseif ($status_codigo=="5") { // Recusado
    //Atualiza tabela de pedidos com novo status
} elseif ($status_codigo=="6") { // Devolvido
    //Atualiza tabela de pedidos com novo status
}

$retorno['retorno'] = 'ok';
return json_encode($retorno);

?>