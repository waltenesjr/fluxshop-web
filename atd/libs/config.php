<?php

if ( file_exists( "../app/database/database.conf.php" ) )
{
    require_once "../app/database/database.conf.php";
    $webimroot = "../atd";
}
elseif ( file_exists( "../../app/database/database.conf.php" ) )
{
    require_once "../../app/database/database.conf.php";
    $webimroot = "../";
}


$mysqlhost = $databases['default']['host'];
$mysqldb = $databases['default']['dbname'];

$mysqllogin = $databases['default']['user'];
$mysqlpass = $databases['default']['password'];

$mailBox = $databases['default']['emailAdmin'];

$mysqlprefix = "";


/*
 *  Application path on server
 */
//$webimroot = "../atd";

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