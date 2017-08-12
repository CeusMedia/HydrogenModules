<?php
/**
 *	PhantomJS Command Runner.
 *
 *	$resource	= new Resource_PhantomJS;
 *	$result		= $resource->execute('\www\test.js', 'http://mysite.com', 'another-arg');
 *
 */
class Resource_PhantomJS{

	/**	@var string Path to phantomjs binary */
	protected $bin		= '/usr/local/bin/phantomjs';


	/**	@var bool If true, all Command output is returned verbatim */
	protected $debug	= FALSE;

	/**
	 *	Constructor
	 *
	 *	@access		public
	 *	@param		string		$bin		Path to phantomjs binary
	 *	@param		boolean		$debug		Debug mode
	 *	@return	void
	 */
	public function __construct( $bin = NULL, $debug = NULL ){
		if( $bin !== NULL)
			$this->bin		= $bin;
		if( $debug !== NULL)
			$this->debug	= $debug;
	}

	/**
	 * Execute a given JS file
	 *
	 * This method should be called with the first argument
	 * being the JS file you wish to execute. Additional
	 * PhantomJS command line arguments can be passed
	 * through as function arguments e.g.:
	 *
	 *     $command->execute('/path/to/my/script.js', 'arg1', 'arg2'[, ...])
	 *
	 * The script tries to automatically decodde JSON
	 * objects if the first character returned is a left
	 * curly brace ({).
	 *
	 * If debug mode is enabled, this method will return
	 * the output of the command verbatim along with any
	 * errors printed out to the shell. Don't use this mode
	 * in production.
	 *
	 * @param string Script file
	 * @param string Arg, ...
	 * @return bool/array False of failure, JSON array on success
	 **/
	public function execute($script) {

		// Escape
		$args = func_get_args();
		$cmd = escapeshellcmd( "{$this->bin} " . implode( ' ', $args ) );
		if( $this->debug )
			$cmd .= ' 2>&1';

		// Execute
		$result = shell_exec( $cmd );
		if( $this->debug )
			return $result;
		if( $result === NULL)
			return FALSE;

		// Return
		if( substr($result, 0, 1) !== '{' )					 // not JSON
			return $result;
		$json = json_decode( $result, $as_array = TRUE );
		if( $json === NULL)
			return FALSE;
		return $json;

	}
}
