<?php

class Pagina extends PHPFrodo
{

    public $user_login;
    public $user_id;
    public $user_name;
    public $user_level;
    public $msgError;
    public $area_id;
    public $area_title;
    public $page_id;
    public $page_title;
    public $page_content;

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
        $this->user_login = $sid->getNode( 'user_login' );
        $this->user_id = $sid->getNode( 'user_id' );
        $this->user_name = $sid->getNode( 'user_name' );
        $this->user_level = ( int ) $sid->getNode( 'user_level' );
        $this->assign( 'user_name', $this->user_name );
        $this->select()
                ->from( 'config' )
                ->execute();
        if ( $this->result() )
        {
            $this->config = ( object ) $this->data[0];
            $this->assignAll();
        }
        if ( isset( $this->uri_segment ) && in_array( 'process-ok', $this->uri_segment ) )
        {
            $this->assign( 'msgOnload', 'notify("<h1>Procedimento realizado com sucesso</h1>")' );
        }
        if ( $this->user_level == 1 ) {
            $this->assign('showhide','hide');
        }         
    }

    public function welcome()
    {
        $this->pagebase = "$this->baseUri/admin/pagina";
        $this->tpl( 'admin/pagina.html' );
        $this->select()
                ->from( 'page' )
                ->join( 'area', 'page_area = area_id', 'INNER' )
                ->paginate( 15 )
                ->orderby( 'page_title asc' )
                ->execute();
        if ( $this->result() )
        {
            $catData = $this->data;
            foreach ( $catData as $idx => $cat )
            {
                $catData[$idx]['page_item'] = 0;
                $c = ( object ) $cat;
                $page_id = $c->page_id;
                $this->select()
                        ->from( 'item' )
                        ->where( "item_sub = $page_id" )
                        ->execute();
                if ( $this->result() )
                {
                    $catData[$idx]['page_item'] = count( $this->data );
                }
            }
            $this->data = $catData;
            $this->fetch( 'rs', $this->data );
            $this->assign( 'page_qtde', count( $catData ) );
        }
        $this->render();
    }

    public function editar()
    {
        $this->tpl( 'admin/pagina_editar.html' );

        if ( isset( $this->uri_segment[2] ) )
        {
            $this->fillCategoria();
            $this->page_id = $this->uri_segment[2];
            $this->select()->from( 'page' )->where( "page_id = $this->page_id" )->execute();
            $this->page_content = $this->data[0]['page_content'];
            $this->assignAll();
            $this->helper( 'redactor' );
            $editor = editor( $this->page_content, 'page_content', '350px', '90%' );
            $this->assign( 'editor', $editor );
            $this->render();
        }
    }

    public function nova()
    {
        $this->tpl( 'admin/pagina_nova.html' );
        $this->fillCategoria();
        $this->helper( 'redactor' );
        $editor = editor( '', 'page_content', '350px', '90%' );
        $this->assign( 'editor', $editor );
        /*
        */
        $this->render();
    }

    public function fillCategoria()
    {
        $this->select()
                ->from( 'area' )
                ->orderby( 'area_title asc' )
                ->execute();
        if ( $this->result() )
        {
            $this->fetch( 'combo', $this->data );
        }
    }

    public function incluir()
    {
        if ( $this->postIsValid( array( 'page_title' => 'string' ) ) )
        {
            $this->postIndexAdd( 'page_url', $this->urlmodr( $this->postGetValue( 'page_title' ) ) );
            $this->postValueChange( 'page_title', ucfirst( $this->postGetValue( 'page_title' ) ) );
            $this->insert( 'page' )->fields()->values()->execute();
            $this->redirect( "$this->baseUri/admin/pagina/process-ok/" );
        }
        else
        {
            $this->msgError = $this->response;
            $this->pageError();
        }
    }

    public function atualizar()
    {
        if ( isset( $this->uri_segment[2] ) )
        {
            if ( $this->postIsValid( array( 'page_title' => 'string' ) ) )
            {
                $this->page_id = $this->uri_segment[2];
                $this->postIndexAdd( 'page_url', $this->urlmodr( $this->postGetValue( 'page_title' ) ) );
                $this->postValueChange( 'page_title', ucfirst( $this->postGetValue( 'page_title' ) ) );
                $this->update( 'page' )->set()->where( "page_id = $this->page_id" )->execute();
                $this->redirect( "$this->baseUri/admin/pagina/process-ok/" );
            }
        }
    }

    public function remover()
    {
        if ( isset( $this->uri_segment[2] ) )
        {
            $this->page_id = $this->uri_segment[2];
            $this->delete()->from( 'page' )->where( "page_id = $this->page_id" )->execute();
            $this->redirect( "$this->baseUri/admin/pagina/process-ok/" );
        }
    }

    public function pageError()
    {
        $this->tpl( 'admin/error.html' );
        $this->assign( 'msgError', $this->msgError );
        $this->render();
    }

}

/*end file*/