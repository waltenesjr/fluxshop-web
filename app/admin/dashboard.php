<?php

class Dashboard extends PHPFrodo
{

    private $user_login;
    private $user_id;
    private $user_name;
    private $user_level;
    public $status_pat = array( '/1/', '/2/', '/3/', '/4/', '/5/', '/6/', '/7/' );
    public $status_rep = array( 'Aguardando pagamento', 'Em análise', 'Aprovado', 'Disponível', 'Em disputa', 'Devolvida', 'Cancelada' );
    public $status_rep_icon = array( 'away.png', 'away.png', 'on.png', 'on.png', 'away.png', 'busy.png', 'busy.png' );

    public function __construct()
    {
        parent::__construct();
        $sid = new Session;
        $sid->start();
        if ( !$sid->check() || $sid->getNode( 'user_id' ) <= 0 )
        {
            $this->redirect( "$this->baseUri/admin/login/logout/" );
            exit;
        }
        $this->select()
                ->from( 'config' )
                ->execute();
        if ( $this->result() )
        {
            $this->config = ( object ) $this->data[0];
            $this->assignAll();
        }        
        $this->user_login = $sid->getNode( 'user_login' );
        $this->user_id = $sid->getNode( 'user_id' );
        $this->user_name = $sid->getNode( 'user_name' );
        $this->user_level = ( int ) $sid->getNode( 'user_level' );
        $this->assign( 'user_name', $this->user_name );
        if ( $this->user_level == 1 ) {
            $this->assign('showhide','hide');
        }         
    }

    public function welcome()
    {
        $this->tpl( 'admin/dashboard.html' );
        $this->render();
    }

    public function pedidos()
    {
        $this->pagebase = "$this->baseUri/admin/dashboard/pedidos";
        $this->tpl( 'admin/dashboard_pedido.html' );
        $this->select()
                ->from( 'pedido' )
                ->join( 'lista', 'lista_pedido = pedido_id', 'INNER' )
                ->join( 'cliente', 'cliente_id = pedido_cliente', 'INNER' )
                //->where( "pedido_cliente = $this->cliente_id" )
                ->paginate( 25 )
                ->groupby( 'pedido_id' )
                ->orderby( 'pedido_id desc' )
                ->execute();
        if ( $this->result() )
        {
            
            $this->money( 'pedido_total' );
            $this->money( 'pedido_total_frete' );
            $this->addkey( 'staticon', '', 'pedido_status' );
            $this->preg( $this->status_pat, $this->status_rep_icon, 'staticon' );
            $this->preg( $this->status_pat, $this->status_rep, 'pedido_status' );
            $this->addkey( 'pedido_tt_frete', '', 'pedido_total_frete' );
            $this->fetch( 'cart', $this->data );
        }
        else
        {
            $this->assign( 'showHide', 'hide' );
            $this->assign( 'msg_pedido', '<h5 class="alert">Nenhum pedido na lista.</h5>' );
        }
        $this->render();
    }

    public function itens()
    {
        $this->pagebase = "$this->baseUri/admin/dashboard/itens";
        $this->tpl( 'admin/dashboard_item.html' );
        $this->select()
                ->from( 'item' )
                ->join( 'sub', 'sub_id = item_sub', 'INNER' )
                ->join( 'categoria', 'sub_categoria = categoria_id', 'INNER' )
                ->paginate(25 )
                ->orderby( 'item_views desc' )
                ->execute();
        if ( $this->result() )
        {
            $this->money( 'item_preco' );
            $this->money( 'item_desconto' );
            $this->fetch( 'rs', $this->data );
        }
        $this->render();
    }

}

/*end file*/