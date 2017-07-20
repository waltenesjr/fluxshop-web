<?php

class Cupom extends PHPFrodo
{
    public $cupom_id;
    public $cupom_alfa;
    public $cupom_status;
    public $cupom_desconto;
    public $cupom_lote;
    public $cupom_update;
    public $cliente_id;

    public function __construct()
    {
        parent:: __construct();
        $sid = new Session;
        $sid->start();
        if ( $sid->check() && $sid->getNode( 'cliente_id' ) >= 1 )
        {
            $this->cliente_email = ( string ) $sid->getNode( 'cliente_email' );
            $this->cliente_id = ( string ) $sid->getNode( 'cliente_id' );
            $this->cliente_nome = ( string ) $sid->getNode( 'cliente_nome' );
        }
    }

    public function welcome()
    {
        $this->cupom_alfa = 'Y2MSFU74E3QG';

    }

    public function validar()
    {
        if ( isset( $_SESSION['cupom'] ) )
        {
            unset( $_SESSION['cupom'] );
        }
        if ( $this->postIsValid( array( 'cupom' => 'string' ) ) )
        {
            $this->cupom_alfa = strtoupper( $this->postGetValue( 'cupom' ) );
            $this->select()
                    ->from( 'cupom' )
                    ->where( "cupom_alfa = '$this->cupom_alfa' AND cupom_status = 0 OR cupom_alfa = '$this->cupom_alfa' AND cupom_limite = 2" )
                    ->execute();
            if ( $this->result() )
            {
                $this->map( $this->data[0] );
                $this->data_hoje = date( 'Y-m-d 00:00:00' );
                $diff = (strtotime( $this->cupom_validade ) - strtotime( $this->data_hoje )) / 86400;
                if ( $diff >= 0 )
                {
                    $_SESSION['cupom']['id'] = $this->cupom_id;
                    $_SESSION['cupom']['alfa'] = $this->cupom_alfa;
                    $_SESSION['cupom']['desconto'] = $this->cupom_desconto;
                    $_SESSION['cupom']['tipo'] = $this->cupom_tipo;
                    $_SESSION['cupom']['min'] = $this->cupom_min;
                    if ( $this->cupom_tipo == 2 )
                    {
                        if ( isset( $_SESSION['mycep_frete'] ) && $_SESSION['mycep_frete'] <= 0 )
                        {
                            echo -3;
                        }
                    }
                }
                else
                {
                    echo -2;
                    $this->remover();
                }
            }
            else
            {
                echo -1;
                $this->remover();
            }
        }
        else
        {
            $this->remover();
        }
    }

    public function remover()
    {
        if ( isset( $_SESSION['cupom'] ) )
        {
            unset( $_SESSION['cupom'] );
        }
    }
}
?>
