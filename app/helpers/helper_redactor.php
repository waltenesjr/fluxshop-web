<?php

function editor( $val = '', $n='editor', $h = '200px', $w = '200px', $dest = 'file_upload.php' )
{
    $body = "";
    //$body .= "<meta charset=\"iso-8859-1\">\n";
    $body .= "<link rel=\"stylesheet\" href=\"helpers/redactor/api/css/redactor.css\" />\n";
    //$body .= "\t<script src=\"helpers/redactor/api/jquery-1.7.min.js\"></script>\n";
    $body .= "\t<script src=\"helpers/redactor/api/redactor.js\"></script>\n";
    $body .= "\t<textarea id=\"$n\" class=\"redac\" name=\"$n\" style=\"height: $h; width:$w !important\">$val</textarea>\n";
    @header( 'Content-Type: text/html; charset=iso-8859-1' );
    return trim( $body );
}
?>
