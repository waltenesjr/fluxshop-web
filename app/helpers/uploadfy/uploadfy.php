<?php
class UploadFy extends PHPFrodo
{

    public $file_name;
    public $file_dst_name;

    public function UploadFy()
    {
        parent::__construct();
        $this->assign( 'baseUri', "$this->baseUri" );
    }

    public function add()
    {
        if( $this->upload() )
        {
            $this->insert( 'fotos' )
                 ->fields( array( 'foto_item', 'foto_url' ) )
                 ->values( array( 1, "$this->file_dst_name" ) )
                 ->execute();
        }
    }

    public function upload()
    {
        $dir_dest = UPLOADDIR;
        $files = array( );
        $files = $_FILES['Filedata'];
        foreach( $files as $file )
        {
            $handle = new Upload( $file );
            if( $handle->uploaded )
            {
                $handle->file_overwrite = true;
                $handle->image_convert = 'jpg';
                /*
                  if( $handle->image_src_x > 800 && $handle->image_y > 600)
                  {
                  $handle->image_resize = true;
                  $handle->image_ratio_crop = true;
                  $handle->image_x = 800;
                  $handle->image_y = 600;
                  }
                 */
                $this->file_name = md5( uniqid( $file['name'] ) );
                $handle->file_new_name_body = $this->file_name;
                $handle->Process( $dir_dest );
                if( $handle->processed )
                {
                    $this->file_dst_name = $handle->file_dst_name;
                    echo true;
                    return true;
                }
                else
                {
                    echo false;
                    return false;
                }
            }
        }
    }
}
?>