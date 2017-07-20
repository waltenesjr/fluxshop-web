<?php

require_once "PaybrasIniciar.class.php";

class PaybrasBiblioteca {
	
	public static $config;
	public static $library;
	public static $path;
	
	private function __construct() {
		self::$path = (dirname(__FILE__));
		PaybrasIniciar::init();
		self::$config = PaybrasConfig::init();
	}
	
	public static function init() {
		self::verificaDependencias();
		if (self::$library == null) {
			self::$library  = new PaybrasBiblioteca();
		}
		return self::$library;
	}

	public final static function getPath() {
		return self::$path;
	}
	
	private static function verificaDependencias() {
		
		$dependencias = true;
		
		if (!function_exists('spl_autoload_register')) {
			throw new Exception("PaybrasBiblioteca: Necessário Standard PHP Library (SPL).");
			$dependencias = false;
		}
		
		if (!function_exists('curl_init')) {
			throw new Exception('PaybrasBiblioteca: Necessário cURL.');
			$dependencias = false;
		}
		
		if (!class_exists('DOMDocument')) {
			throw new Exception('PaybrasBiblioteca: Necessário DOM XML.');
			$dependencias = false;
		}
		
		return $dependencias;
		
	}
}
PaybrasBiblioteca::init();
?>
