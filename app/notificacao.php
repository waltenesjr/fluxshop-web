<?php

class Notificacao extends PHPFrodo
{
    public $config = array( );
    public $pay = array( );
    public $pReq = null;
    public $pedido_id = null;
    public $pedido_status = null;
    public $status_pat = array( '/1/', '/2/', '/3/', '/4/', '/5/', '/6/', '/7/' );
    public $status_rep = array( 'Aguardando pagamento', 'Em análise', 'Aprovado', 'Disponível', 'Em disputa', 'Devolvida', 'Cancelada' );

    public function __construct()
    {
        parent:: __construct();
        $this->select()->from( 'pay' )->where( 'pay_name = "PagSeguro"' )->execute();
        $this->map( $this->data[0] );        
    }

    public function welcome()
    {
        
    }

    public function pagSeguro()
    {
        $this->select()->from( 'pay' )->where( 'pay_name = "PagSeguro"' )->execute();
        if ( !$this->result() )
        {
            $body = "<p>Retorno PagSeguro: Módulo não configurado! <br/>";
            $body .= "Hora: " . date( 'd/m/Y H:i:s' ) . " <br />";
            $body .= "Url: " . $this->baseUri;
            $body .= "</p>";
            $this->notificarErro( $body );
            exit;
        }
        $this->map( $this->data[0] );
        if ( isset( $_POST ) && !empty( $_POST ) )
        {
            $this->helper( 'pagseguro' );
            $type = $_POST['notificationType'];
            $code = $_POST['notificationCode'];
            //Verificamos se tipo da notificação é transaction
            if ( $type === 'TRANSACTION' || $type === 'transaction' )
            {
                //Informa as credenciais : Email, e TOKEN
                $credential = new PagSeguroAccountCredentials( "$this->pay_user", "$this->pay_key" );
                //Verifica as informações da transação, e retorna 
                //o objeto Transaction com todas as informações
                $transacao = PagSeguroNotificationService::checkTransaction( $credential, $code );
                //Retorna o objeto TransactionStatus, que vamos resgatar o valor do status
                $status = $transacao->getStatus();
                $this->pedido_status = $status->getValue();
                $this->pedido_id = $transacao->getReference();
                $this->update( 'pedido' )
                        ->set( array( 'pedido_pay_situacao', 'pedido_status' ), array( $this->pedido_status, $this->pedido_status ) )
                        ->where( "pedido_id = $this->pedido_id" )
                        ->execute();
                $this->notificarAdmin();
                if ( $this->pedido_status != 4 )
                {
                    $this->notificarCliente();
                }
            }
        }
        else
        {
            echo 'Nenhum POST enviado para o processamento do retorno!';
        }
    }

    public function paybras()
    {
        $this->select()->from( 'pay' )->where( 'pay_name = "PayBras"' )->execute();
        if ( !$this->result() )
        {
            $body = "<p>Retorno PayBras: Módulo não configurado! <br/>";
            $body .= "Hora: " . date( 'd/m/Y H:i:s' ) . " <br />";
            $body .= "Url: " . $this->baseUri;
            $body .= "</p>";
            $this->notificarErro( $body );
            exit;
        }
        if ( !isset( $_POST ) || empty( $_POST ) )
        {
            $body = "<p>Retorno PayBras: POST Vazio! <br/>";
            $body .= "Hora: " . date( 'd/m/Y H:i:s' ) . " <br />";
            $body .= "Url: " . $this->baseUri;
            $body .= "</p>";
            $this->notificarErro( $body );
            exit;
        }
        $this->map( $this->data[0] );

        $this->helper( 'paybras' );
        _payBrasGetTrans();

        if ( isset( $_POST['meio_de_pagamento'] ) )
        {
            $meio_de_pagamento = $_POST['pedido_meio_pagamento'];
        }
        $dados_logista['email'] = $this->pay_key;
        $dados_logista['token'] = $this->pay_user;
        $retorno = PaybrasCriaTransacao::main( $_POST, $dados_logista );
        if ( isset( $retorno['sucesso'] ) )
        {
            $sucesso = $retorno['sucesso']; // 0: erro; 1: sucesso
        }
        else
        {
            $this->printr( $retorno );
            die;
        }
        if ( !$sucesso )
        {
            $mensagem_erro = $retorno['mensagem_erro'];
            $body = "<p>Retorno PayBras, ERRO! <br/>";
            foreach ( $mensagem_erro as $key => $value )
            {
                $body .= "<br/>" . $value;
            }
            $body .= "Hora: " . date( 'd/m/Y H:i:s' ) . " <br />";
            $body .= "Url: " . $this->baseUri;
            $body .= "</p>";
            $this->notificarErro( $body );
            exit;
        }
        else
        {
            $this->pedido_id = $_POST['pedido_id']; //ID do pedido
            $this->pay_code = $retorno['transacao_id']; //ID da transacoo
            $this->pay_status = $retorno['status_codigo'];
            $status_nome = $retorno['status_nome']; // Nome do status da transação
            $this->pay_url = isset( $retorno['url_pagamento'] ) ? $retorno['url_pagamento'] : null; // URL para pagamento de boleto ou TEF
            $this->fatura_link = "$this->pay_url";
            $nao_autorizado_codigo = isset( $retorno['nao_autorizado_codigo'] ) ? $retorno['nao_autorizado_codigo'] : null; // não autorização de transação cartão
            $nao_autorizado_mensagem = isset( $retorno['nao_autorizado_mensagem'] ) ? $retorno['nao_autorizado_mensagem'] : null; // não autorização de transação com cartão

            $this->pay_obs = "";
            if ( $nao_autorizado_codigo )
            {
                $this->pay_obs .= "Cod. não autorização:  $nao_autorizado_codigo \n";
            }
            if ( $nao_autorizado_mensagem )
            {
                $this->pay_obs .= "Msg. não autorização: $nao_autorizado_mensagem";
            }
            // Código de status da transação:
            //  1: Ag. Pgto
            //  2: Em Análise
            //  3: Não Autorizado
            //  4: Aprovado
            //  5: Recusado
            //atualiza pedido com url e codigo paybras
            $this->update( 'pedido' )
                    ->set( array( 'pedido_pay_code', 'pedido_pay_url', 'pedido_status', 'pedido_pay_gw', 'pedido_pay_obs' ), array( $this->pay_code, $this->pay_url, $this->pay_status, 3, $this->pay_obs ) )
                    ->where( "pedido_id = $this->pedido_id" )->execute();
            $this->notificarAdmin();
            $this->notificarCliente();
        }
    }

    public function notificarAdmin()
    {
        $this->select()
                ->from( 'pedido' )
                ->join( 'cliente', 'cliente_id = pedido_cliente', 'INNER' )
                ->join( 'lista', 'lista_pedido = pedido_id', 'INNER' )
                ->where( "pedido_id = $this->pedido_id" )
                ->groupby( 'pedido_id' )
                ->execute();
        if ( $this->result() )
        {
            $this->cut( 'lista_title', 60, '' );
            $cliente_email = $this->data[0]['cliente_email'];
            $cliente_nome = $this->data[0]['cliente_nome'];
            $this->lista_title = $this->data[0]['lista_title'];
            $this->pedido_status = preg_replace( $this->status_pat, $this->status_rep, $this->pedido_status );
            $body = '<html><body>';
            $body .= '<h1 style="font-size:15px;">Status do pedido ' . $this->pedido_id . ' foi atualizado!</h1>';
            $body .= '<table style="border-color: #666; font-size:11px" cellpadding="10">';
            $body .= '<tr style="background: #fff;"><td><strong>Data:</strong> </td><td>' . date( 'd/m/Y h:s' ) . '</td></tr>';
            $body .= '<tr style="background: #eee;"><td><strong>Número do Pedido:</strong> </td><td>' . $this->pedido_id . '</td></tr>';
            $body .= '<tr style="background: #fff;"><td><strong>Status do Pedido:</strong> </td><td>' . $this->pedido_status . '</td></tr>';
            $body .= '<tr style="background: #eee;"><td><strong>Resumo do Pedido:</strong> </td><td>' . $this->lista_title . '...</td></tr>';
            $body .= '<tr style="background: #fff;"><td><strong>Cliente:</strong> </td><td>' . $cliente_nome . '...</td></tr>';
            $body .= '</table>';
            $body .= '<br/><br/>';
            $body .= '</body></html>';
            $m = new sendmail;
            $n = array(
                'subject' => "Status do Pedido Nº$this->pedido_id Atualizado",
                'body' => $body );
            $m->sender( $n );
        }
    }

    public function notificarCliente()
    {
        $this->select()
                ->from( 'pedido' )
                ->join( 'cliente', 'cliente_id = pedido_cliente', 'INNER' )
                ->join( 'lista', 'lista_pedido = pedido_id', 'INNER' )
                ->where( "pedido_id = $this->pedido_id" )
                ->groupby( 'pedido_id' )
                ->execute();
        if ( $this->result() )
        {
            $this->cut( 'lista_title', 60, '' );
            $cliente_email = $this->data[0]['cliente_email'];
            $cliente_nome = $this->data[0]['cliente_nome'];
            $this->lista_title = $this->data[0]['lista_title'];
            $this->pedido_status = preg_replace( $this->status_pat, $this->status_rep, $this->pedido_status );
            $body = '<html><body>';
            $body .= '<h1 style="font-size:15px;">Olá ' . $cliente_nome . ', o status do seu pedido foi atualizado!</h1>';
            $body .= '<table style="border-color: #666; font-size:11px" cellpadding="10">';
            $body .= '<tr style="background: #fff;"><td><strong>Data:</strong> </td><td>' . date( 'd/m/Y h:s' ) . '</td></tr>';
            $body .= '<tr style="background: #eee;"><td><strong>Número do Pedido:</strong> </td><td>' . $this->pedido_id . '</td></tr>';
            $body .= '<tr style="background: #fff;"><td><strong>Status do Pedido:</strong> </td><td>' . $this->pedido_status . '</td></tr>';
            $body .= '<tr style="background: #eee;"><td><strong>Resumo do Pedido:</strong> </td><td>' . $this->lista_title . '...</td></tr>';
            $body .= '</table>';
            $body .= '<br/><br/>';
            $body .= "<a href='$this->baseUri/cliente/'>Acesse a área do cliente em nosso site para ver mais detalhes.</a>";
            $body .= '<br/><br/>';
            $body .= '</body></html>';
            $m = new sendmail;
            $n = array(
                'email' => " $cliente_email",
                'subject' => "Status do Pedido Nº$this->pedido_id Atualizado",
                'body' => $body );
            $m->sender( $n );
        }
    }

    public function notificarErro( $body )
    {
        $m = new sendmail;
        $n = array(
            'subject' => "Erro no retorno de dados",
            'body' => $body );
        $m->sender( $n );
    }

    public function status()
    {
        $this->helper( 'pagseguro' );
        $ano = date( 'Y' );
        $mes = date( 'm' );
        $dia = date( 'd' );
        $initialDate = date( 'Y-m-d', mktime( 0, 0, 0, date( 'm' ), date( 'd' ) - 5, date( 'Y' ) ) ) . "T00:00";
        $finalDate = '';
        $pageNumber = 1;
        $maxPageResults = 20;
        try
        {
            $credentials = new PagSeguroAccountCredentials( "$this->pay_user", "$this->pay_key" );
            $result = PagSeguroTransactionSearchService::searchByDate( $credentials, $pageNumber, $maxPageResults, $initialDate, $finalDate );
            self::printResult( $result, $initialDate, $finalDate );
        }
        catch ( PagSeguroServiceException $e )
        {
            die( $e->getMessage() );
        }
    }

    public function printResult( PagSeguroTransactionSearchResult $result, $initialDate, $finalDate )
    {
        $finalDate = $finalDate ? $finalDate : 'now';
        $transactions = $result->getTransactions();
        if ( is_array( $transactions ) && count( $transactions ) > 0 )
        {
            foreach ( $transactions as $key => $transactionSummary )
            {
                $this->pedido_status = ( int ) $transactionSummary->getStatus()->getValue();
                $this->pedido_ref = $transactionSummary->getReference();
                $this->pedido_cod = $transactionSummary->getCode();
                $this->pedido_id = $transactionSummary->getReference();
                //$this->pedido_status = "Aprovada / Paga";
                if ( $this->pedido_status == 3 )
                {
                    if ( $this->pedido_ref != '' )
                    {
                        $this->update( 'pedido' )
                                ->set( array( 'pedido_pay_situacao', 'pedido_status' ), array( $this->pedido_status, $this->pedido_status ) )
                                ->where( "pedido_id = $this->pedido_id" )
                                ->execute();
                        $this->notificarAdmin();
                        if ( $this->pedido_status != 4 )
                        {
                            echo "Pedido Atualizado <br>";
                            $this->notificarCliente();
                        }
                    }
                }
                if ( $this->pedido_status == 7 )
                {
                    //$this->remove();
                }
            }
        }
    }
}
/* end file */