<?php

class Route 
{

    public function __construct()
    {
        $sid = new Session;
        $sid->start();
    }

    public function set( $url )
    {
        $this->drop();
        $_SESSION['FLUX_CURRENT_URL'] = $url;
    }

    public function drop()
    {
        if ( isset( $_SESSION['FLUX_CURRENT_URL'] ) )
        {
            unset( $_SESSION['FLUX_CURRENT_URL'] );
        }
        $_SESSION['FLUX_CURRENT_URL'] = "";
    }

    public function get()
    {
        if ( !isset( $_SESSION['FLUX_CURRENT_URL'] ) )
        {
            $_SESSION['FLUX_CURRENT_URL'] = "";
        }
        return (string) $_SESSION['FLUX_CURRENT_URL'];
    }
    
}
/*end file*/