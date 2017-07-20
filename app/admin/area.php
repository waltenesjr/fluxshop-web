<?php

class Area extends PHPFrodo
{

    public $user_login;
    public $user_id;
    public $user_name;
    public $user_level;
    public $msgError;
    public $area_id;
    public $area_title;
    public $page_title;
    public $page_id;

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
        $this->pagebase = "$this->baseUri/admin/area";
        $this->tpl( 'admin/area.html' );
        $this->select()
                ->from( 'area' )
                ->paginate( 15 )
                ->orderby( 'area_title asc' )
                ->execute();
        if ( $this->result() )
        {
            $catData = $this->data;
            foreach ( $catData as $idx => $cat )
            {
                $catData[$idx]['area_page'] = 0;
                $c = ( object ) $cat;
                $this->select()
                        ->from( 'page' )
                        ->where( "page_area = $c->area_id" )
                        ->execute();
                if ( $this->result() )
                {
                    $catData[$idx]['area_page'] = count( $this->data );
                }
            }
            $this->data = $catData;
            $this->fetch( 'rs', $this->data );
            $this->assign( 'area_qtde', count( $catData ) );
        }
        $this->render();
    }

    public function incluir()
    {
        if ( $this->postIsValid( array( 'area_title' => 'string' ) ) )
        {
            $this->postIndexAdd( 'area_url', $this->urlmodr( $this->postGetValue( 'area_title' ) ) );
            $this->postValueChange( 'area_title', ucfirst( $this->postGetValue( 'area_title' ) ) );
            $this->insert( 'area' )->fields()->values()->execute();
            $this->redirect( "$this->baseUri/admin/area/process-ok/" );
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
            if ( $this->postIsValid( array( 'area_title' => 'string' ) ) )
            {
                $this->area_id = $this->uri_segment[2];
                $this->postIndexAdd( 'area_url', $this->urlmodr( $this->postGetValue( 'area_title' ) ) );
                $this->postValueChange( 'area_title', ucfirst( $this->postGetValue( 'area_title' ) ) );
                $this->update( 'area' )->set()->where( "area_id = $this->area_id" )->execute();
                $this->redirect( "$this->baseUri/admin/area/process-ok/" );
            }
        }
    }

    public function remover()
    {
        if ( isset( $this->uri_segment[2] ) )
        {
            $this->area_id = $this->uri_segment[2];
            $this->delete()->from( 'area' )->where( "area_id = $this->area_id" )->execute();
            $this->redirect( "$this->baseUri/admin/area/process-ok/" );
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