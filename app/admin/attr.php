<?php

class Attr extends PHPFrodo
{

    public $user_login;
    public $user_id;
    public $user_name;
    public $user_level;
    public $msgError;
    public $atributo_id;
    public $atributo_title;
    public $iattr_id;
    public $iattr_valor;
    public $item_id;
    public $item_title;

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
        if ( isset( $this->uri_segment ) && in_array('process-ok',$this->uri_segment) )
        {
            $this->assign( 'msgOnload', 'notify("<h1>Procedimento realizado com sucesso</h1>")' );
        }
        if ( $this->user_level == 1 ) {
            $this->assign('showhide','hide');
        }         
    }

    public function welcome()
    {
        $this->pagebase = "$this->baseUri/admin/attr";
        $this->tpl( 'admin/attr.html' );
        $this->select()
                ->from( 'iattr' )
                ->join( 'atributo', 'iattr_atributo = atributo_id', 'INNER' )
                ->paginate( 15 )
                ->orderby( 'iattr_valor asc' )
                ->execute();
        if ( $this->result() )
        {
            $this->fetch( 'rs', $this->data );
            $this->assign( 'iattr_qtde', count( $this->data  ) );
        }
        $this->fillCategoria();
        $this->render();
    }

    public function fillCategoria()
    {
        $this->select()
                ->from( 'atributo' )
                ->orderby( 'atributo_title asc' )
                ->execute();
        if ( $this->result() )
        {
            $this->fetch( 'combo', $this->data );
        }
    }

    public function incluir()
    {
        if ( $this->postIsValid( array( 'iattr_valor' => 'string' ) ) )
        {
            $this->postIndexAdd( 'iattr_url', $this->urlmodr( $this->postGetValue( 'iattr_valor' ) ) );
            $this->postValueChange( 'iattr_valor', ucfirst( $this->postGetValue( 'iattr_valor' ) ) );
            $this->insert( 'iattr' )->fields()->values()->execute();
            $this->redirect( "$this->baseUri/admin/attr/process-ok/" );
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
            if ( $this->postIsValid( array( 'iattr_valor' => 'string' ) ) )
            {
                $this->iattr_id = $this->uri_segment[2];
                $this->postIndexAdd( 'iattr_url', $this->urlmodr( $this->postGetValue( 'iattr_valor' ) ) );
                $this->postValueChange( 'iattr_valor', ucfirst( $this->postGetValue( 'iattr_valor' ) ) );
                $this->update( 'iattr' )->set()->where( "iattr_id = $this->iattr_id" )->execute();
                $this->redirect( "$this->baseUri/admin/attr/process-ok/" );
            }
        }
    }

    public function remover()
    {
        if ( isset( $this->uri_segment[2] ) )
        {
            $this->iattr_id = $this->uri_segment[2];
            if ( $this->removeItem() )
            {
                $this->delete()->from( 'iattr' )->where( "iattr_id = $this->iattr_id" )->execute();
                $this->redirect( "$this->baseUri/admin/attr/process-ok/" );
            }
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