<?php

use CeusMedia\HydrogenFramework\Environment;

/**
 *	PhantomJS Command Runner.
 *
 *	Example:
 *		$resource	= new Resource_PhantomJS( $env );
 *		$result		= $resource->execute('\www\test.js', 'http://mysite.com', 'another-arg');
 *
 */
class Resource_PhantomJS
{
	protected Environment $env;

	/**	@var string Path to phantomjs binary */
	protected string $binaryPath	= 'bin/phantomjs';

	/**	@var integer Debug mode level (0 or 1) */
	protected int $debugLevel		= 0;

	protected ?string $script		= NULL;

	/**
	 *	Constructor.
	 *
	 *	@access		public
	 *	@param		Environment		$env		Environment instance
	 *	@return		void
	 */
	public function __construct( Environment $env )
	{
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
	 *     $result = Resource_PhantomJS::getInstance( $env )
	 *               ->setScript('/path/to/my/script.js')
	 *               ->execute('arg1', 'arg2'[, ...]);
	 *
	 *	The script tries to automatically decode JSON
	 *	objects if the first character returned is a left
	 *	curly brace ({).
	 *
	 *	If debug mode is enabled, this method will return
	 *	the output of the command verbatim along with any
	 *	errors printed out to the shell. Don't use this mode
	 *	in production.
	 *
	 *	@return		bool|array	False of failure, JSON array on success
	 */
	public function execute(): bool|array
	{
		if( NULL === $this->script )
			throw new RuntimeException( 'No script to execute set' );

		// Escape
		$arguments	= func_get_args();
		$command	= escapeshellcmd( implode( ' ', [
			$this->binaryPath,
			$this->script,
			implode( ' ', $arguments )
		] ) );
		if( $this->debugLevel )
			$command .= ' 2>&1';

		// Execute
		$result = shell_exec( $command );
		if( $this->debugLevel )
			return $result;
		if( $result === NULL )
			return FALSE;

		// Return
		if( !str_starts_with( $result, '{' ) )					 // not JSON
			return $result;
		$json = json_decode( $result, TRUE );
		if( $json === NULL )
			return FALSE;
		return $json;
	}

	/**
	 *	@param		Environment		$env
	 *	@return		self
	 */
	public static function getInstance( Environment $env ): self
	{
		return new self( $env );
	}

	/**
	 *	Set path to PhantomJS binary.
	 *
	 *	@access		public
	 *	@param		string		$path		Path to phantomjs binary
	 *	@return		self
	 */
	public function setBinaryPath( string $path ): self
	{
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
	public function setDebug( int $level = 1 ): self
	{
		$this->debugLevel	= $level;
		return $this;
	}

	/**
	 *	JavaScript file to execute.
	 *
	 *	@param		string		$script		JavaScript file to execute
	 *	@return		self
	 */
	public function setScript( string $script ): self
	{
		$this->script	= $script;
		return $this;
	}
}
