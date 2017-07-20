<?php
error_reporting( 0 );
class Pay extends PHPFrodo
{
    public $_pay = array( );

    public function __construct()
    {
        parent:: __construct();
    }

    public function getPaysOn()
    {
        $this->select()->from( 'pay' )->where( 'pay_status = 1' )->execute();
        $this->_pay = $this->data;
        foreach ( $this->data as $pay )
        {
            $pays[] = $pay['pay_name'];
        }
        $this->assigndata = array( );
        if ( !in_array( 'PagSeguro', $pays ) )
        {
            ///$this->assigndata['showPagSeguro'] = 'hide';
            $this->assign( 'showPagSeguro', 'hide' );
        }
        if ( !in_array( 'PayPal', $pays ) )
        {
            $this->assign( 'showPayPal', 'hide' );
            //$this->assigndata['showPayPal'] = 'hide';
        }
        if ( !in_array( 'PayBras', $pays ) )
        {
            $this->assign( 'showPayBras', 'hide' );
            //$this->assigndata['showPayBras'] = 'hide';
        }
        return $this->assigndata;
    }

    public function parcelamento( $valor, $parcs )
    {
        //$fator = array( 1.00000, 0.52255, 0.35347, 0.26898, 0.21830, 0.18453, 0.16044, 0.14240, 0.12838, 0.11717, 0.10802, 0.10040 );
        $fator = explode( ",", $this->_pay[0]['pay_fator_juros'] );
        return $this->round_up( $valor * $fator[$parcs - 1], $parcs - 1 );
    }


    public function parcelamentoTabela( $valor, $parcs )
    {
        $tabela = "";
        $k = 0;
        $display = 'block';
        foreach ( $this->_pay as $k => $v )
        {
            if ( $this->_pay[$k]['pay_fator_juros'] != '' )
            {
                $fator = explode( ",", $this->_pay[$k]['pay_fator_juros'] );
                $tabela .= "<b><small><a  href='javascript:void(0)' onclick='$(\"#parc_$k\").toggle();' title='tabela de parcelamento com " . $this->_pay[$k]['pay_name'] . "'>Parcelamento com " . $this->_pay[$k]['pay_name'] . "</b></a></small><div class='$display' id='parc_$k'>";
                if ( $k == 0 )
                {
                    $display = 'hide';
                    $k++;
                }
                for ( $i = 0; $i <= $parcs - 1; $i++ )
                {
                    $resultado = $this->round_up( $valor * $fator[$i], $i );
                    $tabela .= "<div class='parcelas-pagseguro'><b>"
                            . $resultado['texto'] . "</b></div>\n";
                }
                $tabela .= "</div><Br/><Br/><Br/><Br/><p>&nbsp;</p>";
            }
        }
        return $tabela;
    }
    
    public function round_up( $value, $num, $places = 2 )
    {
        $mult = pow( 10, $places );
        $parcela = number_format( ($value >= 0 ? ceil( $value * $mult ) : floor( $value * $mult )) / $mult, 2, ',', '.' );
        $total = number_format( $parcela * ($num + 1), 2, ',', '.' );
        return array( 'parcela' => $parcela, 'total' => $total, 'texto' => "" .($num + 1) . "x de R$ " . $parcela, 'num' => ($num + 1) );
    }
}
?>
