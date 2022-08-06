<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Logic;

class Logic_FileBucket extends Logic
{
	const HASH_MD5			= 0;
	const HASH_UUID			= 1;

	protected $env;
	protected $filePath;
	protected $hashFunction	= 0;
	protected $model;

	public function __construct( Environment $env )
	{
		$this->env		= $env;
		$this->model	= new Model_File( $this->env );
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.resource_filebucket.', TRUE );
		switch( strtoupper( $this->moduleConfig->get( 'hash' ) ) ){
			case 'UUID':
				$this->setHashFunction( self::HASH_UUID );
				break;
			case 'MD5':
			default:
				$this->setHashFunction( self::HASH_MD5 );
		}
		$basePath	= $this->env->getConfig()->get( 'path.contents' );
		if( $this->env->getModules()->has( 'Resource_Frontend' ) )
			$basePath	= Logic_Frontend::getInstance( $this->env )->getPath( 'contents' );
		$this->filePath	= $basePath.$this->moduleConfig->get( 'path' );
		if( !file_exists( $this->filePath ) )
			\FS_Folder_Editor::createFolder( $this->filePath );
		if( !file_exists( $this->filePath.'.htaccess' ) )
			file_put_contents( $this->filePath.'.htaccess', 'Deny from all'.PHP_EOL );
	}

	public function add( $sourceFilePath, $uriPath, $mimeType, $moduleId = NULL )
	{
		if( !file_exists( $sourceFilePath ) )
			throw new RuntimeException( 'Given source file is not existing' );
		if( !is_readable( $sourceFilePath ) )
			throw new RuntimeException( 'Given source file is not readable' );

		$parts		= $this->getFilePartsFromUriPath( $uriPath );
		$hash		= $this->getNewHash();
		if( !@copy( $sourceFilePath, $this->filePath.$hash ) )
			throw new RuntimeException( 'Copying file to bucket failed' );
		clearstatcache();
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

	public function get( $fileId )
	{
		return $this->model->get( $fileId );
	}

	public function getAllFromModuleAndPath( $moduleId, $filePath, $orders = [], $limits = [] )
	{
		return $this->getAllByIndices( array(
			'moduleId'		=> $moduleId,
			'filePath'		=> $filePath,
		), $orders, $limits );
	}

	public function getAllByIndices( $indices, $orders = [], $limits = [] )
	{
		if( !$orders )
			$orders		= array( 'filePath' => 'ASC', 'fileName' => 'ASC' );
		return $this->model->getAllByIndices( $indices, $orders, $limits );
	}

	public function getAllFromModule( $moduleId, $orders = [], $limits = [] )
	{
		return $this->getAllByIndices( array( 'moduleId' => $moduleId ), $orders, $limits );
	}

	public function getAllFromPath( $filePath, $orders = [], $limits = [] )
	{
		return $this->getAllByIndices( array( 'filePath' => $filePath ), $orders, $limits );
	}

	public function getByHash( $hash )
	{
		return $this->model->getByIndex( 'hash', $hash );
	}

	public function getByPath( $uriPath, $moduleId = NULL )
	{
		$parts		= $this->getFilePartsFromUriPath( $uriPath );
		$indices	= array(
			'filePath'	=> $parts->filePath,
			'fileName'	=> $parts->fileName,
		);
		if( $moduleId )
			$indices['moduleId']	= $moduleId;
		return $this->model->getByIndices( $indices );
	}

	protected function getFilePartsFromUriPath( $uriPath )
	{
		$parts		= explode( "/", $uriPath );
		$fileName	= array_pop( $parts );
		$filePath	= join( "/", $parts );
		return (object) array(
			'filePath'	=> $filePath,
			'fileName'	=> $fileName,
		);
	}

	protected function getNewHash()
	{
		do{
			$hash		= md5( microtime( TRUE ) );
			if( $this->hashFunction === self::HASH_UUID )
				$hash	= Alg_ID::uuid();
		}
		while( $this->model->countByIndex( 'hash', $hash ) );
		return $hash;
	}

	public function getPath()
	{
		return $this->filePath;
	}

	public function limitImageSize( $fileId, $maxWidth, $maxHeight, $quality = NULL )
	{
		$file		= $this->get( $fileId );
		if( !in_array( $file->mimeType, array( 'image/png', 'image/gif', 'image/jpeg' ) ) )
			throw new Exception( 'File is not an image' );
		$image		= new UI_Image( $this->getPath().$file->hash );
		if( $image->getWidth() <= $maxWidth && $image->getHeight() <= $maxHeight )
			return FALSE;
		$processor	= new UI_Image_Processing( $image );
		$processor->scaleDownToLimit( $maxWidth, $maxHeight, $quality );
		$image->save();
		$this->model->edit( $fileId, array(
			'fileSize' => filesize( $this->getPath().$file->hash )
		) );
		return TRUE;
	}

	public function noteView( $fileId )
	{
		if( $file = $this->get( $fileId ) )
			$this->model->edit( $fileId, array(
				'viewedAt'	=> time(),
				'viewCount'	=> $file->viewCount + 1,
			) );
	}

	public function replace( $fileId, $sourceFilePath, $mimeType = NULL )
	{
		$file	= $this->get( $fileId );
		if( !$file )
			throw new DomainException( 'Given source file is not existing' );
		if( !file_exists( $sourceFilePath ) )
			throw new RuntimeException( 'Given source file is not existing' );
		if( !is_readable( $sourceFilePath ) )
			throw new RuntimeException( 'Given source file is not readable' );
		$mimeType	= $mimeType ? $mimeType : $file->mimeType;
		$this->remove( $fileId );
		$uriPath	= $file->filePath ? $file->filePath.'/'.$file->fileName : $file->fileName;
		return $this->add( $sourceFilePath, $uriPath, $mimeType, $file->moduleId );
	}

	public function rename( $fileId, $name )
	{
		$this->model->edit( $fileId, array( 'fileName' => $name ) );
	}

	public function remove( $fileId )
	{
		$file	= $this->get( $fileId );
		if( !$file )
			throw new DomainException( 'Given source file is not existing' );
		@unlink( $this->getPath().$file->hash );
		return $this->model->remove( $fileId );
	}

	public function setHashFunction( $function )
	{
		if( !is_int( $function ) )
			throw new InvalidArgumentException( 'Hash function must be an integer (see constants of Logic_File)' );
		$this->hashFunction	= $function;
	}
}
