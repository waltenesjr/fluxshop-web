<?php
error_reporting( 0 );
class Atendimento extends PHPFrodo
{
    public $config = array( );
    public $smtp = array( );
    public $page_url;

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
            $this->assign( 'cliente_nome', current( explode( ' ', $this->cliente_nome ) ) );
            $this->assign( 'cliente_email', $this->cliente_email );
            $this->assign( 'cliente_msg', 'acesse aqui sua conta.' );
            $this->assign( 'logged', 'true' );
            $this->getCliente();
        }
        else
        {
            $this->assign( 'cliente_nome', 'visitante' );
            $this->assign( 'cliente_msg', 'faça seu login ou cadastre-se.' );
            $this->assign( 'logged', 'false' );
        }
        $qtdeITem = 0;
        if ( isset( $_SESSION['cart'] ) && count( $_SESSION['cart'] ) >= 1 )
        {
            $qtdeITem = count( $_SESSION['cart'] );
            $cart = new Carrinho;
            $cart->getTotal();
            $cart->total_compra = @number_format( $cart->total_compra, 2, ",", "." );
            $this->assign( 'cartTotal', "R$ " . $cart->total_compra );
        }
        $this->assign( 'qtdeItem', $qtdeITem );

        $this->select()
                ->from( 'config' )
                ->execute();
        if ( $this->result() )
        {
            $this->config = ( object ) $this->data[0];
            $this->assignAll();
        }

        $this->select()->from( 'smtp' )->execute();
        if ( $this->result() )
        {
            $this->assignAll();
            $this->smtp = ( object ) $this->data[0];
        }
    }

    public function welcome()
    {
        $this->tpl( 'public/atendimento.html' );

        if ( isset( $this->uri_segment ) && in_array( 'enviado', $this->uri_segment ) )
        {
            $this->assign( 'msg_onload', 'messageOk()' );
        }
        if ( isset( $this->uri_segment ) && in_array( 'nao-enviado', $this->uri_segment ) )
        {
            $this->assign( 'msg_onload', 'messageError()' );
        }
        $this->menu = new Menu;
        $this->fetch( 'f', $this->menu->getFooter() );
        $this->fetch( 'cat', current( $this->menu->getMenuDepto() ) );
        $this->fetch( 'depto', end( $this->menu->getMenuDepto() ) );
        $this->fetch( 'depto-full', end( $this->menu->getMenuDepto() ) );
        //redes sociais footer
        $plug = new Social;
        $this->assign( 'social_fb', $plug->social_fb );
        $this->assign( 'social_tw', $plug->social_tw );

        if(isset($_SESSION['FLUX_SOB_CONSULTA'])){
            $flux_sob_consulta = $_SESSION['FLUX_SOB_CONSULTA'];
            $this->assign( 'sob_consulta_msg',"Olá, gostaria de receber mais informações sobre: ".$flux_sob_consulta );
            $this->assign( 'sob_consulta_assunto', $flux_sob_consulta );
        }
        $this->render();
    }

    public function enviar()
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
            $mail->Subject = "Assunto - " . ( string ) $this->config->config_site_title;
            $mail->AddBCC( $m->smtp_bcc );
            $mail->AddAddress( $m->smtp_username );

            $nome = $_POST['nome'];
            $email = $_POST['email'];
			
            $mail->AddReplyTo( $email );
            $assunto = $_POST['assunto'];
            $text = $_POST['mensagem'];
            $fone = $_POST['telefone'];
            $mensagem = '<html><body>';
            $mensagem .= '<h1 style="font-size:15px;">Atendimento ' . $assunto . '</h1>';
            $mensagem .= '<table style="border-color: #666; font-size:11px" cellpadding="10">';
            $mensagem .= '<tr style="background: #eee;"><td><strong>Nome:</strong> </td><td>' . $nome . '</td></tr>';
            $mensagem .= '<tr style="background: #fff;"><td><strong>Email:</strong> </td><td>' . $email . '</td></tr>';
            $mensagem .= '<tr style="background: #eee;"><td><strong>Telefone:</strong> </td><td>' . $fone . '</td></tr>';
            $mensagem .= '<tr style="background: #fff;"><td><strong>Mensagem:</strong> </td><td>' . nl2br( $text ) . '</td></tr>';
            $mensagem .= '</table>';
            $mensagem .= '</body></html>';
            $mail->Body = $mensagem;
            if ( @$mail->Send() )
            {
                $this->redirect( "$this->baseUri/atendimento/enviado/" );
            }
            else
            {
                $this->redirect( "$this->baseUri/atendimento/nao-enviado/" );
            }
        }
    }

    public function getCliente()
    {
        $this->select()
                ->from( 'cliente' )
                ->where( "cliente_id = $this->cliente_id" )
                ->execute();
        if ( $this->result() )
        {
            $this->map( $this->data[0] );
            $this->config = ( object ) $this->data[0];
            $this->assignAll();
        }
    }
}
/*end file*/
