<?php

class Slideshow extends PHPFrodo
{
    public $slide_id;
    public $slide_title;
    public $slide_url;
    public $slide_desc;
    public $slide_link;
    public $user_name;
    public $user_id;
    public $user_login;
    public $user_level;

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
        $this->user_login = @$sid->getNode( 'user_login' );
        $this->user_id = @$sid->getNode( 'user_id' );
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
        if ( $this->user_name == "" )
        {
            $this->redirect( "$this->baseUri/admin/login/logout/" );
        }
        if ( isset( $this->uri_segment ) && in_array( 'process-ok', $this->uri_segment ) )
        {
            $this->assign( 'msgOnload', 'notify("<h1>Procedimento realizado com sucesso</h1>")' );
        }
        if ( isset( $this->uri_segment ) && in_array( 'no-file', $this->uri_segment ) )
        {
            $this->assign( 'msgOnload', 'notify("<h1>Nenhuma imagem foi enviada!</h1>")' );
        }
        if ( $this->user_level == 1 )
        {
            $this->assign( 'showhide', 'hide' );
        }
    }

    public function welcome()
    {
        $this->tpl( 'admin/slide.html' );
        $this->select()->from( 'slide' )->orderby( 'slide_local asc, slide_id desc' )->execute();
        if ( $this->result() )
        {
            $this->preg( '/NULL/', '&nbsp;', 'slide_title' );
            $this->preg( '/NULL/', '&nbsp;', 'slide_desc' );
            $this->preg( array('/\.jpg/','/\.png/'), array('',''),  'slide_url' );
            $this->preg( array( '/1/', '/2/','/3/' ), array( 'Slide Topo', 'Banner Produtos','Banner Lateral' ), 'slide_local' );
            $this->preg( '/[0]/', '', 'slide_link' );
            $this->fetch( 'rs', $this->data );
        }
        $this->assign( 'page_qtde', count( $this->data ) );
        $this->render();
    }

    public function editar()
    {
        $this->tpl( 'admin/slide_editar.html' );
        if ( isset( $this->uri_segment[2] ) )
        {
            $this->slide_id = $this->uri_segment[2];
            $this->select()->from( 'slide' )->where( "slide_id = $this->slide_id" )->execute();
            if ( $this->result() )
            {
                $this->preg( '/NULL/', '&nbsp;', 'slide_title' );
                $this->preg( '/NULL/', '&nbsp;', 'slide_desc' );
                $this->addkey( 'slide_thumb', '', 'slide_url' );
                $this->preg( array('/\.jpg/','/\.png/'), array('',''),  'slide_thumb' );
                $this->assignAll();
            }
            $this->render();
        }
        else
        {
            $this->redirect( "$this->baseUri/admin/slideshow/" );
        }
    }

    public function novo()
    {
        $this->tpl( 'admin/slide_novo.html' );
        $this->render();
    }

    public function incluir()
    {
        if ( isset( $_FILES['filedata'] ) && strlen( $_FILES['filedata']['name'] ) >= 1 )
        {
            $this->uploads();
            $this->slide_title = $_POST['slide_title'];
            $this->slide_desc = $_POST['slide_desc'];
            $this->slide_link = trim(ltrim(rtrim($_POST['slide_link'])));
            $this->slide_local = $_POST['slide_local'];
            if ( $this->slide_link == "" || strlen( $this->slide_link ) <= 2 )
            {
                //$this->slide_link = "$this->baseUri";
                $this->slide_link = "javascript:void(0)";
                $this->slide_link = "0";
            }
            $f = array( 'slide_title', 'slide_desc', 'slide_url', 'slide_link', 'slide_local' );
            $v = array( "$this->slide_title", "$this->slide_desc", "$this->slide_url", "$this->slide_link", "$this->slide_local" );
            $this->insert( 'slide' )->fields( $f )->values( $v )->execute();
            $this->redirect( "$this->baseUri/admin/slideshow/process-ok/" );
        }
        else
        {
            $this->redirect( "$this->baseUri/admin/slideshow/no-file/" );
        }
    }

    public function atualizar()
    {
        $this->slide_id = $this->uri_segment[2];
        $this->slide_title = $_POST['slide_title'];
        $this->slide_desc = $_POST['slide_desc'];
        $this->slide_link = trim(ltrim(rtrim($_POST['slide_link'])));
        $this->slide_local = $_POST['slide_local'];
        if ( $this->slide_link == "" || strlen( $this->slide_link ) <= 2 )
        {
            $this->slide_link = "$this->baseUri";
            $this->slide_link = "javascript:void(0)";
            $this->slide_link = 0;
        }
        if ( isset( $_FILES['filedata'] ) && strlen( $_FILES['filedata']['name'] ) >= 1 )
        {
            if ( $this->uploads() )
            {
                $this->removeAtual();
                $f = array( 'slide_title', 'slide_desc', 'slide_url', 'slide_link', 'slide_local' );
                $v = array( "$this->slide_title", "$this->slide_desc", "$this->slide_url", "$this->slide_link", "$this->slide_local" );
            }
        }
        else
        {
            $f = array( 'slide_title', 'slide_desc', 'slide_link', 'slide_local' );
            $v = array( "$this->slide_title", "$this->slide_desc", "$this->slide_link", "$this->slide_local" );
        }
        $this->update( 'slide' )->set( $f, $v )->where( "slide_id = $this->slide_id" )->execute();
        $this->redirect( "$this->baseUri/admin/slideshow/process-ok/" );
    }

    public function remover()
    {
        $this->slide_id = $this->uri_segment[2];
        $this->removeAtual();
        $this->delete()->from( 'slide' )->where( "slide_id = $this->slide_id" )->execute();
        $this->redirect( "$this->baseUri/admin/slideshow/process-ok/" );
    }

    public function removeAtual()
    {
        $this->select()->from( 'slide' )->where( "slide_id = $this->slide_id" )->execute();
        if ( $this->result() )
        {
            $this->slide_url_current = "app/fotos/slide/" . $this->data[0]['slide_url'];
            if ( file_exists( $this->slide_url_current ) )
            {
                @unlink( $this->slide_url_current );
            }
        }
    }

    public function uploads()
    {
        $file_dst_name = "";
        $dir_dest = 'app/fotos/slide';
        $files = array( );

        if ( isset( $_FILES['filedata'] ) && strlen( $_FILES['filedata']['name'] ) >= 1 )
        {
           	$file = $_FILES['filedata'];
		$handle = new Upload( $file );
		if ( $handle->uploaded )
		{
		    $handle->file_overwrite = true;
		    $handle->image_convert = 'png';
		    $handle->png_compression = 9;
		    if ( $handle->image_src_x > 1200 || $handle->image_y > 700 )
		    {
			$handle->image_resize = true;
			//$handle->image_ratio_crop = true;
			$handle->image_x = 900;
			$handle->image_y = 350;
		    }
		    $handle->file_new_name_body = md5( uniqid( $file['name'] ) );
		    $handle->Process( $dir_dest );
		    if ( $handle->processed )
		    {
			$this->slide_url = $handle->file_dst_name;
			return true;
		    }
		    else
		    {
			return false;
			//echo $handle->Error;
		    }
		}
            
        }
        else
        {
            return false;
        }
    }
}
