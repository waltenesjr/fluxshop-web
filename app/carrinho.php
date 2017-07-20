<?php
error_reporting( 0 );
class Carrinho extends PHPFrodo
{
    public $config = array( );
    public $menu = null;
    public $item_categoria = null;
    public $item_sub = null;
    public $item_url = null;
    public $item_id = null;
    public $item = null;
    public $login = null;
    public $cart = array( );
    public $f_foto = null;
    public $total_compra = 0;
    public $total_compra_no_frete = 0;
    public $cliente_id;
    public $cliente_cep = null;
    public $cliente_nome;
    public $cliente_email;
    public $valor_frete = 0;
    public $prazo_frete = 0;
    public $pedido_total_frete;
    public $pedido_id = 0;
    public $last_pedido_id = 0;
    public $fatura_link = 0;
    public $itens_da_fatura;
    public $pedido_endereco;
    public $qtde_item = -1;
    public $estoque = 0;
    public $desconto = 0;
    public $total_com_desconto = 0;
    public $total_sem_desconto = 0;

    public function __destruct()
    {
        
    }

    public function __construct()
    {
        parent:: __construct();
        $this->login = null;
        $sid = new Session;
        $sid->start();
        if ( $sid->check() && $sid->getNode( 'cliente_id' ) >= 1 )
        {
            $this->cliente_email = ( string ) $sid->getNode( 'cliente_email' );
            $this->cliente_id = $sid->getNode( 'cliente_id' );
            $this->cliente_nome = ( string ) $sid->getNode( 'cliente_nome' );
            $this->cliente_cep = $sid->getNode( 'cliente_cep' );
            $this->login = array(
                'cliente_email' => "$this->cliente_email",
                'cliente_nome' => "$this->cliente_nome",
                'cliente_id' => "$this->cliente_id",
            );
            //$this->assign( 'cliente_nome', "&nbsp;$this->cliente_nome" );
            $this->assign( 'cliente_nome', current( explode( ' ', $this->cliente_nome ) ) );
            $this->assign( 'cliente_email', $this->cliente_email );
            $this->assign( 'cliente_cep', $this->cliente_cep );
            $this->assign( 'onload', 'freteReload()' );
            $this->assign( 'cliente_msg', 'acesse aqui sua conta.' );
            $this->assign( 'urlPedido', "$this->baseUri/carrinho/incluirPedido/" );
            $this->assign( 'logged', 'true' );
            $this->assign( 'showHide', 'show' );
        }
        else
        {
            $this->assign( 'showHide', 'hidden' );
            $this->assign( 'cliente_nome', 'visitante' );
            $this->assign( 'cliente_msg', 'faça seu login ou cadastre-se.' );
            $this->assign( 'urlPedido', "$this->baseUri/cliente/login/" );
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
        //mostra meios de pagamento no rodape
        $pay = new Pay;
        $this->view_prepend_data = $pay->getPaysOn();
    }

    public function welcome()
    {
        //set route
        $r = new Route;
        $r->set( "CARRINHO" );
        $this->tpl( 'public/carrinho.html' );
        $this->getLastCep();
        $this->getTotal();
        $this->getCarrinho();
        $this->getMenu();
        $this->render();
    }

    public function getLastCep()
    {
        if ( isset( $_SESSION['mycep'] ) )
        {
            $this->assign( 'mycep', $_SESSION['mycep'] );
        }
        if ( isset( $_SESSION['mycep_frete'] ) )
        {
            if ( $_SESSION['mycep_frete'] == '0.00' )
            {
                $this->assign( 'mycep_frete', 'Frete Grátis' );
            }
            else
            {
                $this->assign( 'mycep_frete', 'R$ ' . preg_replace( '/\./', ',', $_SESSION['mycep_frete'] ) );
            }
        }
    }

    public function getCarrinho()
    {
        if ( isset( $_SESSION['cart'] ) && count( $_SESSION['cart'] ) >= 1 )
        {
            $this->qtde_item = count( $_SESSION['cart'] );
            $this->data = $_SESSION['cart'];
            $this->money( 'item_preco' );
            $this->money( 'valor_total' );
            $this->cut( 'item_title', 75, '...' );
            /*
              foreach ( $this->data as $k => $v )
              {
              //$this->data[$k]['valor_total'] = $this->data[$k]['item_preco'] * $this->data[$k]['item_qtde'];
              }
             */
            $this->fetch( 'cart', $this->data );
            $this->assign( 'showCheckout', '' );
            $this->assign( 'showEmptyCart', 'hidden' );
            $this->assign( 'cartTotal', "R$ " . $this->_money( $this->total_compra ) );
        }
        else
        {
            $this->clear();
            $this->assign( 'msg_carrinho_vazio', 'SEU CARRINHO ESTÁ VAZIO!' );
            $this->assign( 'cartTotal', "R$ 0,00" );
            $this->assign( 'showCheckout', 'hide' );
            $this->assign( 'showEmptyCart', 'showin' );
        }
    }


    public function nCalculo()
    {
        if ( isset( $_SESSION['cart'] ) )
        {
            $this->data = $_SESSION['cart'];
            $this->money( 'item_preco' );
            $this->money( 'valor_total' );
            $this->cut( 'item_title', 90, '...' );
            $calcula = false;
            $max = 0;
            $lid = 0;
            $p = 0;
            $a = 0;
            $l = 0;
            $c = 0;
            $t = 0;
            foreach ( $this->data as $d )
            {
                $max = $d['item_peso'];
                $lid = $d['item_id'];
                $p += $d['item_peso'] * $d['item_qtde'];
		//somar tudo
                //$a += $d['item_altura'];
                //$l += $d['item_largura'];
                //$c +=  $d['item_comprimento'];
                $a = $d['item_altura'];
                $l = $d['item_largura'];
                $c =  $d['item_comprimento'];
                $t = $this->total_compra;
                if ( $d['item_calcula_frete'] == 2 )
                {
                    $calcula = true;
                }
            }
            if ( $calcula == true )
            {
                echo "{\"p\":\"$p\",\"a\":\"$a\",\"l\":\"$l\",\"c\":\"$c\",\"t\":\"$t\",\"cf\":\"sim\"}";
            }
            else
            {
                echo "{\"p\":\"$p\",\"a\":\"$a\",\"l\":\"$l\",\"c\":\"$c\",\"t\":\"$t\",\"cf\":\"nao\"}";
            }
        }
        else
        {
            echo "{\"p\":\"-1\"}";
        }
    }

    public function nFormata()
    {

        if ( isset( $_POST['v1'] ) )
        {
            $_SESSION['mycep_frete'] = $_POST['v1']; //valor frete
            $this->getTotal();
            echo $this->_money( $this->total_com_frete );
        }
        else
        {
            $this->getTotal();
            echo $this->total_compra;
        }
        //prazo entrega
        if ( isset( $_POST['v2'] ) && $_POST['v2'] != "" )
        {
            if ( $_POST['v2'] >= 2 )
            {
                //$_SESSION['mycep_prazo'] = '1 á ' . $_POST['v2'] . ' dias úteis ';
                $_SESSION['mycep_prazo'] =  $_POST['v2'];
            }
            else
            {
                $_SESSION['mycep_prazo'] = '1 dia útil ';
            }
        }
        //tipo entrega
        if ( isset( $_POST['v3'] ) && $_POST['v3'] != "" )
        {
            $_SESSION['mycep_tipo_frete'] = " (" . utf8_decode( $_POST['v3'] ) . ") ";
        }
    }

    public function getEstoque()
    {
        $this->select()
                ->from( 'item' )
                ->where( "item_id = $this->item_id" )
                ->execute();
        if ( $this->result() )
        {
            $this->estoque = $this->data[0]['item_estoque'] + 1;
        }
        else
        {
            $this->estoque = 0;
        }
    }

public function adicionar()
    {
        if ( isset( $this->uri_segment[2] ) )
        {
            $this->item_id = $this->uri_segment[2];
            $strAtrrPed = "";
            $strAttr = "";
            $attr_qtde = 0;

            if ( isset( $_POST['attr'] ) && !empty( $_POST['attr'] ) )
            {
                $attr = array( );
                $i = 0;               
                foreach ( $_POST['attr'] as $k => $v )
                {
                    $attr[$i]['atributo'] = $v['name'];
                    $v = explode("|",$v['value']);
                    if(!isset($v[1])){
                        $v[1] = 0;
                    }
                    $attr[$i]['item'] = $v[0];
                    $attr[$i]['qtde'] = $v[1];
                    $i++;
                }
                $atributos = array( );
                foreach ( $attr as $at )
                {
                    $attGet = $this->getAttr( $at['item'] );
                    $atributos[] = $attGet;
                    $strAtrrPed .= $attGet[1] . " - ";
                    $attr_qtde = $at['qtde'];
                }

                if(isset($_SESSION['cart'][$this->cart_id]['atributo_qtde'])){
                    $attr_qtde = $_SESSION['cart'][$this->cart_id]['atributo_qtde'];
                }               

                $strAtrrPed = substr( $strAtrrPed, 0, -2 );
                $strAttr = array( );
                foreach ( $atributos as $at )
                {
                    $strAttr[] = implode( ",", $at );
                }
                $strAttr = implode( "|", $strAttr );
            }
            if ( isset( $_POST['id'] ) && !empty( $_POST['id'] ) )
            {
                $this->cart_id = base64_encode( uniqid( md5( time() ) ) );
                if ( $this->checkItemCart( $strAtrrPed ) == false )
                {
                    $this->item_id = intval($_POST['id']);
                    if ( $this->getItem() ){
                        $this->cart[$this->cart_id] = $this->item;
                        $_SESSION['cart'][$this->cart_id] = $this->item;
                        $t = $_SESSION['cart'][$this->cart_id]['item_preco'] * $_SESSION['cart'][$this->cart_id]['item_qtde'];
                        $_SESSION['cart'][$this->cart_id]['valor_total'] = ( string ) $t;
                        $_SESSION['cart'][$this->cart_id]['atributos'] = ( string ) $strAttr;
                        $_SESSION['cart'][$this->cart_id]['atributo_ped'] = ( string ) $strAtrrPed;               
                        $_SESSION['cart'][$this->cart_id]['cart_id'] = ( string ) $this->cart_id;
                        $_SESSION['cart'][$this->cart_id]['item_id'] = ( string ) $this->item_id;
                        $_SESSION['cart'][$this->cart_id]['item_estoque'] = ( string ) $this->estoque;
                        $_SESSION['cart'][$this->cart_id]['item_estoque']--;
                        if ( isset( $_POST['attr'] ) && !empty( $_POST['attr'] ) ){
                            if(!isset($_SESSION['cart'][$this->cart_id]['atributo_qtde'])){
                                $_SESSION['cart'][$this->cart_id]['atributo_qtde'] = intval($attr_qtde);
                            }                        
                         $_SESSION['cart'][$this->cart_id]['atributo_qtde']--;      
                        }       
                    }
                }
                else
                {
                   if( $_SESSION['cart'][$this->cart_id]['item_estoque'] >= 1) {

                     if(isset($_SESSION['cart'][$this->cart_id]['atributo_qtde'])){
                        if($_SESSION['cart'][$this->cart_id]['atributo_qtde'] >= 1){
                            $_SESSION['cart'][$this->cart_id]['item_qtde']++;
                            $t = $_SESSION['cart'][$this->cart_id]['item_preco'] * $_SESSION['cart'][$this->cart_id]['item_qtde'];
                            $_SESSION['cart'][$this->cart_id]['valor_total'] = $t;
                            $_SESSION['cart'][$this->cart_id]['item_estoque']--;
                            $_SESSION['cart'][$this->cart_id]['atributo_qtde']--;
                        }
                    }else{
                        $_SESSION['cart'][$this->cart_id]['item_qtde']++;
                        $t = $_SESSION['cart'][$this->cart_id]['item_preco'] * $_SESSION['cart'][$this->cart_id]['item_qtde'];
                        $_SESSION['cart'][$this->cart_id]['valor_total'] = $t;
                        $_SESSION['cart'][$this->cart_id]['item_estoque']--;
                    }
                   }
                }
            }
        }
    }

    public function checkItemCart( $strAtrrPed = "" )
    {
        $incart = false;
        if ( isset( $_SESSION['cart'] ) && count( $_SESSION['cart'] ) >= 1 )
        {
            foreach ( $_SESSION['cart'] as $k => $v )
            {
                if ( isset( $_SESSION['cart'][$k]['item_id'] ) && $_SESSION['cart'][$k]['item_id'] == $this->item_id )
                {
                    if ( isset( $_SESSION['cart'][$k]['atributo_ped'] ) )
                    {
                        if ( $_SESSION['cart'][$k]['atributo_ped'] == $strAtrrPed )
                        {
                            $this->cart_id = $_SESSION['cart'][$k]['cart_id'];
                            $incart = true;
                        }
                    }
                }
            }
        }
        return $incart;
    }

    public function getAttr( $iattr_id )
    {
        $this->select()
                ->from( 'atributo' )
                ->join( 'iattr', 'iattr_atributo = atributo_id' )
                ->where( "iattr_id = $iattr_id" )
                ->execute();
        if ( $this->result() )
        {
            return array( $this->data[0]['atributo_nome'], $this->data[0]['iattr_nome'], $this->data[0]['atributo_id'], $this->data[0]['iattr_id'] );
        }
    }

    public function getItemUrl()
    {
        if ( $this->item_id != null )
        {
            $this->select()
                    ->from( 'item' )
                    ->join( 'sub', 'item_sub = sub_id', 'INNER' )
                    ->join( 'categoria', 'sub_categoria = categoria_id', 'INNER' )
                    ->where( "item_id = $this->item_id" )
                    ->execute();
            if ( $this->result() )
            {
                $this->data[0]['item_qtde'] = 1;
                $this->data[0]['full_url'] = "$this->baseUri/produto/"
                        . $this->data[0]['categoria_url'] . "/"
                        . $this->data[0]['sub_url'] . "/"
                        . $this->data[0]['item_url'] . "/"
                        . $this->data[0]['item_id'] . "/";
                return $this->data[0]['full_url'];
            }
            else
            {
                return "$this->baseUri/";
            }
        }
    }

    public function getItem()
    {
        if ( $this->item_id != null )
        {
            $this->select()
                    ->from( 'item' )
                    ->join( 'sub', 'item_sub = sub_id', 'INNER' )
                    ->join( 'categoria', 'sub_categoria = categoria_id', 'INNER' )
                    ->where( "item_id = $this->item_id" )
                    ->execute();
            if ( $this->result() )
            {
                $this->estoque = $this->data[0]['item_estoque'];
                $this->addkey( 'item_bread_title', '', 'item_title' );
                $this->cut( 'item_bread_title', 50, '...' );
                //desconto
                $item_desconto = $this->data[0]['item_desconto'];
                if ( $item_desconto > 1 )
                {
                    $desconto = ($this->data[0]['item_preco'] - $this->data[0]['item_desconto'] );
                    $this->data[0]['item_valor_original'] = @number_format( $this->data[0]['item_preco'], 2, ",", "." );
                    $this->data[0]['item_desconto'] = preg_replace( '/,/', '.', $this->data[0]['item_desconto'] );
                    $this->data[0]['item_preco'] = $desconto;
                    $this->data[0]['showhide_valorOri'] = 'showv';
                }
                else
                {
                    $this->data[0]['item_valor_original'] = '';
                    $this->data[0]['showhide_valorOri'] = 'hide';
                }

                //parcelamento
                $item_parc = $this->data[0]['item_parc'];
                if ( $item_parc > 1 )
                {
                    $item_valor_parc = ceil( (($this->data[0]['item_preco']) / $item_parc ) );
                    $item_valor_parc = @number_format( $item_valor_parc, 2, ",", "." );
                    $item_valor_parc = "$item_parc x $item_valor_parc";
                    $this->data[0]['item_valor_parc'] = $item_valor_parc;
                }

                unset( $this->data[0]['item_desc'] );
                $this->data[0]['item_qtde'] = 1;
                $this->data[0]['full_url'] = "$this->baseUri/produto/"
                        . $this->data[0]['categoria_url'] . "/"
                        . $this->data[0]['sub_url'] . "/"
                        . $this->data[0]['item_url'] . "/"
                        . $this->data[0]['item_id'] . "/";
                $this->item = $this->data[0];
                $this->item['item_foto'] = $this->fillFoto();
                return true;
            }
            else
            {
                return false;
            }
        }
    }

    
public function incrementa()
    {
        if ( isset( $this->uri_segment[2] ) )
        {
            $this->cart_id = $this->uri_segment[2];
            if ( isset( $_SESSION['cart'][$this->cart_id]['item_id'] ) ){
                if(isset($_SESSION['cart'][$this->cart_id]['atributo_qtde'])){
                    if($_SESSION['cart'][$this->cart_id]['atributo_qtde'] >= 1){
                        $_SESSION['cart'][$this->cart_id]['item_qtde']++;
                        $t = $_SESSION['cart'][$this->cart_id]['item_preco'] * $_SESSION['cart'][$this->cart_id]['item_qtde'];
                        $_SESSION['cart'][$this->cart_id]['valor_total'] = $t;
                        $total = number_format( $_SESSION['cart'][$this->cart_id]['valor_total'], 2, ',', '.' );
                        $qtde = $_SESSION['cart'][$this->cart_id]['item_qtde'];
                        $this->getTotal();
                        $this->estoque = $_SESSION['cart'][$this->cart_id]['item_estoque'];
                        $_SESSION['cart'][$this->cart_id]['item_estoque']--;
                        $_SESSION['cart'][$this->cart_id]['atributo_qtde']--;
                        $attr_qtde =  intval( $_SESSION['cart'][$this->cart_id]['atributo_qtde'] );
                        echo "{\"total\":\"$total\",\"qtde\":\"$qtde\",\"attr_qtde\":\"$attr_qtde\",\"total_compra\":\"" . $this->_money( $this->total_compra ) . "\",\"estoque\":\"$this->estoque\"}";                        
                    }
                    else{
                        echo "{\"estoque\":\"0\"}";
                    }
                }
                else{                
                    if ( $_SESSION['cart'][$this->cart_id]['item_estoque'] >= 1 )
                    {
                        $_SESSION['cart'][$this->cart_id]['item_qtde']++;
                        $t = $_SESSION['cart'][$this->cart_id]['item_preco'] * $_SESSION['cart'][$this->cart_id]['item_qtde'];
                        $_SESSION['cart'][$this->cart_id]['valor_total'] = $t;
                        $total = number_format( $_SESSION['cart'][$this->cart_id]['valor_total'], 2, ',', '.' );
                        $qtde = $_SESSION['cart'][$this->cart_id]['item_qtde'];
                        $this->getTotal();
                        $this->estoque = $_SESSION['cart'][$this->cart_id]['item_estoque'];
                        echo "{\"total\":\"$total\",\"qtde\":\"$qtde\",\"attr_qtde\":\"null\",\"total_compra\":\"" . $this->_money( $this->total_compra ) . "\",\"estoque\":\"$this->estoque\"}";
                        $_SESSION['cart'][$this->cart_id]['item_estoque']--;
                    }
                    else
                    {
                        echo "{\"estoque\":\"0\"}";
                    }
            }


            }
        }
    }

    public function decrementa()
    {
        if ( isset( $this->uri_segment[2] ) )
        {
            $this->cart_id = $this->uri_segment[2];
            if ( isset( $_SESSION['cart'][$this->cart_id] ) )
            {
                $_SESSION['cart'][$this->cart_id]['item_qtde']--;
                $t = $_SESSION['cart'][$this->cart_id]['item_preco'] * $_SESSION['cart'][$this->cart_id]['item_qtde'];
                $_SESSION['cart'][$this->cart_id]['valor_total'] = $t;
                $total = number_format( $_SESSION['cart'][$this->cart_id]['valor_total'], 2, ',', '.' );
                $qtde = $_SESSION['cart'][$this->cart_id]['item_qtde'];

                $_SESSION['cart'][$this->cart_id]['item_estoque']++;
                $this->estoque = $_SESSION['cart'][$this->cart_id]['item_estoque'];

                if(isset($_SESSION['cart'][$this->cart_id]['atributo_qtde'])){
                   $_SESSION['cart'][$this->cart_id]['atributo_qtde']++;
                }                
                if ( $_SESSION['cart'][$this->cart_id]['item_qtde'] == 0 )
                {
                    unset( $_SESSION['cart'][$this->cart_id] );
                }
                $itens_carrinho = count( $_SESSION['cart'] );

                $this->getTotal();
                echo "{\"total\":\"$total\",\"qtde\":\"$qtde\",\"total_compra\":\"" . $this->_money( $this->total_compra ) . "\",\"itens\":\"$itens_carrinho\",\"estoque\":\"$this->estoque\"}";
            }
            else
            {
                echo "{\"total\":\"0,00\",\"qtde\":\"0\",\"total_compra\":\"0,00\",\"itens\":\"0\",\"estoque\":\"$this->estoque\"}";
            }
        }
        else
        {
            echo -1;
        }
    }


    public function clear()
    {
        if ( isset( $_SESSION['cart'] ) )
        {
            $_SESSION['cart'] = null;
            unset( $_SESSION['cart'] );
            $this->assign( 'mybasket', 'icon-basket' );
            $this->assign( 'qtdeItem', '0' );
        }
        if ( isset( $_SESSION['mycep_frete'] ) )
        {
            unset( $_SESSION['mycep_frete'] );
        }
        if ( isset( $_SESSION['mycep'] ) )
        {
            unset( $_SESSION['mycep'] );
        }
        if ( isset( $_SESSION['mycep_prazo'] ) )
        {
            unset( $_SESSION['mycep_prazo'] );
        }
        if ( isset( $_SESSION['finaliza-entrega'] ) )
        {
            unset( $_SESSION['finaliza-entrega'] );
        }
        if ( isset( $_SESSION['mycep_entrega'] ) )
        {
            unset( $_SESSION['mycep_entrega'] );
        }
        if ( isset( $_SESSION['finaliza-pagamento'] ) )
        {
            unset( $_SESSION['finaliza-pagamento'] );
        }
        if ( isset( $_SESSION['cupom'] ) )
        {
            unset( $_SESSION['cupom'] );
        }
        if ( isset( $this->uri_segment[2] ) )
        {
            $this->redirect( "$this->baseUri/carrinho/" );
        }
    }

    public function remove()
    {
        if ( isset( $this->uri_segment[2] ) )
        {
            $this->cart_id = $this->uri_segment[2];
            if ( isset( $_SESSION['cart'][$this->cart_id] ) )
            {
                $_SESSION['cart'][$this->cart_id]['item_estoque']--;
                unset( $_SESSION['cart'][$this->cart_id] );
                $this->getTotal();
            }
            if ( !isset( $this->uri_segment[3] ) )
            {
                $this->redirect( "$this->baseUri/carrinho/" );
            }
            else
            {
                echo $this->_money( $this->total_compra );
            }
        }
    }

    public function getTotal()
    {
        if ( isset( $_SESSION['cart'] ) && count( $_SESSION['cart'] ) >= 1 )
        {
            $this->cart = $_SESSION['cart'];
            foreach ( $this->cart as $k => $v )
            {
                $this->total_compra += ( float ) $this->cart[$k]['item_preco'] * ( int ) $this->cart[$k]['item_qtde'];
            }
            //TOTAL FRETE
            if ( !isset( $_SESSION['mycep_frete'] ) )
            {
                $_SESSION['mycep_frete'] = 0;
            }
            $this->valor_frete = $_SESSION['mycep_frete'];
            $this->valor_total = $this->total_compra;
            $this->total_com_frete = $this->total_compra + $this->valor_frete;
            $this->valor_desconto = 0;
            $this->cupom_desconto_info = "";
            $this->cupom_desconto = 0;
            $this->total_sem_desconto = $this->total_compra;
            $this->total_com_desconto = $this->total_compra;
            $r = new Route;
            if ( $r->get() == "FINALIZAR" )
            {
                $this->getTotalCupom();
            }
        }
    }

    public function getTotalCupom()
    {
        //TOTAL FRETE
        if ( !isset( $_SESSION['mycep_frete'] ) )
        {
            $_SESSION['mycep_frete'] = 0;
        }
        $this->valor_frete = $_SESSION['mycep_frete'];

        $this->cupom_desconto = 0;
        $this->cupom_tipo = 0;
        $this->cupom_alfa = '';
        $this->cupom_desconto_ext = "";

        $this->total_sem_desconto = $this->total_compra;
        $this->total_com_desconto = $this->total_compra;
        $this->total_com_frete = $this->total_compra + $this->valor_frete;
        $this->total_sem_frete = $this->total_compra - $this->valor_frete;
        $this->total_produtos = $this->total_compra;

        $this->valor_desconto = 0;
        $this->cupom_desconto_info = "";
        $this->cupom_msg = "";

        if ( isset( $_SESSION['cupom']['desconto'] ) && $_SESSION['cupom']['desconto'] >= 1 )
        {
            $this->cupom_desconto = $_SESSION['cupom']['desconto'];
            $this->cupom_desconto_ext = $this->cupom_desconto;
            $this->cupom_min = $_SESSION['cupom']['min'];
            $this->cupom_tipo = $_SESSION['cupom']['tipo'];
            $this->cupom_alfa = $_SESSION['cupom']['alfa'];

            $this->valor_desconto = ($this->cupom_desconto / 100 ) * $this->total_compra;
            //tipo 1 = total  / 2 = frete 
            if ( $this->cupom_tipo == 1 )
            {
                if ( $this->valor_frete >= 1 )
                {
                    $this->valor_total = $this->total_com_frete - $this->valor_desconto;
                    $this->cupom_desconto_ext = " -$this->cupom_desconto_ext% ";
                    $this->total_com_desconto = $this->valor_total;
                    $this->cupom_desconto_info = "total dos produtos (" . $this->_money( $this->total_sem_desconto ) . ") - 
                            cupom (" . $this->_money( $this->valor_desconto ) . ") + frete (" . $this->_money( $this->valor_frete ) . ")";
                    $this->cupom_msg = "<b class='alert alert-success'>Cupom Desconto -" . $this->_money( $this->valor_desconto ) . "</b>";
                }
                else
                {
                    $this->valor_total = $this->total_sem_desconto;
                    $this->cupom_desconto_ext = "( -$this->cupom_desconto_ext% )";
                    $this->total_com_desconto = $this->valor_total - $this->valor_desconto;
                    $this->cupom_desconto_info = "total dos produtos (" . $this->_money( $this->total_sem_desconto ) . ") - 
                            cupom (" . $this->_money( $this->valor_desconto ) . ")";
                    $this->cupom_msg = "<b class='alert alert-success'>Cupom Desconto -" . $this->_money( $this->valor_desconto ) . "</b>";
                }
            }
            elseif ( $this->cupom_tipo == 2 )
            {
                $this->cupom_desconto = 0;
                if ( $this->valor_frete >= 1 )
                {
                    $this->valor_total = $this->total_sem_frete;
                    $this->cupom_desconto = $this->valor_frete;
                    $this->valor_desconto = $this->valor_frete;
                    $this->cupom_desconto_ext = "( cupom frete grátis )";
                    //$this->total_com_desconto = $this->total_sem_desconto;
                    $this->cupom_desconto_info = "total dos produtos (" . $this->_money( $this->total_sem_desconto ) . ") - 
                            frete (" . $this->_money( $this->valor_frete ) . ")";
                    $this->cupom_desconto_info = "cupom frete grátis";
                    $this->cupom_msg = "<b class='alert alert-success'>Cupom Frete Grátis!</b>";
                }
                else
                {
                    $this->valor_desconto = "0,00";
                    $this->valor_total = $this->total_sem_frete;
                    $this->cupom_msg = "<b class='alert alert-success'>Cupom Frete Grátis!</b>";
                }
            }
            //quando não há frete ele cobra o valor mínimo
            if ( $this->total_com_desconto <= $this->cupom_min )
            {
                $this->cupom_desconto_info = "cupom valor mínimo " . $this->_money( $this->cupom_min );
                $this->valor_total = $this->cupom_min;
                $this->cupom_desconto = $this->total_sem_desconto - $this->cupom_min;
                $this->total_com_desconto = $this->valor_total;
            }
        }
        else
        {
            if ( $this->valor_frete >= 1 )
            {
                $this->total_sem_desconto = $this->total_com_frete;
                $this->total_com_desconto = $this->total_com_frete;
            }
        }

        $debug = "
            <p class='alert alert-danger'><strong>
                DEGUB:... <br/>
                Total Produtos: $this->total_compra <br />
                Total C/ desconto:  $this->total_com_desconto <br />
                Total S/ desconto:  $this->total_sem_desconto <br />
                Total C/ frete:   $this->total_com_frete <br />
                Total S/ frete:   $this->total_sem_frete <br />
                Frete:  $this->valor_frete  <br />
                Cupom Desc:  $this->valor_desconto  | $this->cupom_tipo<br />
                MSG Cupom:  $this->cupom_desconto_info<br />
            </strong></p>";
        //echo $debug;
    }

    public function fillFoto()
    {
        $this->select()
                ->from( 'foto' )
                ->where( "foto_item = $this->item_id" )
                ->orderby( 'foto_pos asc' )
                ->execute();
        if ( $this->result() )
        {
            $this->preg( array( '/\.jpg/', '/\.png/' ), array( '', '' ), 'foto_url' );
            return $this->data[0]['foto_url'];
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

    public function double( $str )
    {
        return preg_replace( '/,/', '.', $str );
    }

    public function _money( $val )
    {
        return @number_format( $val, 2, ",", "." );
    }
}
