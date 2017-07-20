<?php

class Cupom extends PHPFrodo
{
    public $loteSize = 50;
    public $allCupons = array( );

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
        $this->tpl( 'admin/cupom.html' );
        $this->pagebase = "$this->baseUri/admin/cupom";
        $this->select()
                ->from( 'cupom' )
                ->join( 'lote', 'cupom_lote = lote_id', 'INNER' )
                ->groupby( 'lote_id' )
                ->orderby( 'lote_id desc' )
                //->paginate( 18 )
                ->execute();
        if ( $this->result() )
        {
            $data = $this->data;
            $usados = 0;
            foreach ( $data as $k => $v )
            {
                $data[$k]['cupom_validade'] = date( 'd/m/Y', strtotime( $data[$k]['cupom_validade'] ) );
                if ( $data[$k]['cupom_status'] == 1 )
                    $usados++;
            }
            $this->data = $data;
            $this->addkey( 'cupom_usados', count( $usados ) );
            $this->preg( array( '/0/', '/1/' ), array( 'Disponível', 'Utilizado' ), 'cupom_status' );
            $this->preg( array( '/1/', '/2/' ), array( 'Produtos', 'Frete' ), 'cupom_tipo' );
            $this->preg( array( '/1/', '/2/' ), array( 'Uso único', 'Sem Limites <small>(até validade)</small>' ), 'cupom_limite' );
            $this->fetch( 'c', $this->data );
            //$this->printr( $this->data);exit;
        }
        $this->render();
    }

    public function lista()
    {
        $this->tpl( 'admin/cupom_lista.html' );
        $this->pagebase = "$this->baseUri/admin/cupom/lista";
        $this->lote_id = $this->uri_segment[2];
        $this->select()
                ->from( 'cupom' )
                ->join( 'lote', 'cupom_lote = lote_id', 'INNER' )
                ->where( "lote_id = $this->lote_id" )
                ->orderby( 'cupom_status desc, cupom_validade desc' )
                ->paginate( 500 )
                ->execute();
        if ( $this->result() )
        {
            $data = $this->data;
            $usados = 0;
            foreach ( $data as $k => $v )
            {
                $data[$k]['cupom_validade'] = date( 'd/m/Y', strtotime( $data[$k]['cupom_validade'] ) );
                if ( $data[$k]['cupom_status'] == 1 )
                    $usados++;
            }
            $this->data = $data;
            $this->addkey( 'cupom_usados', count( $usados ) );
            $this->preg( array( '/0/', '/1/' ), array( 'Disponível', 'Utilizado' ), 'cupom_status' );
            $this->preg( array( '/1/', '/2/' ), array( 'Produtos', 'Frete' ), 'cupom_tipo' );
            $this->preg( array( '/1/', '/2/' ), array( 'Uso único', 'Sem Limites <small>(até validade)</small>' ), 'cupom_limite' );
            $this->fetch( 'c', $this->data );
            $this->assignAll();
        }
        $this->render();
    }

    public function busca()
    {
        $this->tpl( 'admin/cupom_lista.html' );
        $this->pagebase = "$this->baseUri/admin/cupom/lista";
        if ( isset( $_POST['cupom_alfa'] ) && !empty( $_POST['cupom_alfa'] ) )
        {
            $this->cupom_alfa = trim( $_POST['cupom_alfa'] );
            $this->select()
                    ->from( 'cupom' )
                    ->join( 'lote', 'cupom_lote = lote_id', 'INNER' )
                    ->where( "cupom_alfa = '$this->cupom_alfa'" )
                    ->orderby( 'cupom_status desc, cupom_validade desc' )
                    //->paginate( 500 )
                    ->execute();
            if ( $this->result() )
            {
                $data = $this->data;
                $usados = 0;
                foreach ( $data as $k => $v )
                {
                    $data[$k]['cupom_validade'] = date( 'd/m/Y', strtotime( $data[$k]['cupom_validade'] ) );
                    if ( $data[$k]['cupom_status'] == 1 )
                        $usados++;
                }
                $this->data = $data;
                $this->addkey( 'cupom_usados', count( $usados ) );
                $this->preg( array( '/0/', '/1/' ), array( 'Disponível', 'Utilizado' ), 'cupom_status' );
                $this->preg( array( '/1/', '/2/' ), array( 'Produtos', 'Frete' ), 'cupom_tipo' );
                $this->preg( array( '/1/', '/2/' ), array( 'Uso único', 'Sem Limites <small>(até validade)</small>' ), 'cupom_limite' );
                $this->fetch( 'c', $this->data );
                $this->assignAll();
            }
        }
        $this->render();
    }

    public function clear()
    {
        $fields = array( 'cupom_cliente', 'cupom_status', 'cupom_update', 'cupom_num', 'cupom_lote' );
        $values = array( "0", "0", "0", "0", "1" );
        $this->update( 'cupom' )
                ->set( $fields, $values )
                ->execute();
    }

    public function gerar()
    {
        if ( $this->postIsValid( array(
                    'lote_nome' => 'string',
                    'lote_size' => 'string',
                    'cupom_desconto' => 'string'
                ) ) )
        {
            $this->lote_nome = $this->postGetValue( 'lote_nome' );
            $this->lote_size = $this->postGetValue( 'lote_size' );
            $this->cupom_limite = $this->postGetValue( 'cupom_limite' );
            $this->cupom_desconto = $this->postGetValue( 'cupom_desconto' );
            $this->postIndexDate( 'cupom_validade' );
            $this->cupom_validade = $this->postGetValue( 'cupom_validade' );

            $this->insert( 'lote' )->fields( array( 'lote_nome', 'lote_size' ) )->values( array( $this->lote_nome, $this->lote_size ) )->execute();
            $this->lote_id = mysql_insert_id();

            $cupons = $this->generate();
            $this->query = "INSERT INTO cupom (cupom_alfa,cupom_lote, cupom_desconto,cupom_validade,cupom_limite) VALUES ";
            foreach ( $cupons as $alfa )
            {
                $this->query .= " ('$alfa',$this->lote_id,$this->cupom_desconto,'$this->cupom_validade','$this->cupom_limite'), ";
            }
            $this->query = substr( $this->query, 0, -2 );
            $this->query .= ";";
            $this->execute();
            $this->redirect( "$this->baseUri/admin/cupom/" );
            //$this->welcome();
        }
    }

    public function removeTudo()
    {
        //$this->delete()->from( 'lote' )->execute();
        //$this->delete()->from( 'cupom' )->execute();
    }

    public function removerLote()
    {
        if ( isset( $this->uri_segment[2] ) )
        {
            $this->lote_id = $this->uri_segment[2];
            $this->delete()->from( 'lote' )->where( "lote_id = $this->lote_id" )->execute();
            $this->redirect( "$this->baseUri/admin/cupom/process-ok/" );
        }
        else
        {
            $this->redirect( "$this->baseUri/admin/cupom/" );
        }
    }

    public function removerCupom()
    {
        if ( isset( $this->uri_segment[2] ) )
        {
            $this->cupom_id = $this->uri_segment[2];
            $this->lote_id = $this->uri_segment[3];
            $this->delete()->from( 'cupom' )->where( "cupom_id = $this->cupom_id" )->execute();
            $this->decrement( 'lote', 'lote_size', 1, "lote_id = $this->lote_id" )->execute();
            $this->redirect( "$this->baseUri/admin/cupom/lista/$this->lote_id/process-ok" );
        }
        else
        {
            $this->redirect( "$this->baseUri/admin/cupom/" );
        }
    }

    public function download()
    {
        if ( isset( $this->uri_segment[2] ) )
        {
            $lote = $this->uri_segment[2];
            $this->select()
                    ->from( 'cupom' )
                    ->where( "cupom_lote = $lote" )
                    ->execute();
            if ( $this->result() )
            {
                $cupom_list = "";
                foreach ( $this->data as $alfa )
                {
                    $alfa = $alfa['cupom_alfa'];
                    $cupom_list .= "$alfa\n";
                }
                @header( "Pragma: public" );
                @header( "Expires: 0" );
                @header( "Cache-Control: must-revalidate, post-result=0, pre-result=0" );
                @header( "Cache-Control: private", false );
                @header( "Content-Type: application/octet-stream" );
                @header( "Content-Disposition: attachment; filename=\"cupom_lote_$lote.csv\";" );
                @header( "Content-Transfer-Encoding: binary" );
                echo $cupom_list;
            }
        }
    }

    public function generate()
    {
        $all = array( );

        function createRandomString( $string_length, $character_set )
        {
            $random_string = array( );
            for ( $i = 1; $i <= $string_length; $i++ )
            {
                $rand_character = $character_set[rand( 0, strlen( $character_set ) - 1 )];
                $random_string[] = $rand_character;
            }
            shuffle( $random_string );
            return implode( '', $random_string );
        }

        function validUniqueString( $string_collection, $new_string, $existing_strings='' )
        {
            if ( !strlen( $string_collection ) && !strlen( $existing_strings ) )
                return true;
            $combined_strings = $string_collection . ", " . $existing_strings;
            return (strlen( strpos( $combined_strings, $new_string ) )) ? false : true;
        }

        function createRandomStringCollection( $string_length, $number_of_strings, $character_set, $existing_strings = '' )
        {
            $string_collection = '';
            for ( $i = 1; $i <= $number_of_strings; $i++ )
            {
                $random_string = createRandomString( $string_length, $character_set );
                while ( !validUniqueString( $string_collection, $random_string, $existing_strings ) )
                {
                    $random_string = createRandomString( $string_length, $character_set );
                }
                $all[] = $random_string;
                $string_collection .= (!strlen( $string_collection )) ? $random_string : "\n " . $random_string;
            }
            //return $string_collection;
            return $all;
        }
        $character_set = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $existing_strings = "ABC";
        $string_length = 13;
        $number_of_strings = $this->lote_size;
        $this->allCupons = createRandomStringCollection( $string_length, $number_of_strings, $character_set, $existing_strings );
        return $this->allCupons;
    }
}
/*end file*/