<?php

@header( 'Content-Type: text/html; charset=iso-8859-1' );
error_reporting( 0 );

function get_current_url()
{
    $protocol = 'http';
    if ( $_SERVER['SERVER_PORT'] == 443 || (!empty( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on') )
    {
        $protocol .= 's';
        $protocol_port = $_SERVER['SERVER_PORT'];
    }
    else
    {
        $protocol_port = 80;
    }
    $host = $_SERVER['HTTP_HOST'];
    $port = $_SERVER['SERVER_PORT'];
    $request = $_SERVER['PHP_SELF'];
    if ( isset( $_SERVER['argv'][0] ) )
        $query = substr( $_SERVER['argv'][0], strpos( $_SERVER['argv'][0], ';' ) + 1 );
    $toret = $protocol . '://' . $host . ($port == $protocol_port ? '' : ':' . $port) . $request . (empty( $query ) ? '' : '?' . $query);
    return $toret;
}
if ( !preg_match( '/www/', get_current_url() ) )
{
    $protocol = "http://";
}
else
{
    $protocol = 'http://www.';
}
$url = explode( "index.php", get_current_url() );
$httpurl = $url[0];
$sub = explode( ".", $httpurl );
if ( count( $sub ) >= 4 )
{
    $sub = preg_replace( '/http:\/\//', '', $sub[0] );
    define( 'SUB', "$sub" );
}
else
{
    define( 'SUB', "" );
}
# Configurar diretorio do projeto HTTPURL
define( "HTTPURL", "$httpurl" );
# Configuracao de Diretorios 
define( 'APP', 'app/' );
define( 'BASEURL', APP );
define( 'CLASSDIR', APP . 'class/' );
define( "VIEWSDIR", APP . "views/" );
define( "HELPERDIR", APP . "helpers/" );
define( "LIB", APP . "lib/" );
define( "DATABASEDIR", APP . "database/" );
define( "REALPATH", dirname( __FILE__ ) );
define( "REALPATH_APP", dirname( __FILE__ ) . "/" . APP );
# Não alterar
if ( file_exists( CLASSDIR . 'PHPFrodo.class.php' ) )
{
    require_once CLASSDIR . 'PHPFrodo.class.php';
}
else
{
    echo "PHPFrodo.class.php não encontrado!";
    exit;
}
# Subdirs e base configurados em .htaccess
# não alterar
if ( isset( $_GET['dir'] ) && $_GET['dir'] != '' )
{
    if ( substr( $_GET['dir'], -1 ) != '/' )
    {
        $dirname = APP . $_GET['dir'] . "/";
    }
    else
    {
        $dirname = APP . $_GET['dir'];
    }
    if ( is_dir( $dirname ) )
    {
        define( 'CTRL', "$dirname" );
    }
    else
    {
        @header( 'Location: error.php' );
    }
}
else
{
    define( 'CTRL', APP );
}
# Route .htaccess
if ( isset( $_GET['route'] ) )
{
    $routes = explode( "/", $_GET['route'] );

    if ( count( $routes ) == 1 )
    {
        $routes[1] = "error";
    }
    $class = $routes[0];
    if ( isset( $routes[1] ) && $routes[0] != SUB )
    {
        $action = $routes[1];
        $obj = new $class;
        # metodo inicial quando nenhum é passado na uri welcome()
        # padronizar apenas se welcome() deve ser o metodo inicial
        ( method_exists( $obj, $action ) ) ? $obj->$action() : $obj->welcome();
    }
}

function __autoload( $class )
{
    $native = array('Finfo','finfo');
    if(!in_array($class, $native))
    {
        $classFile = CLASSDIR . ucfirst( $class ) . '.class.php';
        $ctrlFile = CTRL . strtolower( $class ) . '.php';
        if ( file_exists( $classFile ) )
        {
            include $classFile;
        }
        elseif ( file_exists( $ctrlFile ) )
        {
            include $ctrlFile;
        }
        elseif ( file_exists( ucfirst( $ctrlFile ) ) )
        {
            include ucfirst( $ctrlFile );
        }
        else
        {
            //echo "";exit;
            //@header( 'Location:' . HTTPURL . '404.php?return='.HTTPURL );
        }
    }
}