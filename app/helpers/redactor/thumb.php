<?php
	require_once( 'canvas.php' );
	$pic = "../../userfiles/".$_GET['img'];
	$t = new Canvas;
	$t->carrega( $pic );
	$t->redimensiona( 60, 60, 'crop' );
	$t->grava();
/*end file*/