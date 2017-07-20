<?php

class Thumb extends PHPFrodo
{

    public function welcome()
    {
        if ( isset( $this->uri_segment[1] ) )
        {
            $pic = "app/fotos/" . $this->uri_segment[1] . ".jpg";
            if ( !file_exists( $pic ) )
            {
                $pic = "app/fotos/" . $this->uri_segment[1] . ".png";
                if ( !file_exists( $pic ) )
                {
                    $pic = "app/images/default/nopic.jpg";
                }
            }
            $this->helper( 'canvas' );
            $t = new Canvas;
            $t->carrega( $pic );
            $image_x = $this->uri_segment[2];
            $image_y = $this->uri_segment[3];
            if ( isset( $this->uri_segment[4] ) && $this->uri_segment[4] == 'ratio' )
            {
                $t->redimensiona( $image_x, $image_y, 'crop' );
            }
            else
            {
                $t->redimensiona( $image_x, $image_y );
            }
            $t->grava( null, 85 );
        }
    }

    public function slide()
    {
        if ( isset( $this->uri_segment[2] ) )
        {
            $pic = "app/fotos/slide/" . $this->uri_segment[2] . ".jpg";
            if ( !file_exists( $pic ) )
            {
                $pic = "app/fotos/slide/" . $this->uri_segment[2] . ".png";
                if ( !file_exists( $pic ) )
                {
                    $pic = "app/images/default/nopic.jpg";
                }
            }
            $this->helper( 'canvas' );
            $t = new Canvas;
            $t->carrega( $pic );
            if ( isset( $this->uri_segment[3] ) )
            {
                $image_x = $this->uri_segment[3];
                $image_y = $this->uri_segment[4];
                if ( isset( $this->uri_segment[5] ) && $this->uri_segment[5] == 'crop' )
                {
                    $t->redimensiona( $image_x, $image_y, 'crop' );
                }
                else
                {
                    $t->redimensiona( $image_x, $image_y );
                }
            }
            $t->grava( null, 85 );
            //$t->grava();
        }
    }

    public function slider()
    {
        $pic = "app/fotos/slide/" . $this->uri_segment[2] . ".jpg";
        if ( !file_exists( $pic ) )
        {
            $pic = "app/fotos/slide/" . $this->uri_segment[2] . ".png";
            if ( !file_exists( $pic ) )
            {
                $pic = "app/images/default/nopic.jpg";
            }
        }
        $handle = new upload( $pic );
        $handle->image_resize = true;
        if ( isset( $this->uri_segment[5] ) && $this->uri_segment[5] == 'crop' )
        {
            $handle->image_x = $this->uri_segment[3];
            $handle->image_y = $this->uri_segment[4];
            $handle->image_ratio_crop = true;
        }
        elseif ( isset( $this->uri_segment[5] ) && $this->uri_segment[5] == 'ratio' )
        {
            $handle->image_x = $this->uri_segment[3];
            $handle->image_y = $this->uri_segment[4];
            $handle->image_ratio_y = true;
            //$handle->image_ratio_x = true;
        }
        else
        {
            $handle->image_x = $this->uri_segment[3];
            $handle->image_y = $this->uri_segment[4];
        }
        $handle->png_compression = 9;
        $handle->jpeg_quality = 85;
        @header( 'Content-type: ' . $handle->file_src_mime );
        echo $handle->Process();
        die();
    }

    public function thumbr()
    {
        $pic = "app/fotos/" . $this->uri_segment[2] . ".jpg";
        if ( !file_exists( $pic ) )
        {
            $pic = "app/fotos/" . $this->uri_segment[2] . ".png";
            if ( !file_exists( $pic ) )
            {
                $pic = "app/images/default/nopic.jpg";
            }
        }
        $handle = new upload( $pic );
        $handle->image_resize = true;
        if ( isset( $this->uri_segment[5] ) && $this->uri_segment[5] == 'crop' )
        {
            $handle->image_x = $this->uri_segment[3];
            $handle->image_y = $this->uri_segment[4];
            $handle->image_ratio_crop = true;
        }
        elseif ( isset( $this->uri_segment[5] ) && $this->uri_segment[5] == 'ratio' )
        {
            $handle->image_x = $this->uri_segment[3];
            $handle->image_y = $this->uri_segment[4];
            //$handle->image_ratio_y = true;
            $handle->image_ratio_x = true;
        }
        else
        {
            $handle->image_x = $this->uri_segment[3];
            $handle->image_y = $this->uri_segment[4];
        }
        $handle->jpeg_quality = 85;
        @header( 'Content-type: ' . $handle->file_src_mime );
        echo $handle->Process();
        die();
    }

    public function t()
    {
        $pic = "app/fotos/" . $this->uri_segment[2] . ".jpg";
        if ( !file_exists( $pic ) )
        {
            $pic = "app/fotos/" . $this->uri_segment[2] . ".png";
            if ( !file_exists( $pic ) )
            {
                $pic = "app/images/default/nopic.jpg";
            }
        }
        if ( empty( $this->uri_segment[2] ) )
        {
            $pic = "app/images/default/nopic.jpg";
        }
        $handle = new upload( $pic );
        $handle->image_resize = true;
        if ( isset( $this->uri_segment[5] ) && $this->uri_segment[5] == 'crop' )
        {
            $handle->image_x = $this->uri_segment[3];
            $handle->image_y = $this->uri_segment[4];
            $handle->image_ratio_crop = true;
        }
        elseif ( isset( $this->uri_segment[5] ) && $this->uri_segment[5] == 'ratio' )
        {
            $handle->image_x = $this->uri_segment[3];
            $handle->image_y = $this->uri_segment[4];
            //$handle->image_ratio_x = true;
            $handle->image_ratio_y = true;
        }
        else
        {
            $handle->image_x = $this->uri_segment[3];
            $handle->image_y = $this->uri_segment[4];
        }
        $handle->jpeg_quality = 90;
        @header( 'Content-type: ' . $handle->file_src_mime );
        echo $handle->Process();
        die();
    }
}
/*end file*/