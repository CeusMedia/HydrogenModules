<?php
class Model_Oauth_ProviderDefault{

	protected $filePath		= 'config/oauth2_providers.json';
	protected $providers	= array();

	public function __construct(){
		if( !file_exists( $this->filePath ) )
			FS_File_Reader::save( $this->filePath, '[]' );
		$this->read();
	}

	public function get( $providerKey ){
		if( !$this->has( $providerKey ) )
			throw new RangeException( 'Invalid provider key: '.$providerKey );
		return $this->providers[$providerKey];
	}

	public function getAll(){
		return $this->providers;
	}

	public function has( $providerKey ){
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
	public function set( $providerKey, $changes = array() ){
		$currentValues	= $this->get( $providerKey );
		$newValues		= array_merge( (array) $currentValues, (array) $changes );
		if( $currentValues === $newValues )
			return 0;
		$this->providers[$providerKey]	= (object) $values;
		FS_File_Writer::save( $this->filePath, $this->providers );
	}

	/*  --  PROTECTED  --  */
	protected function read(){
		$reader				= new FS_File_JSON_Reader( $this->filePath );
		$this->providers	= array();
		foreach( $reader->read( FALSE ) as $provider ){
			$key	= strtolower( $provider->title );
			if( !isset( $provider->options ) )
				$provider->options	= (object) array();
			if( !isset( $provider->scopes ) )
				$provider->scopes	= array();
			$this->providers[$key]	= $provider;
		}
		ksort( $this->providers );
	}
}
