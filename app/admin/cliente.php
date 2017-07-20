<?php

class Cliente extends PHPFrodo
{

    public $login = null;
    public $user_login;
    public $user_id;
    public $user_name;
    public $user_level;
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
        $this->select()
                ->from( 'config' )
                ->execute();
        if ( $this->result() )
        {
            $this->config = ( object ) $this->data[0];
            $this->assignAll();
        }
        $this->user_login = $sid->getNode( 'user_login' );
        $this->user_id = $sid->getNode( 'user_id' );
        $this->user_name = $sid->getNode( 'user_name' );
        $this->user_level = ( int ) $sid->getNode( 'user_level' );
        $this->assign( 'user_name', $this->user_name );
        if ( $this->user_level == 1 ) {
            $this->assign('showhide','hide');
        }         
    }

    public function welcome()
    {
        $this->pagebase = "$this->baseUri/admin/cliente";
        $this->tpl( 'admin/cliente.html' );
        $this->select()
                ->from( 'cliente' )
                ->join( 'endereco', 'cliente_id = endereco_cliente', 'INNER' )
                ->where( 'endereco_tipo = 1' )
                ->paginate( 25 )
                ->orderby( 'cliente_nome asc' )
                ->execute();
        if ( $this->result() )
        {
            $this->fetch( 'cl', $this->data );
            $this->assign( 'item_qtde', $this->getTotalCliente() );
        }
        $this->render();
    }

    public function getTotalCliente()
    {
        $this->select()->from( 'cliente' )->execute();
        if ( $this->result() )
        {
            return count( $this->data );
        }
        else
        {
            return 0;
        }
    }

    public function editar()
    {
        if ( isset( $this->uri_segment[2] ) )
        {
            $this->cliente_id = $this->uri_segment[2];
            $this->tpl( 'admin/cliente_editar.html' );
            $this->select()
                    ->from( 'cliente' )
                    ->join( 'endereco', 'cliente_id = endereco_cliente', 'INNER' )
                    ->where( "cliente_id = $this->cliente_id AND endereco_tipo = 1" )
                    ->execute();
            if ( $this->result() )
            {
                $this->assignAll();
                $this->select()
                        ->from( 'endereco' )
                        ->where( "endereco_cliente = $this->cliente_id AND endereco_tipo = 2" )
                        ->execute();
                if ( $this->result() )
                {
                    $this->fetch( 'addr', $this->data );
                    $this->assignAll();
                }
            }
            else
            {
                $this->redirect( "$this->baseUri/admin/cliente/" );
            }
            $this->render();
        }
    }

    public function atualizar()
    {
        $this->tpl( 'admin/cliente_editar.html' );
        $valid = array(
            'cliente_id' => 'string',
            'cliente_nome' => 'string',
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
            $this->login = array(
                'cliente_email' => $this->postGetValue( 'cliente_email' ),
                'cliente_nome' => $this->postGetValue( 'cliente_nome' ),
                'cliente_id' => $this->postGetValue( 'cliente_id' )
            );
            $this->cliente_cpf = $this->postGetValue( 'cliente_cpf' );
            $this->cliente_id = $this->postGetValue( 'cliente_id' );
            $this->cliente_email = $this->postGetValue( 'cliente_email' );
            $pass = $this->postGetValue( 'cliente_password' );
            if ( $pass == "" )
            {
                $this->postIndexDrop( 'cliente_password' );
            }
            else
            {
                $this->postValueChange( 'cliente_password', md5( $this->postGetValue( 'cliente_password' ) ) );
            }
            $this->msg_error = "";
            if ( $this->checkCPF() )
            {
                $this->msg_error .= "$('#cliente_cpf').addClass('invalid');\n";
                $this->msg_error .= "$('#cliente_cpf').popover({'trigger':'focus','placement':'top','title': 'Atenção:','content':'CPF já cadastrado!'});\n";
                $this->msg_error .= "$('#cliente_cpf').popover('show');\n";
            }
            if ( $this->msg_error == "" )
            {
                $this->update( 'cliente' )->set()->where( "cliente_id = $this->cliente_id" )->execute();
            }
            else
            {
                $this->assign( 'msg_error', $this->msg_error );
            }
            $this->fillDados();
            if ( $this->msg_error != "" )
            {
                $this->assign( 'cliente_cpf', $this->cliente_cpf );
                $this->assign( 'cliente_email', $this->cliente_email );
                $this->assign( 'cliente_nome', $this->postGetValue( 'cliente_nome' ) );
                $this->assign( 'cliente_datan', $this->postGetValue( 'cliente_datan' ) );
                $this->assign( 'cliente_telefone', $this->postGetValue( 'cliente_telefone' ) );
                $this->assign( 'cliente_celular', $this->postGetValue( 'cliente_celular' ) );
                $this->assign( 'cliente_cep', $this->postGetValue( 'cliente_cep' ) );
                $this->assign( 'cliente_rua', $this->postGetValue( 'cliente_rua' ) );
                $this->assign( 'cliente_num', $this->postGetValue( 'cliente_num' ) );
                $this->assign( 'cliente_cidade', $this->postGetValue( 'cliente_cidade' ) );
                $this->assign( 'cliente_bairro', $this->postGetValue( 'cliente_bairro' ) );
                $this->assign( 'cliente_complemento', $this->postGetValue( 'cliente_complemento' ) );
                $this->assign( 'cliente_uf', $this->postGetValue( 'cliente_uf' ) );
            }
            else
            {
                $this->msg_error = "$('#f-cliente').popover({'trigger':'focus','placement':'top','title': 'Atualizado com sucesso!','content':'O cadastro foi atualizado!'});\n";
                $this->msg_error .= "$('#f-cliente').popover('show');\n";
                $this->assign( 'msg_error', $this->msg_error );
            }
        }
        else
        {
            $this->pageError();
        }
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

    public function remover()
    {
        if ( isset( $this->uri_segment[2] ) )
        {
            $this->cliente_id = $this->uri_segment[2];
            $this->delete()
                    ->from( 'cliente' )
                    ->where( "cliente_id = $this->cliente_id" )
                    ->execute();
            $this->redirect( "$this->baseUri/admin/cliente/" );
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

    public function pageError()
    {
        
    }
    
    public function pedido()
    {
        $this->cliente_id = $this->uri_segment[2];
        $this->pagebase = "$this->baseUri/admin/cliente/pedido";
        $this->tpl( 'admin/pedido.html' );
        $this->select()
                ->from( 'pedido' )
                ->join( 'lista', 'lista_pedido = pedido_id', 'INNER' )
                ->join( 'cliente', 'cliente_id = pedido_cliente', 'INNER' )
                ->where( "pedido_cliente = $this->cliente_id" )
                ->paginate( 15 )
                ->groupby( 'pedido_id' )
                ->orderby( 'pedido_id desc' )
                ->execute();
        if ( $this->result() )
        {
            $this->money( 'pedido_total_frete' );
            $this->addkey( 'staticon', '', 'pedido_status' );
            $this->preg( $this->status_pat, $this->status_rep_icon, 'staticon' );
            $this->preg( $this->status_pat, $this->status_rep, 'pedido_status' );
            $this->fetch( 'cart', $this->data );
            $this->assign('para','para: ' . $this->data[0]['cliente_nome']);
        }
        else
        {
            $this->assign( 'showHide', 'hide' );
            $this->assign( 'msg_pedido', '<h5 class="alert">Nenhum pedido na lista.</h5>' );
        }
        $this->render();
    }    

}

/*end file*/