<?php
error_reporting( 0 );

class Produto extends PHPFrodo
{
    public $config = array( );
    public $config_cep = array( );
    public $menu;
    public $item_categoria = null;
    public $item_sub = null;
    public $item_url = null;
    public $item_id = null;
    public $item = null;
    public $f_foto = null;
    public $f_foto_big = null;
    public $payConfig;

    public function __construct()
    {
        parent:: __construct();
        $sid = new Session;
        $sid->start();
        if ( $sid->check() && $sid->getNode( 'cliente_id' ) >= 1 )
        {
            $this->cliente_email = ( string ) $sid->getNode( 'cliente_email' );
            $this->cliente_id = $sid->getNode( 'cliente_id' );
            $this->cliente_nome = ( string ) $sid->getNode( 'cliente_nome' );
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
        $this->select()
                ->from( 'config' )
                ->execute();
        if ( $this->result() )
        {
            $this->config = ( object ) $this->data[0];
            $this->assignAll();
        }
        if ( isset( $this->uri_segment[1] ) && isset( $this->uri_segment[2] ) && isset( $this->uri_segment[3] ) && isset( $this->uri_segment[4] ) )
        {
            $this->item_categoria = $this->uri_segment[1];
            $this->item_sub = $this->uri_segment[2];
            $this->item_url = $this->uri_segment[3];
            $this->item_id = $this->uri_segment[4];
        }
        //mostra meios de pagamento no rodape
        $this->payConfig = new Pay;
        $this->view_prepend_data = $this->payConfig->getPaysOn();
    }

    public function welcome()
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
                $this->addkey( 'item_bread_title', '', 'item_title' );
                $this->cut( 'item_bread_title', 35, '...' );
                //desconto
                $item_title = $this->data[0]['item_title'];
                $item_desconto = $this->data[0]['item_desconto'];
                $item_estoque = $this->data[0]['item_estoque'];
                if ( $item_estoque >= 1 )
                {
                    $this->tpl( 'public/produto.html' );
                    if ( $item_desconto > 1 )
                    {
                        $this->assign( 'item_valor_original', @number_format( $this->data[0]['item_preco'], 2, ",", "." ) );
                        $this->data[0]['item_preco'] = ($this->data[0]['item_preco'] - $this->data[0]['item_desconto'] );
                    }
                    else
                    {
                        $this->assign( 'item_valor_original', '' );
                        $this->assign( 'showHide', "hider" );
                    }
                    //parcelamento
                    $item_parc = $this->data[0]['item_parc'];
                }
                else
                {
                    $this->tpl( 'public/produto_sem_estoque.html' );
                }
                $item_preco_final = $this->data[0]['item_preco'];
                $this->money( 'item_preco' );
                if ( $this->data[0]['item_preco'] <= 0 )
                {
                    $_SESSION['FLUX_SOB_CONSULTA'] = "$item_title - Cód. do Produto # $this->item_id";
                    $this->data[0]['item_preco'] = "Sob consulta <br><small> Cód. do Produto # $this->item_id </small>";
                    $this->data[0]['item_preco'] .= "<br /><br />  <a href='$this->baseUri/atendimento/' class='form-control btn btn-success'>Solicitar mais informações</a>";
                    $this->data[0]['show_hide_btn_comprar'] = "hide";
                    $this->assign( 'show_valor_parc', 'hide' );
                    $this->assignAll();
                }
                else
                {
                    $this->data[0]['item_preco'] = "Por R$ " . $this->data[0]['item_preco'];
                    $this->assignAll();
                    $parcelamento = '';
                    if ( $item_estoque >= 1 )
                    {
                        $this->fillAtributos();
                        if ( $item_parc >= 2 )
                        {
                            $parcelamento = $this->payConfig->parcelamentoTabela( $item_preco_final, $item_parc );
                            $this->assign( 'parcelas', $parcelamento );
                        }
                        else
                        {
                            $this->assign( 'show_valor_parc', 'hide' );
                        }
                    }
                    $this->assign( 'parcelas', $parcelamento );
                }
                //$this->item = $this->data[0];
            }
            $this->fillFoto();

            $this->menu = new Menu;
            $this->fetch( 'f', $this->menu->getFooter() );
            $this->fetch( 'cat', current( $this->menu->getMenuDepto() ) );
            $this->fetch( 'depto', end( $this->menu->getMenuDepto() ) );
            $this->fetch( 'depto-full', end( $this->menu->getMenuDepto() ) );
            //redes sociais footer
            $plug = new Social;
            $this->assign( 'social_fb', $plug->social_fb );
            $this->assign( 'social_tw', $plug->social_tw );
            $this->render();
            $this->viewcount();
        }
    }

    public function viewcount()
    {
        $this->increment( 'item', 'item_views', 1, "item_id = $this->item_id" );
    }

    public function fillAtributos()
    {
        $itemA = array( );
        $itemB = array( );
        $itemC = array( );
        $this->select()
                ->from( 'atributo' )
                ->join( 'iattr', 'iattr_atributo = atributo_id', 'INNER' )
                ->join( 'relatrr', 'relatrr_atributo = atributo_id', 'INNER' )
                ->groupby( 'atributo_id' )
                ->orderby( 'atributo_nome asc' )
                ->execute();
        if ( $this->result() )
        {
            $attr = $this->data;
            foreach ( $attr as $k => $v )
            {
                $this->attr_id = $attr[$k]['atributo_id'];
                $this->attr_nome = $attr[$k]['atributo_nome'];
                $this->attr_short = strtolower( current( explode( " ", $attr[$k]['atributo_nome'] ) ) );
                $this->select()->from( 'iattr' )->where( "iattr_atributo = $this->attr_id" )->orderby( 'iattr_nome asc' )->execute();
                if ( $this->result() )
                {
                    $itemA = array(
                        'atributo_id' => $this->attr_id,
                        'atributo_nome' => $this->attr_nome,
                        'atributo_short' => $this->attr_short,
                    );
                    $iattr = $this->data;
                    foreach ( $iattr as $m => $n )
                    {
                        $this->iattr_id = $iattr[$m]['iattr_id'];
                        $this->iattr_nome = $iattr[$m]['iattr_nome'];
                        $this->iattr_atributo = $iattr[$m]['iattr_atributo'];
                        $this->select()
                                ->from( 'relatrr' )
                                ->where( "relatrr_iattr = $this->iattr_id and relatrr_item = $this->item_id and relatrr_qtde >= 1" )
                                ->execute();
                        if ( $this->result() )
                        {
                            $itemB = array(
                                'iattr_nome' => $this->iattr_nome,
                                'iattr_id' => $this->iattr_id,
                                'iattr_atributo' => $this->iattr_atributo,
                            );
                            if ( $this->data[0]['relatrr_qtde'] >= 1 )
                            {
                                $itemB['iattr_qtde'] = $this->data[0]['relatrr_qtde'];
                                $itemA['item'][] = $itemB;
                            }
                        }
                    }
                    $itemC[] = $itemA;
                }
            }
        }
        foreach ( $itemC as $k => $v )
        {
            if ( !isset( $itemC[$k]['item'] ) )
            {
                unset( $itemC[$k] );
            }
        }
        if ( isset( $itemC ) && count( $itemC ) >= 1 )
        {
            sort( $itemC );
            $this->fetch( 'att', $itemC );
        }
        unset( $itemC );
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
            $this->addkey( 'foto_big', '', 'foto_url' );
            $this->preg( array( '/\.jpg/', '/\.png/' ), array( '', '' ), 'foto_url' );
            $this->f_foto = $this->data[0]['foto_url'];
            $this->f_foto_big = $this->data[0]['foto_big'];
            $this->assign( 'f_foto', $this->f_foto );
            $this->assign( 'f_big', $this->f_foto_big );
            $this->assignAll();
            $this->fetch( 'fg', $this->data );
        }
        else
        {
            $this->assign( 'semFoto', 'hide' );
        }
    }
}
/* end file */
