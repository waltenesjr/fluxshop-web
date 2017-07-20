<?php
error_reporting( 0 );
class Cliente extends PHPFrodo
{
    public $config = array( );
    public $menu;
    public $proccess_msg = null;
    public $msg_error = null;
    public $message_login;
    public $login = null;
    public $sid = null;
    public $cliente_cpf = null;
    public $cliente_email = null;
    public $cliente_id = null;
    public $cliente_nome = null;
    public $status_pat = array( '/1/', '/2/', '/3/', '/4/', '/5/', '/6/', '/7/' );
    public $status_rep = array( 'Aguardando pagamento', 'Em análise', 'Aprovado', 'Disponível', 'Em disputa', 'Devolvida', 'Cancelada' );

    public function __construct()
    {
        parent:: __construct();
        $this->login = null;
        $this->sid = new Session;
        $this->sid->start();
        if ( $this->sid->check() && $this->sid->getNode( 'cliente_id' ) >= 1 )
        {
            $this->cliente_cep = ( string ) $this->sid->getNode( 'cliente_cep' );
            $this->cliente_email = ( string ) $this->sid->getNode( 'cliente_email' );
            $this->cliente_id = $this->sid->getNode( 'cliente_id' );
            $this->cliente_nome = ( string ) $this->sid->getNode( 'cliente_nome' );
            $this->assign( 'cliente_nome', current( explode( ' ', $this->cliente_nome ) ) );
            $this->assign( 'cliente_email', $this->cliente_email );
            $this->assign( 'cliente_msg', 'acesse aqui sua conta.' );
            $this->login = array(
                'cliente_email' => "$this->cliente_email",
                'cliente_nome' => "$this->cliente_nome",
                'cliente_id' => "$this->cliente_id",
            );
            $this->assign( 'logged', 'true' );
        }
        else
        {
            $this->assign( 'cliente_nome', 'visitante' );
            $this->assign( 'cliente_msg', 'faça seu login ou cadastre-se.' );
            $this->assign( 'logged', 'false' );
        }
        $this->select()
                ->from( 'config' )
                ->execute();
        if ( $this->result() )
        {
            $this->map( $this->data[0] );
            $this->config = ( object ) $this->data[0];
            $this->assignAll();
        }
        $this->getCarrinho();
    }

    public function welcome()
    {
        if ( $this->login == null || $this->cliente_id < 1 )
        {
            $this->tpl( 'public/cliente_login.html' );
        }
        else
        {
            $this->tpl( 'public/cliente_area.html' );
        }
        $this->getMenu();
        $this->render();
    }

    public function cadastro()
    {
        if ( $this->login == null || $this->cliente_id < 1 )
        {
            $this->tpl( 'public/cliente_cadastro.html' );
            if ( isset( $_SESSION['email_cadastro'] ) )
            {
                $cliente_email = trim( strtolower( $_SESSION['email_cadastro'] ) );
                $this->assign( 'cliente_email', "$cliente_email" );
            }
            $this->getMenu();
            $this->render();
        }
        else
        {
            $this->redirect( "$this->baseUri/cliente/" );
        }
    }

    public function dados()
    {
        if ( $this->login != null )
        {
            $this->tpl( 'public/cliente_dados.html' );
            $this->select()
                    ->from( 'cliente' )
                    ->where( "cliente_id = $this->cliente_id" )
                    ->execute();
            if ( $this->result() )
            {
                $this->assignAll();
            }
            if ( isset( $this->uri_segment ) && in_array( 'atualizado', $this->uri_segment ) )
            {
                $this->assign( 'message_default', '<p class="well well-small">DADOS ATUALIZADOS COM SUCESSO!</p>' );
            }
            $this->getMenu();
            $this->render();
        }
        else
        {
            $this->redirect( "$this->baseUri/cliente/" );
        }
    }

    public function enderecoAdd()
    {
        if ( $this->login != null )
        {
            $valid = array(
                'endereco_cep' => 'string',
                'endereco_rua' => 'string',
                'endereco_num' => 'string',
                'endereco_bairro' => 'string',
                'endereco_cidade' => 'string',
                'endereco_uf' => 'string',
                'endereco_title' => 'string'
            );
            if ( $this->postIsValid( $valid ) )
            {
                $this->postIndexAdd( 'endereco_cliente', $this->cliente_id );
                $this->postIndexAdd( 'endereco_tipo', 2 );
                $this->insert( 'endereco' )->fields()->values()->execute();
                if ( isset( $_SESSION['referer'] ) )
                {
                    $url_retorno = $_SESSION['referer'];
                    unset( $_SESSION['referer'] );
                    $this->redirect( "$url_retorno" );
                }
                else
                {
                    $this->redirect( "$this->baseUri/cliente/endereco/cadastrado/" );
                }
            }
        }
    }

    public function enderecoVSpedido()
    {
        $this->endereco_id = $_POST['eid'];
        $this->select()
                ->from( 'endereco' )
                ->join( 'pedido', 'pedido_endereco = endereco_id', 'INNER' )
                ->where( "endereco_id = $this->endereco_id" )
                ->execute();
        if ( $this->result() )
        {
            echo 0;
        }
        else
        {
            echo 1;
        }
    }

    public function endereco()
    {
        if ( $this->login != null )
        {
            $this->tpl( 'public/cliente_endereco.html' );
            $this->select()
                    ->from( 'endereco' )
                    ->where( "endereco_cliente = $this->cliente_id AND endereco_tipo = 1" )
                    ->execute();
            if ( $this->result() )
            {
                $this->fetch( 'addr', $this->data );
                $this->assignAll();
            }
            $this->select()
                    ->from( 'endereco' )
                    ->where( "endereco_cliente = $this->cliente_id AND endereco_tipo = 2" )
                    ->execute();
            if ( $this->result() )
            {
                $this->fetch( 'baddr', $this->data );
                $this->assignAll();
            }
            if ( isset( $this->uri_segment ) && in_array( 'atualizado', $this->uri_segment ) )
            {
                $this->assign( 'message_default', '<p class="well well-small">ENDEREÇO ATUALIZADO COM SUCESSO!</p>' );
            }
            if ( isset( $this->uri_segment ) && in_array( 'removido', $this->uri_segment ) )
            {
                $this->assign( 'message_default', '<p class="well well-small">ENDEREÇO REMOVIDO COM SUCESSO!</p>' );
            }
            if ( isset( $this->uri_segment ) && in_array( 'cadastrado', $this->uri_segment ) )
            {
                $this->assign( 'message_default', '<p class="well well-small">ENDEREÇO CADASTRADO COM SUCESSO!</p>' );
            }
            $this->getMenu();
            $this->render();
        }
        else
        {
            $this->redirect( "$this->baseUri/cliente/" );
        }
    }

    public function enderecoNovo()
    {
        if ( $this->login != null )
        {
            $this->tpl( 'public/cliente_endereco_novo.html' );
            if ( isset( $this->uri_segment ) && in_array( 'cadastrado', $this->uri_segment ) )
            {
                $this->assign( 'message_default', '<p class="well well-small">ENDEREÇO CADASTRADO COM SUCESSO!</p>' );
            }
            $this->getMenu();
            $this->render();
        }
        else
        {
            $this->redirect( "$this->baseUri/cliente/" );
        }
    }

    public function enderecoRemove()
    {
        if ( $this->login != null )
        {
            $this->endereco_id = $this->uri_segment[2];
            $this->delete()
                    ->from( 'endereco' )
                    ->where( "endereco_id = $this->endereco_id AND endereco_cliente = $this->cliente_id" )
                    ->execute();
            $this->redirect( "$this->baseUri/cliente/endereco/removido/" );
        }
        else
        {
            $this->redirect( "$this->baseUri/cliente/" );
        }
    }

    public function fillDados()
    {
        $this->select()
                ->from( 'cliente' )
                ->where( "cliente_id = $this->cliente_id" )
                ->execute();
        if ( $this->result() )
        {
            $this->assignAll();
        }
    }

    public function getMenu()
    {
        $this->menu = new Menu;
        $this->fetch( 'f', $this->menu->getFooter() );
        $this->fetch( 'cat', current( $this->menu->getMenuDepto() ) );
        $this->fetch( 'depto', end( $this->menu->getMenuDepto() ) );
        $this->fetch( 'depto-full', end( $this->menu->getMenuDepto() ) );
    }

    public function getCarrinho()
    {
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
    }

    public function enderecoAtualizar()
    {
        if ( $this->login != null || $this->cliente_id < 1 )
        {
            $valid = array(
                'endereco_cep' => 'string',
                'endereco_rua' => 'string',
                'endereco_num' => 'string',
                'endereco_bairro' => 'string',
                'endereco_cidade' => 'string',
                'endereco_uf' => 'string'
            );
            if ( $this->postIsValid( $valid ) )
            {
                $this->endereco_id = $this->uri_segment[2];
                $this->update( 'endereco' )
                        ->set()
                        ->where( "endereco_id = $this->endereco_id AND endereco_cliente = $this->cliente_id" )
                        ->execute();
                $this->redirect( "$this->baseUri/cliente/endereco/atualizado/" );
            }
            else
            {
                $this->pageError();
            }
        }
        else
        {
            $this->redirect( "$this->baseUri/cliente/" );
        }
    }

    public function atualizarDados()
    {
        if ( $this->login != null || $this->cliente_id < 1 )
        {
            $valid = array(
                'cliente_nome' => 'string',
                'cliente_cpf' => 'cpf',
                'cliente_datan' => 'string',
                'cliente_telefone' => 'string'
            );
            if ( $this->postIsValid( $valid ) )
            {
                $this->cliente_nome = $this->postGetValue( 'cliente_nome' );
                $this->cliente_cpf = $this->postGetValue( 'cliente_cpf' );
                /*
                if ( !$this->checkNome() )
                {
                    $this->msg_error = 'Informe o nome completo!';
                }
                */
                $pass = $this->postGetValue( 'cliente_password' );
                if ( $pass == "" )
                {
                    $this->postIndexDrop( 'cliente_password' );
                }
                else
                {
                    $this->postValueChange( 'cliente_password', md5( $pass ) );
                }
                $this->postIndexDrop( 'cliente_passwordr' );
                $this->postIndexDrop( 'cliente_email' );
                /*
                if ( $this->checkCPF() )
                {
                    $this->msg_error = "CPF já cadastrado!";
                }
                */
                if ( $this->msg_error == "" )
                {
                    $this->update( 'cliente' )->set()->where( "cliente_id = $this->cliente_id" )->execute();
                    $this->redirect( "$this->baseUri/cliente/dados/atualizado/" );
                }
                else
                {
                    $this->assign( 'msg_error', $this->msg_error );
                    $this->pageError();
                }
            }
            else
            {
                $this->pageError();
            }
        }
        else
        {
            $this->redirect( "$this->baseUri/cliente/" );
        }
    }

    public function cadastrar()
    {
        $this->tpl( 'public/cliente_cadastro.html' );
        if ( $this->login == null )
        {
            $valid = array(
                'cliente_nome' => 'string',
                'cliente_password' => 'password',
                'cliente_cpf' => 'cpf',
                'cliente_datan' => 'string',
                'cliente_telefone' => 'string',
                'cliente_cep' => 'string',
                'cliente_rua' => 'string',
                'cliente_num' => 'string',
                'cliente_bairro' => 'string',
                'cliente_cidade' => 'string',
                'cliente_uf' => 'string',
            );
            if ( $this->postIsValid( $valid ) )
            {
                $this->postIndexDrop( 'cliente_passwordr' );
                $this->postValueChange( 'cliente_password', md5( $this->postGetValue( 'cliente_password' ) ) );
                $this->cliente_cpf = $this->postGetValue( 'cliente_cpf' );
                $this->cliente_email = $this->postGetValue( 'cliente_email' );
                $this->cliente_nome = $this->postGetValue( 'cliente_nome' );
                /*
                if ( $this->checkCPF() )
                {
                    $this->msg_error = 'CPF já cadastrado!';
                }
                if ( $this->checkMail() )
                {
                   // $this->msg_error = 'E-mail já cadastrado!';
                }    
                if ( !$this->checkNome() )
                {
                    //$this->msg_error = 'Informe o nome completo!';
                }
                */
                if ( $this->msg_error == "" )
                {
                    //endereco
                    $rua = $this->postGetValue( 'cliente_rua' );
                    $num = $this->postGetValue( 'cliente_num' );
                    $com = $this->postGetValue( 'cliente_complemento' );
                    $bai = $this->postGetValue( 'cliente_bairro' );
                    $cid = $this->postGetValue( 'cliente_cidade' );
                    $uf = $this->postGetValue( 'cliente_uf' );
                    $cep = $this->postGetValue( 'cliente_cep' );
                    $this->postIndexDrop( 'cliente_rua' );
                    $this->postIndexDrop( 'cliente_num' );
                    $this->postIndexDrop( 'cliente_complemento' );
                    $this->postIndexDrop( 'cliente_bairro' );
                    $this->postIndexDrop( 'cliente_cidade' );
                    $this->postIndexDrop( 'cliente_uf' );
                    $this->postIndexDrop( 'cliente_cep' );
                    $this->postIndexAdd( 'cliente_datacad', date( 'd/m/Y h:s' ) );
                    //add cliente
                    $this->insert( 'cliente' )->fields()->values()->execute();
                    $this->cliente_id = mysql_insert_id();
                    $f = array( 'endereco_rua', 'endereco_num',
                        'endereco_complemento', 'endereco_bairro',
                        'endereco_cidade', 'endereco_uf',
                        'endereco_cep', 'endereco_cliente',
                        'endereco_title' );
                    $v = array( "$rua", "$num", "$com", "$bai", "$cid", "$uf", "$cep",
                        "$this->cliente_id", "Endereço de Correspondência" );
                    //add endereco
                    $this->insert( 'endereco' )->fields( $f )->values( $v )->execute();
                    //sessao cadastro
                    //sessao cadastro
                    $this->select( '*' )
                            ->from( 'cliente' )
                            ->where( "cliente_id = $this->cliente_id" )
                            ->execute();
                    if ( $this->result() )
                    {
                        $this->preg( '/\s+/', ' ', 'cliente_nome' );
                        $this->sid = new Session;
                        $this->sid->start();
                        $this->sid->init( 36000 );
                        $this->sid->addNode( 'start', date( 'd/m/Y - h:i' ) );
                        $this->sid->addNode( 'cliente_id', $this->data[0]['cliente_id'] );
                        $this->sid->addNode( 'cliente_email', $this->data[0]['cliente_email'] );
                        $this->sid->addNode( 'cliente_nome', $this->data[0]['cliente_nome'] );
                        $this->sid->addNode( 'cliente_cep', $cep );
                        $this->sid->check();
                        $this->login_status = true;
                        if(isset($_SESSION['cart']))
                        {
                            $this->redirect( "$this->baseUri/finalizar/entrega/" );
                        }
                        else
                        {
                            $this->redirect( "$this->baseUri/cliente/" );
                        }
                    }
                    else
                    {
                        $this->redirect( "$this->baseUri/cliente/" );
                    }
                }
                else
                {
                    $this->assign( 'msg_error', $this->msg_error );
                    $this->pageError();
                }
            }
            else
            {
                $this->pageError();
            }
        }
        else
        {
            $this->redirect( "$this->baseUri/cliente/" );
        }
    }

    public function cadastroPass()
    {
        $this->tpl( 'public/cliente_cadastro_pass.html' );
        $this->getMenu();
        $this->render();
    }

    public function checkCPF()
    {
        if ( $this->login != null )
        {
            $cond = "cliente_cpf = '$this->cliente_cpf' AND cliente_id <> $this->cliente_id";
        }
        else
        {
            $cond = "cliente_cpf = '$this->cliente_cpf'";
        }
        $this->select()
                ->from( 'cliente' )
                ->join( 'endereco', 'cliente_id = endereco_cliente', 'INNER' )
                ->where( "$cond AND endereco_tipo = 1" )
                ->execute();
        if ( $this->result() )
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function checkMail()
    {
        if ( $this->login != null )
        {
            $cond = "cliente_email = '$this->cliente_email' AND cliente_id <> $this->cliente_id";
        }
        else
        {
            $cond = "cliente_email = '$this->cliente_email'";
        }
        $this->select()
                ->from( 'cliente' )
                ->where( "$cond" )
                ->execute();
        if ( $this->result() )
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function checkPreExistCPF()
    {
        $valid = array(
            'cliente_cpf' => 'cpf'
        );
        if ( $this->postIsValid( $valid ) )
        {
            $this->cliente_cpf = $this->postGetValue( 'cliente_cpf' );
            if ( $this->checkCPF() )
            {
                //CPF existe
                echo 1;
            }
            else
            {
                //CPF nao existe
                echo 0;
            }
        }
        else
        {
            //CPF invalido
            echo 2;
        }
    }

    public function checkPreExistEmail()
    {
        $valid = array(
            'cliente_email' => 'email'
        );
        if ( $this->postIsValid( $valid ) )
        {
            $this->cliente_email = $this->postGetValue( 'cliente_email' );
            if ( $this->checkMail() )
            {
                //MAIL existe
                echo 1;
            }
            else
            {
                //MAIL nao existe
                echo 0;
            }
        }
        else
        {
            //MAIL invalido
            echo 2;
        }
    }

    public function checkNome()
    {
        if ( isset( $_POST['nome'] ) )
        {
            $this->cliente_nome = $_POST['nome'];
        }
        $this->cliente_nome = trim( preg_replace( '/\s+/', ' ', $this->cliente_nome ) );
        $t = count( explode( " ", $this->cliente_nome ) );
        if ( $t < 2 )
        {
            echo 1;
            return false;
        }
        else
        {
            echo 0;
            return true;
        }
    }

    public function login()
    {
        $this->tpl( 'public/cliente_login.html' );
        if ( $this->postIsValid( array( 'cliente_cadastrado' => 'string' ) ) )
        {
            $cadastrado = $this->postGetValue( 'cliente_cadastrado' );
            if ( $cadastrado == 'nao' )
            {
                $_SESSION['email_cadastro'] = $this->postGetValue( 'cliente_email' );
                $this->redirect( "$this->baseUri/cliente/cadastro/" );
            }
            else
            {
                $this->proccess();
                $this->assign( 'message_login', "$this->message_login" );
                $this->getMenu();
                $this->render();
            }
        }
        else
        {
            $this->redirect( "$this->baseUri/cliente/cadastro/" );
        }
    }

    public function logout()
    {
        unset( $_SESSION['LAST_ACTIVITY'] );
        /* preversa carrinho
          $this->sid = new Session;
          @$this->sid->start();
          $this->sid->destroy();
          $this->sid->check();
         */
        $this->redirect( "$this->baseUri/" );
    }

    public function proccess()
    {
        if ( $this->postIsValid( array( 'cliente_email' => 'email', 'cliente_password' => 'string' ) ) )
        {
            $cliente_email = $this->postGetValue( 'cliente_email' );
            $cliente_password = md5( $this->postGetValue( 'cliente_password' ) );
            $this->select( '*' )
                    ->from( 'cliente' )
                    ->join( 'endereco', 'cliente_id = endereco_cliente', 'INNER' )
                    ->where( "cliente_email = '$cliente_email' and cliente_password = '$cliente_password'" )
                    ->execute();
            if ( $this->result() )
            {
                $this->preg( '/\s+/', ' ', 'cliente_nome' );
                $this->sid = new Session;
                $this->sid->start();
                $this->sid->init( 36000 );
                $this->sid->addNode( 'start', date( 'd/m/Y - h:i' ) );
                $this->sid->addNode( 'cliente_id', $this->data[0]['cliente_id'] );
                $this->sid->addNode( 'cliente_email', $this->data[0]['cliente_email'] );
                $this->sid->addNode( 'cliente_nome', $this->data[0]['cliente_nome'] );
                $this->sid->addNode( 'cliente_cep', $this->data[0]['endereco_cep'] );
                $this->sid->check();
                $url_retorno = "$this->baseUri/cliente/";
                if ( isset( $_POST['url_retorno'] ) )
                {
                    $url_retorno = $_POST['url_retorno'];
                }
                $this->login_status = true;
                $this->redirect( "$url_retorno" );
            }
            else
            {
                $this->login_status = false;
                $this->message_login .= "<p class=\"alert alert-error\">e-mail ou senha incorretos!</p>";
            }
        }
        else
        {
            $this->login_status = false;
            $this->message_login = "<p class=\"alert alert-error\">e-mail e senha requeridos!</p>";
        }
    }

    public function novasenha()
    {
        if ( $this->postIsValid( array( 'cliente_email' => 'string' ) ) )
        {
            $cliente_email = $this->postGetValue( 'cliente_email' );
            $chars = 'abcdefghijlmnopqrstuvxwzABCDEFGHIJLMNOPQRSTUVXYWZ0123456789';
            $max = strlen( $chars ) - 1;
            $pass = "";
            $width = 8;
            for ( $i = 0; $i < $width; $i++ )
            {
                $pass .= $chars{mt_rand( 0, $max )};
            }
            $this->select( '*' )
                    ->from( 'cliente' )
                    ->where( "cliente_email = '$cliente_email'" )
                    ->execute();
            if ( !$this->result() )
            {
                $this->tpl( 'public/cliente_login.html' );
                $this->message_login = "<p class=\"alert alert-error\">O e-mail informado não está cadastrado!</p>";
                $this->assign( 'message_login', "$this->message_login" );
                $this->getMenu();
                $this->render();
                exit;
            }
            $this->update( 'cliente' )
                    ->set( array( 'cliente_password' ), array( md5( $pass ) ) )
                    ->where( "cliente_email = '$cliente_email'" );
            if ( $this->execute() )
            {
                $body = '<html><body>';
                $body .= '<h1 style="font-size:15px;">Sua nova senha foi gerada!</h1>';
                $body .= '<table style="border-color: #666; font-size:11px" cellpadding="10">';
                $body .= '<tr style="background: #eee;"><td><strong>IP Solicitante:</strong> </td><td>' . $_SERVER['REMOTE_ADDR'] . '</td></tr>';
                $body .= '<tr style="background: #fff;"><td><strong>Data:</strong> </td><td>' . date( 'd/m/Y h:s' ) . '</td></tr>';
                $body .= '<tr style="background: #eee;"><td><strong>Nova Senha:</strong> </td><td>' . $pass . '</td></tr>';
                $body .= '</table>';
                $body .= '<br/><br/>';
                $body .= '</body></html>';
                $m = new sendmail;
                $n = array(
                    'email' => "$cliente_email",
                    'subject' => "$this->config_site_title - Recuperação de senha",
                    'body' => $body );
                if ( $m->sender( $n ) )
                {
                    $this->tpl( 'public/cliente_login.html' );
                    $this->message_login = "<p class=\"alert alert-success\">Sua nova senha foi enviada por e-mail! Verifique sua caixa de entrada.</p>";
                    $this->assign( 'message_login', "$this->message_login" );
                    $this->getMenu();
                    $this->render();
                    exit;
                }
                else
                {
                    $this->tpl( 'public/cliente_login.html' );
                    $this->message_login = "<p class=\"alert alert-error\">Houve um erro ao enviar o e-mail! Entre em contato com suporte!</p>";
                    $this->assign( 'message_login', "$this->message_login" );
                    $this->getMenu();
                    $this->render();
                    exit;
                }
            }
            else
            {
                $this->tpl( 'public/cliente_login.html' );
                $this->message_login = "<p class=\"alert alert-error\">Houve um erro ao enviar o e-mail! Entre em contato com suporte!</p>";
                $this->assign( 'message_login', "$this->message_login" );
                $this->getMenu();
                $this->render();
                exit;
            }
        }
        else
        {
            $this->redirect( "$this->baseUri/cliente/" );
        }
    }

    public function pedidos()
    {
        if ( $this->login != null )
        {
            $this->tpl( 'public/pedido.html' );
            $this->select()
                    ->from( 'pedido' )
                    ->join( 'lista', 'lista_pedido = pedido_id', 'INNER' )
                    ->where( "pedido_cliente = $this->cliente_id" )
                    ->groupby( 'pedido_id' )
                    ->orderby( 'pedido_id desc' )
                    ->execute();
            if ( $this->result() )
            {
                $this->preg( array( '/1/', '/2/', '/3/', '/4/' ), array( 'warning', 'info', 'success', 'error' ), 'pedstatus' );
                $this->preg( $this->status_pat, $this->status_rep, 'pedido_status' );
                $this->cut( 'lista_title', 50, '...' );
                //$this->money( 'pedido_total_frete' );

                $data = $this->data;
                //remove pedidos abandonados a mais de 10 minutos
                foreach ( $data as $k => $v )
                {
		   $data[$k]['lista_total'] = $data[$k]['lista_preco'] * $data[$k]['lista_qtde'];

                    //total produtos - descontos cupom + frete
                    $data[$k]['pedido_total_frete'] = ( $data[$k]['pedido_total_produto'] - $data[$k]['pedido_cupom_desconto'] ) + $data[$k]['pedido_frete'];
                    if ( $this->data[0]['pedido_cupom_desconto'] != 0 )
                    {
                        $data[$k]['pedido_total_produto_desconto'] = ( $data[$k]['pedido_total_produto'] - $data[$k]['pedido_cupom_desconto'] );
                        $showCupomDesconto = 'showin';
                        //$this->money( 'pedido_cupom_desconto' );
                        //$this->money( 'pedido_total_produto_desconto' );
                    }

                    if ( $data[$k]['pedido_pay_url'] == "" )
                    {
                        $pedido_id = $data[$k]['pedido_id'];
                        $updated = $data[$k]['pedido_update'];
                        $now = date( "Y-m-d h:i:s" );

                        $date1 = $updated;
                        $date2 = $now;
                        $diff = abs( strtotime( $date2 ) - strtotime( $date1 ) );
                        $years = floor( $diff / (365 * 60 * 60 * 24) );
                        $months = floor( ($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24) );
                        $days = floor( ($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24) );
                        $hours = floor( ($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24) / (60 * 60) );
                        $minuts = floor( ($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24 - $hours * 60 * 60) / 60 );
                        $seconds = floor( ($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24 - $hours * 60 * 60 - $minuts * 60 ) );
                        //printf( "%d years, %d months, %d days, %d hours, %d minuts\n, %d seconds\n", $years, $months, $days, $hours, $minuts, $seconds );
                        if ( $minuts >= 10 )
                        {
                            if ( isset( $_SESSION['FLUX_PEDIDO_ID'] ) )
                            {
                                unset( $_SESSION['FLUX_PEDIDO_ID'] );
                            }
                            $this->delete()->from( 'pedido' )->where( "pedido_id = $pedido_id" )->execute();
                        }
                    }
                }
		$this->data = $data;
		$this->money( 'pedido_total_frete' );
                $this->fetch( 'cart', $this->data );
            }
            else
            {
                $this->assign( 'showHide', 'hide' );
                $this->assign( 'msg_pedido', '<h5 class="alert">Nenhum pedido em sua lista.</h5>' );
            }
            $this->getMenu();
            $this->render();
        }
        else
        {
            $this->redirect( "$this->baseUri/cliente/" );
        }
    }

    public function __pedido()
    {
        if ( $this->login != null )
        {
            $this->tpl( 'public/pedido_detalhes.html' );
            if ( isset( $this->uri_segment[2] ) )
            {
                $pedido_id = $this->uri_segment[2];
                $this->select()
                        ->from( 'pedido' )
                        ->join( 'lista', 'lista_pedido = pedido_id', 'INNER' )
                        ->where( "pedido_cliente = $this->cliente_id and pedido_id = $pedido_id" )
                        ->execute();
                if ( $this->result() )
                {
                    $showInvoice = ( $this->data[0]['pedido_status'] == 1 ) ? 'showin' : 'hide';
                    $this->assign( 'showInvoice', $showInvoice );
                    $this->cut( 'lista_title', 40, '...' );

                    $this->preg( '/\,/', '.', 'pedido_frete' );
                    $this->preg( '/\,/', '.', 'lista_frete' );

                    $this->addkey( 'pedstatus', '', 'pedido_status' );
                    $this->preg( array( '/1/', '/2/', '/3/', '/4/', '/6/', '/7/' ), array( 'error', 'info', 'success', 'success', 'error', 'error' ), 'pedstatus' );
                    $this->preg( $this->status_pat, $this->status_rep, 'pedido_status' );

                    if ( $this->data[0]['pedido_cupom_desconto'] >= 1 )
                    {
                        $this->data[0]['pedido_total'] = $this->data[0]['pedido_total'];
                    }
                    else
                    {
                        $this->data[0]['pedido_total'] = $this->data[0]['pedido_total'] * $this->data[0]['pedido_frete'];
                    }

                    $this->data[0]['pedido_com_frete'] = $this->data[0]['pedido_total'] + $this->data[0]['pedido_frete'];
                    foreach ( $this->data as $k => $v )
                    {
                        $this->data[$k]['lista_total'] = $this->data[$k]['lista_preco'] * $this->data[$k]['lista_qtde'];
                    }
                    $this->data[0]['pedido_total'] = $this->val2bd( $this->data[0]['pedido_total'] );

                    $showCupomDesconto = 'hide';

                    if ( $this->data[0]['pedido_cupom_desconto'] >= 1 )
                    {
                        $this->data[0]['pedido_total_desconto'] = ( $this->data[0]['pedido_total'] + $this->data[0]['pedido_frete'] ) - $this->data[0]['pedido_cupom_desconto'];
                        $showCupomDesconto = 'showin';
                        $this->money( 'pedido_cupom_desconto' );
                    }
                    else
                    {
                        $this->data[0]['pedido_total_desconto'] = $this->data[0]['pedido_total'] + $this->data[0]['pedido_frete'];
                    }
                    $this->assign( 'showCupomDesconto', $showCupomDesconto );

                    $this->money( 'pedido_total_desconto' );
                    $this->money( 'lista_total' );
                    $this->money( 'lista_preco' );
                    $this->money( 'pedido_total' );
                    $this->money( 'pedido_frete' );
                    $this->money( 'pedido_com_frete' );

                    $this->assignAll();
                    $this->assign( 'pedido_total', $this->data[0]['pedido_total'] );

                    $this->fetch( 'cart', $this->data );
                    $pedido_entrega = $this->data[0]['pedido_entrega'];
                    $endereco_id = $this->data[0]['pedido_endereco'];

                    if ( $this->data[0]['pedido_pay_gw'] == 2 )
                    {
                        if ( $this->sid->getTime() >= 25 )
                        {
                            $this->data[0]['pedido_pay_url'] = "$this->baseUri/cliente/faturapaypal/" . $this->data[0]['pedido_id'] . "/";
                            $this->assign( 'pedido_pay_url', $this->data[0]['pedido_pay_url'] );
                        }
                    }

                    if ( $pedido_entrega == 1 )
                    {
                        $this->assign( 'tipo_local', 'Entrega' );
                        $this->select()->from( 'endereco' )->where( "endereco_cliente = $this->cliente_id AND endereco_id = $endereco_id" )->execute();
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
                    }
                    if ( isset( $this->uri_segment[3] ) && $this->uri_segment[3] == 'show' )
                    {
                        $this->assign( 'showPayWindow', 'ligthPag();' );
                    }
                    else
                    {
                        $this->assign( 'showPayWindow', '' );
                    }
                }
                else
                {
                    $this->redirect( "$this->baseUri/cliente/pedidos/" );
                }
            }
            else
            {
                $this->redirect( "$this->baseUri/cliente/pedidos/" );
            }
            $this->getMenu();
            $this->render();
        }
        else
        {
            $this->redirect( "$this->baseUri/cliente/" );
        }
    }

    public function rastrear( $codigo = null, $ret = null )
    {
        if ( $codigo != null && isset( $_POST['codigo'] ) )
        {
            $codigo = $_POST['codigo'];
        }
        $url = 'http://websro.correios.com.br/sro_bin/txect01$.Inexistente?P_LINGUA=001&P_TIPO=002&P_COD_LIS=' . $codigo;
        $retorno = file_get_contents( $url );
        preg_match( '/<table  border cellpadding=1 hspace=10>.*<\/TABLE>/s', $retorno, $tabela );
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

    public function pedido()
    {
        if ( $this->login != null )
        {
            $this->tpl( 'public/pedido_detalhes.html' );
            if ( isset( $this->uri_segment[2] ) )
            {
                $pedido_id = $this->uri_segment[2];
                $this->select()
                        ->from( 'pedido' )
                        ->join( 'lista', 'lista_pedido = pedido_id', 'INNER' )
                        ->where( "pedido_cliente = $this->cliente_id and pedido_id = $pedido_id" )
                        ->execute();
                if ( $this->result() )
                {
                    if ( $this->data[0]['pedido_pay_url'] == "" )
                    {
                        $updated = $this->data[0]['pedido_update'];
                        $now = date( "Y-m-d h:i:s" );

                        $date1 = $updated;
                        $date2 = $now;
                        $diff = abs( strtotime( $date2 ) - strtotime( $date1 ) );
                        $years = floor( $diff / (365 * 60 * 60 * 24) );
                        $months = floor( ($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24) );
                        $days = floor( ($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24) );
                        $hours = floor( ($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24) / (60 * 60) );
                        $minuts = floor( ($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24 - $hours * 60 * 60) / 60 );
                        $seconds = floor( ($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24 - $hours * 60 * 60 - $minuts * 60 ) );
                        //printf( "%d years, %d months, %d days, %d hours, %d minuts\n, %d seconds\n", $years, $months, $days, $hours, $minuts, $seconds );
                        //
                        //remove pedidos abandonados a mais de 10 minutos
                        if ( $minuts >= 10 )
                        {
                            if ( isset( $_SESSION['FLUX_PEDIDO_ID'] ) )
                            {
                                unset( $_SESSION['FLUX_PEDIDO_ID'] );
                            }
                            $this->delete()->from( 'pedido' )->where( "pedido_id = $pedido_id" )->execute();
                            $this->redirect( "$this->baseUri/cliente/pedidos/" );
                        }
                    }
                    if ( $this->data[0]['pedido_codigo_rastreio'] != "" )
                    {
                        $rastreio = $this->rastrear( $this->data[0]['pedido_codigo_rastreio'], 1 );
                        $this->assign( 'rastreio', $rastreio );
                    }
                    else
                    {
                        $this->assign( 'show-rastreio', 'hide' );
                    }

                    $showInvoice = ( $this->data[0]['pedido_status'] == 1 ) ? 'showin' : 'hide';
                    $this->assign( 'showInvoice', $showInvoice );
                    $this->cut( 'lista_title', 40, '...' );

                    $this->addkey( 'pedstatus', '', 'pedido_status' );
                    $this->preg( array( '/1/', '/2/', '/3/', '/4/', '/6/', '/7/' ), array( 'error', 'info', 'success', 'success', 'error', 'error' ), 'pedstatus' );
                    $this->preg( $this->status_pat, $this->status_rep, 'pedido_status' );
                    foreach ( $this->data as $k => $v )
                    {
                        $this->data[$k]['lista_total'] = $this->data[$k]['lista_preco'] * $this->data[$k]['lista_qtde'];
                    }
                    $showCupomDesconto = 'hide';

                    //total produtos - descontos cupom + frete
                    $this->data[0]['pedido_total_frete'] = ( $this->data[0]['pedido_total_produto'] - $this->data[0]['pedido_cupom_desconto'] ) + $this->data[0]['pedido_frete'];
                    if ( $this->data[0]['pedido_cupom_desconto'] != 0 )
                    {
                        $this->data[0]['pedido_total_produto_desconto'] = ( $this->data[0]['pedido_total_produto'] - $this->data[0]['pedido_cupom_desconto'] );
                        $showCupomDesconto = 'showin';
                        $this->money( 'pedido_cupom_desconto' );
                        $this->money( 'pedido_total_produto_desconto' );
                    }
                    $this->assign( 'showCupomDesconto', $showCupomDesconto );
                    $this->money( 'pedido_total_desconto' );
                    $this->money( 'lista_total' );
                    $this->money( 'lista_preco' );
                    $this->money( 'pedido_total_produto' );
                    $this->money( 'pedido_total_frete' );
                    $this->money( 'pedido_frete' );
                    $this->money( 'pedido_com_frete' );
                    $this->assignAll();

                    $this->fetch( 'cart', $this->data );
                    $pedido_entrega = $this->data[0]['pedido_entrega'];
                    $endereco_id = $this->data[0]['pedido_endereco'];

                    if ( $pedido_entrega == 1 )
                    {
                        $this->assign( 'tipo_local', 'Entrega' );
                        $this->select()->from( 'endereco' )->where( "endereco_cliente = $this->cliente_id AND endereco_id = $endereco_id" )->execute();
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
                    }
                    if ( isset( $this->uri_segment[3] ) && $this->uri_segment[3] == 'show' )
                    {
                        $this->assign( 'showPayWindow', 'ligthPag();' );
                    }
                    else
                    {
                        $this->assign( 'showPayWindow', '' );
                    }
                }
                else
                {
                    $this->redirect( "$this->baseUri/cliente/pedidos/" );
                }
            }
            else
            {
                $this->redirect( "$this->baseUri/cliente/pedidos/" );
            }
            $this->getMenu();
            $this->render();
        }
        else
        {
            $this->redirect( "$this->baseUri/cliente/" );
        }
    }

    public function recuperar()
    {
        $action = $this->uri_segment[2];
        switch ( $action )
        {
            case 'senha':
                $this->recuperarSenha();
                break;
            case 'email':
                $this->recuperarEmail();
                break;
            case 'emailmudou':
                $this->recuperarEmailMudou();
                break;
        }
    }

    public function recuperarSenha()
    {
        $this->tpl( 'public/cliente_login_recuperar_senha.html' );
        $this->getMenu();
        $this->render();
    }

    public function recuperarEmail()
    {
        echo "recuperar e-mail";
    }

    public function recuperarEmailMudou()
    {
        echo "recuperar e-mail mudou";
    }

    public function pageError()
    {
        $this->tpl( 'public/page_error.html' );
        $this->assign( 'msg_error', $this->msg_error );
        $this->getMenu();
        $this->render();
    }

    public function val2bd( $str )
    {
        //$str = preg_replace( '/\./', '', $str );
        $str = preg_replace( '/\,/', '.', $str );
        return $str;
    }

    public function _money( $val )
    {
        return @number_format( $val, 2, ",", "." );
    }

    public function _moneyUS( $val )
    {
        return @number_format( $val, 2, ".", "" );
    }

    public function _double( $val )
    {
        return @number_format( $val, 2, ".", "," );
    }
}
/*end file*/

