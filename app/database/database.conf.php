<?php

# Configura��o dos bancos de dados suportados no PDO
$databases = array(
    # MYSQL
    'default' => array
        (
        'driver' => 'mysql',
        'host' => '10.0.75.1',
        'port' => 3306,
        'dbname' => 'fluxshop',
        'user' => 'root',
        'password' => 'root',
	    'limite_produto' => 1000, //limite de produtos cadastrados
        'emailAdmin' => 'wjr.ans@gmail.com'
    )
);

/* end file */
