<?php
/**
 * Logger
 *
 * PSR-3 (https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md) compatable logger
 *
 * To use:
 * 	`\TomDavidson\Components\Utilities\Logger::warning('your message here');`
 *
 * Dependancies:
 *	php 5.3.0+
 *	php SPL
 *  php Psr
 *
 * @package		Components
 * @subpackage	Utilities
 * @author		Tom Davidson <tom@davidson.me.uk>
 */
namespace TomDavidson\Components\Utilities;
abstract class Logger implements \Psr\Log\LoggerInterface
{
	/**
	 * Logger::log
	 *
	 * Logs a message with a severity level
	 *
	 * @param	string	$level		The level of the message.
	 * @param	variant	$message	The message to write or the object that can be handled as a string.
	 * @return	null
	 */
	public function log($level, $message, array $context = array()){
		// Check the $level is valid
		if($level == null || empty($level)){
			throw new \Psr\Log\InvalidArgumentException('Log level must be specified (null or empty given)');
		}elseif(!in_array($level, array('debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency'))){
			throw new \Psr\Log\InvalidArgumentException('Invalid log level: '.$level);
		}
		// Check the $message is valid
		if(is_object($message) && method_exists($message, '__toString()')) {
			$message = $message->__toString();
		}
		if(!is_string($message)){
			$type = gettype($message);
			if($type == 'object'){
				$type = get_class($message).' object';
			}
			throw new \Psr\Log\InvalidArgumentException('Message is not a string (or an object that can __toString()), it\'s a '.$type);
		}
		// Parse out the placeholders
		if(is_array($context) && count($context)>0){
			$message = self::interpolate($message, $context);
		}
		// Do the actual logging
		switch($level){
			case '':
				echo $level.': '.$message."\n";
			break;
			default:
				if(php_sapi_name() === 'cli'){
					fwrite(STDERR, $level.': '.$message."\n");
				}else{
					error_log($level.': '.$message."\n");
				}
			break;
		}
	}
	/**
	 * Logger::interpolate
	 *
	 * Interpolates context values into the message placeholders.
	 *
	 * @param	string	$message	The message with placeholders.
	 * @param	array	$context	The data to replace into the message.
	 * @return	string				The message with data.
	 */
	private function interpolate($message, array $context = array()) {
		// build a replacement array with braces around the context keys
		$replace = array();
		foreach ($context as $key => $val) {
			$replace['{' . $key . '}'] = $val;
		}
		// interpolate replacement values into the message and return
		return strtr($message, $replace);
	}
	public function debug($message, array $context = array()){
		self::log('debug', $message, $context);
	}
	public function info($message, array $context = array()){
		self::log('info', $message, $context);
	}
	public function notice($message, array $context = array()){
		self::log('notice', $message, $context);
	}
	public function warning($message, array $context = array()){
		self::log('warning', $message, $context);
	}
	public function error($message, array $context = array()){
		self::log('error', $message, $context);
	}
	public function critical($message, array $context = array()){
		self::log('critical', $message, $context);
	}
	public function alert($message, array $context = array()){
		self::log('alert', $message, $context);
	}
	public function emergency($message, array $context = array()){
		self::log('emergency', $message, $context);
	}
}