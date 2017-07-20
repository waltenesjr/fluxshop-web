<?php

class Retirada extends PHPFrodo
{

    public $login = null;
    public $user_login;
    public $user_id;
    public $user_name;
    public $user_level;
    public $retirada_id;
    public $retirada_cpf;
    public $retirada_nome;
    public $retirada_email;

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
        $this->tpl( 'admin/retirada.html' );
        $this->select()
                ->from( 'retirada' )
                ->orderby( 'retirada_local asc' )
                ->execute();
        if ( $this->result() )
        {
            $this->fetch( 'addr', $this->data );
        }
        $this->render();
    }

    public function incluir()
    {
        if ( $this->postIsValid( array( 'retirada_local' => 'string' ) ) )
        {
            $this->insert( 'retirada' )->fields()->values()->execute();
        }
        $this->redirect( "$this->baseUri/admin/retirada/" );
    }

    public function atualizar()
    {
        if ( isset( $this->uri_segment[2] ) )
        {
            $this->retirada_id = $this->uri_segment[2];
            if ( $this->postIsValid( array( 'retirada_local' => 'string' ) ) )
            {
                $this->update( 'retirada' )->set()->where( "retirada_id = $this->retirada_id" )->execute();
            }
        }
        $this->redirect( "$this->baseUri/admin/retirada/" );
    }

    public function remover()
    {
        if ( isset( $this->uri_segment[2] ) )
        {
            $this->retirada_id = $this->uri_segment[2];
            $this->delete()
                    ->from( 'retirada' )
                    ->where( "retirada_id = $this->retirada_id" )
                    ->execute();
            $this->redirect( "$this->baseUri/admin/retirada/" );
        }
    }

    public function fillDados()
    {
        $this->select()
                ->from( 'retirada' )
                ->where( "retirada_id = $this->retirada_id" )
                ->execute();
        if ( $this->result() )
        {
            $this->assignAll();
        }
    }

    public function pageError()
    {
        
    }

}

/*end file*/