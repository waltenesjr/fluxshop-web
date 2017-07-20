<?php

class Smtpc extends PHPFrodo
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
        if ( $this->user_level == 1 )
        {
            $this->assign( 'showhide', 'hide' );
            $this->redirect( "$this->baseUri/admin/" );
        }
    }

    public function welcome()
    {
        $this->tpl( 'admin/smtp.html' );
        $this->select()
                ->from( 'smtp' )
                ->execute();
        if ( $this->result() )
        {
            $this->assignAll();
        }
        $this->render();
    }

    public function atualizar()
    {
        if ( $this->postIsValid( array( 'smtp_host' => 'string', 'smtp_username' => 'string' ) ) )
        {
            if ( trim( $this->postGetValue( 'smtp_password' ) ) == "" )
            {
                $this->postIndexDrop( 'smtp_password' );
            }
            $this->update( 'smtp' )->set()->execute();
            $this->redirect( "$this->baseUri/admin/smtpc/process-ok/" );
        }
    }

    public function test()
    {
        parse_str( $_POST['dados'], $post );
        $this->post2Query( $post );
        if ( trim( $this->postGetValue( 'smtp_password' ) ) == "" )
        {
            $this->postIndexDrop( 'smtp_password' );
        }
        $this->postValueChange( 'smtp_fromname', utf8_decode( $this->postGetValue( 'smtp_fromname' ) ) );
        $this->update( 'smtp' )->set()->execute();
        $this->select()->from( 'smtp' )->execute();
        if ( $this->result() )
        {
            $m = ( object ) $this->data[0];
            $this->helper( 'mail' );
            global $mail;
            $mail->Port = $m->smtp_port;
            $mail->Host = "$m->smtp_host";
            $mail->Username = $m->smtp_username;
            $mail->Password = $m->smtp_password;
            $mail->From = $m->smtp_username;
            $mail->FromName = $m->smtp_fromname;
            $mail->Subject = "Teste Envio";
            $mail->AddBCC( $m->smtp_bcc );
            $mail->AddAddress( $m->smtp_username );
            $mail->AddReplyTo( $m->smtp_replyto );
            $mail->Body = 'teste smtp';
            if ( $mail->Send() )
            {
                echo 0;
            }
            else
            {
                echo "Erro: $mail->ErrorInfo <br/> Provaveis causas: <br> - E-mail, Senha, Porta ou Servidor SMTP incorretos.";
            }
        }
        else
        {
            echo "Configuração incompleta, verifique os campos!";
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