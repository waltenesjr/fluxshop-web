<?php

class Usuario extends PHPFrodo
{
    public $user_login;
    public $user_id;
    public $user_name;
    public $user_level;
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
        $this->pagebase = "$this->baseUri/admin/usuario";
        $this->tpl( 'admin/user.html' );
        $this->select()
                ->from( 'user' )
                ->paginate( 15 )
                ->orderby( 'user_name asc' )
                ->execute();
        if ( $this->result() )
        {
            $this->preg( array( '/1/', '/2/' ), array( 'Operador', 'Administrador' ), 'user_level' );
            $this->fetch( 'rs', $this->data );
            $this->assign( 'user_qtde', count( $this->data ) );
        }
        $this->render();
    }

    public function operadores()
    {
        $this->pagebase = "$this->baseUri/admin/usuario/operadores";
        $this->tpl( 'admin/user.html' );
        $this->select()
                ->from( 'user' )
                ->paginate( 15 )
                ->where( 'user_level = 1' )
                ->orderby( 'user_name asc' )
                ->execute();
        if ( $this->result() )
        {
            $this->preg( array( '/1/', '/2/' ), array( 'Operador', 'Administrador' ), 'user_level' );
            $this->assign( 'ulevel', "1" );
            $this->fetch( 'rs', $this->data );
            $this->assign( 'user_qtde', count( $this->data ) );
        }
        $this->render();
    }

    public function administradores()
    {
        $this->pagebase = "$this->baseUri/admin/usuario/administradores";
        $this->tpl( 'admin/user.html' );
        $this->select()
                ->from( 'user' )
                ->paginate( 15 )
                ->where( 'user_level = 2' )
                ->orderby( 'user_name asc' )
                ->execute();
        if ( $this->result() )
        {
            $this->preg( array( '/1/', '/2/' ), array( 'Operador', 'Administrador' ), 'user_level' );
            $this->assign( 'ulevel', "2" );
            $this->fetch( 'rs', $this->data );
            $this->assign( 'user_qtde', count( $this->data ) );
        }
        $this->render();
    }

    public function editar()
    {
        if ( isset( $this->uri_segment[2] ) )
        {
            $this->user_id = $this->uri_segment[2];
        }
        $this->tpl( 'admin/user_editar.html' );
        $this->select()
                ->from( 'user' )
                ->where( "user_id = $this->user_id" )
                ->execute();
        if ( $this->result() )
        {
            $this->assignAll();
        }
        $this->render();
    }

    public function me()
    {
        $this->editar();
    }

    public function novo()
    {
        $this->tpl( 'admin/user_novo.html' );
        $this->render();
    }

    public function incluir()
    {
        if ( $this->postIsValid( array( 'user_login' => 'string', 'user_password' => 'string' ) ) )
        {
            $this->postIndexDrop( 'user_passwordr' );
            $this->postValueChange( 'user_password', md5( $this->postGetValue( 'user_password' ) ) );
            $this->insert( 'user' )->fields()->values()->execute();

            $user_id = mysql_insert_id();
            //inclui user chat
            $login = $this->postGetValue( 'user_login' );
            $level = $this->postGetValue( 'user_level' );
            $email = $this->postGetValue( 'user_email' );
            $nome = $this->postGetValue( 'user_name' );
            $pass = $this->postGetValue( 'user_password' );
            $this->post_fields = array( );
            $this->post_values = array( );
            $permAdmin = "65535";
            $permOp = "65520";
            $perm = $permOp;
            if ( $level == 2 )
            {
                $perm = $permAdmin;
            }
            $this->insert( 'chatoperator' )
                    ->fields( array( 'vclogin', 'vclocalename', 'vccommonname', 'iperm', 'vcemail', 'vcpassword', 'vcuser' ) )
                    ->values( array( "$login", "$nome", "$nome", "$perm", "$email", "$pass", "$user_id" ) )
                    ->execute();
            $operid = mysql_insert_id();

            $this->insert( 'chatgroup' )
                    ->fields( array( 'vcemail', 'vclocalname', 'vccommonname', 'vclocaldescription', 'vccommondescription', 'vcuser' ) )
                    ->values( array( "$email", "$nome", "$nome", "$nome", "$nome", "$user_id" ) )
                    ->execute();
            $grupoid = mysql_insert_id();

            $this->insert( 'chatgroupoperator' )
                    ->fields( array( 'groupid', 'operatorid', 'vcuser' ) )
                    ->values( array( "$grupoid", "$operid", "$user_id" ) )
                    ->execute();

            $this->redirect( "$this->baseUri/admin/usuario/process-ok/" );
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
            if ( $this->postIsValid( array( 'user_email' => 'string', 'user_login' => 'string' ) ) )
            {
                $this->user_id = $this->uri_segment[2];
                $this->postIndexDrop( 'user_passwordr' );
                if ( !$this->postGetValue( 'user_password' ) )
                {
                    $this->postIndexDrop( 'user_password' );
                }
                else
                {
                    $this->postValueChange( 'user_password', md5( $this->postGetValue( 'user_password' ) ) );
                }
                $this->update( 'user' )->set()->where( "user_id = $this->user_id" )->execute();

                //inclui user chat
                $login = $this->postGetValue( 'user_login' );
                $level = $this->postGetValue( 'user_level' );
                $email = $this->postGetValue( 'user_email' );
                $nome = $this->postGetValue( 'user_name' );
                $pass = $this->postGetValue( 'user_password' );
                $this->post_fields = array( );
                $this->post_values = array( );
                $permAdmin = "65535";
                $permOp = "65520";
                $perm = $permOp;
                if ( $level == 1 )
                {
                    $perm = $permAdmin;
                }
                if ( isset( $pass ) && $pass != "")
                {
                    $f = array( 'vclogin', 'vclocalename', 'vccommonname', 'iperm', 'vcemail', 'vcpassword' );
                    $v = array( "$login", "$nome", "$nome", "$perm", "$email", "$pass" );
                }
                else
                {
                    $f = array( 'vclogin', 'vclocalename', 'vccommonname', 'iperm', 'vcemail' );
                    $v = array( "$login", "$nome", "$nome", "$perm", "$email" );
                }
                $this->update( 'chatoperator' )
                        ->set( $f, $v )
                        ->where( "vclogin = '$login'" )
                        ->execute();
                //echo $this->query;

                //atualiza grupo chat
                $f = array( 'vcemail', 'vclocalname', 'vccommonname', 'vclocaldescription', 'vccommondescription', 'vcuser' );
                $v = array( "$email", "$nome", "$nome", "$nome", "$nome", "$this->user_id" );
                $this->update( 'chatgroup' )
                        ->set( $f, $v )
                        ->where( "vcuser = '$this->user_id'" )
                        ->execute();
                $this->redirect( "$this->baseUri/admin/usuario/process-ok/" );
            }
        }
    }

    public function remover()
    {
        if ( isset( $this->uri_segment[2] ) )
        {
            $this->user_id = $this->uri_segment[2];

            //operador chat
            $this->select()->from( 'user' )->where( "user_id = $this->user_id" )->execute();
            if ( $this->result() )
            {
                $login = $this->data[0]['user_login'];
                $email = $this->data[0]['user_email'];
                $this->delete()->from( 'chatoperator' )->where( "vclogin = '$login'" )->execute();
                $this->delete()->from( 'chatgroup' )->where( "vcuser = '$this->user_id'" )->execute();
                $this->delete()->from( 'chatgroupoperator' )->where( "vcuser = '$this->user_id'" )->execute();
            }
            $this->delete()->from( 'user' )->where( "user_id = $this->user_id" )->execute();
            $this->redirect( "$this->baseUri/admin/usuario/process-ok/" );
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