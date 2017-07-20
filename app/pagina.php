<?php
error_reporting( 0 );
class Pagina extends PHPFrodo
{
    public $config = array( );
    public $page_url;

    public function __construct()
    {
        parent:: __construct();
        $sid = new Session;
        $sid->start();
        if ( $sid->check() && $sid->getNode( 'cliente_id' ) >= 1 )
        {
            $this->cliente_email = $sid->getNode( 'cliente_email' );
            $this->cliente_id = $sid->getNode( 'cliente_id' );
            $this->cliente_nome = $sid->getNode( 'cliente_nome' );
            $this->assign( 'cliente_nome', current( explode( ' ', $this->cliente_nome ) ) );
            $this->assign( 'cliente_email', $this->cliente_email );
            $this->assign( 'cliente_msg', 'acesse aqui sua conta.' );
            $this->assign( 'logged', 'true' );
        }
        else
        {
            $this->assign( 'cliente_nome', 'visitante' );
            $this->assign( 'cliente_msg', 'faça seu login ou cadastre-se.' );
            $this->assign( 'logged', 'false' );
        }
        $qtdeITem = 0;
        if ( isset( $_SESSION['cart'] ) && count( $_SESSION['cart'] ) >= 1 )
        {
            $qtdeITem = count( $_SESSION['cart'] );
            $cart = new Carrinho;
            $cart->getTotal();
            $cart->total_compra = @number_format( $cart->total_compra, 2, ",", "." );
            $this->assign( 'cartTotal', "R$ " . $cart->total_compra );
        }
        $this->assign( 'qtdeItem', $qtdeITem );

        $this->select()
                ->from( 'config' )
                ->execute();
        if ( $this->result() )
        {
            $this->config = ( object ) $this->data[0];
            $this->assignAll();
        }
    }

    public function welcome()
    {
        $this->tpl( 'public/pagina.html' );
        $this->page_url = $this->uri_segment[1];
        $this->select()->from( 'page' )->where( "page_url = '$this->page_url'" )->execute();
        if ( $this->result() )
        {
            $this->menu = new Menu;
            $this->fetch( 'f', $this->menu->getFooter() );
            $this->fetch( 'cat', current( $this->menu->getMenuDepto() ) );
            $this->fetch( 'depto', end( $this->menu->getMenuDepto() ) );
            $this->fetch( 'depto-full', end( $this->menu->getMenuDepto() ) );
            $this->assignAll();
            $this->render();
        }
        else
        {
            $this->redirect( "$this->baseUri/" );
        }
    }
}
/*end file*/
