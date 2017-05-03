<?php
class Logic_FileBucket{

	const HASH_MD5			= 0;
	const HASH_UUID			= 1;

	protected $env;
	protected $filePath;
	protected $hashFunction	= 0;
	protected $model;

	public function __construct( CMF_Hydrogen_Environment_Abstract $env ){
		$this->env		= $env;
		$this->model	= new Model_File( $this->env );
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.resource_file.', TRUE );
		switch( strtoupper( $this->moduleConfig->get( 'hash' ) ) ){
			case 'UUID':
				$this->setHashFunction( Logic_File::HASH_UUID );
				break;
			case 'MD5':
			default:
				$this->setHashFunction( Logic_File::HASH_MD5 );
		}
		$basePath	= $this->env->getConfig()->get( 'path.contents' );
		if( $this->env->getModules()->has( 'Resource_Frontend' ) )
			$basePath	= Logic_Frontend::getInstance( $this->env )->getPath( 'contents' );
		$this->filePath	= $basePath.$this->moduleConfig->get( 'path' );
	}

	public function getPath(){
		return $this->filePath;
	}

	public function add( $sourceFilePath, $uriPath, $mimeType, $moduleId = NULL ){
		if( !file_exists( $sourceFilePath ) )
			throw new RuntimeException( 'Given source file is not existing' );
		if( !is_readable( $sourceFilePath ) )
			throw new RuntimeException( 'Given source file is not readable' );

		$parts		= $this->getFilePartsFromUriPath( $uriPath );
		$hash		= $this->getNewHash();
		copy( $sourceFilePath, $this->filePath.$hash );
		$data	= array(
//			'creatorId'		=> 0,
			'moduleId'		=> $moduleId,
			'hash'			=> $hash,
			'mimeType'		=> $mimeType,
			'fileSize'		=> filesize( $sourceFilePath ),
			'filePath'		=> $parts->filePath,
			'fileName'		=> $parts->fileName,
			'createdAt'		=> filemtime( $sourceFilePath ),
			'modifiedAt'	=> filemtime( $sourceFilePath ),
		);
		return $this->model->add( $data );
	}

	public function getAllFromModuleAndPath( $moduleId, $filePath, $orders = array(), $limits = array() ){
		return $this->getAllByIndices( array(
			'moduleId'		=> $moduleId,
			'filePath'		=> $filePath,
		), $orders, $limits );
	}

	public function getAllByIndices( $indices, $orders = array(), $limits = array() ){
		if( !$orders )
			$orders		= array( 'filePath' => 'ASC', 'fileName' => 'ASC' );
		return $this->model->getAllByIndices( $indices, $orders, $limits );
	}

	public function getAllFromModule( $moduleId, $orders = array(), $limits = array() ){
		return $this->getAllByIndices( array( 'moduleId' => $moduleId ), $orders, $limits );
	}

	public function getAllFromPath( $filePath, $orders = array(), $limits = array() ){
		return $this->getAllByIndices( array( 'filePath' => $filePath ), $orders, $limits );
	}

	public function getByHash( $hash ){
		return $this->model->getByIndex( 'hash', $hash );
	}

	public function getByPath( $uriPath ){
		$parts		= $this->getFilePartsFromUriPath( $uriPath );
		return $this->model->getByIndices( array(
			'filePath'		=> $parts->filePath,
			'fileName'		=> $parts->fileName,
		) );
	}

	protected function getFilePartsFromUriPath( $uriPath ){
		$parts		= explode( "/", $uriPath );
		$fileName	= array_pop( $parts );
		$filePath	= join( "/", $parts );
		return (object) array(
			'filePath'	=> $filePath,
			'fileName'	=> $fileName,
		);
	}

	protected function getNewHash(){
		do{
			$hash		= md5( microtime( TRUE ) );
			if( $this->hashFunction === self::HASH_UUID )
				$hash	= Alg_ID::uuid();
		}
		while( $this->model->countByIndex( 'hash', $hash ) );
		return $hash;
	}

	public function replace( $fileId, $sourceFilePath, $uriPath = NULL ){

	}

	public function remove( $fileId ){

	}

	public function setHashFunction( $function ){
		if( !is_int( $function ) )
			throw new InvalidArgumentException( 'Hash function must be an integer (see constants of Logic_File)' );
		$this->hashFunction	= $function;
	}
}
?>
