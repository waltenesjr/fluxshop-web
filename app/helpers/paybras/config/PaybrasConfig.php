<?php
/*
***********************************
 Arquivo de Configuração - PAYBRAS
 ***********************************
*/

$PaybrasConfig = array();

$PaybrasConfig['ambiente'] = Array();

//Serviço de consulta de status de Transação para ambiente de produção
$PaybrasConfig['ambiente']['status']['producao'] = "https://service.paybras.com/payment/getStatus";
//Serviço de consulta de status de Transação para ambiente de sandbox
$PaybrasConfig['ambiente']['status']['sandbox'] = "https://sandbox.paybras.com/payment/getStatus";
//Serviço de consulta de status de Transação para ambiente local - apenas para desenvolvedores paybras
$PaybrasConfig['ambiente']['status']['local'] = "http://localhost/paybras/payment/getStatus";

//Serviço de consulta de parcelas para ambiente de produção
$PaybrasConfig['ambiente']['parcelas']['producao'] = "https://service.paybras.com/payment/getParcelas";
//Serviço de consulta de parcelas para ambiente de sandbox
$PaybrasConfig['ambiente']['parcelas']['sandbox'] = "https://sandbox.paybras.com/payment/getParcelas";
//Serviço de consulta de parcelas para ambiente local - apenas para desenvolvedores paybras
$PaybrasConfig['ambiente']['parcelas']['local'] = "http://localhost/paybras/payment/getParcelas";

//Serviço de criação de transação para ambiente de produção
$PaybrasConfig['ambiente']['criacao']['producao'] = "https://service.paybras.com/payment/api/criaTransacao";
//Serviço de criação de transação para ambiente de sandbox
$PaybrasConfig['ambiente']['criacao']['sandbox'] = "https://sandbox.paybras.com/payment/api/criaTransacao";
//Serviço de criação de transação para ambiente local
$PaybrasConfig['ambiente']['criacao']['local'] = "http://localhost/paybras/payment/api/criaTransacao";
$PaybrasConfig['lojista'] = Array();

//E-mail do lojista usado como nome de usuário no Paybras
//$PaybrasConfig['lojista']['email'] = "";
//Token do lojista. Encontrada no menu "integração -> Token de segurança" do painel Paybras
//$PaybrasConfig['lojista']['token'] = "";
//Ambiente que se deseja utilizar:
// - sandbox: ambiente de teste; 
// - producao: ambiente de produção; 
// - local: ambiente local (apenas para desenvolvedores paybras)
$PaybrasConfig['lojista']['ambiente'] = "sandbox";


?>