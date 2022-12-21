<?php
/**
 *	PhantomJS Command Runner.
 *
 *	Example:
 *		$resource	= new Resource_PhantomJS( $env );
 *		$result		= $resource->execute('\www\test.js', 'http://mysite.com', 'another-arg');
 *
 */
class Resource_PhantomJS{

	/**	@var string Path to phantomjs binary */
	protected $binaryPath	= 'bin/phantomjs';

	/**	@var integer Debug mode level (0 or 1) */
	protected $debugLevel	= 0;

	/**
	 *	Constructor.
	 *
	 *	@access		public
	 *	@param		Environment		$env		Environment instance
	 *	@return		void
	 */
	public function __construct( $env ){
		$this->env		= $env;
		$moduleConfig	= $env->getConfig()->getAll( 'module.tool_phantomjs.', TRUE );
		$this->setBinaryPath( $moduleConfig->get( 'path.binary' ) );
	}

	/**
	 *	Execute a given JS file
	 *
	 *	This method should be called with the first argument
	 *	being the JS file you wish to execute. Additional
	 *	PhantomJS command line arguments can be passed
	 *	through as function arguments e.g.:
	 *
	 *     $command->execute('/path/to/my/script.js', 'arg1', 'arg2'[, ...])
	 *
	 *	The script tries to automatically decodde JSON
	 *	objects if the first character returned is a left
	 *	curly brace ({).
	 *
	 *	If debug mode is enabled, this method will return
	 *	the output of the command verbatim along with any
	 *	errors printed out to the shell. Don't use this mode
	 *	in production.
	 *
	 *	@param		string		Script file
	 *	@param		string		Arg, ...
	 *	@return		bool/array	False of failure, JSON array on success
	 */
	public function execute( $script ) {

		// Escape
		$arguments	= func_get_args();
		$command	= escapeshellcmd( $this->binaryPath." " . implode( ' ', $arguments ) );
		if( $this->debug )
			$command .= ' 2>&1';

		// Execute
		$result = shell_exec( $command );
		if( $this->debug )
			return $result;
		if( $result === NULL )
			return FALSE;

		// Return
		if( substr( $result, 0, 1 ) !== '{' )					 // not JSON
			return $result;
		$json = json_decode( $result, $as_array = TRUE );
		if( $json === NULL )
			return FALSE;
		return $json;
	}

	static public function getInstance( $env ){
		return new static( $env );
	}

	/**
	 *	Set path to PhantomJS binary.
	 *
	 *	@access		public
	 *	@param		string		$path		Path to phantomjs binary
	 *	@return		self
	 */
	public function setBinaryPath( $path ){
		$this->binaryPath	= $path;
		return $this;
	}

	/**
	 *	Set debug mode level.
	 *
	 *	@access		public
	 *	@param		int			$level		Debug mode level
	 *	@return		self
	 */
	public function setDebug( $level = 1 ){
		$this->debug	= $level;
		return $this;
	}
}
