<?php

class Frete extends PHPFrodo
{

    public $user_login;
    public $user_id;
    public $user_name;
    public $user_level;
    public $frete_param;

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
        $this->tpl( 'admin/frete.html' );
        $this->select()
                ->from( 'frete' )
                ->execute();
        if ( $this->result() )
        {
            $this->money( 'frete_taxa' );
            $this->assignAll();
        }
        $this->render();
    }

    public function atualizar()
    {
        if ( $this->postIsValid( array( 'frete_cep_origem' => 'string' ) ) )
        {
            $this->postValueChange( 'frete_taxa', preg_replace( array( '/\./', '/\,/' ), array( '', '.' ), $this->postGetValue( 'frete_taxa' ) ) );

            if ( !$this->postGetValue( 'frete_sedex' ) )
            {
                $this->postIndexAdd( 'frete_sedex', '0' );
            }
            if ( !$this->postGetValue( 'frete_sedex10' ) )
            {
                $this->postIndexAdd( 'frete_sedex10', '0' );
            }
            if ( !$this->postGetValue( 'frete_sedexac' ) )
            {
                $this->postIndexAdd( 'frete_sedexac', '0' );
            }
            if ( !$this->postGetValue( 'frete_pac' ) )
            {
                $this->postIndexAdd( 'frete_pac', '0' );
            }
            $this->update( 'frete' )->set()->execute();
            $this->redirect( "$this->baseUri/admin/frete/process-ok/" );
        }
    }

}

/*end file*/