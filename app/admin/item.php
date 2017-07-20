<?php

class Item extends PHPFrodo
{
    public $user_login;
    public $user_level;
    public $user_id;
    public $user_name;
    public $msgError;
    public $categoria_id;
    public $categoria_title;
    public $sub_id;
    public $sub_title;
    public $item_id;
    public $item_title;
    public $item_sub;
    public $item_preco;
    public $item_keywords;
    public $item_desc;
    public $item_show;
    public $item_oferta;
    public $item_url;

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
        if ( isset( $this->uri_segment ) && in_array( 'process-ok', $this->uri_segment ) )
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
        $this->pagebase = "$this->baseUri/admin/item";
        $this->tpl( 'admin/item.html' );
        $this->select()
                ->from( 'item' )
                ->join( 'sub', 'sub_id = item_sub', 'INNER' )
                ->join( 'categoria', 'sub_categoria = categoria_id', 'INNER' )
                ->paginate( 15 )
                ->orderby( 'item_title asc' )
                ->execute();
        if ( $this->result() )
        {
            $this->cut( 'item_title', '30', '...' );
            $this->money( 'item_preco' );
            $this->money( 'item_desconto' );
            $this->fetch( 'rs', $this->data );
            $this->assign( 'item_qtde', $this->getTotalItem() );
        }
	$this->assign('limite_produto',$this->limite_produto);
        $this->render();
    }

    public function getTotalItem()
    {
        $this->select()->from( 'item' )->execute();
        if ( $this->result() )
        {
            return count( $this->data );
        }
        else
        {
            return 0;
        }
    }

    public function busca()
    {
        //$this->pagebase = "$this->baseUri/admin/item";
        $item_title = "";
        if ( isset( $_POST['busca'] ) )
        {
            $item_title = $_POST['busca'];
        }
        $this->tpl( 'admin/item_busca.html' );

        if ( $item_title != "" )
        {
            $this->select()
                    ->from( 'item' )
                    ->join( 'sub', 'sub_id = item_sub', 'INNER' )
                    ->join( 'categoria', 'sub_categoria = categoria_id', 'INNER' )
                    ->where( "item_title like'%$item_title%'" )
                    ->orderby( 'item_title asc' )
                    ->execute();
            if ( $this->result() )
            {
                $this->money( 'item_preco' );
                $this->money( 'item_desconto' );
                $this->assign( 'item_qtde', count( $this->data ) );
                $this->fetch( 'rs', $this->data );
            }
            else
            {
                $this->assign( 'showHide', "hide" );
                $this->assign( 'msg_busca', '<h5 class="alert">Nenhum item encontrado.</h5>' );
            }
        }
        else
        {
            $this->assign( 'showHide', "hide" );
        }
        $this->assign( 'busca', "$item_title" );
        $this->render();
    }

    public function editar()
    {
        if ( isset( $this->uri_segment[2] ) )
        {
            $this->item_id = $this->uri_segment[2];
            $this->tpl( 'admin/item_editar.html' );
            $this->select()
                    ->from( 'item' )
                    ->join( 'sub', 'sub_id = item_sub', 'INNER' )
                    ->join( 'categoria', 'categoria_id = sub_categoria', 'INNER' )
                    ->where( "item_id = $this->item_id" )
                    ->execute();
            if ( $this->result() )
            {
                $this->money( 'item_preco' );
                $this->money( 'item_desconto' );
                $this->addkey( 'item_title_short', '', 'item_title' );
                $this->cut( 'item_title_short', 70, '...' );
                $this->assignAll();
                //$this->helper( 'redactor' );
                //$editor = editor( $this->data[0]['item_desc'], 'item_desc', '350px', '90%' );
                //$this->assign( 'editor', $editor );
                $this->fillCategoria();
            }
            if ( isset( $this->uri_segment[3] ) )
            {
                $tab = $this->uri_segment[3];
                $tab = "$('#myTab a[href=\"#$tab\"]').tab('show')";
                $this->assign( 'loadTab', $tab );
            }
            //fill fotos
            $this->fillFotos();
            $this->fillAtributos();
            $this->render();
        }
    }

    public function novo()
    {
         $this->tpl( 'admin/item_novo.html' );
          $limite = $this->getItems();
          if($_SESSION['plano_limite'] == true || $limite >= $this->limite_produto )
          {
         	 $this->msgError = utf8_decode ("VOCÊ ATINGIU O LIMITE DE $limite ITENS CADASTRADOS");
         	 $this->pageError();
          exit;
          }
     
        $this->fillCategoria();
        $this->render();
    }

    public function getItems()
    {
        $plano = $this->limite_produto;
        $this->select()->from( 'item' )->execute();
        if ( $this->result() )
        {
            if ( count( $this->data ) >= $plano )
            {
                $_SESSION['plano_limite'] = true;
            }
            else
            {
                $_SESSION['plano_limite'] = false;
            }
        }
        return count( $this->data );
    }

    public function fillFotos()
    {
        $this->select()
                ->from( 'foto' )
                ->where( "foto_item = $this->item_id" )
                ->orderby( 'foto_pos asc' )
                ->execute();
        if ( $this->result() )
        {
            $this->preg( array('/\.jpg/','/\.png/'), array('',''),  'foto_url' );
            $this->fetch( 'ft', $this->data );
        }
        else
        {
            $this->assign( 'fotoControl', 'hide' );
        }
    }

    public function fillCategoria()
    {
        $this->select()
                ->from( 'categoria' )
                ->orderby( 'categoria_title asc' )
                ->execute();
        if ( $this->result() )
        {
            $this->fetch( 'combo', $this->data );
        }
    }

    public function fillSubCategoria()
    {
        if ( isset( $this->uri_segment[2] ) )
        {
            $this->categoria_id = $this->uri_segment[2];
            $this->select( 'sub_id,sub_title' )
                    ->from( 'sub' )
                    ->where( "sub_categoria = $this->categoria_id" )
                    ->orderby( 'sub_title asc' )
                    ->execute();
            if ( $this->result() )
            {
                @header( 'Content-Type: text/html; charset=iso-8859-1' );
                echo $this->toJson();
            }
            else
            {
                echo 0;
            }
        }
    }

    public function fillAtributos()
    {
        $this->select()
                ->from( 'atributo' )
                ->orderby( 'atributo_nome asc' )
                ->execute();
        if ( $this->result() )
        {
            $data = $this->data;
            foreach ( $data as $k => $v )
            {
                $id = $v['atributo_id'];
                $this->select()
                        ->from( 'iattr' )
                        ->where( "iattr_atributo = $id" )
                        ->orderby( 'iattr_nome asc' )
                        ->execute();
                if ( $this->result() )
                {
                    $aux = $this->data;
                    foreach ( $aux as $j => $p )
                    {
                        if ( $this->item_id != null )
                        {
                            $iattr = $p['iattr_id'];
                            $this->select()->from( 'relatrr' )->where( "relatrr_iattr = $iattr and relatrr_item = $this->item_id" )->execute();
                            if ( $this->result() )
                            {
                                $aux[$j]['iattr_qtde'] = $this->data[0]['relatrr_qtde'];
                            }
                            else
                            {
                                $aux[$j]['iattr_qtde'] = '';
                            }
                        }
                        else
                        {
                            $aux[$j]['iattr_qtde'] = '';
                        }
                    }
                    $data[$k]['item'] = $aux;
                }
            }
            $this->data = $data;
            $this->fetch( 'attrs', $this->data );
        }
    }

    public function incluir()
    {
        if ( $this->postIsValid( array(
                    'item_title' => 'string',
                    'item_categoria' => 'string',
                    'item_sub' => 'string'
                ) ) )
        {
            $this->postIndexDrop( 'upload' );
            //remove especial characters
            $title = $this->postGetValue( 'item_title' );
            $pat = array( '/\"/', '/\'/' );
            $rep = array( '&#034;', '&#096;' );
            $title = ucfirst( preg_replace( $pat, $rep, $title ) );
            $this->postIndexAdd( 'item_url', $this->urlmodr( $this->postGetValue( 'item_title' ) ) );
            $this->postValueChange( 'item_title', $title );
            $this->postValueChange( 'item_preco', preg_replace( array( '/\./', '/\,/' ), array( '', '.' ), $this->postGetValue( 'item_preco' ) ) );
            $this->postValueChange( 'item_desconto', preg_replace( array( '/\./', '/\,/' ), array( '', '.' ), $this->postGetValue( 'item_desconto' ) ) );
            $this->insert( 'item' )->fields()->values()->execute();
            $item = mysql_insert_id();
            $this->redirect( "$this->baseUri/admin/item/editar/$item/attr/" );
        }
        else
        {
            $this->msgError = $this->response;
            $this->pageError();
        }
    }

    public function atualizar()
    {
        if ( isset( $this->uri_segment[2] ) )
        {
            if ( $this->postIsValid( array(
                        'item_title' => 'string',
                        'item_categoria' => 'string',
                        'item_sub' => 'string'
                    ) ) )
            {

                //$this->showPostData();exit;
                $this->postIndexDrop( 'upload' );
                $this->item_id = $this->uri_segment[2];
                //remove especial characters
                $title = $this->postGetValue( 'item_title' );
                $pat = array( '/\"/', '/\'/' );
                $rep = array( '&#034;', '&#096;' );
                $title = ucfirst( preg_replace( $pat, $rep, $title ) );
                $this->postIndexAdd( 'item_url', $this->urlmodr( $this->postGetValue( 'item_title' ) ) );
                $this->postValueChange( 'item_title', $title );
                $this->postValueChange( 'item_preco', preg_replace( array( '/\./', '/\,/' ), array( '', '.' ), $this->postGetValue( 'item_preco' ) ) );
                $this->postValueChange( 'item_desconto', preg_replace( array( '/\./', '/\,/' ), array( '', '.' ), $this->postGetValue( 'item_desconto' ) ) );

                $this->update( 'item' )->set()->where( "item_id = $this->item_id" )->execute();
                $this->redirect( "$this->baseUri/admin/item/editar/$this->item_id/process-ok/" );
            }
        }
    }

    public function remover()
    {
        if ( isset( $this->uri_segment[2] ) )
        {
            $this->item_id = $this->uri_segment[2];
            $this->removeFotos();
            $this->delete()->from( 'item' )->where( "item_id = $this->item_id" )->execute();
            $this->redirect( "$this->baseUri/admin/item/process-ok/" );
        }
    }

    public function removeFotos()
    {
        $this->select()
                ->from( 'foto' )
                ->where( "foto_item = $this->item_id" )
                ->execute();
        if ( $this->result() )
        {
            foreach ( $this->data as $f )
            {
                $f = ( object ) $f;
                $file = "app/fotos/$f->foto_url";
                if ( file_exists( $file ) )
                {
                    @unlink( $file );
                }
            }
        }
    }

    public function removeUniqFoto()
    {
        if ( isset( $this->uri_segment[2] ) )
        {
            $foto_id = $this->uri_segment[2];
        }
        elseif ( isset( $_POST['foto_id'] ) && !empty( $_POST['foto_id'] ) )
        {
            $foto_id = $_POST['foto_id'];
        }
        if ( isset( $foto_id ) )
        {
            $this->select()
                    ->from( 'foto' )
                    ->where( "foto_id = $foto_id" )
                    ->execute();
            if ( $this->result() )
            {
                $f = ( object ) $this->data[0];
                $file = "app/fotos/$f->foto_url";
                if ( file_exists( $file ) )
                {
                    @unlink( $file );
                    echo "$file removido";
                }
                $this->delete()->from( 'foto' )->where( "foto_id = $foto_id" )->execute();
            }
            else
            {
                echo 'error';
            }
        }
    }

    public function updateFotoPos()
    {
        $item = $_POST['item'];
        parse_str( $item, $arr );
        foreach ( $arr['li'] as $pos => $foto_id )
        {
            $this->update( 'foto' )
                    ->set( array( 'foto_pos' ), array( "$pos" ) )
                    ->where( "foto_id = $foto_id" )
                    ->execute();
        }
    }

    public function addAttr()
    {
        if ( $this->postIsValid( array( 'item_id' => 'string', 'iattr_id' => 'string', 'atributo_id' => 'string' ) ) )
        {
            $this->item_id = $this->postGetValue( 'item_id' );
            $this->iattr_id = $this->postGetValue( 'iattr_id' );
            $this->iattr_qtde = ( int ) $this->postGetValue( 'iattr_qtde' );
            $this->atributo_id = $this->postGetValue( 'atributo_id' );
            $this->relatrr_id = $this->postGetValue( 'relatrr_id' );

            $f = array( 'relatrr_item', 'relatrr_atributo', 'relatrr_iattr', 'relatrr_qtde' );
            $v = array( $this->item_id, $this->atributo_id, $this->iattr_id, $this->iattr_qtde );
            $cond = "relatrr_item = $this->item_id AND relatrr_iattr = $this->iattr_id";
            if ( $this->iattr_qtde >= 1 )
            {
                $this->select()->from( 'relatrr' )->where( "$cond" )->execute();
                if ( $this->result() )
                {
                    $this->relatrr_id = $this->data[0]['relatrr_id'];
                    $f = array( 'relatrr_qtde' );
                    $v = array( $this->iattr_qtde );
                    $this->update( 'relatrr' )->set( $f, $v )->where( "relatrr_id = $this->relatrr_id" )->execute();
                    echo 'Atualizado';
                }
                else
                {
                    $this->insert( 'relatrr' )->fields( $f )->values( $v )->execute();
                    echo 'Atualizado';
                }
            }
            else
            {
                $this->select()->from( 'relatrr' )->where( "$cond" )->execute();
                if ( $this->result() )
                {
                    $this->relatrr_id = $this->data[0]['relatrr_id'];
                    $this->delete()->from( 'relatrr' )->where( "relatrr_id = $this->relatrr_id" )->execute();
                }
                echo 'Atualizado';
            }
        }
        else
        {
            echo 'nope';
        }
    }

    public function pageError()
    {
        $this->tpl( 'admin/error.html' );
        $this->assign( 'msgError', $this->msgError );
        $this->render();
    }
}
/*end file*/
