<?php

/**
 * TemplateFy
 *
 * @author Rafael Clares <rafadinix@gmail.com>
 * @version 1.0  <10/2010>
 * web: www.clares.wordpress.com
 *
 *  Utilização de templates para separar camadas (MVC)
 *
 */
Class TemplateFy
{
    public $tpl;
    public $contents = null;
    public $target;
    public $html;
    public $dataArr;
    public $chars;
    public $fetchRun = null;
    public $tpldata = array( );
    public $assigndata = array( );
    public $tpldir;
    public $baseApp;
    public $compressed = false;
    public $referer = false;
    public $baseUri = false;
    public $uri_segment = false;

    public function setTplDir( $tpldir )
    {
        $this->tpldir = $tpldir;
    }

    public function setbaseApp( $base )
    {
        $this->baseApp = $base;
    }

    public function assign( $key = null, $value = null )
    {
        $this->assigndata[$key] = trim( $value );
        $this->tpldata[] = $this->assigndata;
        return $this;
    }

    public function data( $data )
    {
        $this->tpldata[] = $data;
        return $this;
    }

    public function tpl( $tpl )
    {
        $this->tpl = $tpl;
        ob_start();
        if ( !file_exists( $this->tpldir . $this->tpl ) )
        {
            echo "Arquivo $this->tpl não encontrado em: " . $this->tpldir . "$this->tpl !";
            exit;
        }
        include($this->tpldir . $this->tpl);
        $this->contents = ob_get_contents();
        ob_end_clean();
        return $this;
    }

    public function render( $printable=null )
    {
        if ( $this->contents == null )
        {
            if ( !file_exists( $this->tpldir . "/$this->tpl" ) )
            {
                echo "Arquivo $this->tpl não encontrado em: " . $this->tpldir . "$this->tpl !";
                exit;
            }
            $this->contents = file_get_contents( $this->tpldir . "$this->tpl" );
        }
        // assing data
        foreach ( $this->tpldata as $item )
        {
            while ( list( $key, $value ) = each( $item ) )
            {
                if ( preg_match_all( "/(\{$key\})/msi", $this->contents, $m ) )
                {
                    $pat = array( '/\<!--\{' . $key . '\}-->/msi', '/(\{' . $key . '\})/msi' );
                    $rep = array( $value, $value );
                    $this->contents = @preg_replace( $pat, $rep, $this->contents );
                }
                if ( preg_match_all( "/(\[$key\])/msi", $this->contents, $m ) )
                {
                    $this->contents = @preg_replace( "/(\[$key\])/msi", $value, $this->contents );
                }
            }
        }
        //insere a tag base no template
        $base_app = $this->baseApp;
        $base_tag = "<head>\n\t<base href=\"" . $base_app . "\" />";
        if ( preg_match( '/\<head\>/i', $this->contents ) )
        {
            $this->contents = @preg_replace( '/\<head\>/', "$base_tag", $this->contents );
        }
        // remove linhas em branco
        $this->contents = @preg_replace( '/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/', "\n", $this->contents );
        //set baseUri
        $this->loadUri();
        $commons = array( '/(\[baseUri\])/', '/(\{baseuri\})/', '/(\[baseVersion\])/' );
        $commonr = array( "$this->baseUri", "$this->baseUri", date( 'dmyhis' ) );
        $this->contents = preg_replace( $commons, $commonr, $this->contents );
        //clear tags
        $this->clear();
        if ( $this->compressed == true )
        {
            $this->compress();
        }
        if ( $printable == null )
        {
            echo $this->contents;
        }
        else
        {
            return $this->contents;
        }
    }

    public function fetch( $target, $data )
    {
        $this->html = array( );
        if ( !empty( $data ) )
        {
            $this->dataArr = $data;
            $this->target = $target;
            $this->fetchRun = 1;
            //$this->contents = @preg_replace( "/\s+/i", ' ', $this->contents );
            if ( !preg_match_all( "/(<!--{loop:$this->target}-->)(.*?)\s*(<!--{end:$this->target}-->)/s", $this->contents, $loop, PREG_SET_ORDER ) )
            {
                //print "fetch error: [$this->target] tag nao encontrada";                
                //exit;
            }
            else
            {
                $loop_html = preg_replace( '/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/', "\n", $loop[0][2] );
                $ifetchRun = false;
                foreach ( $data as $item )
                {
                    $html = $loop_html;
                    while ( list( $key, $value ) = each( $item ) )
                    {
                        if ( preg_match_all( "/$this->target.$key/msi", $loop_html, $m ) )
                        {
                            if ( !is_array( $value ) )
                            {
                                if ( empty( $value ) || $value == "" || $value == NULL || strlen( $value ) < 0 && $value != 0 && $value != '0' )
                                {
                                    //$value = "&nbsp;";
                                }
                                $pattern = array( "/$this->target.$key/" );
                                $replace = array( "$value" );
                                $html = preg_replace( $pattern, $replace, $html );
                                //$html = preg_replace( array( "/<!--{$value}-->/is" ), array( "$value" ), $html );
                            }
                        }
                        $ihtml = "";
                        if ( preg_match_all( "/(<!--{loop:$key}-->)(.*?)\s*(<!--{end:$key}-->)/msi", $html, $iloop, PREG_SET_ORDER ) )
                        {
                            $datar = $item[$key];
                            if ( is_array( $item[$key] ) && !empty( $item[$key] ) )
                            {
                                $iloop_html = preg_replace( '/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/', "\n", $iloop[0][2] );
                                $ihtml .= $this->ifetch( $datar, $key, $iloop_html );
                                $html = preg_replace( "/(<!--\{loop:$key\}-->)(.*?)(<!--\{end:$key\}-->)/s", "$ihtml", $html );
                                $html = preg_replace( array( "/\<!--\{/", "/\}--\>/" ), array( "", "" ), $html );
                                $ifetchRun = true;
                            }
                        }
                    }

                    if ( $ifetchRun == false )
                    {
                        $html = preg_replace( array( "/\<!--\{/", "/\}--\>/" ), array( "", "" ), $html );
                    }
                    $this->html[] = $html;
                }
                $this->html = implode( "\r", $this->html );
                $this->html = preg_replace( "/\<!--{\s*(.*?)\s*\}-->/i", "", $this->html );
                $this->contents = preg_replace( "/(<!--{loop:$this->target}-->)(.*?)\s*(<!--{end:$this->target}-->)/msi", $this->html, $this->contents );
            }
        }
    }

    public function fetchr( $target, $data )
    {
        $this->html = array( );
        if ( !empty( $data ) )
        {
            $this->dataArr = $data;
            $this->target = $target;
            $this->fetchRun = 1;
            if ( !preg_match_all( "/(<!--{loop:$this->target}-->)(.*?)\s*(<!--{end:$this->target}-->)/s", $this->contents, $loop, PREG_SET_ORDER ) )
            {
                print "fetch error: [$this->target] tag nao encontrada";
            }
            $loop_html = preg_replace( '/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/', "\n", $loop[0][2] );
            $ifetchRun = false;
            foreach ( $data as $item )
            {
                $html = $loop_html;
                while ( list( $key, $value ) = each( $item ) )
                {
                    if ( preg_match_all( "/$this->target.$key/msi", $loop_html, $m ) )
                    {
                        if ( !is_array( $value ) )
                        {
                            $pattern = array( "/(?<!\.)\b$this->target.$key\b(?!\.)/" );
                            $replace = array( "$value" );
                            $html = preg_replace( $pattern, $replace, $html );
                            $pat = array( '/\<!--\{' . $key . '\}-->/msi', '/(\{' . $key . '\})/msi' );
                            $rep = array( $value, $value );
                            $html = @preg_replace( $pat, $rep, $html );
                        }
                    }
                    $ihtml = "";
                    if ( preg_match_all( "/(<!--{loop:$key}-->)(.*?)\s*(<!--{end:$key}-->)/msi", $html, $iloop, PREG_SET_ORDER ) )
                    {
                        $datar = $item[$key];
                        if ( is_array( $item[$key] ) && !empty( $item[$key] ) )
                        {
                            $iloop_html = preg_replace( '/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/', "\n", $iloop[0][2] );
                            $ihtml .= $this->ifetch( $datar, $key, $iloop_html );
                            $html = preg_replace( "/(<!--\{loop:$key\}-->)(.*?)(<!--\{end:$key\}-->)/s", "$ihtml", $html );
                            $html = preg_replace( array( "/\<!--\{/", "/\}--\>/" ), array( "", "" ), $html );
                            $ifetchRun = true;
                        }
                    }
                }

                if ( $ifetchRun == false )
                {
                    $html = preg_replace( array( "/\<!--\{/", "/\}--\>/" ), array( "", "" ), $html );
                }
                $this->html[] = $html;
            }
            $this->html = implode( "\r", $this->html );
            $this->html = preg_replace( "/\<!--{\s*(.*?)\s*\}-->/i", " ", $this->html );
            $this->contents = preg_replace( "/(<!--{loop:$this->target}-->)(.*?)\s*(<!--{end:$this->target}-->)/msi", $this->html, $this->contents );
        }
    }

    //inner loop
    private function ifetch( $data, $target, $html )
    {
        $ihtml = "";
        foreach ( $data as $item )
        {
            $pattern = array( );
            $replace = array( );
            while ( list( $key, $value ) = each( $item ) )
            {
                $pattern[] = "/$this->target.$target.$key/msi";
                $replace[] = "$value";
            }
            $ihtml .= preg_replace( $pattern, $replace, $html );
        }
        return $ihtml;
    }

    private function clear()
    {
        $this->contents = @preg_replace( "/\<!--{\s*(.*?)\s*\}-->/i", "", $this->contents );
        $this->contents = @preg_replace( "/\{[a-z]\s*(.*?)[a-z]\s*}/i", "", $this->contents );
        $this->contents = @preg_replace( "/\[[a-z]\s*(.*?)[a-z]\s*]/i", "", $this->contents );
        $this->contents = @preg_replace( array( "/\{\}/" ), array( "" ), $this->contents );
        //$this->contents = @preg_replace( "/\[\s*(.*?)\s*\]/i", "", $this->contents );
        $this->contents = @preg_replace( '/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/', "\n", $this->contents );
    }

    private function clearOld()
    {
        $this->contents = @preg_replace( "/\<!--{\s*(.*?)\s*\}-->/i", "", $this->contents );
        //$this->contents = @preg_replace( "/\[\s*(.*?)\s*\]/i", "", $this->contents );
        //evita remover ['125'], somente letrar
        $this->contents = @preg_replace( "/\[[a-z]\s*(.*?)[a-z]\s*]/i", "", $this->contents );
        //$this->contents = @preg_replace( "/\{\s*(.*?)\s*\}/i", "", $this->contents );
        //remove tags elementos vazios
        if ( $this->fetchRun != null )
        {
            $this->contents = @preg_replace( "/\<!--{\s*(.*?)\s*\}-->/i", "", $this->contents );
            $find = array
                (
                "/<a[^>]*>([\s]?)*<\/a>/",
                "/<button[^>]*>([\s]?)*<\/button>/",
                "/<p[^>]*>([\s]?)*<\/p>/",
                "/<thead[^>]*>([\s]?)*<\/thead>/",
                "/<tfoot[^>]*>([\s]?)*<\/tfoot>/",
                "/<tbody[^>]*>([\s]?)*<\/tbody>/",
                "/<td[^>]*>([\s]?)*<\/td>/",
                "/<tr[^>]*>([\s]?)*<\/tr>/",
                "/<th[^>]*>([\s]?)*<\/th>/",
                    //"/<img [^\.>]*>([\s]?)*/",
                    //"/<li[^>]*>([\s]?)*<\/li>/i",
                    //"/<ul[^>]*>([\s]?)*<\/ul>/i"
            );
            $replace = array( "", "", "", "<td>&nbsp;</td>", "", "", "" );
            $this->contents = @preg_replace( $find, $replace, $this->contents, -1 );
        }
        $this->contents = @preg_replace( '/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/', "\n", $this->contents );
    }

    //pouco usada
    public function compress()
    {
        $this->contents = @preg_replace( "/\s+/i", ' ', $this->contents );
    }

    /**
     * Extrai as variaveis do get e armazena no atrivuto uri_segment URL
     * loadUri
     */
    public function loadUri()
    {
        try
        {
            if ( !isset( $_GET ) || empty( $_GET ) )
            {
                throw new Exception( 'loadUri: Segment Null' );
            }
            else
            {
                $routes = explode( "/", $_GET['route'] );
                foreach ( $routes as $uri )
                {
                    if ( $uri != "" )
                    {
                        $this->uri_segment[] = $uri;
                    }
                    (isset( $_SERVER['HTTP_REFERER'] )) ? $this->referer = $_SERVER['HTTP_REFERER'] : $this->referer = '';
                }
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        //base_uri arquivo atual
        $this->baseUri = substr( HTTPURL, 0, -1 );
        return $this;
    }
}
/* end file */
