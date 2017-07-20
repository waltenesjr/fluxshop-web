<?php

/**
 * PHPFrodo class
 *
 * @author Rafael Clares <rafadinix@gmail.com>
 * @version 1.0 <10/2012>
 * web: www.clares.wordpress.com
 *       ,
 *      ((_,-.
 *        '-.\_)'-,
 *          )  _ )'-   PHPFrodo
 * ,.;.,;,,(/(/ \));,;.,.,
 *
 * Simplifica o acesso ao banco de dados utilizando o PDO
 *
 */
error_reporting( E_ALL );

class PHPFrodo
{
    public $objBanco = null;
    public $dbname = null;
    public $driver = null;
    public $adapter = null;
    public $data = null;
    public $tabela = null;
    public $campo = "";
    public $query = null;
    public $valor = null;
    public $post_fields = array( );
    public $post_values = array( );
    public $strupdate = null;
    public $strorderby = null;
    public $view = null;
    public $stmt = null;
    public $numrows = null;
    public $affected_rows = null;
    public $pagelinks = null;
    public $method = null;
    public $limit = "";
    public $offset = "";
    public $limitOffset = null;
    public $paginateNum = null;
    public $pagebase = "";
    public $response = null;
    public $uri_segment = array( );
    public $baseUri = "";
    public $referer = "";
    public $buffer = "";
    public $jsonData = "";
    public $paginateStyle = null;
    public $mailSubject = "";
    public $mailMsg = "";
    public $mailAddress = "";

    public function __construct()
    {
        @setlocale( LC_ALL, 'pt_BR', 'ptb' );
        $this->loadUri();
        $this->database();
        $this->assign( 'baseUri', "$this->baseUri" );
        @header( "Cache-Control: no-cache, must-revalidate" );
        @header( 'Content-Type: text/html; charset=iso-8859-1' );
        return $this;
    }

    public function __destruct()
    {//               
    }

    public function __clone()
    {//
    }

    /**
     *  O método deve ser chamado quando não desejar utilizar o PDO
     *  @param String $adapter nome do adapter
     *  @example $obj->adapter('mysql');
     *  @example $obj->adapter('pgsql');
     */
    public function adapter( $adapter = null )
    {
        try
        {
            if ( $adapter == null )
            {
                throw new Exception( 'adapter: O adapter deve ser informado como parâmetro do método.' );
            }
            else
            {
                $this->adapter = $adapter;
                $this->driver = DATABASEDIR . $this->adapter . ".php";
                if ( !file_exists( $this->driver ) )
                {
                    $this->adapter = null;
                    throw new Exception( "adapter: O arquivo $this->driver não existe." );
                }
                else
                {
                    require_once $this->driver;
                }
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    /**
     *  Método utilizado para conexão com o banco
     *  @name database
     *  @param String $sgbd  - índice do array {database/database.conf.php} que contém as informações da conexão.
     *  @example $obj->database('blog');
     */
    public function database( $dbname = null )
    {
        try
        {
            if ( $dbname == null || $dbname == '' || !isset( $dbname ) )
            {
                $this->dbname = 'default';
            }
            else
            {
                $this->dbname = $dbname;
            }

            if ( file_exists( DATABASEDIR . 'database.conf.php' ) )
            {
                include DATABASEDIR . 'database.conf.php';
            }
            else
            {
                throw new Exception( "database: Arquivo de configuração do banco inexistente!" );
            }


            if ( $this->adapter == null )
            {
                if ( !isset( $databases[$this->dbname] ) )
                {
                    throw new Exception( "database: banco [$this->dbname] não configurado em " . DATABASEDIR . "database.conf.php" );
                }

                if ( $databases[$this->dbname]['driver'] != '' && $databases[$this->dbname]['driver'] != 'pdo' )
                {
                    $this->adapter( $databases[$this->dbname]['driver'] );
                    $this->objBanco = new $this->adapter( $databases[$this->dbname] );
                }
            }
            else
            {
                //conexao adapters
                $this->objBanco = new $this->adapter( $databases[$this->dbname] );
            }
	    $this->limite_produto = $databases[$this->dbname]['limite_produto'];
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    /**
     *  Método utilizado para realizar a selação
     *  Se o argumento for omitido o campo assumido será "*"
     *  @name database
     *  @param String
     *  @example $obj->select();
     *  @example $obj->select('*');
     *  @example $obj->select('user_id','user_name');
     *  @example $obj->select('user_id as id','user_name as name');
     */
    public function select( $campo = null )
    {
        if ( $campo != null )
        {
            $this->campo = $campo;
        }
        else
        {
            $this->campo = "*";
        }
        $this->method = "SELECT";
        $this->data = null;
        $this->query = "SELECT $this->campo FROM ";
        return $this;
    }

    /**
     * Utilizado após o método select para realizar join's
     *
     * @name join
     * @param String $table Nome da tabela
     * @param String $condition Condição do JOIN
     * @param String $method  INNER, LEFT...
     * @example $obj->join("t1","t1.id = t2.id","INNER");
     */
    public function join( $table = '', $condition = '', $method = '' )
    {
        try
        {
            if ( $table == '' || $condition == '' )
            {
                throw new Exception( "join: tabela e condição devem ser informados como parâmetros do método." );
            }
            else
            {
                if ( $method != '' )
                {
                    $this->query .= " $method JOIN $table ON ($condition) ";
                }
                else
                {
                    $this->query .= " JOIN $table ON ($condition) ";
                }
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    /**
     * Utilizado após o método select, aponta a tabela destino
     *
     * @name from
     * @param String $table Nome da tabela
     * @example $obj->from("table");
     */
    public function from( $table = null )
    {
        try
        {
            if ( $table == null )
            {
                throw new Exception( "from - A(s) tabela(s) deve(m) ser informada(s) no método." );
            }
            else
            {
                $this->tabela = $table;
                $this->query .= " $this->tabela ";
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    /**
     * Utilizado para sql insert
     *
     * @name insert
     * @param String $table Nome da tabela
     * @example $obj->insert("produtos");
     */
    public function insert( $table = null )
    {
        try
        {
            if ( $table == null )
            {
                throw new Exception( "insert: Uma tabela deve ser informada como parâmetro do método." );
            }
            else
            {
                $this->tabela = $table;
                $this->campo = null;
                $this->valor = null;
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    /**
     * Utilizado em insert para informar os campos de destino
     *
     * @name fields
     * @param Array $field Nome do campo
     * @example $obj->fields(array('campo1','campo2'));
     */
    public function fields( $fields = array( ) )
    {
        try
        {
            if ( isset( $this->post_fields ) && !empty( $this->post_fields ) && empty( $fields ) )
            {
                $fields = $this->post_fields;
            }
            if ( empty( $fields ) )
            {
                throw new Exception( "fields: O(s) campo(s) destino da inserção deve(m) ser informado(s) no método." );
            }
            else
            {
                $this->campo = implode( ",", $fields );
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    /**
     * Utilizado no insert para informar os valores dos campos
     *
     * @name values
     * @param Array $value Valor do campo
     * @example $obj->values(array(10,'foo'));
     */
    public function values( $values = array( ) )
    {
        try
        {
            if ( isset( $this->post_values ) && !empty( $this->post_values ) && empty( $values ) )
            {
                $values = $this->post_values;
            }
            if ( empty( $values ) )
            {
                throw new Exception( "values: O(s) valor(es) deve(m) ser informado(s) como parâmetro(s) do método." );
            }
            else
            {
                $this->valor = "'" . implode( "','", $values ) . "'";
                $this->query = "INSERT INTO $this->tabela ($this->campo) VALUES ($this->valor);";
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    /**
     * Utilizado para Update no banco
     *
     * @name update
     * @param String $table Nome da tabela
     * @example $obj->update('users');
     */
    public function update( $table = null )
    {
        try
        {
            if ( $table == null )
            {
                throw new Exception( "update: A tabela destino deve ser informada como parâmetro do método." );
            }
            else
            {
                $this->strupdate = "";
                $this->tabela = $table;
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    /**
     * Utilizado após update, define nome e valor do campo
     *
     * @name set
     * @param Array $field Nome do campo
     * @param Array $value Valor do campo
     * @example $obj->set(array("nome","idade"),array("Blair",28));
     */
    public function set( $fields = array( ), $values = array( ) )
    {
        try
        {
            if ( empty( $fields ) || empty( $values ) )
            {
                $fields = $this->post_fields;
                $values = $this->post_values;
            }

            if ( !empty( $fields ) && !empty( $values ) )
            {
                $params = (array_combine( $fields, $values ));
                foreach ( $params as $key => $value )
                {
                    $this->strupdate .= " $key = '$value',";
                }
                $this->query = "UPDATE $this->tabela SET " . substr( $this->strupdate, 0, -1 );
            }
            else
            {
                throw new Exception( "set: Arrays fields ou values vazios." );
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    /**
     * Incrementa determinado campo da tabela
     *
     * @name increment
     * @param String $table Nome da tabela
     * @param String $field Nome do campo
     * @param Int $value valor a ser incrementado
     * @example $obj->increment('visitas','count',1,'id = 1');
     */
    public function increment( $table = null, $field = null, $value = null, $cond = null )
    {
        try
        {
            if ( $table == null || $field == null || $value == null )
            {
                throw new Exception( 'increment: O nome da tabela,campo e valor devem ser informados!' );
            }
            else
            {
                $this->query = "UPDATE $table SET $field = $field+$value where $cond";
                $this->execute();
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    /**
     * Decrementa determinado campo da tabela
     *
     * @name decrement
     * @param String $table Nome da tabela
     * @param String $field Nome do campo
     * @param Int $value valor a ser incrementado
     * @example $obj->decrement('visitas','count',1,'id=1');
     */
    public function decrement( $table = null, $field = null, $value = null, $cond = null )
    {
        try
        {
            if ( $table == null || $field == null || $value == null )
            {
                throw new Exception( 'decrement: O nome da tabela,campo e valor devem ser informados!' );
            }
            else
            {
                $this->query = "UPDATE $table SET $field = $field-$value where $cond";
                $this->execute();
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    /**
     * Utilizado para deletar registros de uma tabela
     *
     * @name delete
     * @example $obj->delete();
     */
    public function delete()
    {
        $this->query = "DELETE FROM ";
        return $this;
    }

    /**
     *
     * Utilizado para realizar seleção com a condição
     * @name where
     * @param String $condition
     * @example $obj->where("id = 1");
     * @example $obj->where("username = 'foo' ");
     */
    public function where( $condition = null )
    {
        try
        {
            if ( $condition == null )
            {
                throw new Exception( "where: A condição deve ser informada como parâmetro do método." );
            }
            else
            {
                $this->query .= " WHERE $condition";
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    /**
     * Metodo Sql orderby
     *
     * @name orderby
     * @param Strin $order campo ordem
     * @example $obj->orderby("nome asc");
     * @example $obj->orderby("nome desc");
     */
    public function orderby( $order = null )
    {
        try
        {
            if ( $order == null )
            {
                throw new Exception( "orderby: O campo e ordem devem ser informadas como parâmetros do método." );
            }
            else
            {
                $this->query .= " order by $order";
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    /**
     * Metodo sql groupby
     *
     * @name orderby
     * @param Strin $order campo ordem
     * @example $obj->orderby("nome asc");
     * @example $obj->orderby("nome desc");
     */
    public function groupby( $field = null )
    {
        try
        {
            if ( $field == null )
            {
                throw new Exception( "groupby: O campo deve ser informado como parâmetro do método." );
            }
            else
            {
                $this->query .= " GROUP BY $field";
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    /**
      /**
     *
     * Popula o array assigndata para utilização no método display
     *
     * @name assign
     * @param key String indice do array
     * @param value String valor do array
     * @example $obj->assign('hoje', date());
     *
     */
    public function assign( $key = null, $value = null )
    {
        try
        {
            if ( $key == null )
            {
                throw new Exception( "assign: O método deve receber ao menos o primeiro parâmetro." );
            }
            else
            {
                $this->assigndata[$key] = trim( $value );
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    public function assignAll()
    {
        try
        {
            if ( isset( $this->view->tpldata ) )
            {
                unset( $this->view->tpldata );
            }
            if ( isset( $this->data[0] ) )
            {
                foreach ( $this->data as $data )
                {
                    foreach ( $data as $key => $value )
                    {
                        $this->assigndata[$key] = trim( $value );
                    }
                }
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }
    /* Tpl for TemplateFy */

    public function tpl( $tpl = null, $tpldir = null, $baseapp = null )
    {
        try
        {
            if ( $tpl == null )
            {
                throw new Exception( "tpl: O arquivo de template deve ser informado como parâmetro do método." );
            }
            else
            {
                $this->view = new TemplateFy;
                if ( $tpldir != null )
                {
                    $this->view->tpldir = $tpldir;
                }
                else
                {
                    $this->view->tpldir = VIEWSDIR;
                }
                if ( $baseapp != null )
                {
                    $this->view->baseApp = $baseapp;
                }
                else
                {
                    $this->view->baseApp = HTTPURL . APP;
                }
                $this->view->tpl( $tpl );
                if ( isset( $this->view_prepend_data ) )
                {
                    $this->view->data( $this->view_prepend_data );
                }
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }
    /* Fecth for TemplateFy */

    public function fetch( $target, $data )
    {
        try
        {
            if ( isset( $this->view ) && is_callable( array( $this->view, 'fetch' ) ) )
            {
                $this->view->fetch( $target, $data );
            }
            else
            {
                throw new Exception( "fetch: O objeto de template não foi inicializado." );
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
    }
    /* render for TemplateFy */

    public function render( $printable=null )
    {
        try
        {
            if ( isset( $this->view ) && is_callable( array( $this->view, 'fetch' ) ) )
            {
                if ( !empty( $this->assigndata ) )
                {

                    $this->view->data( $this->assigndata );
                }

                if ( $printable == null )
                {
                    $this->view->render();
                }
                else
                {
                    return $this->view->render( 'printable' );
                }
            }
            else
            {
                throw new Exception( "render: O objeto de template não foi inicializado. obj->tpl()" );
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
    }

    /**
     * Executa a Query e disponibiliza os dados em $this->data
     *
     */
    public function execute()
    {
        try
        {
            if ( !$this->objBanco )
            {
                throw new Exception( "execute: Objeto da conexão não inciado ou falhou." );
            }

            if ( $this->paginateNum != null )
            {
                $this->stmt = $this->objBanco->query( "$this->query" );
                if ( $this->stmt )
                {
                    $this->numrows_total = $this->stmt->rowCount();
                    $this->numrows = $this->numrows_total;
                    $this->paginateLinks( $this->paginateNum );
                }
                if ( $this->limitOffset != null )
                {
                    $this->query .= " $this->limitOffset";
                }
            }

            $this->stmt = $this->objBanco->query( "$this->query" );
            if ( $this->stmt )
            {
                $this->numrows = $this->stmt->rowCount();
                if ( $this->method != null )
                {
                    $this->data = $this->stmt->fetchAll();
                }
            }
            if ( $this->objBanco->response != 'success' )
            {
                $this->response = $this->objBanco->response;
                throw new Exception( $this->response );
            }
        }
        catch ( Exception $e )
        {
            echo "<p style=\"color:red;padding:6px;border:1px solid red; width:99%\">" . $e->getMessage() . "</p>";
            exit;
        }
        return $this;
    }

    /**
     *  result - Verifica se há dados retornados da query
     *  @return bool
     *  @example $obj->result()
     */
    public function result()
    {
        if ( isset( $this->data ) )
        {
            if ( count( $this->data ) && !empty( $this->data[0] ) )
            {
                return true;
            }
            else
            {
                return false;
            }
        }
    }

    /**
     * Utilizado para criar url amigavel
     * @name urlmod
     * @param String $str
     * @param String $key
     * @param String $reverse
     * @example $obj->urlmod('titulo');
     * @example $obj->urlmod('titulo','link');
     * @example $obj->urlmod('titulo','link','reverse');
     * @return str teste-a-solucao
     */
    public function urlmod( $key, $nkey=null, $reverse=null )
    {
        $group_a = array( 'À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç',
            'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð',
            'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú',
            'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä',
            'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í',
            'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø',
            'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'A', 'a', 'A',
            'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c',
            'C', 'c', 'D', 'd', 'Ð', 'd', 'E', 'e', 'E',
            'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g',
            'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H',
            'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i',
            'I', 'i', '?', '?', 'J', 'j', 'K', 'k', 'L',
            'l', 'L', 'l', 'L', 'l', '?', '?', 'L', 'l',
            'N', 'n', 'N', 'n', 'N', 'n', '?', 'O', 'o',
            'O', 'o', 'O', 'o', '?', '?', 'R', 'r', 'R',
            'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's',
            '?', '?', 'T', 't', 'T', 't', 'T', 't', 'U',
            'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u',
            'U', 'u', 'W', 'w', 'Y', 'y', '?', 'Z', 'z',
            'Z', 'z', '?', '?', '?', '?', 'O', 'o', 'U',
            'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u',
            'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', '?',
            '?', '?', '?', '?', '?' );
        $group_b = array( 'A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C',
            'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D',
            'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U',
            'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a',
            'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i',
            'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o',
            'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A',
            'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c',
            'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E',
            'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g',
            'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H',
            'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i',
            'I', 'i', '', '', 'J', 'j', 'K', 'k', 'L',
            'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l',
            'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o',
            'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R',
            'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's',
            'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U',
            'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u',
            'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z',
            'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U',
            'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u',
            'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A',
            'a', 'AE', 'ae', 'O', 'o' );

        $pattern = array( '/[^a-zA-Z0-9 -]/', '/[ -]+/', '/^-|-$/' );
        $replace = array( ' ', '-', '' );
        try
        {
            if ( $reverse != null )
            {
                $replace = array( '-', '-', '' );
            }
            if ( !empty( $this->data ) )
            {
                foreach ( $this->data as $idx => $item )
                {
                    if ( isset( $item[trim( $key )] ) )
                    {
                        $replaced = str_replace( $group_a, $group_b, $this->data[$idx][trim( $key )] );
                        if ( $nkey == null )
                        {
                            $this->data[$idx]['urlmode'] = strtolower( preg_replace( $pattern, $replace, $replaced ) );
                        }
                        else
                        {
                            $this->data[$idx]["$nkey"] = strtolower( preg_replace( $pattern, $replace, $replaced ) );
                        }
                    }
                }
            }
            else
            {
                $this->response = "urlmode: O array de origem está vazio.";
                //throw  new Exception("urlmod: O array de origem está vazio.");
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
    }

    public function urlmodr( $key, $nkey=null, $reverse=null )
    {
        $group_a = array( 'À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç',
            'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð',
            'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú',
            'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä',
            'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í',
            'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø',
            'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'A', 'a', 'A',
            'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c',
            'C', 'c', 'D', 'd', 'Ð', 'd', 'E', 'e', 'E',
            'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g',
            'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H',
            'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i',
            'I', 'i', '?', '?', 'J', 'j', 'K', 'k', 'L',
            'l', 'L', 'l', 'L', 'l', '?', '?', 'L', 'l',
            'N', 'n', 'N', 'n', 'N', 'n', '?', 'O', 'o',
            'O', 'o', 'O', 'o', '?', '?', 'R', 'r', 'R',
            'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's',
            '?', '?', 'T', 't', 'T', 't', 'T', 't', 'U',
            'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u',
            'U', 'u', 'W', 'w', 'Y', 'y', '?', 'Z', 'z',
            'Z', 'z', '?', '?', '?', '?', 'O', 'o', 'U',
            'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u',
            'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', '?',
            '?', '?', '?', '?', '?' );
        $group_b = array( 'A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C',
            'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D',
            'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U',
            'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a',
            'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i',
            'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o',
            'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A',
            'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c',
            'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E',
            'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g',
            'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H',
            'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i',
            'I', 'i', '', '', 'J', 'j', 'K', 'k', 'L',
            'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l',
            'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o',
            'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R',
            'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's',
            'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U',
            'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u',
            'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z',
            'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U',
            'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u',
            'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A',
            'a', 'AE', 'ae', 'O', 'o' );

        $pattern = array( '/[^a-zA-Z0-9 -]/', '/[ -]+/', '/^-|-$/' );
        $replace = array( ' ', '-', '' );

        if ( $reverse != null )
        {
            $replace = array( '-', ' ', '' );
        }
        $replaced = str_replace( $group_a, $group_b, $key );
        return strtolower( preg_replace( $pattern, $replace, $replaced ) );
    }

    /**
     * Utilizado para adicionar indices ao array
     * @name addkey
     * @param String $key
     * @param String $value
     * @param String $concat
     * @example $obj->addkey('title','test');
     * @example $obj->addkey('title','test','exists key');
     *
     */
    public function addkey( $key, $value, $concat=null )
    {
        try
        {
            if ( !empty( $this->data ) )
            {
                foreach ( $this->data as $idx => $item )
                {
                    if ( $concat != null && isset( $this->data[$idx][$concat] ) )
                    {
                        $this->data[$idx][$key] = $value . $this->data[$idx][$concat];
                    }
                    else
                    {
                        $this->data[$idx][$key] = $value;
                    }
                }
            }
            else
            {
                $this->response = "addkey: O array de origem está vazio.";
                //throw  new Exception("addkey: O array de origem está vazio.");
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    /**
     * Utilizado para numerar indices ao array
     * @name addindex
     * @param String $key
     * @example $obj->addkey('idx');
     *
     */
    public function addindex( $key )
    {
        try
        {
            if ( !empty( $this->data ) )
            {
                $k = 0;
                foreach ( $this->data as $idx => $item )
                {
                    $this->data[$idx][$key] = $k;
                    $k++;
                }
            }
            else
            {
                $this->response = "addkey: O array de origem está vazio.";
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    /**
     * Utilizado para concatenar 2 indices
     * @name clonekey
     * @param String $new
     * @param String $separator
     * @param Array $keys
     * @example $obj->clonekey('new key',array('key a','key b'));
     * @example $obj->clonekey('new key',array('key a','key b'),' - ');
     *
     */
    public function clonekey( $new, $keys, $sep = " " )
    {
        try
        {
            if ( !empty( $this->data ) )
            {
                foreach ( $this->data as $idx => $item )
                {
                    $t = "";
                    foreach ( $keys as $key )
                    {
                        if ( isset( $this->data[$idx][$key] ) )
                        {
                            $t .= $this->data[$idx][$key] . $sep;
                        }
                    }
                    $this->data[$idx][$new] = $t;
                }
            }
            else
            {
                $this->response = "clonekey: O array de origem está vazio.";
                //throw  new Exception("concat: O array de origem está vazio.");
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    /**
     * Utilizado para função preg_replace
     * @name preg
     * @param String $key
     * @param String $pattern
     * @param String $replace
     * @example $obj->preg('/./','-','key_data_cad');
     *
     */
    public function preg( $pattern, $replace, $key )
    {
        try
        {
            if ( !empty( $this->data ) )
            {
                foreach ( $this->data as $idx => $item )
                {
                    if ( isset( $item[trim( $key )] ) )
                    {
                        if ( strlen( $this->data[$idx][trim( $key )] ) <= 0 )
                        {
                            $this->data[$idx][trim( $key )] = "NULL";
                        }
                        $this->data[$idx][trim( $key )] = preg_replace( $pattern, $replace, $this->data[$idx][trim( $key )] );
                    }
                }
            }
            else
            {
                $this->response = "preg: O array de origem está vazio.";
                //throw  new Exception("preg: O array de origem está vazio.");
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    /**
     * Utilizado para  somar valores do campo determinado
     * @name sum
     * @param String $key
     * @example $obj->sum('produto_valor');
     * return $this->sum;
     */
    public function sum( $key )
    {
        try
        {
            if ( !empty( $this->data ) )
            {
                $ret = 0;
                foreach ( $this->data as $id => $data )
                {
                    if ( isset( $data[$key] ) )
                    {
                        $dkey = $data[$key];
                        $num = preg_replace( '/\,/', '', $dkey );
                        $num = preg_replace( '/\./', '', $dkey );
                        $ret += $num;
                    }
                    else
                    {
                        $ret = 0;
                    }
                }
                $this->sum = $ret;
            }
            else
            {
                //throw  new Exception("encode: O array de origem está vazio.");
                $this->response = "sum: O array de origem está vazio.";
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this->sum;
    }

    /**
     * Utilizado para formatar moeda em real ...
     * @name money
     * @param String key
     * @param String decimals
     * @param String sep1
     * @param String sep2
     * @example $obj->money('price');
     * @example $obj->money('price',2,'.','');
     */
    public function money( $tbkey = null, $decimals = 2, $sep1 = ',', $sep2 = '.' )
    {
        try
        {
            if ( !empty( $this->data ) )
            {
                foreach ( $this->data as $idx => $val )
                {
                    if ( isset( $this->data[$idx]["$tbkey"] ) )
                    {
                        $this->data[$idx]["$tbkey"] = @number_format( $this->data[$idx]["$tbkey"], $decimals, $sep1, $sep2 );
                    }
                }
            }
            else
            {
                //throw  new Exception("money: O array de origem está vazio.");
                $this->response = "money: O array de origem está vazio.";
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    /**
     * Utilizado para mudar o encoding utf8_decode, utf8_encode, htmlentities ...
     * @name encode
     * @param String $encoding
     * @example $obj->encode('key','utf8_decode');
     * @example $obj->encode();
     * defauls all keys to utf8_decode
     */
    public function encode( $tbkey = null, $encoding = 'utf8_decode' )
    {
        try
        {
            if ( !empty( $this->data ) )
            {
                foreach ( $this->data as $idx => $val )
                {
                    if ( $tbkey != null )
                    {
                        if ( isset( $this->data[$idx]["$tbkey"] ) )
                        {
                            $this->data[$idx]["$tbkey"] = $encoding( $this->data[$idx]["$tbkey"] );
                        }
                    }
                    else
                    {
                        foreach ( $val as $key => $v )
                        {
                            if ( isset( $this->data[$idx]["$key"] ) )
                            {
                                $this->data[$idx]["$key"] = $encoding( $this->data[$idx]["$key"] );
                            }
                        }
                    }
                }
            }
            else
            {
                //throw  new Exception("encode: O array de origem está vazio.");
                $this->response = "encode: O array de origem está vazio.";
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    /**
     * Utilizado para cortar uma string do array ...
     * @name cut
     * @param String $key
     * @param int $chars
     * @example $obj->cut('name',40);
     *
     */
    public function cut( $key, $chars, $info )
    {
        try
        {
            if ( !empty( $this->data ) )
            {
                foreach ( $this->data as $idx => $item )
                {
                    if ( isset( $item[trim( $key )] ) )
                    {
                        $str = $item[trim( $key )];
                        if ( strlen( $str ) >= $chars )
                        {
                            $str = preg_replace( '/\s\s+/', ' ', $str );
                            $str = strip_tags( $str );
                            $str = preg_replace( '/\s\s+/', ' ', $str );
                            $str = substr( $str, 0, $chars );
                            $str = preg_replace( '/\s\s+/', ' ', $str );
                            $arr = explode( ' ', $str );
                            array_pop( $arr );
                            //$arr = preg_replace('/\&nbsp;/i',' ',$arr);
                            $final = implode( ' ', $arr ) . $info;
                        }
                        else
                        {
                            $final = $str;
                        }
                        $this->data[$idx][trim( $key )] = strip_tags( $final );
                    }
                }
            }
            else
            {
                //throw  new Exception("cut: O array de origem está vazio.");
                $this->response = "cut: O array de origem está vazio.";
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    /**
     * Utilizado para realizar seleção com limit
     * deve ser o ultimo método antes de execute
     * @param Int $limit
     * @param Int $offset
     * @example $obj->limit(10,20)->execute();
     */
    public function limit( $limit = null, $offset = null )
    {
        try
        {
            if ( $limit == null || $offset == null )
            {
                $this->response = "limit: Os parâmetros limit e offset devem ser informados.";
                //throw  new Exception('limit: Os parâmetros limit e offset devem ser informados.');
            }

            $this->limit = $limit;
            $this->offset = $offset;
            $this->query .= " LIMIT " . ( int ) $this->offset . " OFFSET " . ( int ) $this->limit;
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    /**
     * Utilizado para realizar limit na paginacao
     *
     * @param Int $limit
     * @param Int $offset
     * @example $obj->plimit(10,20);
     */
    protected function plimit( $limit = null, $offset = null )
    {
        try
        {
            if ( $limit == null || $offset == null )
            {
                $this->response = "limit: Os parâmetros limit e offset devem ser informados.";
                //throw  new Exception('limit: Os parâmetros limit e offset devem ser informados.');
            }

            $this->limit = $limit;
            $this->offset = $offset;
            if ( $this->adapter == null )
            {
                // PDO inverse sequence limit x offset value
                $this->limitOffset = "LIMIT " . ( int ) $this->offset . " OFFSET " . ( int ) $this->limit;
            }
            else
            {
                $this->limitOffset = $this->objBanco->limit( $this->limit, $this->offset );
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    /**
     *
     * Utilizado para realizar paginacao, utiliza para isso o limit()
     *
     * @name paginate
     * @param Int $rows Número de registros por página
     * @example $obj->paginate(10);
     */
    public function paginate( $rows = null )
    {
        $this->paginateNum = $rows;
        return $this;
    }

    /**
     * utilizado internamente para criar os links html para paginação
     *
     * @name paginateLinks
     * @param Int $rows
     *
     */
    public function paginateLinks( $rows = null )
    {
        try
        {
            if ( $rows == null )
            {
                throw new Exception( "paginate: O número de registros por página deve ser informado como parâmetro." );
            }
            else
            {
                if ( $this->pagebase == "" && isset( $this->uri_segment ) )
                {
                    if ( isset( $this->uri_segment[1] ) && $this->uri_segment[1] != 'page' )
                    {
                        $this->pagebase = HTTPURL . $this->uri_segment[0] . "/" . $this->uri_segment[1];
                    }
                    elseif ( isset( $this->uri_segment[0] ) )
                    {
                        $this->pagebase = HTTPURL . $this->uri_segment[0];
                    }
                }

                //if( !empty( $this->data ) ){
                $page = '0';
                $this->pagelinks = "";
                $ant = 0;
                $prox = 2;
                $total = ceil( $this->numrows / $rows );
                $ult = $total;

                if ( in_array( 'page', $this->uri_segment ) )
                {
                    $page = array_search( 'page', $this->uri_segment );
                    if ( isset( $this->uri_segment[$page + 1] ) )
                    {
                        $page = $this->uri_segment[$page + 1];
                        $prox = ($page + 1);
                        $ant = ($page - 1);
                    }
                }

                $lim = (($page * $rows) - $rows);
                if ( $lim <= 0 )
                {
                    $lim = 0;
                }
                $off = ($rows);
                $this->plimit( $lim, $off );

                $maxPages = 10;
                $this->pageArr = "";
                //if( $total >= $rows ){
                for ( $i = 1; $i <= $total; $i++ )
                {
                    if ( $i == 1 && $page <= 1 )
                    {
                        $this->pageArr[] = "<li class=\"active\"><span>$i</span></li>";
                    }
                    elseif ( $i == $page )
                    {
                        $this->pageArr[] = "<li class=\"active\"><span>$i</span></li>";
                    }
                    elseif ( $i == 1 )
                    {
                        $this->pageArr[] = "<li><a href=\"$this->pagebase/page/$i/\">$i</a></li>";
                    }
                    else
                    {
                        $this->pageArr[] = "<li><a href=\"$this->pagebase/page/$i/\">$i</a></li>";
                    }
                }

                $continue = "<li class=\"disabled\"><a>...</a></li>";
                $primeira = "<li><a href=\"$this->pagebase/page/1/\" title=\"primeira\">««</a></li>";
                $ultima = "<li><a href=\"$this->pagebase/page/$ult/\" title=\"última\">»»</a></li>";

                if ( $total != $page )
                {
                    $proxima = "<li><a href=\"$this->pagebase/page/$prox/\" title=\"próxima\">»</a></li>";
                }
                else
                {
                    $proxima = "<li class=\"disabled\"><span>»</span></li>";
                    $ultima = "<li class=\"disabled\"><span>»»</span></li>";
                }
                if ( $ant >= 1 )
                {
                    $anteriror = "<li><a href=\"$this->pagebase/page/$ant/\" title=\"anterior\">«</a></li>";
                }
                if ( $ant == 0 )
                {
                    $primeira = "<li class=\"disabled\"><span>««</span></li>";
                    $anteriror = "<li class=\"disabled\"><span>«</span></li>";
                }

                if ( $page < $maxPages )
                {
                    if ( !empty( $this->pageArr ) )
                        $arr = array_slice( $this->pageArr, 0, $maxPages - 1 );
                }
                else
                {
                    if ( ($page % $maxPages) == 0 )
                    {
                        if ( !empty( $this->pageArr ) )
                            $arr = array_slice( $this->pageArr, $page - 1, $maxPages );
                    }
                    else
                    {
                        if ( !empty( $this->pageArr ) )
                            $arr = array_slice( $this->pageArr, ($page - 1) - ($page % $maxPages), $maxPages );
                    }
                }
                if ( $total >= $maxPages )
                {
                    $arr[] = $continue;
                }

                if ( $total != $page )
                {
                    if ( ($page + $maxPages) < $total && $page >= ($maxPages * 2) )
                    {
                        $offjump = $page - ($page % $maxPages) + ($maxPages * 2) - 1;
                        if ( isset( $this->pageArr[$offjump] ) )
                        {
                            // $arr[] = $this->pageArr[$offjump];
                        }
                    }
                    else
                    {
                        if ( $page <= ($total - $maxPages) - 1 )
                        {
                            // $arr[] = $this->pageArr[($maxPages * 2) - 1];
                        }
                    }
                    if ( $total >= $maxPages )
                    {
                        $arr[] = $this->pageArr[count( $this->pageArr ) - 1];
                    }
                }
                else
                {
                    if ( $total >= $maxPages )
                        $arr = array_slice( $this->pageArr, $total - $maxPages, $maxPages );
                }
                if ( $page >= $maxPages * 2 )
                {
                    array_unshift( $arr, $continue );
                    if ( $page < $total )
                    {
                        array_unshift( $arr, $this->pageArr[($page - ($page % $maxPages) - 1) - ($maxPages)] );
                    }
                    else
                    {
                        array_unshift( $arr, $this->pageArr[$total - ($maxPages * 2)] );
                    }
                }
                if ( !empty( $arr ) )
                {
                    array_unshift( $arr, $anteriror );
                }
                //array_unshift( $arr, $primeira );
                $arr[] = $proxima;
                //$arr[] = $ultima;

                $this->pagelinks = implode( "\n", $arr );
                $this->paginateNum = null;

                if ( $total <= 1 )
                {
                    $this->pagelinks = "";
                }

                //}
            }
            $this->pagelinks = "$this->pagelinks\n";
            $this->assign( 'pages', $this->pagelinks );
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    /**
     * Uitilizado para carregar funções de outros arquivos
     *
     * @name helper
     * @param String $helper nome do arquivo com as funções
     * @example $obj->helper('sendmail');
     *
     */
    public function helper( $helper )
    {
        try
        {
            if ( file_exists( HELPERDIR . "helper_" . $helper . ".php" ) )
            {
                require_once HELPERDIR . "helper_" . $helper . ".php";
            }
            else
            {
                throw new Exception( "helper: Arquivo não encontrado no diretório " . HELPERDIR );
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    /**
     * Converte Array em Json
     * toJson
     * @param Array param
     * @example print_r($obj->jextract($_POST['dataform']));
     */
    public function toJson( $param = null )
    {
        try
        {
            $jarray = array( );
            if ( $param == null && empty( $this->data[0] ) )
            {
                throw new Exception( 'toJson: array vazio' );
            }
            else
            {
                if ( $param == null )
                {
                    $param = $this->data;
                }
                $json = "{ \"rs\" : [";
                foreach ( $param as $p )
                {
                    $json .= "{";
                    foreach ( $p as $k => $v )
                    {
                        //$v = utf8_decode($v);
                        $json .= "\"$k\":\"$v\",";
                    }
                    $json .= "},";
                }
                $json = substr_replace( $json, '', -1, 1 );
                $json = preg_replace( '/,}/', '}', $json );
                $json .= "] }";
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        $this->jsonData = $json;
        return $this->jsonData;
    }

    /**
     * Redireciona URL
     * redirect
     * @param String $url
     */
    public function redirect( $url = null )
    {
        @header( "Location: $url" );
    }

    /**
     * Extrai as variaveis do get e armazena no atrivuto uri_segment URL
     * loadUri
     */
    public function loadUri()
    {
        try
        {
            if ( !isset( $_GET ) || empty( $_GET ) )
            {
                throw new Exception( 'loadUri: Segment Null' );
            }
            else
            {
                $routes = explode( "/", $_GET['route'] );
                foreach ( $routes as $uri )
                {
                    if ( $uri != "" )
                    {
                        $this->uri_segment[] = $uri;
                    }
                    (isset( $_SERVER['HTTP_REFERER'] )) ? $this->referer = $_SERVER['HTTP_REFERER'] : $this->referer = '';
                }
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        //base_uri arquivo atual
        $this->baseUri = substr( HTTPURL, 0, -1 );
        return $this;
    }
    /*
     * Retorna oarray pre formatado 
     * printr
     * @param Array $array
     * @example $obj->printr($data);
     */

    public function printr( $data )
    {
        echo "<pre>";
        print_r( $data );
        echo "</pre>";
    }

    /**
     * Popula os arrays fields e values para montar a query
     * post2Query
     * retorna os dados em $this->post_fields e $this->post_values
     * @param Array $arr2query
     * @example $obj->post2Query($_POST);
     * @example $obj->post2Query($_GET);
     */
    public function post2Query( $arr2query )
    {
        try
        {
            if ( !is_array( $arr2query ) || empty( $arr2query ) )
            {
                throw new Exception( 'post2query: O paramêtro não é um array ou está vazio!' );
            }
            else
            {
                foreach ( $arr2query as $key => $value )
                {
                    $this->post_fields[] = trim( "$key" );
                    $this->post_values[] = trim( "$value" );
                    //$this->post_values[] = preg_replace('/\s+/', ' ', $value);
                }
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    /**
     * Valida campos submetidos pelo post, 
     * Se TRUE, retorna os dados em $this->post_fields e $this->post_values
     * postIsValid
     * @param Array $post
     * @example $obj->->postIsValid( array( 'item_desc' => 'string', 'item_cat' => 'numeric' );
     * @example $obj->->postIsValid( array('item_cat' => 'numeric' );
     */
    public function postIsValid( $post = array( ) )
    {
        $this->response = "";
        $is_valid = true;
        if ( !is_array( $post ) || empty( $post ) )
        {
            $this->response = 'O paramêtro não é um array ou está vazio!';
            $is_valid = false;
        }
        else
        {
            foreach ( $post as $key => $value )
            {
                if ( isset( $_POST[$key] ) && preg_replace( '/\s+/', '', $_POST[$key] ) == "" || !isset( $_POST[$key] ) )
                {
                    $this->response .= '<p>O campo [' . $key . '] deve ser preenchido! </p>';
                    $is_valid = false;
                }
                else
                {
                    if ( isset( $_POST[$key] ) )
                    {
                        if ( $value == 'numeric' && !is_numeric( $_POST[$key] ) )
                        {
                            $this->response .= '<p>O campo [' . $key . '] deve ser númerico! </p>';
                            $is_valid = false;
                        }
                        elseif ( $value == 'mail' && !eregi( "^[a-z0-9_\.\-]+@[a-z0-9_\.\-]*[a-z0-9_\-]+\.[a-z]{2,4}$", $_POST[$key] ) )
                        {
                            $this->response .= '<p>O campo [' . $key . '] deve conter um e-mail válido! </p>';
                            $is_valid = false;
                        }
                        elseif ( $value == 'cpf' )
                        {
                            $this->helper( 'str' );
                            if ( !validaCpf( $_POST[$key] ) )
                            {
                                $this->response .= '<p>O campo [' . $key . '] deve ser conter um CPF válido! </p>';
                                $is_valid = false;
                            }
                        }
                    }
                }
            }
            if ( $is_valid == false )
            {
                return false;
            }
            else
            {
                $this->post2Query( $_POST );
                $this->response = "";
                return true;
            }
        }
    }

    //add index do post_fields e post_values
    public function postValueChange( $index, $value )
    {
        $change = array_search( $index, $this->post_fields );
        if ( isset( $this->post_fields["$change"] ) )
        {
            $this->post_values[$change] = "$value";
        }
    }

    //add index do post_fields e post_values
    public function postIndexAdd( $index, $value )
    {
        $this->post_fields[] = "$index";
        $this->post_values[] = "$value";
    }

    //get value do post_fields
    public function postGetValue( $index )
    {
        $value = array_search( $index, $this->post_fields );
        if ( $value || isset( $this->post_fields["$value"] ) )
        {
            return $this->trimmer( $this->post_values[$value] );
        }
        else
        {
            return false;
        }
    }

    //add index do post_fields e post_values
    public function postIndexDate( $index )
    {
        $change = array_search( $index, $this->post_fields );
        if ( isset( $this->post_fields["$change"] ) )
        {
            if ( $this->post_values[$change] != "" )
            {
                $todate = preg_replace( '/\//', '-', $this->post_values[$change] );
                $this->post_values[$change] = date( 'Y-m-d', strtotime( $todate ) );
            }
        }
    }

    //trim
    public function trimmer( $str )
    {
        return preg_replace( '/\s+/', ' ', $str );
    }

    //formata para campo double 10,2
    public function postIndexFormat( $index, $format = 'money' )
    {
        if ( $format == 'money' )
        {
            $change = array_search( $index, $this->post_fields );
            $this->post_values[$change] = preg_replace( array( '/\./', '/\,00/' ), array( '', '' ), $this->post_values[$change] );
        }
    }

    //remove index do post_fields e post_values
    public function postIndexDrop( $index )
    {
        $remove = array_search( $index, $this->post_fields );
        if ( $remove )
        {
            unset( $this->post_fields[$remove] );
            unset( $this->post_values[$remove] );
        }
        else
        {
            $this->response .= 'O index informado não existe no array';
        }
    }

    //remove blank post_fields e post_values
    public function postBlankDrop()
    {
        foreach ( $this->post_fields as $index )
        {
            $idx = @array_search( $index, $this->post_fields );
            if ( $this->trimmer( $this->post_values[$idx] ) == "" || empty( $this->post_values[$idx] ) )
            {
                unset( $this->post_fields[$idx] );
                unset( $this->post_values[$idx] );
            }
        }
        //sort($this->post_fields);
        //sort($this->post_values);
    }

    //exibe os post_fields e values
    public function showPostData()
    {
        $this->printr( $this->post_fields );
        $this->printr( $this->post_values );
        exit;
    }

    //abre arquivo ou url com curl ou fgets
    public function openUrl( $param = array( ) )
    {
        try
        {
            if ( empty( $param ) )
            {
                throw new Exception( 'openUrl: Array de parâmetros vazio!' );
            }
            else
            {
                if ( isset( $param['method'] ) )
                {
                    $method = strtoupper( $param['method'] );
                }
                else
                {
                    throw new Exception( 'openUrl: Parâmetro method deve ser informado no array de parâmetros!' );
                }
                if ( $method == 'C' )
                {
                    $url = $param['url'];
                    $buffer = "";
                    $ch = curl_init();
                    curl_setopt( $ch, CURLOPT_URL, $url );
                    curl_setopt( $ch, CURLOPT_HEADER, 0 );
                    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
                    $buffer = trim( curl_exec( $ch ) );
                    if ( curl_errno( $ch ) )
                    {
                        throw new Exception( 'Curl error: ' . curl_error( $ch ) );
                    }
                    else
                    {
                        $this->buffer = $buffer;
                        return $buffer;
                    }
                    curl_close( $ch );
                }
                elseif ( $method == 'F' )
                {
                    $url = $param['url'];
                    $line = "";
                    $buffer = "";
                    $handle = @fopen( "$url", "r" );
                    if ( $handle )
                    {
                        while ( !feof( $handle ) )
                        {
                            $line = trim( @fgets( $handle, 4096 ) );
                            if ( isset( $param['return'] ) && $param['return'] == 'array' )
                            {
                                $buffer[] = explode( ",", $line );
                            }
                            else
                            {
                                $buffer .= $line . "\n";
                            }
                        }
                        fclose( $handle );
                        $this->buffer = $buffer;
                        return $buffer;
                    }
                }
                elseif ( $method == 'FC' )
                {
                    $url = $param['url'];
                    $buffer = trim( @file_get_contents( $url, 0, null ) );
                    $this->buffer = $buffer;
                    return $buffer;
                }
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    public function map( $arr = array( ) )
    {
        try
        {
            if ( $arr == null )
            {
                throw new Exception( 'ArrayMapNull' );
            }
            else
            {
                foreach ( $arr as $k => $v )
                {
                    if ( !isset( $this->$k ) )
                    {
                        $this->$k = "";
                    }
                    $this->$k = $v;
                }
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }
}
?>
