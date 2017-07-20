<?php

class Social extends PHPFrodo
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
        $this->tpl( 'admin/social.html' );
        $this->select()->from( 'social' )->execute();
        if ( $this->result() )
        {
            $this->map( $this->data[0] );
            $this->assignAll();
        }
        $this->render();
    }

    public function atualizar()
    {
        if ( isset( $_POST['config_site_social'] ) && !empty( $_POST['config_site_social'] ) )
        {
            $plugin = $_POST['config_site_social'];
            $plugin = ( preg_replace( '/\s+/', ' ', $plugin ) );
            $this->update( 'config' )->set( array( 'config_site_social' ), array( "$plugin" ) )->execute();
        }

        $this->redirect( "$this->baseUri/admin/social/process-ok/" );
    }

    public function restore()
    {
        $plugin = "<div class=\"shareaholic-canvas\" data-app=\"share_buttons\" data-app-id=\"5390245\"></div> <script type=\"text/javascript\"> var shr = document.createElement(\"script\"); shr.setAttribute(\"data-cfasync\", \"false\"); shr.src = \"//dsms0mj1bbhn4.cloudfront.net/assets/pub/shareaholic.js\"; shr.type = \"text/javascript\"; shr.async = \"true\"; shr.onload = shr.onreadystatechange = function() { var rs = this.readyState; if (rs && rs != \"complete\" && rs != \"loaded\") return; var site_id = \"39e07923cec488add2e8c7d4263934e0\"; try { Shareaholic.init(site_id); } catch (e) {console.log(e)} }; var s = document.getElementsByTagName(\"script\")[0]; s.parentNode.insertBefore(shr, s); </script>";
        $plugin = ( preg_replace( '/\s+/', ' ', $plugin ) );
        $this->update( 'config' )->set( array( 'config_site_social' ), array( "$plugin" ) )->execute();
        $this->redirect( "$this->baseUri/admin/social/process-ok/" );
    }


    public function atualizarfb()
    {
        if ( isset( $_POST['social_fb'] )  )
        {
            $plugin = $_POST['social_fb'];
            $plugin = addslashes( preg_replace( '/\s+/', ' ', $plugin ) );
            $this->update( 'social' )->set( array( 'social_fb' ), array( "$plugin" ) )->execute();
        }
        $this->redirect( "$this->baseUri/admin/social/process-ok/" );
    }    
    
}

/*end file*/