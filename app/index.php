<?php
error_reporting( 0 );
class Index extends PHPFrodo
{
    public $config = array( );
    public $menu;
    public $condition;
    public $condition_order_by = 'item_categoria desc, item_id desc';
    public $page_active = null;
    public $categoria_title;
    public $paginate_default = 12;
    public $sub_title;
    public $sid;
    public $payConfig;
    public $message_bar = "Produtos em Destaque";

    public function __construct()
    {
        parent:: __construct();
        $this->sid = new Session;
        $this->sid->start();
        if ( $this->sid->check() && $this->sid->getNode( 'cliente_id' ) >= 1 )
        {
            $this->cliente_email = ( string ) $this->sid->getNode( 'cliente_email' );
            $this->cliente_id = $this->sid->getNode( 'cliente_id' );
            $this->cliente_nome = ( string ) $this->sid->getNode( 'cliente_nome' );
            @$this->assign( 'cliente_nome', current( explode( ' ', $this->cliente_nome ) ) );
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
        //get all config site
        $this->select()
                ->from( 'config' )
                ->execute();
        if ( $this->result() )
        {
            $this->config = ( object ) $this->data[0];
            $this->sid->addNode( 'config_site_menu', $this->config->config_site_menu );
            $this->assignAll();
        }
        //initial condition for home itens
        $this->condition = 'item_show = 1 and item_estoque >= 1 and item_destaque = 1 ';
        $this->message_bar = "Produtos m Destaque";
        //get current category
        $this->getActiveCategory();
        //mostra meios de pagamento no rodape
        $this->payConfig = new Pay;
        $this->view_prepend_data = $this->payConfig->getPaysOn();
        //set route
        $r = new Route;
        $r->set( "HOME" );
    }

    public function welcome()
    {
        $this->tpl_page = "public/index.html";
        if ( $this->page_active != null )
        {
            $this->tpl_page = "public/index_sem_slide.html";
        }
        $this->tpl( $this->tpl_page );
        //slideshow
        $this->fillSlideShow();
        //carrinho lateral
        $this->getCarrinho();

        $action_reload = '';
        $this->select()
                ->from( 'item' )
                ->join( 'sub', 'item_sub = sub_id', 'INNER' )
                ->join( 'categoria', 'sub_categoria = categoria_id', 'INNER' )
                ->join( 'foto', 'foto_item = item_id and foto.foto_pos = ( SELECT MIN( foto_pos ) FROM foto where foto_item = item_id)', 'LEFT' )
                ->where( $this->condition )
                ->paginate( $this->paginate_default )
                ->groupby( 'item_id' )
                ->orderby( "$this->condition_order_by" )
                ->execute();
        if ( $this->result() )
        {
            $this->categoria_title = $this->data[0]['categoria_title'];
            $this->categoria_url = $this->data[0]['categoria_url'];
            $this->sub_title = $this->data[0]['sub_title'];
            $this->sub_url = $this->data[0]['sub_url'];
            $data = $this->data;
            $preco_min = 5;
            $preco_max = 10;
            $preco_max_last = 0;
            $preco_min_last = $data[0]['item_preco'];

            foreach ( $data as $k => $v )
            {
                //desconto
                $item_desconto = $data[$k]['item_desconto'];
                if ( $item_desconto > 1 )
                {
                    $data[$k]['item_valor_original'] = $data[$k]['item_preco'];
                    $data[$k]['item_preco'] = ($data[$k]['item_preco'] - $data[$k]['item_desconto']);
                    $data[$k]['item_valor_original'] = $this->_money( $data[$k]['item_valor_original'] );
                    $data[$k]['showHide'] = "";
                }
                else
                {
                    $data[$k]['item_valor_original'] = "";
                    $data[$k]['showHide'] = "hide";
                }
                //parcelamento
                $item_parc = $data[$k]['item_parc'];
                $data[$k]['item_valor_parc'] = "";
                if ( $item_parc >= 2 )
                {
                    $parcela = $this->payConfig->parcelamento( $data[$k]['item_preco'], $item_parc );
                    $data[$k]['item_valor_parc'] = $parcela['texto'];
                }
                //valor minimo
                if ( $data[$k]['item_preco'] >= $preco_max_last )
                {
                    $preco_max = $data[$k]['item_preco'];
                    $preco_max_last = $preco_max;
                }
                //valor maximo
                if ( $data[$k]['item_preco'] <= $preco_min_last )
                {
                    $preco_min = $data[$k]['item_preco'];
                    $preco_min_last = $preco_min;
                }
                $data[$k]['item_valor_sem_mask'] = $data[$k]['item_preco'];
                $data[$k]['item_preco'] = $this->_money( $data[$k]['item_preco'] );
                $data[$k]['foto_url'] = preg_replace( array( '/\.jpg/', '/\.png/' ), array( '', '' ), $data[$k]['foto_url'] );
                if ( $data[$k]['foto_url'] == "" )
                {
                    $data[$k]['foto_url'] = 'nopic';
                }
                $data[$k]['item_short_title'] = $data[$k]['item_title'];

                if ( $data[$k]['item_estoque'] <= 0 )
                {
                    $data[$k]['showHide'] = "hide";
                    $data[$k]['item_valor_original'] = "";
                    $data[$k]['item_valor_parc'] = "";
                    $data[$k]['item_preco'] = "Indisponível";
                }
                else
                {
                    if ( $data[$k]['item_preco'] <= 0 )
                    {
                        $data[$k]['item_preco'] = "Sob consulta";
                    }
                    else
                    {
                        $data[$k]['item_preco'] = "R$ " . $data[$k]['item_preco'];
                    }
                }
            }
            $this->data = $data;
            $this->cut( 'item_short_title', 80, '...' );
            $this->fetch( 'i', $this->data );
            //valor min e max | show hide mostrar mais
            $this->assign( 'preco_max', $preco_max );
            $this->assign( 'preco_min', $preco_min );
            if ( $this->page_active != null )
            {
                if ( isset( $preco_max ) && isset( $preco_min ) )
                {
                    $preco_max = round( $preco_max );
                    $preco_min = round( $preco_min );
                    $action_reload .= " setRangePreco($preco_min,$preco_max);";
                }
            }
        }
        else
        {
            $this->assign( 'message_default', '<h5> &nbsp; Desculpe, nenhum produto foi encontrado!</h5>' );
            $action_reload .= " $('#elm-filtro-range-preco').hide();";
        }
        if ( $this->numrows < $this->paginate_default )
        {
            $action_reload .= "hideShowBtnMore(1);";
        }
        else
        {
            $action_reload .= "hideShowBtnMore(2);";
        }
        $this->assign( 'action_on_load', $action_reload );
        //menu left e footer
        $this->getMenu();
        //titulo bar
        $this->getTitleBar();
        //redes sociais footer
        $plug = new Social;
        $this->assign( 'social_fb', $plug->social_fb );
        //$this->assign( 'social_tw', $plug->social_tw );        
        $this->render();
        $_SESSION['FLUX_BUSCA_COND'] = $this->condition;                
    }

    public function getLoadParams()
    {
        if ( in_array( 'categoria', $this->uri_segment ) )
        {
            if ( isset( $this->uri_segment[2] ) )
            {
                $categoria = $this->uri_segment[2];
                $this->condition = "categoria_url = '$categoria' ";
                if ( isset( $this->uri_segment[3] ) )
                {
                    $sub = $this->uri_segment[3];
                    if ( isset( $sub ) && $sub != 'page' && $sub != 'categoria' )
                    {
                        $this->condition .= "AND sub_url = '$sub'";
                    }
                }
                $this->condition .= "  AND item_show = 1";
                $_SESSION['FLUX_BUSCA_COND'] = $this->condition;
                $this->paginate_default = 16;
                $this->loadMore();
            }
        }
        if ( in_array( 'promocoes', $this->uri_segment ) )
        {
            $this->paginate_default = 16;
            $this->condition = 'item_show = 1 and item_oferta = 1 and item_estoque >= 1';
            $this->page_active = 'promocoes';
            $this->pagebase = "$this->baseUri/index/getLoadParams/promocoes";
            $this->assign( 'currentUri', $this->pagebase );
            $_SESSION['FLUX_BUSCA_COND'] = $this->condition;
            $this->loadMore();
        }
        if ( in_array( 'busca', $this->uri_segment ) )
        {
            $this->busca();
        }
    }

    public function loadMore()
    {
        if ( isset( $_SESSION['FLUX_BUSCA_COND'] ) && !empty( $_SESSION['FLUX_BUSCA_COND'] ) )
        {
            $this->condition = $_SESSION['FLUX_BUSCA_COND'];
        }
        $this->select()
                ->from( 'item' )
                ->join( 'sub', 'item_sub = sub_id', 'INNER' )
                ->join( 'categoria', 'sub_categoria = categoria_id', 'INNER' )
                ->join( 'foto', 'foto_item = item_id and foto.foto_pos = ( SELECT MIN( foto_pos ) FROM foto where foto_item = item_id)', 'LEFT' )
                ->where( $this->condition )
                ->paginate( $this->paginate_default )
                ->groupby( 'item_id' )
                ->orderby( "$this->condition_order_by" )
                ->execute();
        if ( $this->result() )
        {
            $this->tpl( 'public/itens_load.html' );
            $this->categoria_title = $this->data[0]['categoria_title'];
            $this->categoria_url = $this->data[0]['categoria_url'];
            $this->sub_title = $this->data[0]['sub_title'];
            $this->sub_url = $this->data[0]['sub_url'];
            $data = $this->data;
            $preco_min = 0;
            $preco_max = 0;
            $preco_max_last = 0;
            $preco_min_last = $data[0]['item_preco'];

            foreach ( $data as $k => $v )
            {
                //desconto
                $item_desconto = $data[$k]['item_desconto'];
                if ( $item_desconto > 1 )
                {
                    $data[$k]['item_valor_original'] = $data[$k]['item_preco'];
                    $data[$k]['item_preco'] = ($data[$k]['item_preco'] - $data[$k]['item_desconto']);
                    $data[$k]['item_valor_original'] = $this->_money( $data[$k]['item_valor_original'] );
                    $data[$k]['showHide'] = "";
                }
                else
                {
                    $data[$k]['item_valor_original'] = "";
                    $data[$k]['showHide'] = "hide";
                }
                //parcelamento
                $item_parc = $data[$k]['item_parc'];
                $data[$k]['item_valor_parc'] = "";
                if ( $item_parc >= 2 )
                {
                    $parcela = $this->payConfig->parcelamento( $data[$k]['item_preco'], $item_parc );
                    $data[$k]['item_valor_parc'] = $parcela['texto'];
                }
                //valor minimo
                if ( $data[$k]['item_preco'] >= $preco_max_last )
                {
                    $preco_max = $data[$k]['item_preco'];
                    $preco_max_last = $preco_max;
                }
                //valor maximo
                if ( $data[$k]['item_preco'] <= $preco_min_last )
                {
                    $preco_min = $data[$k]['item_preco'];
                    $preco_min_last = $preco_min;
                }
                $data[$k]['item_valor_sem_mask'] = $data[$k]['item_preco'];
                $data[$k]['item_preco'] = $this->_money( $data[$k]['item_preco'] );
                $data[$k]['foto_url'] = preg_replace( array( '/\.jpg/', '/\.png/' ), array( '', '' ), $data[$k]['foto_url'] );
                $data[$k]['item_short_title'] = $data[$k]['item_title'];

                if ( $data[$k]['item_preco'] <= 0 )
                {
                    $data[$k]['item_preco'] = "Sob consulta";
                }
                else
                {
                    $data[$k]['item_preco'] = "R$ " . $data[$k]['item_preco'];
                }
            }
            $this->data = $data;
            $this->cut( 'item_short_title', 80, '...' );
            $this->fetch( 'i', $this->data );

            //valor min e max | show hide mostrar mais
            $this->assign( 'preco_max', $preco_max );
            $this->assign( 'preco_min', $preco_min );
            $action_reload = '';
            if ( $this->numrows < $this->paginate_default )
            {
                $action_reload .= "hideShowBtnMore(1);";
            }
            else
            {
                $action_reload .= "hideShowBtnMore(2);";
            }
            if ( $this->page_active != null )
            {
                $preco_max = round( $preco_max );
                $preco_min = round( $preco_min );
                $action_reload .= " setRangePreco($preco_min,$preco_max);";
            }
            $this->assign( 'action_on_reload', $action_reload );
            $this->render();
        }
        else
        {
            echo -1;
        }
    }

    public function promocoes()
    {
        $this->message_bar = "Promoções e Ofertas";
        $this->paginate_default = 16;
        $this->condition = 'item_show = 1 and item_oferta = 1 and item_estoque >= 1';
        $_SESSION['FLUX_BUSCA_COND'] = $this->condition;
        $this->page_active = 'promocoes';
        $this->pagebase = "$this->baseUri/index/getLoadParams/promocoes";
        $this->assign( 'currentUri', $this->pagebase );
        $this->welcome();
    }

    public function ordenar()
    {
        if ( isset( $_SESSION['FLUX_BUSCA_COND'] ) && !empty( $_SESSION['FLUX_BUSCA_COND'] ) )
        {
            $this->condition = $_SESSION['FLUX_BUSCA_COND'];
        }
        $order = '';
        if ( isset( $this->uri_segment[2] ) )
        {
            $order = $this->uri_segment[2];
        }
        switch ( $order )
        {
            case 'menor-preco':
                $this->condition_order_by = 'item_preco asc, item_id desc';
                break;
            case 'maior-preco':
                $this->condition_order_by = 'item_preco desc, item_id desc';
                break;
            case 'mais-vistos':
                $this->condition_order_by = 'item_views desc, item_id desc';
                break;
            case 'mais-novos':
                $this->condition_order_by = 'item_id desc';
                break;
            case 'mais-antigos':
                $this->condition_order_by = 'item_id asc';
                break;
            case 'a-z':
                $this->condition_order_by = 'item_title asc';
                break;
            case 'z-a':
                $this->condition_order_by = 'item_title desc';
                break;
            default:
                $this->condition_order_by = 'item_categoria desc, item_id desc';
                break;
        }
        $this->paginate_default = 16;
        $this->page_active = 'ordenar';
        $this->pagebase = "$this->baseUri/index/ordenar/$order";
        $this->loadMore();
    }

    public function busca()
    {
        $this->pagebase = "$this->baseUri/index/getLoadParams/busca";
        $this->assign( 'currentUri', $this->pagebase );
        $this->paginate_default = 16;
        if ( isset( $_POST['busca'] ) && !empty( $_POST['busca'] ) )
        {
            $busca = trim( $_POST['busca'] );
            $this->assign( 'busca', "$busca" );
            $this->term_busca = $busca;
            //codicao para busca
            $this->condition = "item_title like'%$busca%' AND item_show = 1 OR ";
            $this->condition .= "item_keywords like'%$busca%' AND item_show = 1 OR ";
            $this->condition .= "categoria_title like'%$busca%' AND item_show = 1 OR ";
            $this->condition .= "sub_title like'%$busca%' AND item_show = 1";
            //foto_item = item_id and foto.foto_pos = ( SELECT MIN( foto_pos ) FROM foto where foto_item = item_id)
            $this->message_bar = "Resultado da busca: $busca";
            $this->page_active = 'busca';
            $_SESSION['FLUX_BUSCA_COND'] = $this->condition;
            $this->condition_order_by = 'item_preco desc';
            $this->welcome();
        }
    }



    public function categoria()
    {
        if ( isset( $this->uri_segment[2] ) )
        {
            $categoria = $this->uri_segment[2];
            $this->paginate_default = 16;
            $this->condition = "categoria_url = '$categoria' ";
            $this->pagebase = "$this->baseUri/index/getLoadParams/$categoria/categoria";
            if ( isset( $this->uri_segment[3] ) )
            {
                $sub = $this->uri_segment[3];
                //condicao para categorias
                if ( isset( $sub ) && $sub != 'page' )
                {
                    $this->pagebase = "$this->baseUri/index/getLoadParams/$categoria/$sub/categoria";
                    $this->condition .= "AND sub_url = '$sub'";
                    $this->assign( 'sub_url', $sub );
                }
            }
            $this->condition .= "  AND item_show = 1";
            $this->page_active = 'categoria';
            $_SESSION['FLUX_BUSCA_COND'] = $this->condition;
            $this->assign( 'currentUri', $this->pagebase );
            $this->assign( 'categoria_url', $categoria );
            $this->welcome();
        }
    }

    public function getTitleBar()
    {
        $rastro = '<ul id="breadcrumbs-one">';
        if ( $this->page_active == "categoria" )
        {
            $this->message_bar = "$this->categoria_title";
            if ( isset( $this->uri_segment[3] ) )
            {
                $this->message_bar = "$this->categoria_title / $this->sub_title";
            }
            $rastro .= "<li><a href=\"[baseUri]/index/categoria/$this->categoria_url/\">$this->categoria_title</a> </li>";
            if ( isset( $this->uri_segment[3] ) )
            {
                $rastro .= "<li><a href=\"[baseUri]/index/categoria/$this->categoria_url/$this->sub_url/\">$this->sub_title</a></li>";
            }
        }
        if ( $this->page_active == "busca" )
        {
            (isset( $this->term_busca )) ? $busca = $this->term_busca : $busca = '';
            $rastro .= "<li><a class=\"current\">Você buscou por \"$busca\" e encontramos ($this->numrows_total) produto(s).</a> </li>";
        }
        if ( $this->page_active == "promocoes" )
        {
            (isset( $this->term_busca )) ? $busca = $this->term_busca : $busca = '';
            $rastro .= "<li><a class=\"current\">Temos ($this->numrows_total) produto(s) em oferta.</a> </li>";
        }
        $rastro .= '</ul>';
        $this->assign( 'migalhas-de-pao', $rastro );
        $this->assign( 'message_bar', $this->message_bar );
    }

    public function getMenu()
    {
        $this->menu = new Menu;
        if ( $this->page_active != null )
        {
            $this->fetch( 'menu', $this->menu->get() );
        }
        $this->fetch( 'f', $this->menu->getFooter() );
        @$this->fetch( 'cat', current( $this->menu->getMenuDepto() ) );
        @$this->fetch( 'depto', end( $this->menu->getMenuDepto() ) );
        @$this->fetch( 'depto-full', end( $this->menu->getMenuDepto() ) );
    }

    public function fillSlideShow()
    {
        if ( $this->page_active == null )
        {
            $this->select()
                    ->from( 'slide' )
                    ->where( 'slide_local = 1' )
                    ->orderby( 'slide_id desc' )
                    ->execute();
            if ( $this->result() )
            {
                //$this->cut( 'slide_title', 90, ' ...' );
                $this->addindex( 'slideto' );
                $this->clonekey( 'slide_foto', array( 'slide_url' ) );
                $this->preg( array( '/\.jpg/', '/\.png/' ), array( '', '' ), 'slide_url' );
                $this->fetch( 'sl', $this->data );
                $this->fetch( 'sli', $this->data );
            }
        }
    }

    public function FillBanner()
    {
        //posicao  2 ou 3
        if ( isset( $this->uri_segment[2] ) )
        {
            $local = $this->uri_segment[2];
        }
        $per_page = 5;
        //paginacao
        if ( isset( $this->uri_segment[3] ) )
        {
            $per_page = $this->uri_segment[3];
        }
        //fillBanner
        $this->select()
                ->from( 'slide' )
                ->where( "slide_local = $local" )
                ->orderby( 'slide_id desc' )
                ->paginate( $per_page )
                ->execute();
        if ( $this->result() )
        {
            $this->encode( null, 'utf8_encode' );
            shuffle( $this->data );
            $this->clonekey( 'slide_foto', array( 'slide_url' ) );
            $this->preg( array( '/\.jpg/', '/\.png/' ), array( '', '' ), 'slide_url' );
            echo json_encode( $this->data );
        }
    }

    public function FillMaisNovosVistos()
    {
        $per_page = 5;
        if ( isset( $this->uri_segment[3] ) )
        {
            $per_page = $this->uri_segment[3];
        }
        $this->order_by = $this->uri_segment[2];
        switch ( $this->order_by )
        {
            case 1 :
                $this->order_by = 'item_views desc'; //mais visitados
                break;
            case 2 :
                $this->order_by = 'item_id desc'; //novos
                break;
            default :
                $this->order_by = 'item_id desc'; //novos
                break;
        }
        $allData = array( );
        $this->select()
                ->from( 'item' )
                ->join( 'sub', 'item_sub = sub_id', 'INNER' )
                ->join( 'categoria', 'sub_categoria = categoria_id', 'INNER' )
                ->join( 'foto', 'foto_item = item_id and foto.foto_pos = ( SELECT MIN( foto_pos ) FROM foto where foto_item = item_id)', 'LEFT' )
                ->where( 'item_show = 1 and item_estoque >= 1 and foto_url <> ""' )
                ->paginate( $per_page )
                ->groupby( 'item_id' )
                ->orderby( "$this->order_by" )
                ->execute();
        if ( $this->result() )
        {
            $this->clonekey( 'item_short_title', array( 'item_title' ) );
            $this->cut( 'item_short_title', 35, '...' );
            $this->encode( null, 'utf8_encode' );
            $data = $this->data;
            $views = array( );
            $last = array( );
            foreach ( $data as $k => $v )
            {
                //desconto
                $item_desconto = $data[$k]['item_desconto'];
                if ( $item_desconto > 1 )
                {
                    $data[$k]['item_valor_original'] = $data[$k]['item_preco'];
                    $data[$k]['item_preco'] = ($data[$k]['item_preco'] - $data[$k]['item_desconto']);
                    $data[$k]['item_valor_original'] = $this->_money( $data[$k]['item_valor_original'] );
                    $data[$k]['showHide'] = "";
                }
                else
                {
                    $data[$k]['item_valor_original'] = "";
                    $data[$k]['showHide'] = "hide";
                }
                //parcelamento
                $item_parc = $data[$k]['item_parc'];
                $data[$k]['item_valor_parc'] = "";
                if ( $item_parc > 1 )
                {
                    $parcela = $this->payConfig->parcelamento( $data[$k]['item_preco'], $item_parc );
                    $data[$k]['item_valor_parc'] = $parcela['texto'];
                }
                $data[$k]['item_preco'] = $this->_money( $data[$k]['item_preco'] );
                $data[$k]['foto_url'] = $this->_2thumb( $data[$k]['foto_url'] );
                $data[$k]['item_desc'] = "";
                $data[$k][$data[$k]['item_views']] = $data[$k]['item_views'];
                $views[$data[$k]['item_views']] = $data[$k];
                $last[$data[$k]['item_id']] = $data[$k];
                if ( $data[$k]['item_preco'] <= 0 )
                {
                    $data[$k]['item_preco'] = "Sob consulta";
                }
                else
                {
                    $data[$k]['item_preco'] = "R$ " . $data[$k]['item_preco'];
                }
            }
            $this->data = $data;
            shuffle( $this->data );
            $this->cut( 'item_short_title', 35, '...' );
            echo json_encode( $this->data );
        }
    }

    public function getActiveCategory()
    {
        if ( isset( $this->uri_segment ) && in_array( 'categoria', $this->uri_segment ) )
        {
            $categoria = $this->uri_segment[2];
            $sub = "";
            if ( isset( $this->uri_segment[3] ) )
            {
                $sub = $this->uri_segment[3];
            }
            $this->assign( 'sub_active', $sub );
            $this->assign( 'cat_active', $categoria );
        }
    }



    public function getCarrinho()
    {
        if ( isset( $_SESSION['cart'] ) && count( $_SESSION['cart'] ) >= 1 )
        {
            $cart = new Carrinho;
            $this->data = $_SESSION['cart'];
            $cart->getTotal();
            $this->money( 'item_preco' );
            $this->cut( 'item_title', 20, '...' );
            $this->assign( 'qtdeItem', count( $this->data ) );
            $this->assign( 'cartTotal', "R$ " . $this->_money( $cart->valor_total ) );
            $this->fetch( 'cart', $this->data );
        }
        else
        {
            $this->assign( 'cartTotal', "O carrinho está vazio! ;(" );
            $this->assign( 'carrinhoVazio', "hide" );
        }
    }

    public function _money( $val )
    {
        return @number_format( $val, 2, ",", "." );
    }

    public function _2thumb( $url )
    {
        return preg_replace( array( '/\.jpg/', '/\.png/', '/\.gif/' ), array( '', '', '' ), $url );
    }
}
/*end file*/
