<?php
use CeusMedia\Common\FS\File\Reader as FileReader;
use CeusMedia\Common\FS\File\Writer as FileWriter;
use CeusMedia\Common\FS\File\JSON\Reader as JsonFileReader;

class Model_Oauth_ProviderDefault
{
	protected $filePath		= 'config/oauth2_providers.json';

	protected $providers	= [];

	public function __construct()
	{
		if( !file_exists( $this->filePath ) )
			FileReader::save( $this->filePath, '[]' );
		$this->read();
	}

	public function get( string $providerKey )
	{
		if( !$this->has( $providerKey ) )
			throw new RangeException( 'Invalid provider key: '.$providerKey );
		return $this->providers[$providerKey];
	}

	public function getAll(): array
	{
		return $this->providers;
	}

	public function has( string $providerKey ): bool
	{
		return array_key_exists( $providerKey, $this->providers );
	}

	/**
	 *	Save changed provier.
	 *	Not used right now.
	 *	@access		public
	 *	@param		string		$providerKey		Key of provider default to change
	 *	@param		array		$changes			Map of changed to apply on provider default
	 *	@return		integer		Number of bytes saved to file.
	 */
	public function set( string $providerKey, array $changes = [] )
	{
		$currentValues	= $this->get( $providerKey );
		$newValues		= array_merge( (array) $currentValues, (array) $changes );
		if( $currentValues === $newValues )
			return 0;
		$this->providers[$providerKey]	= (object) $values;
		FileWriter::save( $this->filePath, $this->providers );
	}

	/*  --  PROTECTED  --  */

	protected function read()
	{
		$reader				= new JsonFileReader( $this->filePath );
		$this->providers	= [];
		foreach( $reader->read( FALSE ) as $provider ){
			$key	= strtolower( $provider->title );
			if( !isset( $provider->options ) )
				$provider->options	= (object) array();
			if( !isset( $provider->scopes ) )
				$provider->scopes	= [];
			$this->providers[$key]	= $provider;
		}
		ksort( $this->providers );
	}
}
