<?php

class Login extends PHPFrodo
{

    public $message_login;

    public function Login()
    {
        parent::__construct();
        $this->select()
                ->from( 'config' )
                ->execute();
        if ( $this->result() )
        {
            $this->config = ( object ) $this->data[0];
            $this->assignAll();
        }
    }

    public function welcome()
    {
        $this->tpl( 'admin/login.html' );
        $this->proccess();
        $this->assign( 'message_login', "$this->message_login" );
        $this->render();
    }

    public function proccess()
    {
        if ( isset( $_POST['user_login'] ) && isset( $_POST['user_password'] ) && !empty( $_POST['user_login'] ) && !empty( $_POST['user_password'] ) )
        {
            $user_login = addslashes( trim( $_POST['user_login'] ));
            $user_password = addslashes( md5( trim( $_POST['user_password'] ) ) );
            $this->select( '*' )
                    ->from( 'user' )
                    ->where( "user_login = '$user_login' and user_password = '$user_password'" )
                    ->execute();
            if ( $this->result() )
            {
                $sid = new Session;
                $sid->start();
                $sid->init( 936000 );
                $sid->addNode( 'start', date( 'd/m/Y - h:i' ) );
                $sid->addNode( 'user_id', $this->data[0]['user_id'] );
                $sid->addNode( 'user_login', $this->data[0]['user_login'] );
                $sid->addNode( 'user_name', $this->data[0]['user_name'] );
                $sid->addNode( 'user_level', $this->data[0]['user_level'] );
                $sid->check();
                $this->redirect( "$this->baseUri/admin/" );
            }
            else
            {
                $this->message_login = "$('#form-login').popover({'trigger':'manual','placement':'right','title': 'Atenção:','content':'Login ou Senha incorretos!'});";
                $this->message_login .= "$('#form-login').popover('show');";
            }
        }
        else
        {
            $this->message_login = "$('#form-login').popover({'trigger':'manual','placement':'right','title': 'Atenção:','content':'Login ou Senha requeridos!'});";
            $this->message_login .= "$('#form-login').popover('show');";
            $this->message_login = "";
        }
    }

    public function logout()
    {
        $sid = new Session;
        @$sid->start();
        $sid->destroy();
        $sid->check();
        //sessao chat
        @setcookie( 'webim_lite', '', time() - 3600, "$this->baseUri/atd/" );
        $this->redirect( "$this->baseUri/admin/login/" );
    }

    public function repass()
    {
        if ( $this->postIsValid( array( 'user_email' => 'string' ) ) )
        {
            $user_email = $this->postGetValue( 'user_email' );
            $chars = 'abcdefghijlmnopqrstuvxwzABCDEFGHIJLMNOPQRSTUVXYWZ0123456789';
            $max = strlen( $chars ) - 1;
            $pass = "";
            $width = 8;
            for ( $i = 0; $i < $width; $i++ )
            {
                $pass .= $chars{mt_rand( 0, $max )};
            }
            $this->select( '*' )
                    ->from( 'user' )
                    ->where( "user_email = '$user_email'" )
                    ->execute();
            if ( !$this->result() )
            {
                $this->tpl( 'admin/login.html' );
                $this->message_login = "$('#form-login').hide();$('#form-login-repass').show();";
                $this->message_login .= "$('#form-login-repass').popover({'trigger':'manual', 'placement':'right', 'title': 'Recuperação de senha:', 'content':'E-mail informado não cadastrado!'});";
                $this->message_login .= "$('#form-login-repass').popover('show');";
                $this->assign( 'message_login', "$this->message_login" );
                $this->render();
                exit;
            }

            $this->update( 'user' )
                    ->set( array( 'user_password' ), array( md5( $pass ) ) )
                    ->where( "user_email = '$user_email'" );
            if ( $this->execute() )
            {
                if ( $this->result() )
                {
                    extract( $this->data[0] );
                }
                $site_title = $this->config->config_site_title;
                $bodyMail = "<h3>Recuperação de senha | $site_title</h3>";
                $bodyMail .= "<h3>Sua nova senha: $pass</h3>";
                $this->helper( 'mail' );
                global $mail;
                ///recupera dados de login da conta
                $this->select()->from( 'smtp' )->execute();
                if ( $this->result() )
                {
                    $m = ( object ) $this->data[0];
                    $mail->Port = $m->smtp_port;
                    $mail->Host = "$m->smtp_host";
                    $mail->Username = $m->smtp_username;
                    $mail->Password = $m->smtp_password;
                    $mail->From = $m->smtp_username;
                    $mail->FromName = $m->smtp_fromname;
                }
                $mail->AddAddress( "$user_email" );
                $mail->Subject = "$site_title | Recuperação de senha";
                $mail->Body = $bodyMail;

                if ( $mail->Send() )
                {
                    $this->tpl( 'admin/login.html' );
                    $this->message_login = "$('#form-login').show();$('#form-login-repass').hide();";
                    $this->message_login .= "$('#form-login #user_password').popover({'trigger':'manual', 'placement':'right', 'title': 'Recuperação de senha:', 'content':'Sua nova senha foi enviada por e-mail!'});";
                    $this->message_login .= "$('#form-login #user_password').popover('show');$('#user_email').val(\"$user_email\");";
                    $this->assign( 'message_login', "$this->message_login" );
                    $this->render();
                    exit;
                }
                else
                {
                    $this->tpl( 'admin/login.html' );
                    $this->message_login = "$('#form-login').hide();$('#form-login-repass').show();";
                    $this->message_login .= "$('#form-login-repass').popover({'trigger':'manual', 'placement':'right', 'title': 'Recuperação de senha:', 'content':'Houve um erro ao enviar o e-mail! Entre em contato com suporte!'});";
                    $this->message_login .= "$('#form-login-repass').popover('show');";
                    $this->assign( 'message_login', "$this->message_login" );
                    $this->render();
                    exit;
                }
            }
            else
            {
                $this->tpl( 'admin/login.html' );
                $this->message_login = "$('#form-login').hide();$('#form-login-repass').show();";
                $this->message_login .= "$('#form-login-repass').popover({'trigger':'manual', 'placement':'right', 'title': 'Recuperação de senha:', 'content':'Houve um erro ao alterar a senha! Entre em contato com suporte!'});";
                $this->message_login .= "$('#form-login-repass').popover('show');";
                $this->assign( 'message_login', "$this->message_login" );
                $this->render();
                exit;
            }
        }
        else
        {
            $this->tpl( 'admin/login.html' );
                $this->message_login = "$('#form-login').hide();$('#form-login-repass').show();";
                $this->message_login .= "$('#form-login-repass').popover({'trigger':'manual', 'placement':'right', 'title': 'Recuperação de senha:', 'content':'E-mail informado não cadastrado!'});";
                $this->message_login .= "$('#form-login-repass').popover('show');";
            $this->assign( 'message_login', "$this->message_login" );
            $this->render();
            exit;
        }
    }

}

/* end file */
