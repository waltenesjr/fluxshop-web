<?php

class Atributo extends PHPFrodo
{

    public $user_login;
    public $user_id;
    public $user_name;
    public $user_level;
    public $msgError;
    public $atributo_id;
    public $iattr_id;
    public $iattr_title;
    public $item_id;
    public $item_title;
    public $atributo_nome;

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
        $this->pagebase = "$this->baseUri/admin/atributo";
        $this->tpl( 'admin/atributo.html' );
        $this->select()
                ->from( 'atributo' )
                ->paginate( 15 )
                ->orderby( 'atributo_nome asc' )
                ->execute();
        if ( $this->result() )
        {
            $catData = $this->data;
            foreach ( $catData as $idx => $cat )
            {
                $catData[$idx]['atributo_item'] = 0;
                $catData[$idx]['atributo_iattr'] = 0;
            }
            $this->data = $catData;
            $this->fetch( 'rs', $this->data );
            $this->assign( 'atributo_qtde', count( $catData ) );
        }
        $this->render();
    }

    public function incluir()
    {
        if ( $this->postIsValid( array( 'atributo_nome' => 'string' ) ) )
        {
            $this->postValueChange( 'atributo_nome', ucfirst( $this->postGetValue( 'atributo_nome' ) ) );
            $this->insert( 'atributo' )->fields()->values()->execute();
            $this->redirect( "$this->baseUri/admin/atributo/process-ok/" );
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
            if ( $this->postIsValid( array( 'atributo_nome' => 'string' ) ) )
            {
                $this->atributo_id = $this->uri_segment[2];
                $this->postValueChange( 'atributo_nome', ucfirst( $this->postGetValue( 'atributo_nome' ) ) );
                $this->update( 'atributo' )->set()->where( "atributo_id = $this->atributo_id" )->execute();
                $this->redirect( "$this->baseUri/admin/atributo/process-ok/" );
            }
        }
    }

    public function remover()
    {
        if ( isset( $this->uri_segment[2] ) )
        {
            $this->atributo_id = $this->uri_segment[2];
            $this->delete()->from( 'atributo' )->where( "atributo_id = $this->atributo_id" )->execute();
            $this->redirect( "$this->baseUri/admin/atributo/process-ok/" );
        }
    }

    public function editar()
    {
        if ( isset( $this->uri_segment[2] ) )
        {
            $this->atributo_id = $this->uri_segment[2];
            $this->tpl( 'admin/atributo_editar.html' );
            $this->select()
                    ->from( 'atributo' )
                    ->where( "atributo_id = $this->atributo_id" )
                    ->execute();
            if ( $this->result() )
            {
                $this->assignAll();
                $this->select()
                        ->from( 'atributo' )
                        ->join( 'iattr', 'iattr_atributo = atributo_id', 'INNER' )
                        ->where( "atributo_id = $this->atributo_id" )
                        ->execute();
                if ( $this->result() )
                {
                    $this->fetch( 'rs', $this->data );
                }
            }
            $this->render();
        }
    }

    public function atualizaitem()
    {
        if ( isset( $this->uri_segment[2] ) )
        {
            if ( $this->postIsValid( array( 'iattr_nome' => 'string' ) ) )
            {
                $this->iattr_id = $this->uri_segment[2];
                $this->atributo_id = $this->uri_segment[3];
                $this->postValueChange( 'attr_nome', ucfirst( $this->postGetValue( 'attr_nome' ) ) );
                $this->update( 'iattr' )->set()->where( "iattr_id = $this->iattr_id" )->execute();
                $this->redirect( "$this->baseUri/admin/atributo/editar/$this->atributo_id/process-ok/" );
            }
        }
    }

    public function additem()
    {
        $this->atributo_id = $this->uri_segment[2];
        if ( $this->postIsValid( array( 'iattr_nome' => 'string' ) ) )
        {
            if ( $this->postGetValue( 'iattr_estoque' ) == "" )
            {
                $this->postValueChange( 'iattr_estoque', 10 );
            }
            $this->postValueChange( 'iattr_nome', $this->postGetValue( 'iattr_nome' ) );
            $this->postIndexAdd( 'iattr_atributo', $this->atributo_id );
            $this->insert( 'iattr' )->fields()->values()->execute();
            $this->redirect( "$this->baseUri/admin/atributo/editar/$this->atributo_id/process-ok/" );
        }
        else
        {
            $this->msgError = $this->response;
            $this->pageError();
        }
    }

    public function removeritem()
    {
        if ( isset( $this->uri_segment[2] ) )
        {
            $this->iattr_id = $this->uri_segment[2];
            $this->atributo_id = $this->uri_segment[3];
            $this->delete()->from( 'iattr' )->where( "iattr_id = $this->iattr_id" )->execute();
            $this->redirect( "$this->baseUri/admin/atributo/editar/$this->atributo_id/process-ok/" );
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