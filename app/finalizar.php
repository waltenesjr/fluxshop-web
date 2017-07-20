<?php
error_reporting( E_ALL );
class Finalizar extends PHPFrodo
{
    public $config = array( );
    public $page_url;
    public $logged = false;
    public $total_compra;
    public $pedido_id = 0;
    public $pedido_total_frete;
    public $pedido_frete;
    public $cliente_id;
    public $cliente_nome;
    public $cliente_email;
    public $fatura_link;
    public $itens_da_fatura;
    public $pedido_endereco;
    public $pedido_entrega;
    public $frete_prazo;

    public function __construct()
    {
        parent:: __construct();
        $sid = new Session;
        $sid->start();
        if ( $sid->check() && $sid->getNode( 'cliente_id' ) >= 1 )
        {
            $this->cliente_email = ( string ) $sid->getNode( 'cliente_email' );
            $this->cliente_id = ( string ) $sid->getNode( 'cliente_id' );
            $this->cliente_nome = ( string ) $sid->getNode( 'cliente_nome' );
            $this->assign( 'cliente_nome', current( explode( ' ', $this->cliente_nome ) ) );
            $this->assign( 'cliente_email', $this->cliente_email );
            $this->assign( 'cliente_msg', 'acesse aqui sua conta.' );
            $this->assign( 'logged', 'true' );
            $this->logged = true;
        }
        else
        {
            $this->assign( 'cliente_nome', 'visitante' );
            $this->assign( 'cliente_msg', 'faça seu login ou cadastre-se.' );
            $this->assign( 'logged', 'false' );
        }
        $this->select()
                ->from( 'config' )
                ->execute();
        if ( $this->result() )
        {
            $this->map( $this->data[0] );
            $this->config = ( object ) $this->data[0];
            $this->assignAll();
        }
        $this->select()->from( 'frete' )->execute();
        $this->map( $this->data[0] );
        //FORCE HTTPS
        /*
          if( $_SERVER['HTTPS'] != "on" ) {
          $redirect = "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
          header("Location:$redirect");
          }
         */
    }

    public function welcome()
    {
        if ( $this->logged == true )
        {
            $this->redirect( "$this->baseUri/finalizar/entrega/" );
        }
        if ( $this->postIsValid( array( 'cliente_cadastrado' => 'string' ) ) )
        {
            $cadastrado = $this->postGetValue( 'cliente_cadastrado' );
            if ( $cadastrado == 'nao' )
            {
                $_SESSION['referer'] = "$this->baseUri/finalizar/";
                $_SESSION['email_cadastro'] = $this->postGetValue( 'cliente_email' );
                $url_retorno = ( string ) $_SESSION['referer'];
                $this->redirect( "$this->baseUri/cliente/cadastro/" );
            }
        }
        $this->tpl( 'public/finalizar_identificacao.html' );
        if ( $this->postIsValid( array( 'cliente_email' => 'email', 'cliente_password' => 'string' ) ) )
        {
            $cliente = new Cliente();
            $cliente->proccess();
            if ( $cliente->login_status == false )
            {
                $msg_login = '<p class="alert alert-danger">';
                $msg_login .= 'Foram encontrados os seguintes problemas: <br>';
                $msg_login .= $cliente->message_login;
                $msg_login .= '</p>';
                $this->assign( 'message_login', "$msg_login" );
            }
            else
            {
                $this->redirect( "$this->baseUri/finalizar/entrega/" );
            }
        }
        $this->getMenu();
        $this->render();
    }

    public function entrega()
    {
        $this->getItens();
        if ( $this->logged == true )
        {
            $this->tpl( 'public/finalizar_entrega.html' );
            $this->getMenu();
            $this->assignAll();
            if ( $this->frete_opcoes == 1 )
            {
                $this->getClienteAddr();
                $this->getRetiradaAddr();
            }
            elseif ( $this->frete_opcoes == 2 )
            {
                $this->getClienteAddr();
                $this->assign( 'evt_onload', 'ocultaRetirada()' );
            }
            elseif ( $this->frete_opcoes == 3 )
            {
                $this->getRetiradaAddr();
                $this->assign( 'evt_onload', 'ocultaEntrega()' );
            }
            $this->render();
        }
        else
        {
            $this->redirect( "$this->baseUri/finalizar/" );
        }
    }

    public function pagamento()
    {
        if ( isset( $_SESSION['finaliza-pagamento'] ) )
        {
            unset( $_SESSION['finaliza-pagamento'] );
        }
        if ( $this->logged == true )
        {
            $_SESSION['finaliza-entrega'] = $_POST;
            $this->tpl( 'public/finalizar_pagamento.html' );
            $this->select()->from( 'pay' )->execute();
            if ( $this->result() )
            {
                $disableMod = '';
                foreach ( $this->data as $k => $v )
                {
                    if ( $this->data[$k]['pay_status'] == '2' )
                    {
                        $mod = $this->data[$k]['pay_name'];
                        $disableMod .= "oculta$mod();\n";
                    }
                }
                $this->assign( 'disableMod', $disableMod );
            }
            $this->getMenu();
            $this->render();
        }
        else
        {
            $this->redirect( "$this->baseUri/finalizar/" );
        }
    }

    public function confirmar()
    {
        $r = new Route;
        $r->set( "FINALIZAR" );

        if ( $this->logged == true )
        {
            if ( isset( $_SESSION['finaliza-entrega']['entrega_selecionada_tipo'] ) )
            {
                if ( $_SESSION['finaliza-entrega']['entrega_selecionada_tipo'] == 1 )
                {
                    $_SESSION['mycep'] = ( string ) $_SESSION['finaliza-entrega']['entrega_selecionada_id'];
                }
                else
                {
                    $_SESSION['mycep_frete'] = "0";
                    $_SESSION['mycep_prazo'] = "Retirada no local";
                    $_SESSION['mycep_tipo_frete'] = "";
                }
                $_SESSION['mycep_entrega'] = ( string ) $_SESSION['finaliza-entrega']['entrega_selecionada'];
            }
            else
            {
                $this->redirect( "$this->baseUri/finalizar/entrega/" );
            }

            $this->local_entrega = "";
            if ( isset( $_POST['pagamento'] ) )
            {
                $_SESSION['finaliza-pagamento'] = ( string ) $_POST['pagamento'];
            }
            if ( !isset( $_SESSION['finaliza-pagamento'] ) )
            {
                $this->redirect( "$this->baseUri/finalizar/entrega/" );
            }
            $this->pay_gw = $_SESSION['finaliza-pagamento'];
            global $btn_popup;
            $btn_popup = false;
            if ( $this->pay_gw == 'paybras' )
            {
                $btn_popup = true;
            }
            $this->assign( 'pay_gw_url', "$this->baseUri/finalizar/checkout/" );
            $this->tpl( 'public/finalizar_confirmar.html' );

            if ( isset( $_SESSION['mycep_frete'] ) )
            {
                $frete_valor = $this->_money( $_SESSION['mycep_frete'] );
                $frete_prazo = ( string ) $_SESSION['mycep_prazo'] . "<br />" . $_SESSION['mycep_tipo_frete'];
                $local_entrega = ( string ) $_SESSION['finaliza-entrega']['entrega_selecionada_desc'];
                //( $frete_valor <= 0 ) ? $frete_valor = '<b>Grátis</b>' : $frete_valor = "R$  $frete_valor ";
                ( $frete_valor <= 0 ) ? $frete_valor = '<b></b>' : $frete_valor = "R$  $frete_valor ";
                $this->assign( 'frete_valor', $frete_valor );
                $this->assign( 'frete_prazo', $frete_prazo );
                $this->assign( 'local_entrega', $local_entrega );
            }
            if ( isset( $_SESSION['cupom']['alfa'] ) )
            {
                $this->assign( 'cupom_alfa', $_SESSION['cupom']['alfa'] );
            }
            $this->getCarrinho();
            $this->getMenu();
            $this->render();
        }
        else
        {
            $this->redirect( "$this->baseUri/finalizar/" );
        }
    }

    public function checkout()
    {
        $this->incluirPedido();
    }

    public function incluirPedido()
    {
        //recupera valor total do pedido
        if ( !isset( $_SESSION['finaliza-entrega'] ) || !isset( $_SESSION['mycep_entrega'] ) )
        {
            $this->redirect( "$this->baseUri/finalizar/" );
        }
        $cart = new Carrinho;
        $cart->getTotal();

        $this->pedido_cupom_desconto = 0;
        $this->pedido_cupom_alfa = $cart->cupom_alfa;
        $this->pedido_cupom_info = $cart->cupom_desconto_info;
        if ( $cart->valor_desconto >= 1 )
        {
            $this->pedido_cupom_desconto = $cart->valor_desconto;
        }

        $this->pedido_entrega = ( string ) $_SESSION['finaliza-entrega']['entrega_selecionada_tipo'];
        $this->pedido_endereco = ( string ) $_SESSION['mycep_entrega'];
        $this->prazo_frete = ( string ) $_SESSION['mycep_prazo'] . " - " . $_SESSION['mycep_tipo_frete'] . " ";
        $this->valor_frete = ( $cart->valor_frete );
        //$this->pedido_tipo_frete = $_SESSION['mycep_tipo_frete'];

        $this->pedido_total_frete = ( $cart->total_com_frete -  $this->pedido_cupom_desconto );
        $this->pedido_total_produto = ( $cart->total_produtos );
	/*
          $k = array(
          'pedido_total_produto',
          'pedido_cupom_desconto',
	  'pedido_sub_total',
          'pedido_frete',
          'pedido_total_frete'
          );
          $y = array(
          "$this->pedido_total_produto",
          "$this->pedido_cupom_desconto",
	   ($this->pedido_total_produto - $this->pedido_cupom_desconto),
          "$this->valor_frete",
          "$this->pedido_total_frete"
          );
          $this->printr( array_combine( $k, $y) );exit;
	*/
      

        //insere pedido               
        $f = array(
            'pedido_cliente',
            'pedido_data',
            'pedido_total_produto',
            'pedido_total_frete',
            'pedido_frete',
            'pedido_prazo',
            'pedido_entrega',
            'pedido_endereco',
            'pedido_cupom_desconto',
            'pedido_cupom_alfa',
            'pedido_cupom_info'
        );

        $v = array(
            $this->cliente_id,
            date( 'd/m/Y h:i' ),
            $this->_moneyUS( $this->pedido_total_produto ),
            $this->_moneyUS( $this->pedido_total_frete ),
            $this->_moneyUS( $this->valor_frete ),
            "$this->prazo_frete",
            "$this->pedido_entrega",
            "$this->pedido_endereco",
            $this->_moneyUS( $this->pedido_cupom_desconto ),
            "$this->pedido_cupom_alfa",
            "$this->pedido_cupom_info"
        );

        if ( !isset( $_SESSION['FLUX_PEDIDO_ID'] ) )
        {
            $this->insert( 'pedido' )->fields( $f )->values( $v )->execute();
            $this->pedido_id = mysql_insert_id();
            $_SESSION['FLUX_PEDIDO_ID'] = $this->pedido_id;
        }
        else
        {
            $this->pedido_id = $_SESSION['FLUX_PEDIDO_ID'];
            $this->update( 'pedido' )->set( $f, $v )->where( "pedido_id = $this->pedido_id" )->execute();
        }

        /*
         * TESTE INSERCAO
          $this->select()->from( 'pedido' )->where( "pedido_id = $this->pedido_id" )->execute();
          $this->printr( $this->data );
          $this->printr( $f );
          $this->printr( $v );
          //echo $this->_money(($this->data[0]['pedido_total_produto'] + $this->data[0]['pedido_cupom_desconto'] ) + $this->data[0]['pedido_frete']);
          exit;
         */

        //insere itens do pedido
        $this->itens_da_fatura = "";
        $itens = $_SESSION['cart'];
        sort( $itens );
        foreach ( $itens as $item )
        {
            $i = ( object ) $item;
            $i->item_preco = number_format( $i->item_preco, 2, '.', '' );
            //if ( !isset( $_SESSION['FLUX_PEDIDO_ID'] ) ){
            $f = array( 'lista_pedido', 'lista_item', 'lista_preco', 'lista_title', 'lista_qtde', 'lista_foto', 'lista_atributos', 'lista_atributo_ped' );
            $v = array( "$this->pedido_id", "$i->item_id", "$i->item_preco", "$i->item_title", "$i->item_qtde", "$i->item_foto", "$i->atributos", "$i->atributo_ped" );
            $this->insert( 'lista' )->fields( $f )->values( $v )->execute();
            //}
            //baixa nos atributos
            if (isset($i->atributos) && !empty($i->atributos)) {
                $attrs = explode("|", $i->atributos);
                foreach ($attrs as $attr) {
                    $attr = explode(",", $attr);
                    if (count($attr) >= 2) {
                        $iattr_id = explode("|", $attr[3]);
                        $iattr_id = $iattr_id[0];
                        $iattr_atributo = $attr[2];
                        $cond = "relatrr_atributo = $iattr_atributo AND relatrr_iattr = $iattr_id AND relatrr_item  = $i->item_id";
                        $this->decrement('relatrr', 'relatrr_qtde', $i->item_qtde, "$cond");
                    }
                }
            }
            //baixa no estoque
            $this->decrement( 'item', 'item_estoque', $i->item_qtde, "item_id = $i->item_id" );

            $i->item_qtde_preco = $i->item_qtde * $i->item_preco;
            $this->itens_da_fatura .= "Item: $i->item_title $i->atributo_ped <br/> Qtde: $i->item_qtde <br />Valor: R$ $i->item_preco <br/>  <br />";
        }

        $this->local_entrega = ( string ) $_SESSION['finaliza-entrega']['entrega_selecionada_desc'];
        if ( isset( $_SESSION['finaliza-pagamento'] ) )
        {
            if ( $_SESSION['finaliza-pagamento'] == 'pagseguro' )
            {
                //inclui fatura pagSeguro
                $this->incluirFaturaPagSeguro();
            }
            if ( $_SESSION['finaliza-pagamento'] == 'paypal' )
            {
                //inclui fatura paypal
                $this->incluirFaturaPayPal();
            }
            if ( $_SESSION['finaliza-pagamento'] == 'paybras' )
            {
                //inclui fatura paybras
                $this->incluirFaturaPayBras();
            }
        }
        else
        {
            $this->redirect( "$this->baseUri/finalizar/" );
        }
    }

    public function incluirFaturaPayPal()
    {
        $this->select()->from( 'pay' )->where( 'pay_name = "PayPal"' )->execute();
        if ( !$this->result() )
        {
            echo 'Módulo PayPal não configurado';
            exit;
        }
        $this->map( $this->data[0] );
        $this->helper( 'paypal' );
        if ( $this->pedido_id >= 1 )
        {
            $this->select()
                    ->from( 'cliente' )
                    ->join( 'endereco', 'endereco_cliente = cliente_id', 'INNER' )
                    ->where( "cliente_id = $this->cliente_id and endereco_tipo = 1" )
                    ->execute();
            $this->encode( 'endereco_uf', 'strtoupper' );
            $this->map( $this->data[0] );
            $this->cliente_telefone = preg_replace( '/\W/', '', $this->cliente_telefone );
            $this->cliente_ddd = substr( $this->cliente_telefone, 0, 2 );
            $this->cliente_telefone = substr( $this->cliente_telefone, 2, -1 );
            $this->select()
                    ->from( 'pedido' )
                    ->where( "pedido_cliente = $this->cliente_id AND pedido_id = $this->pedido_id" )
                    ->execute();
            if ( $this->result() )
            {
                $this->map( $this->data[0] );
                $pedidos = $this->data;
                $requestParams = array(
                    'RETURNURL' => "$this->baseUri/notificacao/retornoPayPal/",
                    'CANCELURL' => "$this->baseUri/notificacao/canceladoPayPal/",
                    'LOCALECODE' => 'pt_BR'
                );
                //$this->total_sem_frete = number_format( $this->total_compra - $this->valor_frete, 2, '.', ',' );
                if ( $this->pedido_frete <= 0 )
                {
                    $this->pedido_frete = "0.00";
                    $this->valor_frete = "0.00";
                }
                $this->total_sem_frete = $this->total_compra - $this->pedido_frete;
                $orderParams = array(
                    'PAYMENTREQUEST_0_AMT' => $this->total_sem_frete + $this->pedido_frete,
                    'PAYMENTREQUEST_0_ITEMAMT' => $this->total_sem_frete,
                    //'PAYMENTREQUEST_0_HANDLINGAMT' => 2,
                    //'PAYMENTREQUEST_0_INSURANCEAMT' => 'valorSeguro',
                    //'PAYMENTREQUEST_0_SHIPDISCAMT' => 'descontoFrete',
                    //'PAYMENTREQUEST_0_TAXAMT' => 'valorImposto',
                    'PAYMENTREQUEST_0_SHIPPINGAMT' => $this->valor_frete,
                    'PAYMENTREQUEST_0_CURRENCYCODE' => 'GBP',
                    'PAYMENTREQUEST_0_CURRENCYCODE' => 'BRL',
                    'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',
                    'USERACTION' => 'COMMIT'
                );
                //start requisicao
                foreach ( $pedidos as $ped )
                {
                    $this->select()
                            ->from( 'lista' )
                            ->where( "lista_pedido = $this->pedido_id" )
                            ->execute();
                    if ( $this->result() )
                    {
                        $this->cut( 'lista_title', 30, '...' );
                        $itens = $this->data;
                        $itemList = array( );
                        $k = 0;
                        foreach ( $itens as $i )
                        {
                            $this->map( $i );
                            $itemList["L_PAYMENTREQUEST_0_NAME$k"] = utf8_encode( $this->lista_title );
                            $itemList["L_PAYMENTREQUEST_0_DESC$k"] = "$this->lista_atributo_ped";
                            $itemList["L_PAYMENTREQUEST_0_AMT$k"] = "$this->lista_preco";
                            $itemList["L_PAYMENTREQUEST_0_QTY$k"] = "$this->lista_qtde";
                            $k++;
                        }
                    }
                }
                $credential = array( 'PWD' => "$this->pay_pass", 'USER' => "$this->pay_user", 'SIGNATURE' => "$this->pay_key" );
                $paypal = new Paypal();
                $paypal->setCredentials( $credential );
                $response = $paypal->request( 'SetExpressCheckout', $requestParams + $orderParams + $itemList );
                if ( is_array( $response ) && $response['ACK'] == 'Success' )
                {
                    $token = $response['TOKEN'];
                    $this->url_code = urlencode( $token );
                    $this->url = 'https://www.paypal.com/webscr?cmd=_express-checkout&useraction=commit&token=' . urlencode( $token );

                    //atualiza cupom
                    $this->cupom_alfa = "";
                    $this->cupom_desconto = "";
                    if ( isset( $_SESSION['cupom']['desconto'] ) )
                    {
                        $this->cupom_desconto = $_SESSION['cupom']['desconto'];
                        $this->cupom_alfa = $_SESSION['cupom']['alfa'];
                        $this->cupom_update = date( 'd/m/Y H:i:s' );

                        $f = array( 'cupom_status', 'cupom_pedido', 'cupom_update' );
                        $v = array( 1, $this->pedido_id, $this->cupom_update );
                        $this->update( 'cupom' )->set( $f, $v )->where( "cupom_alfa = '$this->cupom_alfa'" )->execute();
                    }
                    //atualiza pedido com url 
                    $this->update( 'pedido' )
                            ->set( array( 'pedido_pay_code', 'pedido_pay_url', 'pedido_pay_gw', 'pedido_cupom_alfa', 'pedido_cupom_desconto' ), array( $this->url_code, $this->url, 2, $this->cupom_alfa, $this->cupom_desconto ) )
                            ->where( "pedido_id = $this->pedido_id" )
                            ->execute();
                    $this->fatura_link = "$this->url";
                    //Notifica Cliente / Admin
                    $this->notificarFaturaCliente();
                    $this->notificarAdmin();
                    $this->clear();
                    $this->redirect( "$this->baseUri/cliente/pedido/$this->pedido_id/show/" );
                }
                else
                {
                    //$this->printr( $response );
                    //notificaClienteError, notificaAdminError
                }
            }
        }
    }

    public function incluirFaturaPagSeguro()
    {
        $this->select()->from( 'pay' )->where( 'pay_name = "PagSeguro"' )->execute();
        $this->map( $this->data[0] );
        $this->helper( 'pagseguro' );
        if ( $this->pedido_id >= 1 )
        {
            $this->select()
                    ->from( 'cliente' )
                    ->join( 'endereco', 'endereco_cliente = cliente_id', 'INNER' )
                    ->where( "cliente_id = $this->cliente_id and endereco_tipo = 1" )
                    ->execute();
            $this->encode( 'endereco_uf', 'strtoupper' );
            $this->map( $this->data[0] );
            $this->cliente_telefone = preg_replace( '/\W/', '', $this->cliente_telefone );
            $this->cliente_ddd = substr( $this->cliente_telefone, 0, 2 );
            $this->cliente_telefone = substr( $this->cliente_telefone, 2, -1 );
            $this->select()
                    ->from( 'pedido' )
                    ->where( "pedido_cliente = $this->cliente_id AND pedido_id = $this->pedido_id" )
                    ->execute();
            if ( $this->result() )
            {
                $this->map( $this->data[0] );
                $pedidos = $this->data;
                //start req
                $this->pReq = new PagSeguroPaymentRequest();
                $this->pReq->setCurrency( "BRL" );
                //add Itens to req
                foreach ( $pedidos as $ped )
                {
                    $this->select()
                            ->from( 'lista' )
                            ->where( "lista_pedido = $this->pedido_id" )
                            ->execute();
                    if ( $this->result() )
                    {
                        $this->cut( 'lista_title', 60, '...' );
                        $itens = $this->data;
                        foreach ( $itens as $i )
                        {
                            $this->map( $i );
                            $this->lista_preco = preg_replace( '/\,/', '', $this->lista_preco );
                            $this->pReq->addItem( $this->lista_item, "$this->lista_title - $this->lista_atributo_ped", $this->lista_qtde, $this->lista_preco );
                        }
                    }
                }
                //atualiza cupom
                if ( $this->pedido_cupom_desconto != 0 )
                {
                    $this->pedido_cupom_desconto = $this->_moneyUS( $this->pedido_cupom_desconto );
                    //Atualiza cupom como usado
                    $this->cupom_update = date( 'd/m/Y H:i:s' );
                    $this->cupom_alfa = $_SESSION['cupom']['alfa'];
                    $f = array( 'cupom_status', 'cupom_pedido', 'cupom_update' );
                    $v = array( 1, $this->pedido_id, $this->cupom_update );
                    $this->update( 'cupom' )->set( $f, $v )->where( "cupom_alfa = '$this->cupom_alfa'" )->execute();
                    $this->pReq->setExtraAmount( -$this->pedido_cupom_desconto );
                }
                if ( $this->pedido_frete <= 0 )
                {
                    $this->pedido_frete = "0.00";
                    $this->valor_frete = "0.00";
                }
                $this->pReq->setReference( "$this->pedido_id" );
                //frete
                $shipping = new PagSeguroShipping();
                $type = new PagSeguroShippingType( $this->pedido_tipo_frete );
                $shipping->setType( $type );
                $shipping->setCost( $this->pedido_frete );
                $address = new PagSeguroAddress( array(
                            $this->endereco_cep,
                            $this->endereco_rua,
                            $this->endereco_num,
                            $this->endereco_complemento,
                            $this->endereco_bairro,
                            $this->endereco_cidade,
                            $this->endereco_uf,
                            'BRA' ) );
                $shipping->setAddress( $address );
                $this->pReq->setShipping( $shipping );
                $lastname = explode( " ", $this->cliente_nome );
                if ( !isset( $lastname[1] ) )
                {
                    $this->cliente_nome = "$this->cliente_nome .";
                }
                $this->pReq->setSender( $this->cliente_nome, $this->cliente_email, $this->cliente_ddd, $this->cliente_telefone );
                //$this->pReq->setRedirectUrl( "$this->pay_url_redir" ); 
                //registrando no pagseguro
                try
                {
                    $credentials = new PagSeguroAccountCredentials( "$this->pay_user", "$this->pay_key" );
                    $this->url = $this->pReq->register( $credentials );
                    $this->url_code = explode( '=', $this->url );
                    $this->url_code = trim( $this->url_code[1] );
                }
                catch ( PagSeguroServiceException $e )
                {
                    $this->_rollback();
                    die( $e->getMessage() );
                }
                //retorno da req
                //atualiza pedido com url e codigo pagseguro
                $this->update( 'pedido' )
                        ->set( array( 'pedido_pay_code', 'pedido_pay_url', 'pedido_pay_gw' ), array( $this->url_code, $this->url, 1 ) )
                        ->where( "pedido_id = $this->pedido_id" )
                        ->execute();
                $this->fatura_link = "$this->url";
                //Notifica Cliente / Admin
                $this->notificarAdmin();
                $this->notificarFaturaCliente();
                $this->clear();
                $this->redirect( "$this->baseUri/cliente/pedido/$this->pedido_id/show/" );
            }
        }
    }

    public function incluirFaturaPayBras()
    {
        $this->select()->from( 'pay' )->where( 'pay_name = "PayBras"' )->execute();
        $this->map( $this->data[0] );

        if ( $this->pedido_id >= 1 )
        {
            $this->select()
                    ->from( 'cliente' )
                    ->join( 'endereco', 'endereco_cliente = cliente_id', 'INNER' )
                    ->where( "cliente_id = $this->cliente_id and endereco_tipo = 1" )
                    ->execute();
            $this->encode( 'endereco_uf', 'strtoupper' );
            $this->map( $this->data[0] );

            $this->cliente_telefone = preg_replace( '/\W/', '', $this->cliente_telefone );
            $this->cliente_ddd = substr( $this->cliente_telefone, 0, 2 );
            $this->cliente_telefone = substr( $this->cliente_telefone, 2, 9 );

            $this->cliente_celular = preg_replace( '/\W/', '', $this->cliente_celular );
            $this->cliente_ddd_celular = substr( $this->cliente_celular, 0, 2 );
            $this->cliente_celular = substr( $this->cliente_celular, 2, 9 );

            $this->select()
                    ->from( 'pedido' )
                    ->where( "pedido_cliente = $this->cliente_id AND pedido_id = $this->pedido_id" )
                    ->execute();
            if ( $this->result() )
            {
                $this->map( $this->data[0] );
                $pedidos = $this->data;

                global $pedido;
                global $pagador;
                global $entrega;
                global $produtos;

                foreach ( $pedidos as $ped )
                {
                    $this->select()
                            ->from( 'lista' )
                            ->join( 'item', 'lista_item = item_id', 'INNER' )
                            ->join( 'categoria', 'categoria_id = item_categoria', 'INNER' )
                            ->where( "lista_pedido = $this->pedido_id" )
                            ->execute();
                    if ( $this->result() )
                    {
                        $this->cut( 'lista_title', 60, '...' );
                        $itens = $this->data;
                        $produtos = array( );
                        foreach ( $itens as $i )
                        {
                            $this->map( $i );
                            $this->lista_preco = preg_replace( '/\,/', '', $this->lista_preco );
                            $produtos[] = array(
                                'produto_codigo' => "$this->lista_item",
                                'produto_nome' => "$this->lista_title - $this->lista_atributo_ped",
                                'produto_categoria' => "$this->categoria_title",
                                'produto_qtd' => "$this->lista_qtde",
                                'produto_valor' => "$this->lista_preco",
                                'produto_peso' => "$this->item_peso",
                            );
                        }
                    }
                }
                //echo $this->pedido_frete;
                if ( $this->pedido_frete <= 0 )
                {
                    $this->pedido_frete = "0.00";
                    $this->valor_frete = "0.00";
                }
                $entrega = array(
                    'entrega_nome' => $this->cliente_nome,
                    'entrega_logradouro' => $this->endereco_rua,
                    'entrega_numero' => $this->endereco_num,
                    'entrega_complemento' => $this->endereco_complemento,
                    'entrega_bairro' => $this->endereco_bairro,
                    'entrega_cep' => $this->endereco_cep,
                    'entrega_cidade' => $this->endereco_cidade,
                    'entrega_estado' => $this->endereco_uf,
                    'entrega_pais' => 'BRA'
                );
                ($this->cliente_sexo == 1) ? $this->cliente_sexo = "M" : $this->cliente_sexo = "F";
                $pagador = array(
                    'pagador_logradouro' => "",
                    'pagador_numero' => "",
                    'pagador_bairro' => "",
                    'pagador_cep' => "",
                    'pagador_cidade' => "",
                    'pagador_estado' => "",
                    'pagador_pais' => "BRA",
                    'pagador_nome' => $this->cliente_nome,
                    'pagador_email' => $this->cliente_email,
                    'pagador_cpf' => $this->cliente_cpf,
                    'pagador_rg' => "",
                    'pagador_telefone_ddd' => $this->cliente_ddd,
                    'pagador_telefone' => $this->cliente_telefone,
                    'pagador_celular_ddd' => $this->cliente_ddd_celular,
                    'pagador_celular' => $this->cliente_celular,
                    'pagador_sexo' => $this->cliente_sexo,
                    'pagador_data_nascimento' => $this->cliente_datan
                );
                $pedido = array(
                    'pedido_id' => $this->pedido_id,
                    'pedido_valor_total_original' => ($this->pedido_total_produto - $this->pedido_cupom_desconto) + $this->_double( $this->pedido_frete ),
                    'pedido_descricao' => "",
                    'pedido_moeda' => "BRL",
                    'pedido_url_redirecionamento' => "",
                        //'pedido_meio_pagamento' => "", 
                );
                $this->helper( 'paybras' );
                _payBrasCheckOut();
            }
        }
    }

    public function paybrasAuth()
    {
        $this->pedido_id = $_POST['pedido_id'];
        $this->select()->from( 'pay' )->where( 'pay_name = "PayBras"' )->execute();
        if ( !$this->result() )
        {
            echo 'Módulo PayBras não configurado';
            exit;
        }
        $this->helper( 'paybras' );
        _payBrasGetTrans();
        $dados_logista['email'] = $this->data[0]['pay_key'];
        $dados_logista['token'] = $this->data[0]['pay_user'];

        $meio_de_pagamento = $_POST['pedido_meio_pagamento'];
        $retorno = PaybrasCriaTransacao::main( $_POST, $dados_logista );
        if ( isset( $retorno['sucesso'] ) )
        {
            $sucesso = $retorno['sucesso']; // 0: erro; 1: sucesso
        }
        else
        {
            $this->printr( $retorno );
            $this->_rollback();
            die;
        }
        if ( !$sucesso )
        {
            $mensagem_erro = $retorno['mensagem_erro'];
            foreach ( $mensagem_erro as $key => $value )
            {
                echo "<br/>" . $value;
            }
            $this->_rollback();
        }
        else
        {
            $this->pedido_id = $_POST['pedido_id']; //ID do pedido
            $this->pay_code = $retorno['transacao_id']; //ID da transacoo
            $this->pay_status = $retorno['status_codigo'];
            $status_nome = $retorno['status_nome']; // Nome do status da transação

            $this->pay_url = isset( $retorno['url_pagamento'] ) ? $retorno['url_pagamento'] : null; // URL para pagamento de boleto ou TEF
            $this->fatura_link = "$this->pay_url";
            $nao_autorizado_codigo = isset( $retorno['nao_autorizado_codigo'] ) ? $retorno['nao_autorizado_codigo'] : null; // não autorização de transação cartão
            $nao_autorizado_mensagem = isset( $retorno['nao_autorizado_mensagem'] ) ? $retorno['nao_autorizado_mensagem'] : null; // não autorização de transação com cartão

            $this->pay_obs = "";
            if ( $nao_autorizado_codigo )
            {
                $this->pay_obs .= "Cod. não autorização:  $nao_autorizado_codigo \n";
            }
            if ( $nao_autorizado_mensagem )
            {
                $this->pay_obs .= "Msg. não autorização: $nao_autorizado_mensagem";
            }

            //atualiza cupom
            $this->cupom_alfa = "";
            $this->cupom_desconto = "";
            if ( isset( $_SESSION['cupom']['desconto'] ) )
            {
                $this->cupom_desconto = $_SESSION['cupom']['desconto'];
                $this->cupom_alfa = $_SESSION['cupom']['alfa'];
                $this->cupom_update = date( 'd/m/Y H:i:s' );
                $f = array( 'cupom_status', 'cupom_pedido', 'cupom_update' );
                $v = array( 1, $this->pedido_id, $this->cupom_update );
                $this->update( 'cupom' )->set( $f, $v )->where( "cupom_alfa = '$this->cupom_alfa'" )->execute();
            }
            // Código de status da transação:
            //  1: Ag. Pgto
            //  2: Em Análise
            //  3: Não Autorizado
            //  4: Aprovado
            //  5: Recusado
            //atualiza pedido com url e codigo paybras                                  
            $this->update( 'pedido' )
                    ->set( array( 'pedido_pay_code', 'pedido_pay_url', 'pedido_status', 'pedido_pay_gw', 'pedido_pay_obs', 'pedido_cupom_alfa', 'pedido_cupom_desconto' ), array( $this->pay_code, $this->pay_url, $this->pay_status, 3, $this->pay_obs, $this->cupom_alfa, $this->cupom_desconto ) )
                    ->where( "pedido_id = $this->pedido_id" )->execute();
            $this->valor_frete = ( string ) $_SESSION['mycep_frete'];
            $this->local_entrega = ( string ) $_SESSION['finaliza-entrega']['entrega_selecionada_desc'];

            //insere itens do pedido
            $this->itens_da_fatura = "";
            $itens = $_SESSION['cart'];
            sort( $itens );
            foreach ( $itens as $item )
            {
                $i = ( object ) $item;
                $i->item_preco = number_format( $i->item_preco, 2, '.', '' );
                $i->item_qtde_preco = $i->item_qtde * $i->item_preco;
                $this->itens_da_fatura .= "Item: $i->item_title $i->atributo_ped <br/> Qtde: $i->item_qtde <br />Valor: R$ $i->item_preco <br/>  <br />";
            }

            $cart = new Carrinho;
            $cart->getTotal();
            $this->total_compra = $this->val2bd( $cart->total_compra );
            //Notifica Cliente / Admin
            $this->notificarAdmin();
            $this->notificarFaturaCliente();
            $this->clear();
            $url_redir = "$this->baseUri/cliente/pedido/$this->pedido_id/";
            if ( $meio_de_pagamento != 'cartao' )
            {
                $url_redir = "$this->baseUri/cliente/pedido/$this->pedido_id/show/";
            }
            echo "<script>";
            echo " parent.window.location = '$url_redir' ";
            echo "</script>";
        }
    }

    public function notificarAdmin()
    {
        $body = '<html><body>';
        $body .= '<h1 style="font-size:15px;">Novo Pedido</h1>';
        $body .= '<table style="border-color: #666; font-size:11px" cellpadding="10">';
        $body .= '<tr style="background: #30ADE7;"><td style="color:#fff"><strong>Pedido ID:</strong> </td><td style="color:#fff">' . $this->pedido_id . '</td></tr>';
        $body .= '<tr style="background: #fff;"><td><strong>Data:</strong> </td><td>' . date( 'd/m/Y h:s' ) . '</td></tr>';
        $body .= '<tr style="background: #30ADE7;"><td style="color:#fff"><strong>Cliente:</strong> </td><td style="color:#fff">' . $this->cliente_nome . '</td></tr>';
        $body .= '<tr style="background: #fff;"><td><strong>Email:</strong> </td><td >' . $this->cliente_email . '</td></tr>';
        $body .= '<tr style="background: #30ADE7;"><td style="color:#fff"><strong>Local de entrega:</strong> </td><td style="color:#fff">' . $this->local_entrega . '</td></tr>';
        $body .= '<tr style="background: #fff;"><td><strong>Itens:</strong> </td><td>' . $this->itens_da_fatura . '</td></tr>';
        $body .= '<tr style="background: #30ADE7;"><td style="color:#fff"><strong>Frete:</strong> </td><td style="color:#fff">' . $this->_money( $this->valor_frete ) . " - $this->prazo_frete " . '</td></tr>';
        if ( $this->pedido_cupom_desconto != 0 )
        {
            $body .= '<tr style="background: #fff;"><td><strong>Subtotal:</strong> </td><td>' . $this->_money( $this->pedido_total_produto + $this->valor_frete ) . '</td></tr>';
            $body .= '<tr style="background: #30ADE7;"><td style="color:#fff"><strong>Desconto:</strong> </td><td style="color:#fff"> ' . $this->_money( $this->pedido_cupom_desconto ) . '</td></tr>';
            $body .= '<tr style="background: #fff;"><td><strong>Valor Total:</strong> </td><td>' . $this->_money( $this->pedido_total_frete ) . '</td></tr>';
        }
        else
        {
            $body .= '<tr style="background: #fff;"><td><strong>Total a Pagar:</strong> </td><td>' . $this->_money( $this->pedido_total_frete ) . '</td></tr>';
        }
        $body .= '</table>';
        $body .= '</body></html>';
        $n = array(
            'subject' => "Novo Pedido Nº $this->pedido_id",
            'body' => $body
        );
        $m = new sendmail;
        $m->sender( $n );
    }

    public function notificarFaturaCliente()
    {
        $body = '<html><body>';
        $body .= '<h1 style="font-size:15px;">Olá ' . $this->cliente_nome . ', recebemos seu pedido #' . $this->pedido_id . '</h1>';
        $body .= '<table style="border-color: #666; font-size:11px" cellpadding="10">';
        $body .= '<tr style="background: #30ADE7;"><td style="color:#fff"><strong>Pedido ID:</strong> </td><td style="color:#fff">' . $this->pedido_id . '</td></tr>';
        $body .= '<tr style="background: #fff;"><td><strong>Data:</strong> </td><td>' . date( 'd/m/Y h:s' ) . '</td></tr>';
        $body .= '<tr style="background: #30ADE7;"><td style="color:#fff"><strong>Cliente:</strong> </td><td style="color:#fff">' . $this->cliente_nome . '</td></tr>';
        $body .= '<tr style="background: #fff;"><td><strong>Email:</strong> </td><td>' . $this->cliente_email . '</td></tr>';
        $body .= '<tr style="background: #30ADE7;"><td style="color:#fff"><strong>Local de entrega:</strong> </td><td style="color:#fff">' . $this->local_entrega . '</td></tr>';
        $body .= '<tr style="background: #fff;"><td><strong>Itens:</strong> </td><td>' . $this->itens_da_fatura . '</td></tr>';
        $body .= '<tr style="background: #30ADE7;"><td style="color:#fff"><strong>Frete:</strong> </td><td style="color:#fff">' . $this->_money( $this->valor_frete ) . " - $this->prazo_frete " . '</td></tr>';
        if ( $this->pedido_cupom_desconto != 0 )
        {
            $body .= '<tr style="background: #fff;"><td><strong>Subtotal:</strong> </td><td>' . $this->_money( $this->pedido_total_produto + $this->valor_frete ) . " - $this->prazo_frete dias " .'</td></tr>';
            $body .= '<tr style="background: #30ADE7;"><td style="color:#fff"><strong>Desconto:</strong> </td><td style="color:#fff"> ' . $this->_money( $this->pedido_cupom_desconto ) . '</td></tr>';
            $body .= '<tr style="background: #fff;"><td><strong>Valor Total:</strong> </td><td>' . $this->_money( $this->pedido_total_frete ) . '</td></tr>';
        }
        else
        {
            $body .= '<tr style="background: #fff;"><td><strong>Total a Pagar:</strong> </td><td>' . $this->_money( $this->pedido_total_frete ) . '</td></tr>';
        }
        $body .= '<br/><br/>';
        $body .= "<a href=\"$this->baseUri/cliente/pedido/$this->pedido_id/\">Acompanhe o status de seu pedido em nosso site.</a>";
        $body .= '</body></html>';
        $n = array(
            'email' => $this->cliente_email,
            'subject' => "Fatura do pedido $this->pedido_id",
            'body' => $body
        );
        $m = new sendmail;
        $m->sender( $n );
    }

    public function clear()
    {
        $_SESSION['cart'] = null;
        unset( $_SESSION['cart'] );

        $_SESSION['mycep_prazo'] = null;
        unset( $_SESSION['mycep_prazo'] );

        $_SESSION['mycep_frete'] = null;
        unset( $_SESSION['mycep_frete'] );

        $_SESSION['mycep_entrega'] = null;
        unset( $_SESSION['mycep_entrega'] );

        $_SESSION['mycep'] = null;
        unset( $_SESSION['mycep'] );

        $_SESSION['referer'] = null;
        unset( $_SESSION['referer'] );

        $_SESSION['finaliza-pagamento'] = null;
        unset( $_SESSION['finaliza-pagamento'] );

        $_SESSION['finaliza-entrega'] = null;
        unset( $_SESSION['finaliza-entrega'] );

        $_SESSION['cupom'] = null;
        unset( $_SESSION['cupom'] );

        $_SESSION['FLUX_PEDIDO_ID'] = null;
        unset( $_SESSION['FLUX_PEDIDO_ID'] );
    }

    public function novoendereco()
    {
        $_SESSION['referer'] = "$this->baseUri/finalizar/entrega/";
        $this->redirect( "$this->baseUri/cliente/enderecoNovo/" );
    }

    public function getClienteAddr()
    {
        $this->select()
                ->from( 'endereco' )
                ->where( "endereco_cliente = $this->cliente_id" )
                ->orderby( 'endereco_title asc' )
                ->execute();
        if ( $this->result() )
        {
            $this->fetch( 'addr', $this->data );
        }
    }

    //retorna enderecos de retirada
    public function getRetiradaAddr()
    {
        $this->select()
                ->from( 'retirada' )
                ->orderby( 'retirada_local asc' )
                ->execute();
        if ( $this->result() )
        {
            foreach ( $this->data as $k => $v )
            {
                if ( strlen( $this->data[$k]['retirada_complemento'] ) >= 2 )
                {
                    $this->data[$k]['retirada_num'] = $this->data[$k]['retirada_num'] . ", " . $this->data[$k]['retirada_complemento'];
                }
            }
            $this->fetch( 'raddr', $this->data );
        }
        else
        {
            $this->assign( 'evt_onload', 'ocultaRetirada()' );
        }
    }

    public function getMenu()
    {
        $this->menu = new Menu;
        $this->fetch( 'f', $this->menu->getFooter() );
        $this->fetch( 'cat', current( $this->menu->getMenuDepto() ) );
        $this->fetch( 'depto', end( $this->menu->getMenuDepto() ) );
        $this->fetch( 'depto-full', end( $this->menu->getMenuDepto() ) );
    }

    public function getItens()
    {
        $cart = new Carrinho;
        $cart->getTotal();
        $this->qtde_item = count( $_SESSION['cart'] );
        if ( $this->qtde_item <= 0 )
        {
            $this->redirect( "$this->baseUri/carrinho/" );
        }
    }

    //em casos de erro no gateway e refresh na conclusão, evita a duplicação de itens no pedido
    public function _rollback()
    {
        //remove pedido
        $this->delete()->from( 'pedido' )->where( "pedido_id = $this->pedido_id" )->execute();
        if ( isset( $_SESSION['FLUX_PEDIDO_ID'] ) )
        {
            unset( $_SESSION['FLUX_PEDIDO_ID'] );
        }
        //reverte cupom
        if ( isset( $_SESSION['cupom']['id'] ) )
        {
            $this->cupom_id = $_SESSION['cupom']['id'];
            $f = array( 'cupom_status', 'cupom_pedido', 'cupom_update' );
            $v = array( 0, 0, '' );
            $this->update( 'cupom' )->set( $f, $v )->where( "cupom_id = $this->cupom_id" )->execute();
        }
    }

    public function getCarrinho()
    {
        $cart = new Carrinho;
        $cart->getTotal();
        if ( count( $_SESSION['cart'] ) <= 0 )
        {
            $this->redirect( "$this->baseUri/carrinho/" );
        }
        $this->data = $_SESSION['cart'];
        $this->money( 'item_preco' );
        $this->money( 'valor_total' );
        $this->cut( 'item_title', 75, '...' );
        $this->fetch( 'cart', $this->data );

        $this->total_compra = $cart->valor_total;
        $this->assign( 'cartTotal', $cart->valor_total );

        if ( isset( $_SESSION['mycep_frete'] ) )
        {
            $frete_valor = ( string ) $_SESSION['mycep_frete'];
            $frete_prazo = ( string ) $_SESSION['mycep_prazo'];
            $this->assign( 'valor_frete', $frete_valor );
            $this->assign( 'valor_prazo', $frete_prazo );
        }

        $this->assign( 'total_sem_desconto', $this->_money( $cart->total_sem_desconto ) );
        $this->assign( 'total_com_desconto', $this->_money( $cart->total_com_desconto ) );

        $this->assign( 'valor_desconto', $this->_money( $cart->valor_desconto ) );
        $this->assign( 'total_com_frete', $this->_money( $cart->total_com_frete ) );

        $this->assign( 'cupom_desconto_info', $cart->cupom_desconto_info );
        $this->assign( 'cupom_msg', $cart->cupom_msg );

        if ( $cart->cupom_desconto > 1 )
        {
            $this->assign( 'valor_total', $this->_money( $cart->valor_total ) );
            $this->assign( 'desconto_ext', $cart->cupom_desconto_ext );
            $this->assign( 'btn-cupom-valida', 'hide' );
        }
        else
        {
            $this->assign( 'btn-cupom-remove', 'hide' );
        }
    }

    public function val2bd( $str )
    {
        $str = preg_replace( '/\./', '', $str );
        $str = preg_replace( '/\,/', '', $str );
        return $str;
    }

    public function _money( $val )
    {
        return @number_format( $val, 2, ",", "." );
    }

    public function _moneyUS( $val )
    {
        return @number_format( $val, 2, ".", "" );
    }

    public function _double( $val )
    {
        return @number_format( $val, 2, ".", "," );
    }

    public function _float( $val )
    {
        return @number_format( $this->val2bd( $val ), 2, ",", "" );
    }
}
/*end file*/
