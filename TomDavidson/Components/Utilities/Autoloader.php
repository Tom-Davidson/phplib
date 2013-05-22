<?php
/**
 * Autoloader
 *
 * PSR-0 compatable autoloader by the php standard working group (http://groups.google.com/group/php-standards?pli=1) - https://gist.github.com/1234504
 *
 * To use:
 *	Check your include path or set using `string set_include_path ( string $new_include_path );`
 * 	`Autoloader::init();`
 *
 * Dependancies:
 *	php 5.3.0+
 *	php SPL
 *
 * @package		Components
 * @subpackage	Utilities
 * @author		Tom Davidson <tom@davidson.me.uk>
 */
namespace TomDavidson\Components\Utilities;
abstract class Autoloader
{
    private $_str = 0;
    /**
     * Autoloader::init
     *
     * Hooks this autoloader into SPL's autoloader stack so it can be used either in isolation or as part of a series of autoloaders.
     *
     */
    function init() {
		if(!function_exists('spl_autoload_register')){
			throw new Exception('Function spl_autoload_register does not exist, do you have php\'s SPL installed?');
		}
		if(!spl_autoload_register('\\'.__CLASS__.'::autoload')){
			throw new Exception('Failed to register autoloader');
		}
    }
	/**
	 * Autoloader::autoload
	 *
	 * Loads the class source from the filesystem before class instantiation. Do not call this method directly, use Autoloader::init() to register this autoloader and let php do the work for you.
	 *
	 * @param	string	$className		The name of the class to locate and load.
	 * @return	boolean					False if the autoloader fails to locate the class.
	 */
    static function autoload($className) {
		# Translate $className into a real path on the filesystem
		$className = ltrim($className, '\\');
		$fileName  = '';
		$namespace = '';
		if ($lastNsPos = strripos($className, '\\')) {
			$namespace = substr($className, 0, $lastNsPos);
			$className = substr($className, $lastNsPos + 1);
			$fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
		}
		$fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
		# Check the possible paths in order to locate it
		$paths = explode(PATH_SEPARATOR, get_include_path());
		foreach($paths as $path){
			# Check the path entry is valid
			if(file_exists($path) && is_readable($path) && filetype($path) == 'dir'){
				# Check the file exists
				if(file_exists($path.DIRECTORY_SEPARATOR.$fileName) && is_readable($path.DIRECTORY_SEPARATOR.$fileName)){
					require($path.DIRECTORY_SEPARATOR.$fileName);
					return true;
				}
			}else{
				trigger_error('Directory in PATH \''.$path.'\' does not exist.', E_USER_WARNING);
			}
		}
		trigger_error('File \''.$fileName.'\' not found using paths ('.get_include_path().').', E_USER_WARNING);
		return false;
    }
}