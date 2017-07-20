<?php

class Subcategoria extends PHPFrodo
{

    public $user_login;
    public $user_id;
    public $user_name;
    public $user_level;
    public $msgError;
    public $categoria_id;
    public $categoria_title;
    public $sub_id;
    public $sub_title;
    public $item_id;
    public $item_title;

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
        if ( isset( $this->uri_segment ) && in_array('process-ok',$this->uri_segment) )
        {
            $this->assign( 'msgOnload', 'notify("<h1>Procedimento realizado com sucesso</h1>")' );
        }
        if ( $this->user_level == 1 ) {
            $this->assign('showhide','hide');
        }         
    }

    public function welcome()
    {
        $this->pagebase = "$this->baseUri/admin/subcategoria";
        $this->tpl( 'admin/subcategoria.html' );
        $this->select()
                ->from( 'sub' )
                ->join( 'categoria', 'sub_categoria = categoria_id', 'INNER' )
                ->paginate( 15 )
                ->orderby( 'sub_title asc' )
                ->execute();
        if ( $this->result() )
        {
            $catData = $this->data;
            foreach ( $catData as $idx => $cat )
            {
                $catData[$idx]['sub_item'] = 0;
                $c = ( object ) $cat;
                $sub_id = $c->sub_id;
                $this->select()
                        ->from( 'item' )
                        ->where( "item_sub = $sub_id" )
                        ->execute();
                if ( $this->result() )
                {
                    $catData[$idx]['sub_item'] = count( $this->data );
                }
            }
            $this->data = $catData;
            $this->fetch( 'rs', $this->data );
            $this->assign( 'sub_qtde', count( $catData ) );
        }
        $this->fillCategoria();
        $this->render();
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

    public function incluir()
    {
        if ( $this->postIsValid( array( 'sub_title' => 'string' ) ) )
        {
            $this->postIndexAdd( 'sub_url', $this->urlmodr( $this->postGetValue( 'sub_title' ) ) );
            $this->postValueChange( 'sub_title', ucfirst( $this->postGetValue( 'sub_title' ) ) );
            $this->insert( 'sub' )->fields()->values()->execute();
            $this->redirect( "$this->baseUri/admin/subcategoria/process-ok/" );
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
            if ( $this->postIsValid( array( 'sub_title' => 'string' ) ) )
            {
                $this->sub_id = $this->uri_segment[2];
                $this->postIndexAdd( 'sub_url', $this->urlmodr( $this->postGetValue( 'sub_title' ) ) );
                $this->postValueChange( 'sub_title', ucfirst( $this->postGetValue( 'sub_title' ) ) );
                $this->update( 'sub' )->set()->where( "sub_id = $this->sub_id" )->execute();
                $this->redirect( "$this->baseUri/admin/subcategoria/process-ok/" );
            }
        }
    }

    public function remover()
    {
        if ( isset( $this->uri_segment[2] ) )
        {
            $this->sub_id = $this->uri_segment[2];
            if ( $this->removeItem() )
            {
                $this->delete()->from( 'sub' )->where( "sub_id = $this->sub_id" )->execute();
                $this->redirect( "$this->baseUri/admin/subcategoria/process-ok/" );
            }
        }
    }

    public function removeItem()
    {
        $this->select()
                ->from( 'sub' )
                ->join( 'item', 'item_sub = sub_id', 'INNER' )
                ->where( "sub_id = $this->sub_id" )
                ->execute();
        if ( $this->result() )
        {
            $itemData = $this->data;
            foreach ( $itemData as $item )
            {
                $item = ( object ) $item;
                $this->item_id = $item->item_id;
                $this->removeFotos();
            }
        }
        return true;
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

    public function pageError()
    {
        $this->tpl( 'admin/error.html' );
        $this->assign( 'msgError', $this->msgError );
        $this->render();
    }

}

/*end file*/