<?php

class Uploadr extends PHPFrodo
{

    public function __construct()
    {
        parent:: __construct();
    }

    public function welcome()
    {
        $file_dst_name = "";
        $item_id = $this->uri_segment[1];
        $dir_dest = 'app/fotos/';
        $file = $_FILES['Filedata'];
        $fileObj = array( );
	$handle = new Upload( $file );
	if ( $handle->uploaded )
	{
	$handle->file_overwrite = true;
	$handle->image_convert = 'jpg';
	if ( $handle->image_src_x > 1300 || $handle->image_y > 1100 )
	{
	    $handle->image_resize = true;
	    $handle->image_ratio_crop = true;
	    $handle->image_x = 1000;
	    $handle->image_y = 900;
	}
	$handle->file_new_name_body = md5( uniqid( $file['name'] ) );
	$handle->Process( $dir_dest );
	if ( $handle->processed )
	{
	    $file_dst_name = $handle->file_dst_name;
	    $this->insert( 'foto' )
		    ->fields( array( 'foto_item', 'foto_url' ) )
		    ->values( array( "$item_id", "$file_dst_name" ) )
		    ->execute();
	    $last_id = mysql_insert_id();
	    echo json_encode( array( 'url' => preg_replace( array('/\.jpg/','/\.png/'), array('',''), $file_dst_name ), 'id' => $last_id, 'time' => time() ) );
	}
	else
	{
	    echo json_encode( array( 'url' => "error", 'id' => '', 'time' => time() ) );
	}
	}
    }

}
