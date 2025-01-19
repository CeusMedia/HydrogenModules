<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Alg\ID;
use CeusMedia\Common\FS\Folder\Editor as FolderEditor;
use CeusMedia\Common\UI\Image;
use CeusMedia\Common\UI\Image\Processing as ImageProcessing;
use CeusMedia\HydrogenFramework\Logic;

class Logic_FileBucket extends Logic
{
	public const HASH_MD5			= 0;
	public const HASH_UUID			= 1;
	public const HASH_SHA1			= 2;

	protected string $filePath;
	protected int $hashFunction	= 0;
	protected Model_File $model;
	protected Dictionary $moduleConfig;

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->model		= new Model_File( $this->env );
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.resource_filebucket.', TRUE );
		$this->setHashFunction( match( strtoupper( $this->moduleConfig->get( 'hash', '' ) ) ){
			'UUID'	=> self::HASH_UUID,
			'SHA1'	=> self::HASH_SHA1,
			default	=> self::HASH_MD5
		} );
		$basePath	= $this->env->getConfig()->get( 'path.contents' );
		if( $this->env->getModules()->has( 'Resource_Frontend' ) )
			$basePath	= Logic_Frontend::getInstance( $this->env )->getPath( 'contents' );
		$this->filePath	= $basePath.$this->moduleConfig->get( 'path' );
		if( !file_exists( $this->filePath ) )
			FolderEditor::createFolder( $this->filePath );
		if( !file_exists( $this->filePath.'.htaccess' ) )
			file_put_contents( $this->filePath.'.htaccess', 'Deny from all'.PHP_EOL );
	}

	/**
	 *	@param		string		$sourceFilePath
	 *	@param		string		$uriPath
	 *	@param		string		$mimeType
	 *	@param		?string		$moduleId
	 *	@return		string
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function add( string $sourceFilePath, string $uriPath, string $mimeType, ?string $moduleId = NULL ): string
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
		return $this->model->add( Entity_File::fromArray( [
//			'creatorId'		=> 0,
			'moduleId'		=> $moduleId ?? '',
			'hash'			=> $hash,
			'mimeType'		=> $mimeType,
			'fileSize'		=> filesize( $sourceFilePath ),
			'filePath'		=> $parts->filePath,
			'fileName'		=> $parts->fileName,
			'createdAt'		=> filemtime( $sourceFilePath ),
			'modifiedAt'	=> filemtime( $sourceFilePath ),
		] ) );
	}

	/**
	 *	@param		int|string		$fileId
	 *	@return		?Entity_File
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function get( int|string $fileId ): ?Entity_File
	{
		/** @var ?Entity_File $entity */
		$entity	= $this->model->get( $fileId );
		return $entity;
	}

	/**
	 *	@param		string		$moduleId
	 *	@param		string		$filePath
	 *	@param		array		$orders
	 *	@param		array		$limits
	 *	@return		Entity_File[]
	 */
	public function getAllFromModuleAndPath( string $moduleId, string $filePath, array $orders = [], array $limits = [] ): array
	{
		return $this->getAllByIndices( [
			'moduleId'		=> $moduleId,
			'filePath'		=> $filePath,
		], $orders, $limits );
	}

	/**
	 *	@param		array		$indices
	 *	@param		array		$orders
	 *	@param		array		$limits
	 *	@return		Entity_File[]
	 */
	public function getAllByIndices( array $indices, array $orders = [], array $limits = [] ): array
	{
		if( !$orders )
			$orders		= ['filePath' => 'ASC', 'fileName' => 'ASC'];
		return $this->model->getAllByIndices( $indices, $orders, $limits );
	}

	/**
	 *	@param		string		$moduleId
	 *	@param		array		$orders
	 *	@param		array		$limits
	 *	@return		Entity_File[]
	 */
	public function getAllFromModule( string $moduleId, array $orders = [], array $limits = [] ): array
	{
		return $this->getAllByIndices( ['moduleId' => $moduleId], $orders, $limits );
	}

	/**
	 *	@param		string		$filePath
	 *	@param		array		$orders
	 *	@param		array		$limits
	 *	@return		Entity_File[]
	 */
	public function getAllFromPath( string $filePath, array $orders = [], array $limits = [] ): array
	{
		return $this->getAllByIndices( ['filePath' => $filePath], $orders, $limits );
	}

	/**
	 *	@param		string		$hash
	 *	@return		?Entity_File
	 */
	public function getByHash( string $hash ): ?Entity_File
	{
		/** @var ?Entity_File $entity */
		$entity	= $this->model->getByIndex( 'hash', $hash );
		return $entity;
	}

	/**
	 *	@param		string		$uriPath
	 *	@param		?string		$moduleId
	 *	@return		?Entity_File
	 */
	public function getByPath( string $uriPath, ?string $moduleId = NULL ): ?Entity_File
	{
		$parts		= $this->getFilePartsFromUriPath( $uriPath );
		$indices	= [
			'filePath'	=> $parts->filePath,
			'fileName'	=> $parts->fileName,
		];
		if( $moduleId )
			$indices['moduleId']	= $moduleId;
		/** @var ?Entity_File $entity */
		$entity	= $this->model->getByIndices( $indices );
		return $entity;
	}

	/**
	 *	@return		string
	 */
	public function getPath(): string
	{
		return $this->filePath;
	}

	/**
	 *	@param		int|string		$fileId
	 *	@param		int				$maxWidth
	 *	@param		int				$maxHeight
	 *	@param		mixed			$quality
	 *	@return		bool
	 *	@throws		InvalidArgumentException	if file is not an image
	 *	@throws		Exception					if image could not been created
	 */
	public function limitImageSize( int|string $fileId, int $maxWidth, int $maxHeight, $quality = NULL ): bool
	{
		$file		= $this->get( $fileId );
		if( !in_array( $file->mimeType, ['image/png', 'image/gif', 'image/jpeg'] ) )
			throw new InvalidArgumentException( 'File is not an image' );
		$image		= new Image( $this->getPath().$file->hash );
		if( $image->getWidth() <= $maxWidth && $image->getHeight() <= $maxHeight )
			return FALSE;
		$processor	= new ImageProcessing( $image );
		$processor->scaleDownToLimit( $maxWidth, $maxHeight, $quality );
		$image->save();
		$this->model->edit( $fileId, [
			'fileSize' => filesize( $this->getPath().$file->hash )
		] );
		return TRUE;
	}

	/**
	 *	@param		int|string		$fileId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function noteView( int|string $fileId ): void
	{
		if( $file = $this->get( $fileId ) )
			$this->model->edit( $fileId, [
				'viewedAt'	=> time(),
				'viewCount'	=> $file->viewCount + 1,
			] );
	}

	/**
	 *	@param		int|string		$fileId
	 *	@param		string			$sourceFilePath
	 *	@param		string|NULL		$mimeType
	 *	@return		string
	 *	@throws		DomainException
	 *	@throws		RuntimeException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function replace( int|string $fileId, string $sourceFilePath, ?string $mimeType = NULL ): string
	{
		$file	= $this->get( $fileId );
		if( !$file )
			throw new DomainException( 'Given source file is not existing' );
		if( !file_exists( $sourceFilePath ) )
			throw new RuntimeException( 'Given source file is not existing' );
		if( !is_readable( $sourceFilePath ) )
			throw new RuntimeException( 'Given source file is not readable' );
		$mimeType	= $mimeType ?: $file->mimeType;
		$this->remove( $fileId );
		$uriPath	= $file->filePath ? $file->filePath.'/'.$file->fileName : $file->fileName;
		return $this->add( $sourceFilePath, $uriPath, $mimeType, $file->moduleId );
	}

	/**
	 *	@param		int|string		$fileId
	 *	@param		string			$name
	 *	@return		bool
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function rename( int|string $fileId, string $name ): bool
	{
		return 1 === $this->model->edit( $fileId, ['fileName' => $name] );
	}

	/**
	 *	@param		int|string		$fileId
	 *	@return		bool
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function remove( int|string $fileId ): bool
	{
		$file	= $this->get( $fileId );
		if( !$file )
			throw new DomainException( 'Given source file is not existing' );
		@unlink( $this->getPath().$file->hash );
		return $this->model->remove( $fileId );
	}

	/**
	 *	@param		int		$function
	 *	@return		static
	 */
	public function setHashFunction( int $function ): static
	{
		$this->hashFunction	= $function;
		return $this;
	}


	//  --  PROTECTED  --  //


	protected function getFilePartsFromUriPath( string $uriPath ): object
	{
		$parts		= explode( "/", $uriPath );
		$fileName	= array_pop( $parts );
		$filePath	= join( "/", $parts );
		return (object) [
			'filePath'	=> $filePath,
			'fileName'	=> $fileName,
		];
	}

	/**
	 *	@return		string
	 */
	protected function getNewHash(): string
	{
		do{
			$hash		= md5( microtime( TRUE ) );
			if( self::HASH_UUID === $this->hashFunction )
				$hash	= ID::uuid();
		}
		while( $this->model->countByIndex( 'hash', $hash ) );
		return $hash;
	}
}
