<?php

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
$url = explode( "/atd/", get_current_url() );
$httpurl = $url[0];

function getBase()
{
	$__self = explode( '/',$_SERVER['PHP_SELF'] );
	$__level = ( count($__self) - 3 );
	if($__level >= 1)
	{
		$__flag = 1;
		$__base = str_repeat( "../", $__level);
		while($__flag == 1)
		{
			if (!file_exists( $__base."app/database/database.conf.php" ) )
			{
				$__base = str_repeat( "../", $__level);
				$__level--;
			}
			else
			{
				$__flag = 0;
			}
		}
		return $__base;
	}
}
//$base = getBase();
if (file_exists( "../../app/database/database.conf.php" ) ){
	require_once "../../app/database/database.conf.php";
}
elseif(file_exists( "../app/database/database.conf.php" ) ){
	  require_once "../app/database/database.conf.php";}
elseif(file_exists( "app/database/database.conf.php" ) ){
	  require_once "app/database/database.conf.php";}	  

$mysqlhost = $databases['default']['host'];
$mysqldb = $databases['default']['dbname'];;
$mysqllogin = $databases['default']['user'];
$mysqlpass = $databases['default']['password'];

$mailBox = $databases['default']['emailAdmin'];

$mysqlprefix = "";


/*
 *  Application path on server
 */
$webimroot = "$httpurl/atd";

/*
 *  Internal encoding
 */
$webim_encoding = "utf-8";


$dbencoding = "utf8";
$force_charset_in_connection = true;

/*
 *  Mailbox
 */
$webim_mailbox = "$mailBox";
$mail_encoding = "utf-8";

/*
 *  Locales
 */
$home_locale = "pt-br"; /* native name will be used in this locale */
$default_locale = "pt-br"; /* if user does not provide known lang */

?>