<?php

class PaybrasIniciar {

	public static $loader;
	public static $dirs = array(
		'config',
		'classes'
	);

	private function __construct() {
		if (function_exists('__autoload')) {
			spl_autoload_register('__autoload');
		}
		spl_autoload_register(Array($this, 'adicionaClasse'));
	}

	public static function init() {
		if (!function_exists('spl_autoload_register')) {
			throw new Exception("PaybrasBiblioteca: Necessário Standard PHP Library (SPL).");
			return false;
		}
		if (self::$loader == null) {
			self::$loader = new PaybrasIniciar();
		}
		return self::$loader;
	}
	
	private function adicionaClasse($class) {
		foreach(self::$dirs as $key => $dir) {
			$file = PaybrasBiblioteca::getPath().DIRECTORY_SEPARATOR.$dir.DIRECTORY_SEPARATOR.$class.'.class.php';
			if (file_exists($file) && is_file($file)) {
				require_once $file;
			}
		}
	}
	
}
?>