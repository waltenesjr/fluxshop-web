<?php

class Update extends PHPFrodo
{

    public function __construct()
    {
        parent::__construct();
    }

    public function welcome()
    {
        $this->select()->from( 'versao' )->execute();
        $this->_v = ( object ) $this->data[0];
        $cURL = @curl_init( 'http://fluxshop.com.br/updates/last15.php' );
        @curl_setopt( $cURL, CURLOPT_RETURNTRANSFER, true );
        @curl_setopt( $cURL, CURLOPT_FOLLOWLOCATION, true );
        $resultado = @curl_exec( $cURL );
        $resposta = @curl_getinfo( $cURL, CURLINFO_HTTP_CODE );
        @curl_close( $cURL );
        if ( $resposta == '404' )
        {
            echo "Versão  Atual [" . $this->_v->versao_update . "] - Não foi possível localizar atualizações!";
        }
        else
        {
            $get_last = explode( "|", $resultado );
            $last = $get_last[0];
            $last_i = ( float ) $get_last[1];
            $current = $this->_v->versao_update;
            $current_i = ( float ) $this->_v->versao_num;
            if ( $last_i > $current_i )
            {
                $this->delete()->from( 'versao' )->execute();
                $this->insert( 'versao' )
                        ->fields( array( 'versao_num', 'versao_data', 'versao_update' ) )
                        ->values( array( '21', '16-09-2014', '1.5.6' ) )
                        ->execute();
                $this->select()->from( 'versao' )->execute();
                $this->_v = ( object ) $this->data[0];
                echo '<h1>Atualização [' . $this->_v->versao_update . '] concluída!</h1>';
                echo "<br /> <a href='$this->baseUri/admin/'>Voltar</a>";
            }
            else
            {
                echo "Versão [$current] *** Você possui a versão mais recente do sistema!";
            }
        }
    }
}
?>
