<?php

class Index extends PHPFrodo
{
    private $user_login;
    private $user_id;
    private $user_name;
    private $user_level;

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

        $this->user_login = @$sid->getNode( 'user_login' );
        $this->user_id = @$sid->getNode( 'user_id' );
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
        if ( $this->user_name == "" )
        {
            $this->redirect( "$this->baseUri/admin/login/logout/" );
        }

        //if (isset($_COOKIE["webim_lite"])){
        //setcookie ("webim_lite", "", time() - 3600);
        //setcookie( 'webim_lite', "asdas", time() + 60 * 60 * 24 * 1000, "$this->baseUri/atd/" );
        //unset($_COOKIE["webim_lite"]);
        //$this->printr($_COOKIE['webim_lite']);
        //}
        setcookie( 'webim_lite', '', time() - 3600, "$this->baseUri/atd/" );

        //$_SESSION["operator"] = "";
        //sessao atendimento
        $this->select()->from( 'chatoperator' )->where( "vclogin = '$this->user_login'" )->execute();
        if ( $this->result() )
        {
            $value = $this->user_login . "," . $this->data[0]['vcpassword'];
            $_SESSION["operator"] = $this->data[0];

            setcookie( 'webim_lite', $value, time() + 60 * 60 * 24 * 1000, "$this->baseUri/atd/" );
        }

        if ( $this->user_level == 1 )
        {
            $this->assign( 'showhide', 'hide' );
        }
    }

    public function welcome()
    {
        $this->tpl( 'admin/dashboard.html' );
        $this->select()->from( 'versao' )->execute();
        $this->_v = ( object ) $this->data[0];
        $server = preg_replace( '/www\./', '', $_SERVER['SERVER_NAME'] );
        $cURL = curl_init( 'http://fluxshop.com.br/updates/last15.php' );
        @curl_setopt( $cURL, CURLOPT_RETURNTRANSFER, true );
        @curl_setopt( $cURL, CURLOPT_FOLLOWLOCATION, true );
        @curl_setopt( $cURL, CURLOPT_POST, 1 );
        @curl_setopt( $cURL, CURLOPT_POSTFIELDS, array(
            'server' => $server,
            'ip' => $_SERVER['REMOTE_ADDR'],
            'host' => "$this->baseUri"
        ));
        @curl_setopt( $cURL, CURLOPT_SSL_VERIFYPEER, false );
        $resultado = curl_exec( $cURL );
        $resposta = curl_getinfo( $cURL, CURLINFO_HTTP_CODE );
        curl_close( $cURL );
        if ( $resposta == '404' )
        {
            $txt = "Versão  Atual [" . $this->_v->versao_update . "] - Não foi possível localizar atualizações!";
        }
        else
        {
            $get_last = explode( "|", $resultado );
            $last = $get_last[0];
            $last_i = ( float ) $get_last[1];
            $current = $this->_v->versao_update;
            $current_i = ( float ) $this->_v->versao_num;
            $link = $get_last[2];
            $news = $get_last[3];
            $txt = "Versão Atual:  $current ";
            if ( $last_i > $current_i )
            {
                $txt .= "<br /> Versão Disponível: $last ";
                $txt .= " | $link";
                if ( $news != "" )
                {
                    $txt .= "<br />" . utf8_decode( $news );
                }
            }
            else
            {
                $txt = " Versão [$current] *** Você possui a versão mais recente do sistema!";
            }
        }
        $this->assign( 'versao', $txt );
        $this->render();
    }
}
/*end file*/
