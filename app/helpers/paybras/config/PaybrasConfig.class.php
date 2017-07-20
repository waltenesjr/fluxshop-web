<?php

class PaybrasConfig extends PHPFrodo
{
    private static $config;
    private static $datar;
    const varName = 'PaybrasConfig';

    public function __construct()
    {
        require_once "PaybrasConfig.php";
        $varName = self::varName;
        if ( isset( $$varName ) )
        {
            self::$datar = $$varName;
            unset( $$varName );
        }
        else
        {
            throw new Exception( "Configuração não definida." );
        }
    }

    public static function init()
    {
        if ( self::$config == null )
        {
            self::$config = new PaybrasConfig();
        }
        return self::$config;
    }

    public static function getDadosLojista( )
    {
        $dd = new PHPFrodo;
        $dd->select()->from( 'pay' )->where( 'pay_name = "PayBras"' )->execute();
        if ( $dd->result() )
        {
            $dados_logista['email'] = $dd->data[0]['pay_user'];
            $dados_logista['token'] = $dd->data[0]['pay_key'];
            self::$datar['lojista']['token'] = $dd->data[0]['pay_key'];
            self::$datar['lojista']['email'] = $dd->data[0]['pay_key'];
            
            if ( isset( $dados_logista['email'] ) && isset( $dados_logista['token'] ) )
            {
                return new PaybrasDadosLojista( $dados_logista['email'], $dados_logista['token'] );
            }
            else
            {
                throw new Exception( "Dados de Lojista não adicionados ao arquivo de configuração" );
            }
        }
        else
        {
            echo 'Módulo PayBras não configurado';
            exit;
        }
    }

    public static function getURL( $servico )
    {
        if ( isset( self::$datar['lojista']['ambiente'] ) )
        {
            $ambiente = self::$datar['lojista']['ambiente'];
            if ( isset( self::$datar['ambiente'] ) && isset( self::$datar['ambiente'][$servico][$ambiente] ) )
            {
                return self::$datar['ambiente'][$servico][$ambiente];
            }
            else
            {
                throw new Exception( "Dados de conexão não adicionados ao arquivo de configuração" );
            }
        }
        else
        {
            throw new Exception( "Ambiente não setado no arquivo de configuração" );
        }
    }

    public function curl( $url, $datar )
    {
        $ch = curl_init( $url );
        $datar_string = json_encode( $datar );

        curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "POST" );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $datar_string );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen( $datar_string ) )
        );
        //$json_response = curl_exec($ch);
        //$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        //if ( $status != 201 ) {
        //die("Error:   $status, response $json_response, curl_error " . curl_error($ch) . ", curl_errno " . curl_errno($ch));
        //}		
        return curl_exec( $ch );
    }

    public function utf8_encode_deep( &$input )
    {
        if ( is_string( $input ) )
        {
            $input = utf8_encode( $input );
        }
        elseif ( is_array( $input ) )
        {
            foreach ( $input as &$value )
            {
                self::utf8_encode_deep( $value );
            }
            unset( $value );
        }
        elseif ( is_object( $input ) )
        {
            $vars = array_keys( get_object_vars( $input ) );
            foreach ( $vars as $var )
            {
                self::utf8_encode_deep( $input->$var );
            }
        }
    }
}
?>
