<?php
/**
 * php CLI interface helper
 * http://php.net/manual/en/features.commandline.php and http://www.slideshare.net/jkeppens/php-in-the-dark has more cool stuff
 *
 * @package   SPL
 * @author	Tom Davidson <tom.davidson@bt.com>
 *
 * @todo	Use http://pear.php.net/package/Console_ProgressBar to show progress bars
 * @todo	Use http://pear.php.net/package/Console_Table to show tabulated data
 */
class CLI {
	/**
	 * CLI::$terminalCodes
	 *
	 * The codes to inject into output to change the output
	 *
	 * @var array
	 * @access private
	 */
	private $terminalCodes = array(
		'clearScreen'	=> "\033[H\033[2J",
		'colours'		=> array(
			'white'			=>	"\033[1;37m",
			'lightgray'		=>	"\033[0;37m",
			'darkgray'		=>	"\033[1;30m",
			'black'			=>	"\033[0;30m",
			'blue'			=>	"\033[0;34m",
			'lightblue'		=>	"\033[1;34m",
			'green'			=>	"\033[0;32m",
			'lightgreen'	=>	"\033[1;32m",
			'cyan'			=>	"\033[0;36m",
			'lightcyan'		=>	"\033[1;36m",
			'red'			=>	"\033[0;31m",
			'lightred'		=>	"\033[1;31m",
			'purple'		=>	"\033[0;35m",
			'lightpurple'	=>	"\033[1;35m",
			'brown'			=>	"\033[0;33m",
			'yellow'		=>	"\033[1;33m",
			'blackonred'	=>	"\033[41;30m",
			'whiteonblack'	=>	"\033[40;37m"
		)
	);
	/**
	 * CLI::$_instance
	 *
	 * The current instance of the CLI class [singleton]
	 *
	 * @var CLI
	 * @access private
	 */
	private static $_instance = null;
	/**
	 * CLI::$stylesheet
	 *
	 * The 'stylesheet' for ouput on the console.
	 *
	 * @var array
	 * @access private
	 */
	private static $stylesheet = array(
		'base'		=>	"\033[40;37m",
		'styles'	=>	array()
	);
	/**
	 * CLI::$executeAudit
	 *
	 * Audit trail of commands executed
	 *
	 * @var array
	 * @access private
	 */
	private $executeAudit = array();
	/**
	 * CLI::__construct
	 *
	 * Initialise the class & reads in the input. Should not be called directly, instead use CLI::getInstance()
	 *
	 * @todo Pull the command line arguments and STDIN into variables
	 */
	function __construct() {
	}
	/**
	 * CLI::getInstance
	 *
	 * Get the [singleton] instance of CLI
	 *
	 * @return class CLI
	 *
	 */
	function getInstance() {
		if(is_null(self::$_instance) || get_class(self::$_instance) != 'CLI'){
			self::$_instance = new CLI();
		}
		return self::$_instance;
	}
	/**
	 * CLI::setStylesheet
	 *
	 * Sets the stylesheet to use for console output
	 *
	 * @param array $stylesheet Associative array of tags and colours to use
	 * @return boolean Returns TRUE
	 *
	 */
	function setStylesheet($base, $stylesheet) {
		if(!is_string($base) || !is_array($stylesheet)){
			throw new Exception('CLI::setStylesheet argument 1 should be a string and argument 2 should be an associative array.');
		}
		if(!array_key_exists($base, $this->terminalCodes['colours'])){
			throw new Exception('CLI::setStylesheet base style is not a valid option.');
		}else{
			$this->stylesheet['base'] = $base;
		}
		foreach($stylesheet as $element => $style){
			if(!array_key_exists($style, $this->terminalCodes['colours'])){
				throw new Exception('CLI::setStylesheet style \''.$style.'\' on element \''.$element.'\' is not a valid option.');
			}else{
				$this->stylesheet['styles'][$element] = $style;
			}
		}
		return true;
	}
	/**
	 * CLI::isCli
	 *
	 * Checks to see if we are being run via the command line or via a browser
	 *
	 * @return boolean Returns TRUE if run from the command line
	 *
	 */
	function isCli() {
		if(php_sapi_name() == 'cli'){
			return true;
		}else{
			return false;
		}
	}
	/**
	 * CLI::execute
	 *
	 * Clear the screen
	 *
	 * @param string   $command The command to execute
	 * @param string[] $arguments Additional arguments
	 * @return boolean Returns TRUE
	 *
	 */
	public function execute($command, $arguments = array()) {
		if(empty($command)){
			throw new Exception('CLI::execute invalid command passed');
		}else{
			array_walk($arguments, array($this, 'escape'), true);
			array_push($this->executeAudit, $this->escape($command).' '.implode(' ', $arguments));
			ob_start();
			system($this->escape($command).' '.implode(' ', $arguments));
			$output = ob_get_contents();
			ob_end_clean();
			return $output;
		}
	}
	/**
	 * CLI::escape
	 *
	 * Make a command line execution safe
	 *
	 * @param string  $command The command to execute
	 * @param boolean $asArg Treat as an argument rather than a command
	 * @return string Returns the cli-safe command
	 *
	 */
	private function escape($command, $asArg = false) {
		if($asArg != true){
			return escapeshellcmd($command);
		}else{
			return escapeshellarg($command);
		}
	}
	/**
	 * CLI::clear
	 *
	 * Clear the screen
	 *
	 * @return boolean Returns TRUE
	 *
	 */
	public function clearScreen() {
		print $this->terminalCodes['clearScreen'];
		return true;
	}
	/**
	 * CLI::output
	 *
	 * Send data somewhere
	 *
	 * @param string $data The data to output
	 * @param string $stream The stream to send the data to, default is STDOUT
	 * @return boolean Returns TRUE
	 *
	 * @todo   Use PEAR::Console_Color (http://pear.php.net/package/Console_Color) instead of directly using control codes.
	 */
	public function output($data, $stream = 'stdout') {
		$streams = array(
			'stdout'	=>	'php://stdout',
			'stderr'	=>	'php://stderr',
		);
		if(!array_key_exists($stream, $streams)){
			throw new Exception('CLI::output unsupported stream \''.$stream.'\'');
		}
		$elements = array();
		$styles = array();
		foreach($this->stylesheet['styles'] as $element => $style){
			array_push($elements, '<'.$element.'>');
			array_push($styles, $this->terminalCodes['colours'][$style]);
			array_push($elements, '</'.$element.'>');
			array_push($styles, $this->terminalCodes['colours'][$this->stylesheet['base']]);
		}
		$data = str_replace($elements, $styles, $data);
		file_put_contents($streams[$stream], $data);
		return true;
	}
}
