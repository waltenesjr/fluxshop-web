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

if ( !preg_match( '/www/', get_current_url() ) )
{
    $protocol = "http://";
}
else
{
    $protocol = 'http://www.';
}
$url = explode( "/helpers", get_current_url() );
$httpurl = $url[0] . "/userfiles";
$filename = $_FILES['file']['name'];

copy($_FILES['file']['tmp_name'], '../../userfiles/'.$_FILES['file']['name']);
$array = array(
	'filelink' => $httpurl . "/" . $filename,
	'filename' => $_FILES['file']['name']
);
echo stripslashes(json_encode($array));
	
?>