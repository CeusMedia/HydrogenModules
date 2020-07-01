<?php
class Job_Job_Util extends Job_Abstract
{
//	protected $logic;

	/**
	 *	Returns current date time depending on format parameter.
	 *	Uses parameter --format (-f), default: 'r' (RFC 2822).
	 *	Supports all date formats (http://php.net/manual/de/function.date.php).
	 *	Supports format constants, like DATE_W3C.
	 *	Removes milliseconds (.v) below PHP version 7.
	 *	@access		public
	 *	@return		string		Current date time in requested format
	 *	@todo		use environment date after framework update, see below
	 */
	public function getDate()
	{
		$format	= 'r';
		if( $this->parameters->get( '-f' ) && !$this->parameters->get( '--format' ) )
			$this->parameters->set( '--format', $this->parameters->get( '-f' ) );
		if( $this->parameters->get( '--format' ) )
			$format	= $this->parameters->get( '--format' );
		if( preg_match( '/^[A-Z0-9_]+$/', $format ) && ADT_Constant::has( $format ) ){
			if( $this->verbose )Job.
				$this->out( 'Found format by constant.' );
			$format	= ADT_Constant::get( $format );
		}
		else if( version_compare( PHP_VERSION, '7.0', '<' ) ){
			if( $this->verbose )
				$this->out( 'Removing milliseconds for PHP < 7.' );
			$format	= preg_replace( '/\.v/', '', $format );
		}
		$this->results	= date_create()->format( $format );						//  @todo replace by line below after framework update
//		$this->results	= $this->env->date->now->format( $format );
		$this->out( $this->results );
	}

	public function getExtensionVersion()
	{
		if( !( $extensions	= $this->commands ) ){
			$this->out( 'No extension(s) given' );
			return;
		}
		foreach( $extensions as $extension ){
			$version	= $this->shortenVersion( phpversion( $extension ) );
			if( count( $extensions ) > 1 )
				$version		= $extension.': '.$version;
			$this->out( $version );
		}
	}

	public function getPhpVersion()
	{
		$phpVersion		= phpversion();
		$this->results	= (object) array(
			'full'	=> $phpVersion,
			'short'	=> $this->shortenVersion( $phpVersion ),
		);
		$this->out( $this->results->short );
	}

	protected function shortenVersion( $version ): stro
	{
		return preg_replace( '/(-.+)$/', '', $version );
	}

	//  --  PROTECTED  --  //

/*	protected function __onInit(): self
	{
		$this->options	= $this->env->getConfig()->getAll( 'module.resource_cache.', TRUE );
		$this->logic	= $this->env->getLogic()->get( 'Job' );
	}*/
}
