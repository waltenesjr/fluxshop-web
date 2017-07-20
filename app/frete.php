<?php

class Frete extends PHPFrodo
{
    public $config_cep = array( );
    public $valor_frete = null;
    public $error = null;
    public $prazo_frete = null;
    public $cliente_cep = null;
    public $item_peso = null;
    public $item_altura = null;
    public $item_largura = null;
    public $item_comprimento = null;
    public $valor_frete_formatado = null;
    public $frete_opcoes = array( );
    public $erros = array(
        '0' => 'Processamento com sucesso',
        '-1' => 'C�digo de servi�o inv�lido',
        '-2' => 'CEP de origem inv�lido',
        '-3' => 'CEP de destino inv�lido',
        '-4' => 'Peso excedido',
        '-5' => 'O Valor Declarado n�o deve exceder R$ 10.000,00',
        '-6' => 'Servi�o indispon�vel para o trecho informado',
        '-7' => 'O Valor Declarado � obrigat�rio para este servi�o',
        '-8' => 'Este servi�o n�o aceita M�o Pr�pria',
        '-9' => 'Este servi�o n�o aceita Aviso de Recebimento',
        '-10' => 'Precifica��o indispon�vel para o trecho informado',
        '-11' => 'Para defini��o do pre�o dever�o ser informados, tamb�m, o comprimento, a largura e altura do objeto em cent�metros (cm)',
        '-12' => 'Comprimento inv�lido',
        '-13' => 'Largura inv�lida',
        '-14' => 'Altura inv�lida',
        '-15' => 'O comprimento n�o pode ser maior que 105 cm',
        '-16' => 'A largura n�o pode ser maior que 105 cm',
        '-17' => 'A altura n�o pode ser maior que 105 cm',
        '-18' => 'A altura n�o pode ser inferior a 2 cm',
        '-20' => 'A largura n�o pode ser inferior a 11 cm',
        '-22' => 'O comprimento n�o pode ser inferior a 16 cm',
        '-23' => 'A soma resultante do comprimento + largura + altura n�o deve superar a 200 cm',
        '-24' => 'Comprimento inv�lido',
        '-25' => 'Di�metro inv�lido',
        '-26' => 'Informe o comprimento',
        '-27' => 'Informe o di�metro',
        '-28' => 'O comprimento n�o pode ser maior que 105 cm',
        '-29' => 'O di�metro n�o pode ser maior que 91 cm',
        '-30' => 'O comprimento n�o pode ser inferior a 18 cm',
        '-31' => 'O di�metro n�o pode ser inferior a 5 cm',
        '-32' => 'A soma resultante do comprimento + o dobro do di�metro n�o deve superar a 200 cm',
        '-33' => 'Sistema temporariamente fora do ar. Favor tentar mais tarde',
        '-34' => 'C�digo Administrativo ou Senha inv�lidos',
        '-35' => 'Senha incorreta',
        '-36' => 'Cliente n�o possui contrato vigente com os Correios',
        '-37' => 'Cliente n�o possui servi�o ativo em seu contrato',
        '-38' => 'Servi�o indispon�vel para este c�digo administrativo',
        '-39' => 'Peso excedido para o formato envelope',
        '-40' => 'Para definicao do preco deverao ser informados, tambem, o comprimento e a largura e altura do objeto em centimetros (cm)',
        '-41' => 'O comprimento nao pode ser maior que 60 cm',
        '-42' => 'O comprimento nao pode ser inferior a 16 cm',
        '-43' => 'A soma resultante do comprimento + largura nao deve superar a 120 cm',
        '-44' => 'A largura nao pode ser inferior a 11 cm',
        '-45' => 'A largura nao pode ser maior que 60 cm',
        '-888' => 'Erro ao calcular a tarifa',
        '006' => 'Localidade de origem n�o abrange o servi�o informado',
        '007' => 'Localidade de destino n�o abrange o servi�o informado',
        '008' => 'Servi�o indispon�vel para o trecho informado',
        '009' => 'CEP inicial pertencente a �rea de Risco',
        '010' => 'CEP final pertencente a �rea de Risco. A entrega ser� realizada, temporariamente, na ag�ncia mais pr�xima do endere�o do destinat�rio',
        '011' => 'CEP inicial e final pertencentes a �rea de Risco',
        '7' => 'Servi�o indispon�vel, tente mais tarde',
        '99' => 'Outros erros diversos do .Net' );

    public function __construct()
    {
        parent:: __construct();
        $sid = new Session;
        @$sid->start();
        $this->select()
                ->from( 'frete' )
                ->execute();
        if ( $this->result() )
        {
            $this->config_cep = null;
            $this->config_cep = ( object ) $this->data[0];
        }
    }

    public function buscaFreteGratis()
    {
        if ( isset( $_POST['uf'] ) && isset( $_POST['cidade'] ) )
        {
            $servico = '';
            $this->uf = strtoupper( $_POST['uf'] );
            $this->cidade = utf8_decode( $_POST['cidade'] );
            if ( isset( $_POST['bairro'] ) )
            {
                $this->bairro = utf8_decode( $_POST['bairro'] );
            }
            $this->cep = $_POST['cep'];
            //Cobertura UF
            $this->select()
                    ->from( 'entrega' )
                    //->where( "entrega_uf = '$this->uf' AND entrega_tipo = 1" )
                    ->where( "entrega_uf = '$this->uf' 
                                    AND entrega_cidade = '$this->cidade' 
                                    AND entrega_bairro = '$this->bairro'
                                    AND entrega_tipo = 3" )
                    ->execute();
            if ( $this->result() )
            {
                //frete 
                $_SESSION['mycep_tipo_frete'] = " (Frete diferenciado) ";
                $_SESSION['mycep_prazo'] = ( string ) $this->data[0]['entrega_prazo'];
                $_SESSION['mycep_name'] = ( string ) $this->data[0]['entrega_desc'];
                $_SESSION['mycep_frete'] = ( string ) preg_replace( '/,/', '.', $this->data[0]['entrega_valor'] );
                if ( isset( $this->uri_segment ) && in_array( 'no-cf', $this->uri_segment ) )
                {
                    $_SESSION['mycep_frete'] = "0.00";
                }
                $this->PrazoEntrega = $_SESSION['mycep_prazo'];
                $this->ValorEntrega = $_SESSION['mycep_frete'];
                $this->nomeEntrega = $_SESSION['mycep_name'];

                $cb = '';
                $cb .= "<table width='100%' style='border:0px !important' class='table table-striped'>";
                $cb .= '<tr>';
                $cb .= '<td width="20" style="line-height:15px; height:15px; vertical-align: top !important; text-align:right">';
                $cb .= '<input type="radio" class="btn-update-frete" name="tipo_frete[]" id="001-diff"  t="' . $this->nomeEntrega . '" value="' . $this->ValorEntrega . '|' . $this->PrazoEntrega . '" v="' . $this->double( $this->ValorEntrega ) . '" p="' . $this->PrazoEntrega . '" />';
                $cb .= '</td>';
                $cb .= '<td width="70">';
                $cb .= '<label for="001-diff">';
                if ( $this->ValorEntrega >= 1 )
                {
                    $cb .= "R$ $this->ValorEntrega";
                }
                else
                {
                    $cb .= 'Frete Gr�tis';
                }
                $cb .= '</label>';
                $cb .= '</td>';
                $cb .= '<td width="100">';
                $cb .= '<label for="001-diff">';
                $cb .= '<b class="f-gray">' . $this->nomeEntrega . '</b>';
                //$cb .= '<img src="images/layout/frete_manual.png" />';
                $cb .= '</label>';
                $cb .= '</td>';
                $cb .= '<td width="150">';
                $cb .= '<label for="001-diff">';
                $cb .= $this->PrazoEntrega;
                /*
                  if ( $this->PrazoEntrega > 1 )
                  {
                  $cb .= ' at� ' . $this->PrazoEntrega . ' dias �teis';
                  }
                  else
                  {
                  $cb .= ' 1 dia �til';
                  }
                 */
                $cb .= '</label>';
                $cb .= '</td>';
                $cb .= '</tr>';
                $this->frete_opcoes[] = $cb;
            }

            //Cobertura Cidade
            $this->select()
                    ->from( 'entrega' )
                    ->where( "entrega_uf = '$this->uf' 
                                    AND entrega_cidade = '$this->cidade' 
                                    AND entrega_tipo = 2" )
                    ->execute();
            if ( $this->result() )
            {
                //frete 
                $_SESSION['mycep_tipo_frete'] = " (Frete diferenciado) ";
                $_SESSION['mycep_frete'] = ( string ) preg_replace( '/,/', '.', $this->data[0]['entrega_valor'] );
                $_SESSION['mycep_prazo'] = ( string ) $this->data[0]['entrega_prazo'];
                $_SESSION['mycep_name'] = ( string ) $this->data[0]['entrega_desc'];
                if ( isset( $this->uri_segment ) && in_array( 'no-cf', $this->uri_segment ) )
                {
                    $_SESSION['mycep_frete'] = "0.00";
                }
                $this->PrazoEntrega = $_SESSION['mycep_prazo'];
                $this->ValorEntrega = $_SESSION['mycep_frete'];
                $this->nomeEntrega = $_SESSION['mycep_name'];

                $cb = '';
                $cb .= "<table width='100%' style='border:0px !important' class='table table-striped'>";
                $cb .= '<tr>';
                $cb .= '<td width="20" style="line-height:15px; height:15px; vertical-align: top !important; text-align:right">';
                $cb .= '<input type="radio" class="btn-update-frete" name="tipo_frete[]" id="002-diff"  t="' . $this->nomeEntrega . '" value="' . $this->ValorEntrega . '|' . $this->PrazoEntrega . '" v="' . $this->double( $this->ValorEntrega ) . '" p="' . $this->PrazoEntrega . '" />';
                $cb .= '</td>';
                $cb .= '<td width="70">';
                $cb .= '<label for="002-diff">';
                if ( $this->ValorEntrega >= 1 )
                {
                    $cb .= "R$ $this->ValorEntrega";
                }
                else
                {
                    $cb .= 'Frete Gr�tis';
                }
                $cb .= '</label>';
                $cb .= '</td>';
                $cb .= '<td width="100">';
                $cb .= '<label for="002-diff">';
                $cb .= '<b class="f-gray">' . $this->nomeEntrega . '</b>';
                //$cb .= '<img src="images/layout/frete_manual.png" />';
                $cb .= '</label>';
                $cb .= '</td>';
                $cb .= '<td width="150">';
                $cb .= '<label for="002-diff">';
                $cb .= $this->PrazoEntrega;
                /*
                  if ( $this->PrazoEntrega > 1 )
                  {
                  $cb .= ' at� ' . $this->PrazoEntrega . ' dias �teis';
                  }
                  else
                  {
                  $cb .= ' 1 dia �til';
                  }
                 */
                $cb .= '</label>';
                $cb .= '</td>';
                $cb .= '</tr>';
                $this->frete_opcoes[] = $cb;
            }

            if ( isset( $_POST['bairro'] ) )
            {
                //Cobertura Bairro
                $this->select()
                        ->from( 'entrega' )
                        ->where( "entrega_uf = '$this->uf' AND entrega_tipo = 1" )
                        /*
                          ->where( "entrega_uf = '$this->uf'
                          AND entrega_cidade = '$this->cidade'
                          AND entrega_bairro = '$this->bairro'
                          AND entrega_tipo = 3" )
                         */
                        ->execute();
                if ( $this->result() )
                {
                    //frete 
                    $_SESSION['mycep_tipo_frete'] = " (Frete diferenciado) ";
                    $_SESSION['mycep_frete'] = ( string ) preg_replace( '/,/', '.', $this->data[0]['entrega_valor'] );
                    $_SESSION['mycep_prazo'] = $this->data[0]['entrega_prazo'];
                    $_SESSION['mycep_name'] = $this->data[0]['entrega_desc'];
                    if ( isset( $this->uri_segment ) && in_array( 'no-cf', $this->uri_segment ) )
                    {
                        $_SESSION['mycep_frete'] = "0.00";
                    }

                    $this->PrazoEntrega = $_SESSION['mycep_prazo'];
                    $this->ValorEntrega = $_SESSION['mycep_frete'];
                    $this->nomeEntrega = $_SESSION['mycep_name'];
                    $cb = '';
                    $cb .= "<table width='100%' style='border:0px !important' class='table table-striped'>";
                    $cb .= '<tr>';
                    $cb .= '<td width="20" style="line-height:15px; height:15px; vertical-align: top !important; text-align:right">';
                    $cb .= '<input type="radio" class="btn-update-frete" name="tipo_frete[]" id="003-diff"  t="' . $this->nomeEntrega . '"  value="' . $this->ValorEntrega . '|' . $this->PrazoEntrega . '" v="' . $this->double( $this->ValorEntrega ) . '" p="' . $this->PrazoEntrega . '" />';
                    $cb .= '</td>';
                    $cb .= '<td width="50">';
                    $cb .= '<label for="003-diff">';
                    if ( $this->ValorEntrega >= 1 )
                    {
                        $cb .= "R$ $this->ValorEntrega";
                    }
                    else
                    {
                        $cb .= 'Frete Gr�tis';
                    }
                    $cb .= '</label>';
                    $cb .= '</td>';
                    $cb .= '<td width="70">';
                    $cb .= '<label for="003-diff">';
                    $cb .= '<b class="f-gray">' . $this->nomeEntrega . '</b>';
                    //$cb .= '<img src="images/layout/frete_manual.png" />';
                    $cb .= '</label>';
                    $cb .= '</td>';
                    $cb .= '<td width="100">';
                    $cb .= '<label for="003-diff">';
                    $cb .= $this->PrazoEntrega;
                    /*
                      if ( $this->PrazoEntrega > 1 )
                      {
                      $cb .= ' at� ' . $this->PrazoEntrega . ' dias �teis';
                      }
                      else
                      {
                      $cb .= ' 1 dia �til';
                      }
                     */
                    $cb .= '</label>';
                    $cb .= '</td>';
                    $cb .= '</tr>';
                    $this->frete_opcoes[] = $cb;
                }
                else
                {
                    $cb = '';
                    $cb .= "<table width='100%' style='border:0px !important' class='table table-striped'>";
                    $this->frete_opcoes[] = $cb;
                }
            }
            else
            {
                $cb = '';
                $cb .= "<table width='100%' style='border:0px !important' class='table table-striped'>";
                $this->frete_opcoes[] = $cb;
            }
        }
    }

    public function correios()
    {
        //@header( 'Content-Type: text/html; charset=utf8' );
        $CEPorigem = $this->config_cep->frete_cep_origem;
        $CEPdestino = $_POST['cep'];
        $peso = $_POST['peso'];
        $altura = $_POST['altura'];
        $largura = $_POST['largura'];
        $comprimento = $_POST['comprimento'];
        $_SESSION['mycep'] = ( string ) $_POST['cep'];

        $data['nCdEmpresa'] = '';
        $data['sDsSenha'] = '';
        $data['nCdFormato'] = '1';
        $data['nVlPeso'] = '1';
        $data['nVlComprimento'] = '16';
        $data['nVlAltura'] = '2';
        $data['nVlLargura'] = '11';

        $data['sCepOrigem'] = $CEPorigem;
        $data['sCepDestino'] = $CEPdestino;

        $data['nVlPeso'] = $peso;
        $data['nVlComprimento'] = $comprimento;
        $data['nVlAltura'] = $altura;
        $data['nVlLargura'] = $largura;

        $data['nVlDiametro'] = '0';
        $data['sCdMaoPropria'] = 'n';
        $data['nVlValorDeclarado'] = '0';
        $data['sCdAvisoRecebimento'] = 'n';
        $data['StrRetorno'] = 'xml';
        // 41106 PAC
        // 40010 SEDEX
        // 40045 SEDEX a Cobrar
        // 40215 SEDEX 10
        $data['nCdServico'] = '41106,40010,40215';
        $data = http_build_query( $data );

        $url = 'http://ws.correios.com.br/calculador/CalcPrecoPrazo.aspx';
        $curl = curl_init( $url . '?' . $data );
        curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, false );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1" );
        $result = curl_exec( $curl );

        $curl_info = curl_getinfo( $curl );
        $resp_code = $curl_info['http_code'];
        if ( $resp_code != '200' )
        {
            echo -1;
            exit;
        }
        curl_close( $curl );
        $result = simplexml_load_string( $result );
        $cb = "";
        $img_correio = "sedex.png";
        foreach ( $result->cServico as $row )
        {
        //Os dados de cada servi�o estar� aqui 
        switch ( $row->Codigo )
        {
            case '41106' :
                $servico = 'Encomenda PAC';
                $img_correio = "pac.gif";
                $img_correio = "pac.png";
                break;
            case '40010' :
                $servico = 'Sedex';
                $img_correio = "sedex.gif";
                $img_correio = "sedex.png";
                break;
            case '40215' :
                $servico = 'Sedex 10';
                $img_correio = "sedex10.gif";
                $img_correio = "sedex10.png";
                break;
        }

            if ( $row->Erro == 0 )
            {
                if ( $this->config_cep->frete_pac == 0 && $this->config_cep->frete_sedex10 == 0 && $this->config_cep->frete_sedex == 0 )
                {
                    echo "<p class='alert alert-danger'>N�o h� nenhum m�todo de envio dispon�vel!</p>";
                    break;
                }
                if ( $row->Codigo == '40010' && $this->config_cep->frete_sedex == 0 )
                {
                    break;
                }
                if ( $row->Codigo == '40215' && $this->config_cep->frete_sedex10 == 0 )
                {
                    break;
                }
                if ( $row->Codigo == '41106' && $this->config_cep->frete_pac == 0 )
                {
                    break;
                }

                if ( isset( $this->uri_segment ) && in_array( 'no-cf', $this->uri_segment ) && $row->Codigo == '41106' )
                {
                    $_SESSION['mycep_frete'] = "0.00";
                    $servico = 'Econ&#244;mico';
                    $row->Valor = "";
                }

                $cb .= '<tr>';
                $cb .= '<td width="20" style="line-height:15px; height:15px; vertical-align: top !important; text-align:right">';
                $cb .= '<input type="radio" class="btn-update-frete" name="tipo_frete[]" id="' . $row->Codigo . '"  t="' . $servico . '" value="' . $row->Valor . '|' . $row->PrazoEntrega . '" v="' . $this->double( $row->Valor ) . '" p="' . $row->PrazoEntrega . '" />';
                $cb .= '</td>';
                $cb .= '<td width="70">';
                $cb .= '<label for="' . $row->Codigo . '">';
                if ( $row->Valor == "" )
                {
                    $cb .= 'Frete Gr�tis';
                }
                else
                {
                    $cb .= 'R$ ' . $row->Valor;
                }
                $cb .= '</label>';
                $cb .= '</td>';
                $cb .= '<td width="100">';
                $cb .= '<label for="' . $row->Codigo . '">';
                $cb .= '<b class="f-gray"> ' . $servico . '</b>';
                //$cb .= '<img src="images/layout/' . $img_correio . '" />';
                $cb .= '</label>';
                $cb .= '</td>';
                $cb .= '<td width="150">';
                $cb .= '<label for="' . $row->Codigo . '">';
                if ( $row->PrazoEntrega > 1 )
                {
                    $cb .= ' at� ' . $row->PrazoEntrega . ' dias �teis';
                }
                else
                {
                    $cb .= ' 1 dia �til';
                }
                $cb .= '</label>';
                $cb .= '</td>';
                $cb .= '</tr>';
            }
            else
            {
                echo "$servico: ", utf8_decode( $row->MsgErro ) , "<br><br>";
            }
        }
        $cb .= "</table>";
        $this->buscaFreteGratis();
        $this->frete_opcoes[] = $cb;
        echo implode( "", $this->frete_opcoes );
    }

    public function double( $str )
    {
        return preg_replace( '/,/', '.', $str );
    }
}
