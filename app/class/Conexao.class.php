<?php
/**
 * Conexao class
 *
 * @author Rafael Clares <rafadinix@gmail.com>
 * @version 1.0  <10/2010>
 * web: www.clares.wordpress.com
 *
 *  Conexao com PHP PDO retorna um objeto PDO
 */
Class Conexao extends PDO
{

    protected $config;
    protected $driver;
    protected $sgbd;
    protected $host;
    protected $port;
    protected $user;
    protected $pass;
    protected $dbname;
    protected $strcon;
    protected $con;

    public function __construct( $config )
    {
        try
        {
            #array com dados do banco
            $this->config = $config;
            # Recuperando os dados de conexao do driver
            $this->dbname = $this->config['dbname'];
            $this->driver = $this->config['driver'];
            $this->sgbd = $this->config['sgbd'];
            $this->host = $this->config['host'];
            $this->port = $this->config['port'];
            $this->user = $this->config['user'];
            $this->pass = $this->config['password'];
            $this->strCon = "$this->sgbd:host=$this->host;port=$this->port;";
            # instancia e retorna objeto PDO
            $this->con = parent :: __construct( "$this->strCon dbname=$this->dbname", $this->user, $this->pass, array( PDO::ATTR_PERSISTENT => true ) );
            return $this->con;
        }
        catch( PDOException $e )
        {
            echo 'A Conexão falhou: ' . $e->getMessage();
            exit;
        }
    }

    public function close()
    {
        unset( $this->con );
        unset( $this->dbname );
        unset( $this->config );
    }
}
/* end file */