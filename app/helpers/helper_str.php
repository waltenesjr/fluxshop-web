<?php

function redirect( $url )
{
    @header( "location: $url" );
}

function fulltrim( $str )
{
    $str = preg_split( "/\s+/", trim( $str ) );
    $str = implode( " ", $str );
    return $str;
}

function clearChars( $str )
{
    $array1 = array( "á", "à", "â", "ã", "ä", "é", "è", "ê", "ë", "í", "ì", "î", "ï", "ó", "ò", "ô",
            "õ", "ö", "ú", "ù", "û", "ü", "ç", "Á", "À", "Â", "Ã", "Ä", "É", "È", "Ê", "Ë", "Í",
            "Ì", "Î", "Ï", "Ó", "Ò", "Ô", "Õ", "Ö", "Ú", "Ù", "Û", "Ü", "Ç" );
    $array2 = array( "a", "a", "a", "a", "a", "e", "e", "e", "e", "i", "i", "i", "i", "o", "o", "o",
            "o", "o", "u", "u", "u", "u", "c", "A", "A", "A", "A", "A", "E", "E", "E", "E", "I",
            "I", "I", "I", "O", "O", "O", "O", "O", "U", "U", "U", "U", "C" );
    return str_replace( $array1, $array2, $str );
}

function strEncoder( $campo, $decode )
{
    return $decode( $campo );
}

function crumbs()
{
    $uri = $_SERVER['QUERY_STRING'];
    $uri = preg_replace( '/\=/', '/', $uri );
    $uri = preg_replace( '/\&/', '/', $uri );
    $uri = explode( '/', $uri );
    $uri = join( "/", $uri );
    return $uri;
}

/**
 * Utilizado para formatar tamanhos de arquivos
 *
 * @param String  $key
 * @example byteSize('file_size');
 */
function byteSize( $key, $data )
{
    if( !empty( $data ) )
    {
        foreach( $data as $idx => $item )
        {
            if( isset( $item[trim( $key )] ) )
            {
                $size = $item[trim( $key )];
                $filesizename = array( " Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB" );
                $data[$idx][trim( $key )] = $size ? round( $size / pow( 1024, ($i = floor( log( $size, 1024 ) ) ) ), 2 ) . $filesizename[$i] : '0 Bytes';
            }
        }
        return $data;
    }
}


function diffDate( $d1, $d2, $type='', $sep='-' )
{
    if( $d1 == "0000-00-00" || $d1 == "0000-00-00" )
    {
        return false;
    }
    $d1 = explode( $sep, $d1 );
    $d2 = explode( $sep, $d2 );
    switch( $type )
    {
        case 'A':
            $X = 31536000;
            break;
        case 'M':
            $X = 2592000;
            break;
        case 'D':
            $X = 86400;
            break;
        case 'H':
            $X = 3600;
            break;
        case 'MI':
            $X = 60;
            break;
        default:
            $X = 1;
    }
    return floor( ((mktime( 0, 0, 0, $d1[1], $d1[2], $d1[0] ) - mktime( 0, 0, 0, $d2[1], $d2[2], $d2[0] )) / $X ) );
}

function urlModQuery( $str, $ret = null, $sep = '-')
{
    $str = strtolower( preg_replace( array( '/[^a-zA-Z0-9 -]/', '/[ -]+/', '/^-|-$/' ), array( "$sep", "$sep", '' ), clearChars( $str ) ) );
    $parts = explode( " ", $str );
    $part = $parts[0];

    if( isset( $parts[1] ) )
    {
        $part .= " " . $parts[1];
    }
    if( isset( $parts[2] ) && count( $parts ) >= 4 )
    {
        $part .= " " . $parts[2];
    }
    if( $ret != null )
        return $part;
    else
        return $str;
}

function cloudTag( $array , $url = null)
{
   if(count($array) >= 2)
   {
    if($url == null)
    {
        $url = "javascript:void(0)";
    }
    $str = "";
    $factor = 0.2;
    $starting_font_size = 11;
    $tag_separator = '&nbsp; &nbsp; &nbsp;';
    $random_order = true;

    $max_count = array_sum( $array );
    
    $rand_items = array_rand( $array, count( $array ) );
    $tags = array();

    foreach( $rand_items as $value )
    {
        $tags[$value] = $array[$value];
    }

    foreach( $tags as $tag => $rating )
    {
        $x = round( ($rating * 100) / $max_count ) * $factor;
        $font_size = $starting_font_size + $x . 'px';
        $str .= "<span style='font-size: " . $font_size . ";'>
	<a href='".$url."".urlModQuery($tag)."/' style='color: #666; text-decoration:none;'>". $tag . "</a></span>" . $tag_separator;
    }
   return $str;
   }

}

//fn obrigatoria
function validaCpf( $cpf )
{
    $cpf  = preg_replace('/[^0-9]/i','',$cpf);
    $s = $cpf;
    $c = substr( $s, 0, 9 );
    $dv = substr( $s, 9, 2 );
    $d1 = 0;
    $v = false;

    for( $i = 0; $i < 9; $i++ )
    {
        $d1 = $d1 + substr( $c, $i, 1 ) * (10 - $i);
    }
    if( $d1 == 0 )
    {
        return false;
        $v = true;
    }
    $d1 = 11 - ($d1 % 11);
    if( $d1 > 9 )
    {
        $d1 = 0;
    }
    if( substr( $dv, 0, 1 ) != $d1 )
    {
        return false;
        $v = true;
    }
    $d1 = $d1 * 2;
    for( $i = 0; $i < 9; $i++ )
    {
        $d1 = $d1 + substr( $c, $i, 1 ) * (11 - $i);
    }
    $d1 = 11 - ($d1 % 11);
    if( $d1 > 9 )
    {
        $d1 = 0;
    }
    if( substr( $dv, 1, 1 ) != $d1 )
    {
        return false;
        $v = true;
    }
    if( !$v )
    {
        return true;
    }
}
?>
