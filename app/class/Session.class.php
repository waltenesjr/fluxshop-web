<?php

/**
 * Session class
 *
 * @author Rafael Clares <rafadinix@gmail.com>
 * @version 1.0  <10/2010>
 * web: www.clares.wordpress.com
 * 
 */
Class Session {

    /**
     * $session = new Session;
     * $session->start();
     * $session->init(TimeLife);
     * $session->setMsg("Hello, ".$session->getId()."." );
     * $session->check();
     * $session->status();
     * $session->destroy();
     */
    private $session_id = null;
    public $session_message = "A sessão requerida não está ativa";
    public $minutos = 0;

    public function start() {
        @session_start();
    }

    public function init( $timeLife = null ) {
        $_SESSION['ACTIVITY_ID'] = md5( uniqid( time() ) );
        $_SESSION['START_ACTIVITY'] = time();
        $_SESSION['LAST_ACTIVITY'] = time();
        if ( $timeLife != null ) {
            $_SESSION['LIFE_TIME'] = $timeLife;
        }
        else {
            $_SESSION['LIFE_TIME'] = 1800;
        }
    }

    public function getTime() {
        $this->minutos = floor( (time() - $_SESSION['START_ACTIVITY']) / 60 );
        return $this->minutos;
    }

    public function getLeftTime() {
        $minutos = floor( ($_SESSION['LIFE_TIME'] - (time() - $_SESSION['LAST_ACTIVITY']) ) / 60 );
        $segundos = (($_SESSION['LIFE_TIME'] - (time() - $_SESSION['LAST_ACTIVITY']) ) % 60 );
        if ( $segundos <= 9 ) {
            $segundos = "0" . $segundos;
        }
        return "$minutos:$segundos";
    }

    public function addNode( $key, $value ) {
        $_SESSION['node'][$key] = $value;
        return $this;
    }

    public function getNode( $key ) {
        if ( isset( $_SESSION['node'][$key] ) ) {
            return $_SESSION['node'][$key];
        }
    }

    public function remNode( $key ) {
        if ( isset( $_SESSION['node'][$key] ) ) {
            unset( $_SESSION['node'][$key] );
        }
        return $this;
    }

    public function destroyNodes() {
        if ( isset( $_SESSION['node'] ) ) {
            unset( $_SESSION['node'] );
        }
        return $this;
    }

    public function check( $showMessage = null ) {
        if ( !isset( $_SESSION['LAST_ACTIVITY'] ) || (time() - $_SESSION['LAST_ACTIVITY'] >= $_SESSION['LIFE_TIME']) ) {
            //$this->destroy();
            return false;
        }
        else {
            return true;
        }
    }

    public function setMsg( $msg ) {
        $this->session_message = $msg;
    }

    public function getId() {
        if ( isset( $_SESSION['ACTIVITY_ID'] ) ) {
            return $_SESSION['ACTIVITY_ID'];
        }
        else {
            $this->setMsg( "sessão nula" );
            return $this->session_message;
        }
    }

    public function status() {
        return $this->session_message;
    }

    public function destroy() {
        @session_destroy();
        if ( isset( $_SESSION['LAST_ACTIVITY'] ) ) {
            unset( $_SESSION['LAST_ACTIVITY'] );
        }
        if ( isset( $_SESSION['LIFE_TIME'] ) ) {
            unset( $_SESSION['LIFE_TIME'] );
        }
        if ( isset( $_SESSION['ACTIVITY_ID'] ) ) {
            unset( $_SESSION['ACTIVITY_ID'] );
        }
        return false;
    }

}

/* end file */