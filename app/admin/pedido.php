<?php

class Pedido extends PHPFrodo
{
    public $login = null;
    public $user_login;
    public $user_id;
    public $user_level;
    public $pedido_id;
    public $pedido_status;
    public $user_name;
    public $cliente_id;
    public $cliente_cpf;
    public $cliente_nome;
    public $cliente_email;
    public $status_pat = array( '/1/', '/2/', '/3/', '/4/', '/5/', '/6/', '/7/' );
    public $status_rep = array( 'Aguardando pagamento', 'Em análise', 'Aprovado', 'Disponível', 'Em disputa', 'Devolvida', 'Cancelada' );
    public $status_rep_icon = array( 'away.png', 'away.png', 'on.png', 'on.png', 'away.png', 'busy.png', 'busy.png' );

    public function __construct()
    {
        parent::__construct();
        $sid = new Session;
        $sid->start();
        if ( !$sid->check() || $sid->getNode( 'user_id' ) <= 0 )
        {
            $this->redirect( "$this->baseUri/admin/login/logout/" );
            exit;
        }
        $this->user_login = $sid->getNode( 'user_login' );
        $this->user_id = $sid->getNode( 'user_id' );
        $this->user_name = $sid->getNode( 'user_name' );
        $this->user_level = ( int ) $sid->getNode( 'user_level' );
        $this->assign( 'user_name', $this->user_name );
        $this->select()
                ->from( 'config' )
                ->execute();
        if ( $this->result() )
        {
            $this->config = ( object ) $this->data[0];
            $this->assignAll();
        }
        if ( in_array( 'process-ok', $this->uri_segment ) )
        {
            $this->assign( 'msgOnload', 'notify("<h1>Procedimento realizado com sucesso</h1>")' );
        }
        if ( $this->user_level == 1 )
        {
            $this->assign( 'showhide', 'hide' );
        }
    }

    public function welcome()
    {
        $this->pagebase = "$this->baseUri/admin/pedido";
        $this->tpl( 'admin/pedido.html' );
        $this->select()
                ->from( 'pedido' )
                ->join( 'lista', 'lista_pedido = pedido_id', 'INNER' )
                ->join( 'cliente', 'cliente_id = pedido_cliente', 'INNER' )
                //->where( "pedido_cliente = $this->cliente_id" )
                ->paginate( 15 )
                ->groupby( 'pedido_id' )
                ->orderby( 'pedido_id desc' )
                ->execute();
        if ( $this->result() )
        {
            //$this->printr($this->data);exit;
            foreach ( $this->data as $k => $v )
            {
               // $this->data[$k]['pedido_total_frete'] = $this->data[$k]['pedido_total_produto'] - $this->data[$k]['pedido_cupom_desconto'];
            }
            $this->money( 'pedido_total_frete' );
            $this->addkey( 'staticon', '', 'pedido_status' );
            $this->preg( $this->status_pat, $this->status_rep_icon, 'staticon' );
            $this->preg( $this->status_pat, $this->status_rep, 'pedido_status' );
            $this->fetch( 'cart', $this->data );
        }
        else
        {
            $this->assign( 'showHidePed', 'hide' );
            $this->assign( 'msg_pedido', '<h5 class="alert">Nenhum pedido na lista.</h5>' );
        }
        $this->render();
    }

    public function observacao()
    {
        if ( $this->postIsValid( array( 'pedido_obs' => 'string' ) ) )
        {
            $this->pedido_id = $this->postGetValue( 'pedido_id' );
            $this->postValueChange( 'pedido_obs', strip_tags( $this->postGetValue( 'pedido_obs' ) ) );
            $this->postIndexDrop( 'pedido_id' );
            $this->update( 'pedido' )->set()->where( "pedido_id = $this->pedido_id" )->execute();
        }
        $this->redirect( "$this->baseUri/admin/pedido/detalhe/$this->pedido_id/process-ok" );
    }

    public function rastreio()
    {
        if ( $this->postIsValid( array( 'pedido_id' => 'string' ) ) )
        {
            $this->pedido_id = $this->postGetValue( 'pedido_id' );
            $this->postValueChange( 'pedido_codigo_rastreio', strip_tags( $this->postGetValue( 'pedido_codigo_rastreio' ) ) );
            $this->postIndexDrop( 'pedido_id' );
            $this->update( 'pedido' )->set()->where( "pedido_id = $this->pedido_id" )->execute();
        }
        $this->redirect( "$this->baseUri/admin/pedido/detalhe/$this->pedido_id/process-ok" );
    }

    public function rastrear( $codigo = null, $ret = null )
    {
        if ( $codigo != null && isset( $_POST['codigo'] ) )
        {
            $codigo = $_POST['codigo'];
        }
        $url = 'http://websro.correios.com.br/sro_bin/txect01$.Inexistente?P_LINGUA=001&P_TIPO=002&P_COD_LIS=' . $codigo;
        $retorno = @file_get_contents( $url );
        @preg_match( '/<table  border cellpadding=1 hspace=10>.*<\/TABLE>/s', $retorno, $tabela );
        if ( count( $tabela ) == 1 )
        {
            $tabela[0] = preg_replace(
                    array( '/<table  border cellpadding=1 hspace=10>/',
                '/<font FACE=Tahoma color=\'#CC0000\' size=2>/',
                '/<FONT COLOR=\"5F9F9F\"\>/',
                '/<FONT COLOR=\"5F9F9F\"\>/',
                '/<FONT COLOR=\"007FFF\"\>/',
                '/<FONT COLOR=\"000000\"\>/',
                '/rowspan=1/',
                '/<\/font>/',
                '/<b>/',
                '/<\/b>/',
                    ), array( '<table class="table table-striped">', '', '', '',
                '',
                '',
                '',
                '' ), $tabela[0] );
            if ( $ret == null )
                echo $tabela[0];
            else
                return $tabela[0];
        }
        //DL803865144BR
    }

    public function detalhe()
    {
        $this->tpl( 'admin/pedido_detalhes.html' );
        if ( isset( $this->uri_segment[2] ) )
        {
            $this->pedido_id = $this->uri_segment[2];
            $this->select()
                    ->from( 'pedido' )
                    ->join( 'lista', 'lista_pedido = pedido_id', 'INNER' )
                    ->join( 'cliente', 'pedido_cliente = cliente_id', 'INNER' )
                    ->join( 'pay', 'pedido_pay_gw = pay_id', 'INNER' )
                    ->where( "pedido_id = $this->pedido_id" )
                    ->execute();
            if ( $this->result() )
            {

                if ( $this->data[0]['pedido_codigo_rastreio'] != "" )
                {
                    $rastreio = $this->rastrear( $this->data[0]['pedido_codigo_rastreio'], 1 );
                    $this->assign( 'rastreio', $rastreio );
                }
                $this->cut( 'lista_title', 65, '...' );
                foreach ( $this->data as $k => $v )
                {
                    $this->data[$k]['lista_total'] = $this->data[$k]['lista_preco'] * $this->data[$k]['lista_qtde'];
                    $this->data[$k]['pedido_total_sem_frete'] = $this->data[$k]['pedido_total_produto'] - $this->data[$k]['pedido_cupom_desconto'];
                }
                if ( $this->data[0]['pedido_cupom_desconto'] <= 0 )
                {
                    $this->assign( "show-desconto", "hide" );
                }

                $this->data[0]['pedido_total_frete'] = ($this->data[0]['pedido_total_produto'] - $this->data[0]['pedido_cupom_desconto']) + $this->data[0]['pedido_frete'];
                $this->money( 'lista_total' );
                $this->money( 'pedido_total_sem_frete' );
                $this->money( 'pedido_total_frete' );
                $this->money( 'pedido_cupom_desconto' );
                $this->money( 'lista_preco' );
                $this->money( 'pedido_total_produto' );
                $this->money( 'pedido_frete' );

                $this->pedido_update = date( 'd/m/Y H:i:s', strtotime( $this->data[0]['pedido_update'] ) );
                $this->assign( "pedido_last_update", "$this->pedido_update" );
                if ( isset($this->data[0]['pedido_comprovante']) && $this->data[0]['pedido_comprovante'] != 0 )
                {
                    $this->assign( 'cupom_desconto_anexo', 'showin' );
                }
                else
                {
                    $this->assign( 'cupom_desconto_anexo', 'hide hider' );
                }

                $this->assignAll();
                $this->fetch( 'cart', $this->data );
                $pedido_entrega = $this->data[0]['pedido_entrega'];
                $endereco_id = $this->data[0]['pedido_endereco'];
                if ( $pedido_entrega == 1 )
                {
                    $this->assign( 'tipo_local', 'Entrega' );
                    $this->select()->from( 'endereco' )->where( "endereco_id = $endereco_id" )->execute();
                    $this->assignAll();
                }
                else
                {
                    $this->assign( 'tipo_local', 'Retirada' );
                    $this->select()->from( 'retirada' )->where( "retirada_id = $endereco_id" )->execute();
                    $this->assign( 'endereco_title', $this->data[0]['retirada_local'] );
                    $this->assign( 'endereco_rua', $this->data[0]['retirada_rua'] );
                    if ( strlen( $this->data[0]['retirada_complemento'] ) >= 2 )
                    {
                        $this->data[0]['retirada_num'] = $this->data[0]['retirada_num'] . ", " . $this->data[0]['retirada_complemento'];
                    }
                    $this->assign( 'endereco_num', $this->data[0]['retirada_num'] );
                    $this->assign( 'endereco_bairro', $this->data[0]['retirada_bairro'] );
                    $this->assign( 'endereco_cidade', $this->data[0]['retirada_cidade'] );
                    $this->assign( 'endereco_uf', $this->data[0]['retirada_uf'] );
                    $this->assign( 'endereco_cep', $this->data[0]['retirada_cep'] );
                    $this->assign( 'endereco_telefone', $this->data[0]['retirada_telefone'] );
                    $this->assign( 'endereco_horario', $this->data[0]['retirada_horario'] );
                    $this->assignAll();
                }
            }
        }
        else
        {
            $this->redirect( "$this->baseUri/admin/pedido/" );
        }
        $this->render();
    }

    public function busca()
    {
        $this->tpl( 'admin/pedido_detalhes.html' );
        if ( $this->postIsValid( array( 'pedido_id' => 'string' ) ) )
        {
            $this->pedido_id = $this->postGetValue( 'pedido_id' );
            $this->assign( 'pedido_id', $this->pedido_id );
            $this->tpl( 'admin/pedido.html' );
            $this->select()
                    ->from( 'pedido' )
                    ->join( 'lista', 'lista_pedido = pedido_id', 'INNER' )
                    ->join( 'cliente', 'cliente_id = pedido_cliente', 'INNER' )
                    ->where( "pedido_id =  $this->pedido_id" )
                    ->paginate( 15 )
                    ->groupby( 'pedido_id' )
                    ->orderby( 'pedido_id desc' )
                    ->execute();
            if ( $this->result() )
            {
                $this->money( 'pedido_total' );
                $this->money( 'pedido_total_frete' );
                $this->addkey( 'staticon', '', 'pedido_status' );
                $this->preg( $this->status_pat, $this->status_rep_icon, 'staticon' );
                $this->preg( $this->status_pat, $this->status_rep, 'pedido_status' );
                $this->fetch( 'cart', $this->data );
            }
            else
            {
                $this->assign( 'showHide', 'hide' );
                $this->assign( 'msg_pedido', '<h5 class="alert">Nenhum pedido na lista.</h5>' );
            }
        }
        $this->render();
    }

    public function status()
    {
        if ( isset( $this->uri_segment[2] ) )
        {
            $this->pedido_id = $this->uri_segment[2];
            $this->pedido_status = $_POST['pedido_status'];
            //baixa no estoque
            if ( $this->pedido_status == 3 )
            {
                $this->select()
                        ->from( 'pedido' )
                        ->join( 'lista', 'lista_pedido = pedido_id', 'INNER' )
                        ->where( "pedido_id =  $this->pedido_id" )
                        ->execute();
                if ( $this->result() )
                {
                    //$this->printr($this->data);exit;
                    $data = $this->data;
                    foreach ( $data as $item )
                    {
                        $this->item_id = $item['lista_item'];
                        $this->lista_qtde = $item['lista_qtde'];
                        if ( strlen( $item['lista_atributos'] ) >= 1 )
                        {
                            $all_attr = explode( "|", $item['lista_atributos'] );
                            foreach ( $all_attr as $attr )
                            {
                                $attr = explode( ",", $attr );
                                $attr_id = $attr[2];
                                $attr_item = $attr[3];
                                $this->select()
                                        ->from( 'relatrr' )
                                        ->where( "relatrr_item = $this->item_id AND relatrr_atributo = $attr_id AND relatrr_iattr = $attr_item" )
                                        ->execute();
                                if ( $this->result() )
                                {
                                    $rel = $this->data;
                                    foreach ( $rel as $r )
                                    {
                                        $relid = $r['relatrr_id'];
                                        $this->decrement( 'relatrr', 'relatrr_qtde', $this->lista_qtde, "relatrr_id = $relid" )->execute();
                                    }
                                }
                            }
                        }
                        $this->decrement( 'item', 'item_estoque', $this->lista_qtde, "item_id = $this->item_id" )->execute();
                    }
                }
            }
            $this->update( 'pedido' )
                    ->set( array( 'pedido_status' ), array( "$this->pedido_status" ) )
                    ->where( "pedido_id = $this->pedido_id" )
                    ->execute();
            $this->notificarAdmin();
            if ( $this->notificarCliente() )
            {
                $this->redirect( "$this->baseUri/admin/pedido/detalhe/$this->pedido_id/process-ok/" );
            }
            else
            {
                $this->redirect( "$this->baseUri/admin/pedido/detalhe/$this->pedido_id/process-error/" );
            }
        }
    }

    public function remover()
    {
        if ( isset( $this->uri_segment[2] ) )
        {
            $this->pedido_id = $this->uri_segment[2];
            $this->delete()
                    ->from( 'pedido' )
                    ->where( "pedido_id = $this->pedido_id" )
                    ->execute();
            $this->redirect( "$this->baseUri/admin/pedido/" );
        }
    }

    public function pageError()
    {
        $this->tpl( 'admin/error.html' );
        $this->msgError = "Módulo não configurado!";
        $this->assign( 'msgError', $this->msgError );
        $this->render();
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
            $body .= '<tr style="background: #fff;"><td><strong>Cliente:</strong> </td><td>' . $cliente_nome . '</td></tr>';
            $body .= '</table>';
            $body .= '<br/><br/>';
            $body .= "<a href='$this->baseUri/cliente/'>Acesse a área do cliente em nosso site para ver mais detalhes.</a>";
            $body .= '<br/><br/>';
            $body .= '</body></html>';
            $n = array(
                'subject' => "Status do Pedido Nº$this->pedido_id Atualizado",
                'body' => $body );
            $this->sender( $n );
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
            $body .= '<tr style="background: #eee;"><td><strong>Resumo do Pedido:</strong> </td><td>' . $this->lista_title . '</td></tr>';
            $body .= '</table>';
            $body .= '<br/><br/>';
            $body .= "<a href='$this->baseUri/cliente/'>Acesse a área do cliente em nosso site para ver mais detalhes.</a>";
            $body .= '<br/><br/>';
            $body .= '</body></html>';
            $n = array(
                'email' => " $cliente_email",
                'subject' => "Status do Pedido Nº$this->pedido_id Atualizado",
                'body' => $body );
            return $this->sender( $n );
        }
    }

    public function sender( $n = array( ) )
    {
        $this->select()->from( 'smtp' )->execute();
        if ( $this->result() )
        {
            $m = ( object ) $this->data[0];
            $this->helper( 'mail' );
            global $mail;
            $mail->Port = $m->smtp_port;
            $mail->Host = "$m->smtp_host";
            $mail->Username = $m->smtp_username;
            $mail->Password = $m->smtp_password;
            $mail->From = $m->smtp_username;
            $mail->FromName = $m->smtp_fromname;
            $mail->Subject = $n['subject'];
            $mail->Body = $n['body'];
            if ( $m->smtp_bcc != "" )
            {
                $mail->AddBCC( $m->smtp_bcc );
            }
            if ( isset( $n['email'] ) )
            {
                $mail->AddAddress( $n['email'] );
            }
            else
            {
                $mail->AddAddress( $m->smtp_username );
            }
            if ( $mail->Send() )
            {
                return true;
            }
            else
            {
                return false;
                //echo "Erro: $mail->ErrorInfo <br/> Provaveis causas: <br> - E-mail, Senha, Porta ou Servidor SMTP incorretos.";
            }
            @$mail->ClearAttachments();
            @$mail->ClearAllRecipients();
        }
    }
}
/*end file*/
