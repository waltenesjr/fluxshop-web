<?php

class Pagamento extends PHPFrodo
{
    private $user_login;
    private $user_id;
    private $user_name;
    private $user_level;
    public $msgError;

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
        if ( $this->user_level == 1 )
        {
            $this->assign( 'showhide', 'hide' );
            $this->redirect( "$this->baseUri/admin/" );
        }
    }

    public function welcome()
    {
        //
    }

    public function pagSeguro()
    {
        $this->tpl( 'admin/pagamento_pagseguro.html' );
        $this->modPagSeguro();
        $this->render();
    }

    public function payPal()
    {
        $file = HELPERDIR . 'helper_paypal.php';
        if ( file_exists( $file ) )
        {
            $this->tpl( 'admin/pagamento_paypal.html' );
            $this->modPayPal();
            $this->render();
        }
        else
        {
            $this->msgError = "Módulo PayPal não instalado! <br>";
            $this->msgError .= "Este módulo é vendido separadamente e pode ser adquirido em http://phpstaff.clares.com.br.";
            $this->pageError();
        }
    }

    public function payBras()
    {
        $file = HELPERDIR . 'helper_paybras.php';
        if ( file_exists( $file ) )
        {
            $this->tpl( 'admin/pagamento_paybras.html' );
            $this->modPayBras();
            $this->render();
        }
        else
        {
            $this->msgError = "Módulo PayBras não instalado! <br>";
            $this->msgError .= "Este módulo é vendido separadamente e pode ser adquirido em http://phpstaff.clares.com.br.";
            $this->pageError();
        }
    }

    public function modPagSeguro()
    {
        $this->select()->from( 'pay' )->where( 'pay_name = "PagSeguro"' )->execute();
        if ( $this->result() )
        {
            $this->assignAll();
        }
    }

    public function modPayPal()
    {
        $this->select()->from( 'pay' )->where( 'pay_name = "PayPal"' )->execute();
        if ( $this->result() )
        {
            $this->assignAll();
        }
    }

    public function modPayBras()
    {
        $this->select()->from( 'pay' )->where( 'pay_name = "PayBras"' )->execute();
        if ( $this->result() )
        {
            $this->assignAll();
        }
    }

    public function atualizar()
    {
        if ( $this->postIsValid( array( 'pay_key' => 'string' ) ) )
        {
            $this->pay_id = $this->postGetValue( 'pay_id' );
            $this->pay_name = $this->uri_segment[2];
            $this->postIndexAdd( 'pay_retorno', "$this->baseUri/notificacao/" );
            $this->update( 'pay' )->set()->where( "pay_id = $this->pay_id" )->execute();
            $this->redirect( "$this->baseUri/admin/pagamento/$this->pay_name/process-ok/" );
        }
    }

    public function pageError()
    {
        $this->tpl( 'admin/error.html' );
        $this->assign( 'msgError', $this->msgError );
        $this->render();
        exit;
    }
}
/*end file*/