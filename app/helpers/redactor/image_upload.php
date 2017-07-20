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
// files storage folder
$dir = '../../userfiles/';
$_FILES['file']['type'] = strtolower( $_FILES['file']['type'] );

if ( $_FILES['file']['type'] == 'image/png'
        || $_FILES['file']['type'] == 'image/jpg'
        || $_FILES['file']['type'] == 'image/gif'
        || $_FILES['file']['type'] == 'image/jpeg'
        || $_FILES['file']['type'] == 'image/pjpeg' )
{
    // setting file's mysterious name
    //$file = $dir.md5(date('YmdHis')).'.jpg';
    $file = $dir . $_FILES['file']['name'];
    $filename = $_FILES['file']['name'];
    // copying
    copy( $_FILES['file']['tmp_name'], $file );
    // displaying file    
    $array = array(
        'filelink' => $httpurl . "/" . $filename
    );
    echo stripslashes( json_encode( $array ) );
}
?>