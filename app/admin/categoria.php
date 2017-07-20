<?php

class Categoria extends PHPFrodo
{

    public $user_login;
    public $user_id;
    public $user_name;
    public $user_level;
    public $msgError;
    public $categoria_id;
    public $sub_id;
    public $sub_title;
    public $item_id;
    public $item_title;
    public $categoria_title;

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
        $this->pagebase = "$this->baseUri/admin/categoria";
        $this->tpl( 'admin/categoria.html' );
        $this->select()
                ->from( 'categoria' )
                ->paginate( 15 )
                ->orderby( 'categoria_title asc' )
                ->execute();
        if ( $this->result() )
        {
            $catData = $this->data;
            foreach ( $catData as $idx => $cat )
            {
                $catData[$idx]['categoria_item'] = 0;
                $catData[$idx]['categoria_sub'] = 0;
                $c = ( object ) $cat;
                $this->select()
                        ->from( 'sub' )
                        ->where( "sub_categoria = $c->categoria_id" )
                        ->execute();
                if ( $this->result() )
                {
                    $catData[$idx]['categoria_sub'] = count( $this->data );
                    $sub_id = $this->data[0]['sub_id'];
                    $this->select()
                            ->from( 'item' )
                            ->where( "item_sub = $sub_id" )
                            ->execute();
                    if ( $this->result() )
                    {
                        $catData[$idx]['categoria_item'] = count( $this->data );
                    }
                }
            }
            $this->data = $catData;
            $this->fetch( 'rs', $this->data );
            $this->assign( 'categoria_qtde', count( $catData ) );
        }
        $this->render();
    }

    public function incluir()
    {
        if ( $this->postIsValid( array( 'categoria_title' => 'string' ) ) )
        {
            $this->postIndexAdd( 'categoria_url', $this->urlmodr( $this->postGetValue( 'categoria_title' ) ) );
            $this->postValueChange( 'categoria_title', ucfirst( $this->postGetValue( 'categoria_title' ) ) );
            $this->insert( 'categoria' )->fields()->values()->execute();
            $this->redirect( "$this->baseUri/admin/categoria/process-ok/" );
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
            if ( $this->postIsValid( array( 'categoria_title' => 'string' ) ) )
            {
                $this->categoria_id = $this->uri_segment[2];
                $this->postIndexAdd( 'categoria_url', $this->urlmodr( $this->postGetValue( 'categoria_title' ) ) );
                $this->postValueChange( 'categoria_title', ucfirst( $this->postGetValue( 'categoria_title' ) ) );
                $this->update( 'categoria' )->set()->where( "categoria_id = $this->categoria_id" )->execute();
                $this->redirect( "$this->baseUri/admin/categoria/process-ok/" );
            }
        }
    }

    public function remover()
    {
        if ( isset( $this->uri_segment[2] ) )
        {
            $this->categoria_id = $this->uri_segment[2];
            if ( $this->removeSub() )
            {
                $this->delete()->from( 'categoria' )->where( "categoria_id = $this->categoria_id" )->execute();
                $this->redirect( "$this->baseUri/admin/categoria/process-ok/" );
            }
        }
    }

    public function removeSub()
    {
        $this->select()
                ->from( 'sub' )
                ->where( "sub_categoria = $this->categoria_id" )
                ->execute();
        if ( $this->result() )
        {
            $subData = $this->data;
            foreach ( $subData as $sub )
            {
                $sub = ( object ) $sub;
                $this->sub_id = $sub->sub_id;
                $this->select()
                        ->from( 'item' )
                        ->where( "item_sub = $this->sub_id" )
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