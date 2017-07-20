<?php

class Entrega extends PHPFrodo
{

    public $login = null;
    public $user_login;
    public $user_id;
    public $user_name;
    public $user_level;
    public $entrega_id;
    public $entrega_cpf;
    public $entrega_nome;
    public $entrega_email;

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
        $this->tpl( 'admin/entrega.html' );
        $this->select()
                ->from( 'entrega' )
                //->orderby( 'entrega_local asc' )
                ->execute();
        if ( $this->result() )
        {
            $this->clonekey( 'entrega_cobertura', array( 'entrega_tipo' ) );
            foreach ( $this->data as $k => $v )
            {
                if ( $this->data[$k]['entrega_tipo'] == 1 )
                {
                    $this->data[$k]['entrega_cobertura'] = " UF - " . $this->data[$k]['entrega_uf'];
                }
                elseif ( $this->data[$k]['entrega_tipo'] == 2 )
                {
                    $this->data[$k]['entrega_cobertura'] = " Cidade - " . $this->data[$k]['entrega_cidade'];
                }
                else
                {
                    $this->data[$k]['entrega_cobertura'] = " Bairro - " . $this->data[$k]['entrega_bairro'];
                }
            }
            $this->fetch( 'addr', $this->data );
        }
        $this->render();
    }

    public function incluir()
    {
        if ( $this->postIsValid( array( 'entrega_cep' => 'string' ) ) )
        {
            $this->insert( 'entrega' )->fields()->values()->execute();
            $this->redirect( "$this->baseUri/admin/entrega/process-ok/" );
        }
    }

    public function atualizar()
    {
        if ( isset( $this->uri_segment[2] ) )
        {
            $this->entrega_id = $this->uri_segment[2];
            if ( $this->postIsValid( array( 'entrega_cep' => 'string' ) ) )
            {
                $this->update( 'entrega' )->set()->where( "entrega_id = $this->entrega_id" )->execute();
                $this->redirect( "$this->baseUri/admin/entrega/process-ok/" );
            }
            else
            {
                $this->redirect( "$this->baseUri/admin/entrega/proccess-fail/" );
            }
        }
        else
        {
            $this->redirect( "$this->baseUri/admin/entrega/proccess-fail/" );
        }
    }

    public function remover()
    {
        if ( isset( $this->uri_segment[2] ) )
        {
            $this->entrega_id = $this->uri_segment[2];
            $this->delete()
                    ->from( 'entrega' )
                    ->where( "entrega_id = $this->entrega_id" )
                    ->execute();
            $this->redirect( "$this->baseUri/admin/entrega/process-ok/" );
        }
    }

    public function fillDados()
    {
        $this->select()
                ->from( 'entrega' )
                ->where( "entrega_id = $this->entrega_id" )
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