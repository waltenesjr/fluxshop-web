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
$httpurl = $url[0];


$dir = "../../userfiles/";
$json = "[\n";
# Extensoes permitidas
$exts = array( 'jpg', 'png', 'jpeg', 'gif', 'bmp' );
if ( is_dir( $dir ) )
{
    $d = opendir( $dir );
    if ( $d )
    {
        while ( ($file = readdir( $d )) !== false )
        {
            if ( filetype( $dir . '/' . $file ) == 'file' )
            {
                # recupera a extensao do arquivo
                $extensao = explode( ".", $file );
                for ( $i = 0; $i <= count( $exts ) - 1; $i++ )
                {
                    if ( $extensao[1] == $exts[$i] )
                    {
                        $json .=" { \"thumb\": \"helpers/redactor/thumb.php?img={$file}\", \"image\": \"{$httpurl}/userfiles/{$file}\" },\n";
                    }
                }
            }
        }
        closedir( $d );
    }
}
$json = substr( $json, 0, -2 );
$json .= " ]";
echo $json;
?>