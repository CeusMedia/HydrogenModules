<?php

use CeusMedia\Common\FS\File\Editor as FileEditor;
use CeusMedia\Common\FS\Folder\Editor as FolderEditor;
use CeusMedia\Common\FS\Folder\Lister as FolderLister;
use CeusMedia\Common\FS\Folder\RecursiveLister as RecursiveFolderLister;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Logic;
use Psr\SimpleCache\InvalidArgumentException as SimpleCacheInvalidArgumentException;

/**
 * Logic class for file and folder management
 */
class Logic_Download extends Logic
{
	protected const TYPE_FILE		= 0;
	protected const TYPE_FOLDER		= 1;


	/**	@var	Model_Download_File								$modelFile			Database model of files */
	protected Model_Download_File $modelFile;

	/**	@var	Model_Download_Folder							$modelFolder		Database model of folders */
	protected Model_Download_Folder $modelFolder;

	/**	@var	string											$path				Base path to files */
	protected string $path;

	/**	@var	array											$rights				List of access rights of current user */
	protected array $rights		= [];

	public function __construct( Environment $env, string $path )
	{
		parent::__construct( $env );
		$this->path		= $path;
	}

	/**
	 *	@param		Logic_Upload	$logicUpload
	 *	@param		int|string		$folderId
	 *	@param		string|NULL		$description
	 *	@return		void
	 */
	public function addFileFromUpload( Logic_Upload $logicUpload, int|string $folderId = 0, ?string $description = NULL ): void
	{
		$folder		= $this->getFolder( $folderId );
		if( NULL === $folder )
			throw new RuntimeException( 'Invalid folder ID' );

		$logicUpload->checkSize( Logic_Upload::getMaxUploadSize(), TRUE );
//		$logicUpload->checkVirus( TRUE );

		$targetFile	= $this->getPathFromFolderId( $folderId, TRUE ).$logicUpload->getFileName();
		$logicUpload->saveTo( $targetFile );
		$this->modelFile->add( [
			'downloadFolderId'	=> $folderId,
			'rank'				=> $this->countFilesInFolder( $folder ),
			'size'				=> $logicUpload->getFileSize(),
			'title'				=> $logicUpload->getFileName(),
			'description'		=> $description,
			'uploadedAt'		=> time()
		] );
		$this->updateNumber( $folder, self::TYPE_FILE );
	}

	/**
	 *	@param		string 			$folder
	 *	@param		int|string		$parentId
	 *	@param		int				$type
	 *	@return		int|string
	 *	@throws		ReflectionException
	 */
	public function addFolder( string $folder, int|string $parentId = 0, int $type = 0 ): int|string
	{
		$path		= $this->getPathFromFolderId( $parentId );
		FolderEditor::createFolder( $this->path.$path.$folder );
		$parent		= $this->getFolder( $parentId );
		$newId	= $this->modelFolder->add( new Entity_Download_Folder( [
			'parentId'	=> (int) $parentId,
			'rank'		=> $this->countFoldersInFolder( $parent ),
			'type'		=> $type,
			'title'		=> $folder,
			'createdAt'	=> time(),
		] ) );
		$this->updateNumber( $parent, self::TYPE_FOLDER );
		return $newId;
	}

	/**
	 *	@param		int|string $parentId
	 *	@param		string		$path
	 *	@param		object		$stats
	 *	@return		void
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function cleanRecursive( int|string $parentId, string $path, object $stats ): void
	{
		$path		= $this->getPathFromFolderId( $parentId );
		$folders	= $this->modelFolder->getAll( ['parentId' => $parentId] );
		$files		= $this->modelFile->getAll( ['downloadFolderId' => $parentId] );
		foreach( $folders as $folder ){
			$this->cleanRecursive( $folder->downloadFolderId, $path.$folder->title.'/', $stats );
			if( !file_exists( $this->path.$path.$folder->title ) ){
				$this->modelFolder->remove( $folder->downloadFolderId );
				$stats->folders[]	= (object) [
					'title'		=> $folder->title,
					'path'		=> $path,
				];
			}
		}
		foreach( $files as $file ){
			if( !file_exists( $this->path.$path.$file->title ) ){
				$this->modelFile->remove( $file->downloadFileId );
				$stats->files[]	= (object) [
					'title'		=> $file->title,
					'path'		=> $path,
				];
			}
		}
	}

	/**
	 *	@param		?Entity_Download_Folder	$folder
	 *	@return		int
	 */
	public function countFilesInFolder( ?Entity_Download_Folder $folder ): int
	{
		$folderId	= NULL !== $folder ? $folder->downloadFolderId : 0;
		return $this->modelFile->count( ['downloadFolderId' => $folderId] );
	}

	/**
	 *	@param		?Entity_Download_Folder	$folder
	 *	@return		int
	 */
	public function countFoldersInFolder( ?Entity_Download_Folder $folder ): int
	{
		$folderId	= NULL !== $folder ? $folder->downloadFolderId : 0;
		return $this->modelFolder->count( ['parentId' => $folderId] );
	}

	/**
	 *	@param		string		$path
	 *	@param		bool		$recursive
	 *	@return		object
	 */
	public function countFilesAndFoldersInPath( string $path, bool $recursive = FALSE ): object
	{
		$files		= $folders		= 0;
		$indexClass	= $recursive ? RecursiveFolderLister::class : FolderLister::class;
		foreach( $indexClass::getMixedList( $this->path.$path ) as $entry )
			$entry->isDir() ? $folders++ : $files++;
		return (object) [
			'folders'	=> $folders,
			'files'		=> $files
		];
	}

	/**
	 *	@param		Entity_Download_File	$file
	 *	@param		array					$data
	 *	@return		bool|NULL
	 */
	public function editFile( Entity_Download_File $file, array $data ): ?bool
	{
		return (bool) $this->modelFile->edit( $file->downloadFileId, array_merge( $data, [
			'modifiedAt' => time()
		] ) );
	}

	/**
	 *	@param		Entity_Download_Folder	$folder
	 *	@param		array					$data
	 *	@return		bool|NULL
	 */
	public function editFolder( Entity_Download_Folder $folder, array $data ): ?bool
	{
		try{
			return (bool) $this->modelFolder->edit( $folder->downloadFolderId, array_merge( $data, [
				'modifiedAt' => time()
			] ) );
		}
		catch( SimpleCacheInvalidArgumentException ){
		}
		return NULL;
	}

	/**
	 *	@param		array		$conditions
	 *	@param		array		$orders
	 *	@param		array		$limits
	 *	@return		array<Entity_Download_File>
	 */
	public function findFiles( array $conditions, array $orders = [], array $limits = [] ): array
	{
		return $this->modelFile->getAll( $conditions, $orders, $limits );
	}

	/**
	 *	@param		array		$conditions
	 *	@param		array		$orders
	 *	@param		array		$limits
	 *	@return		array<Entity_Download_Folder>
	 */
	public function findFolders( array $conditions, array $orders = [], array $limits = [] ): array
	{
		return $this->modelFolder->getAll( $conditions, $orders, $limits );
	}

	/**
	 *	@param		Entity_Download_Folder		$folder
	 *	@return		bool
	 */
	public function folderPathExists( Entity_Download_Folder $folder ): bool
	{
		return file_exists( $this->getPathFromFolderId( $folder->downloadFolderId, TRUE ) );
	}

	/**
	 *	@param		int|string		$fileId
	 *	@return		?Entity_Download_File
	 */
	public function getFile( int|string $fileId ): ?Entity_Download_File
	{
		try{
			/** @var ?Entity_Download_File $file */
			$file	= $this->modelFile->get( $fileId );
			return $file;
		}
		catch( SimpleCacheInvalidArgumentException ){
		}
		return NULL;
	}

	/**
	 *	@param		int|string		$folderId
	 *	@return		?Entity_Download_Folder
	 */
	public function getFolder( int|string $folderId ): ?Entity_Download_Folder
	{
		try{
			/** @var ?Entity_Download_Folder $folder */
			$folder	= $this->modelFolder->get( $folderId );
			return $folder;
		}
		catch( SimpleCacheInvalidArgumentException  ){
		}
		return NULL;
	}

	/**
	 *	@param		int|string		$folderId
	 *	@param		bool			$withBasePath
	 *	@return		string
	 */
	public function getPathFromFolderId( int|string $folderId, bool $withBasePath = FALSE ): string
	{
		$path	= '';
		while( $folderId ){
			$folder	= $this->modelFolder->get( $folderId );
			if( NULL === $folder )
				throw new RuntimeException( 'Invalid folder ID: %s', $folderId );
			$path		= $folder->title.'/'.$path;
			$folderId	= $folder->parentId;
		}
		return $withBasePath ? $this->path.$path : $path;
	}

	/**
	 *	@param		int|string		$parentId
	 *	@return		array
	 */
	public function getNestedFolderIds( int|string $parentId ): array
	{
		$list		= [];
		/** @var array<Entity_Download_Folder> $folders */
		$folders	= $this->modelFolder->getAllByIndex( 'parentId', $parentId );
		foreach( $folders as $folder ){
			$list[]	= $folder->downloadFolderId;
			foreach( $this->getNestedFolderIds( $folder->downloadFolderId ) as $id )
				$list[]	= $id;
		}
		return $list;
	}

	/**
	 *	@param		int|string		$folderId
	 *	@return		array
	 */
	public function getStepsFromFolderId( int|string $folderId ): array
	{
		$steps		= [];
		while( $folderId ){
			/** @var ?Entity_Download_Folder $folder */
			$folder	= $this->modelFolder->get( $folderId );
			if( NULL === $folder )
				throw new RuntimeException( 'Invalid folder ID: %s', $folderId );
			$steps[$folder->downloadFolderId]	= $folder;
			$folderId	= $folder->parentId;
		}
		return array_reverse( $steps );
	}

	/**
	 *	@param		int|string		$parentId
	 *	@param		int|string		$excludeFolderId
	 *	@param		int				$level
	 *	@return		array
	 */
	public function listFolderNested( int|string $parentId = 0, int|string $excludeFolderId = 0, int $level = 0 ): array
	{
		$list		= [];
		$orders		= ['title' => 'ASC'];
		/** @var array<Entity_Download_Folder> $folders */
		$folders	= $this->modelFolder->getAll( ['parentId' => $parentId], $orders );
		foreach( $folders as $folder ){
			if( $folder->downloadFolderId == $excludeFolderId )
				continue;
			$list[$folder->downloadFolderId]	= str_repeat( '- ', $level ).$folder->title;
			$children	= $this->listFolderNested( $folder->downloadFolderId, $excludeFolderId, $level + 1 );
			foreach( $children as $childId => $childLabel )
				$list[$childId]	= $childLabel;
		}
		return $list;
	}

	/**
	 *	@param		int|string		$fileId
	 *	@return		void
	 */
	public function makeDownloadCount( int|string $fileId ): void
	{
		/** @var ?Entity_Download_File $file */
		$file	= $this->getFile( $fileId );
		if( NULL !== $file )
			$this->modelFile->edit( $fileId, [
				'nrDownloads'	=> $file->nrDownloads + 1,
				'downloadedAt'	=> time(),
			] );
	}

	/**
	 *	@param		Entity_Download_File	$file
	 *	@param		Entity_Download_Folder	$folder
	 *	@return		void
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function moveFile( Entity_Download_File $file, Entity_Download_Folder $folder ): void
	{
		$path		= $this->getPathFromFolderId( $file->downloadFolderId, TRUE );
		$pathTarget	= $this->getPathFromFolderId( $folder->downloadFolderId, TRUE );
		$editor		= new FileEditor( $path.$file->title );
		$editor->rename( $pathTarget.$file->title );
		$this->editFile( $file, ['downloadFolderId' => $folder->downloadFolderId] );
		$this->updateNumbers( $this->getFolder( $file->downloadFolderId ) );
		$this->updateNumbers( $folder );
	}

	/**
	 *	@param		Entity_Download_Folder	$folder
	 *	@param		int|string				$parentId
	 *	@return		void
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function moveFolder( Entity_Download_Folder $folder, int|string $parentId ): void
	{
		$path		= $this->getPathFromFolderId( $folder->parentId, TRUE );
		$pathTarget	= $this->getPathFromFolderId( $parentId, TRUE );
		$editor		= new FolderEditor( $path.$folder->title );
		$editor->move( $pathTarget );
		$this->updateNumbers( $this->getFolder( $folder->parentId ) );
		$this->updateNumbers( $this->getFolder( $parentId ) );
		$this->editFolder( $folder, ['parentId' => $parentId] );
	}

	/**
	 *	@param		int|string		$folderId
	 *	@param		int				$direction
	 *	@return		void
	 */
	public function rankFolder( int|string $folderId, int $direction ): void
	{
		$folder		= $this->getFolder( $folderId );
		$rank		= $folder->rank + $direction;
		$conditions	= ['rank' => $rank, 'parentId' => $folder->parentId];
		$next		= current( $this->findFolders( $conditions ) );
		if( $next ){
			$this->editFolder( $folder, ['rank' => $rank] );
			$this->editFolder( $this->getFolder( $next->downloadFolderId ), ['rank' => $folder->rank] );
		}
	}

	/**
	 *	@param		int|string		$fileId
	 *	@return		void
	 */
	public function removeFile( int|string $fileId ): void
	{
		$file	= $this->getFile( $fileId );
		$path	= $this->path;
		if( $file->downloadFolderId )
			$path	= $this->getPathFromFolderId( $file->downloadFolderId, TRUE );
		@unlink( $path.$file->title );
		$this->modelFile->remove( $fileId );
		$this->updateNumber( $this->getFolder( $file->downloadFolderId ), self::TYPE_FILE, -1 );

	}

	/**
	 *	@param		Entity_Download_File	$file
	 *	@param		string					$title
	 *	@return		void
	 */
	public function renameFile( Entity_Download_File $file, string $title ): void
	{
		$path	= $this->getPathFromFolderId( $file->downloadFolderId, TRUE );
		$editor	= new FileEditor( $path.$file->title );
		$editor->rename( $path.$title );
		$file->title	= $title;
		$this->editFile( $file, ['title' => $title ] );
	}

	/**
	 *	@param		Entity_Download_Folder	$folder
	 *	@param		string					$title
	 *	@return		void
	 */
	public function renameFolder( Entity_Download_Folder $folder, string $title ): void
	{
		$path		= $this->getPathFromFolderId( $folder->parentId, TRUE );
		$editor	= new FolderEditor( $path.$folder->title );
		$editor->rename( $path.$title );
		$folder->title	= $title;
		$this->editFolder( $folder, ['title' => $title] );
	}

	/**
	 *	@param		Entity_Download_Folder	$folder
	 *	@return		void
	 */
	public function removeFolder( Entity_Download_Folder $folder ): void
	{
		rmdir( $this->getPathFromFolderId( $folder->downloadFolderId, TRUE ) );
		$this->modelFolder->remove( $folder->downloadFolderId );
		$this->updateNumber( $this->getFolder( $folder->parentId ), self::TYPE_FOLDER, -1 );
	}

	/**
	 *	@param		int|string		$parentId
	 *	@param		string			$path
	 *	@param		object			$stats
	 *	@return		void
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function scanRecursive( int|string $parentId, string $path, object $stats ): void
	{
		$index	= new DirectoryIterator( $this->path.$path );
		foreach( $index as $entry ){
			if( $entry->isDot() || str_starts_with( $entry->getFilename(), '.' ) )
				continue;
			$nrFolders	= $this->modelFolder->count( ['parentId' => $parentId] );
			$nrFiles	= $this->modelFile->count( ['downloadFolderId' => $parentId] );
			$entryName	= $entry->getFilename();
			if( $entry->isDir() ){
				$data	= [
					'parentId'	=> $parentId,
					'title'		=> $entryName
				];
				$folder	= $this->modelFolder->getByIndices( $data );
				if( $folder )
					$folderId	= $folder->downloadFolderId;
				else{
					$data['rank']		= ++$nrFolders;
					$data['createdAt']	= filemtime( $entry->getPathname() );
					$folderId			= $this->modelFolder->add( $data );
					$this->updateNumber( $this->getFolder( $parentId ), self::TYPE_FOLDER );
					$stats->folders[]	= (object) [
						'title'		=> $entryName,
						'path'		=> $path,
					];
				}
				$this->scanRecursive( $folderId, $path.$entryName.'/',  $stats );
			}
			else if( $entry->isFile() ){
				$data		= [
					'downloadFolderId'	=> $parentId,
					'title'				=> $entryName,
				];
				if( !$this->modelFile->count( $data ) ){
					$data['rank']		= ++$nrFiles;
					$data['size']		= filesize( $entry->getPathname() );
					$data['uploadedAt']	= filemtime( $entry->getPathname() );
					$this->modelFile->add( $data );
					$this->updateNumber( $this->getFolder( $parentId ), self::TYPE_FILE );
					$stats->files[]	= (object) [
						'title'		=> $entryName,
						'path'		=> $path,
					];
				}
			}
		}
	}

	/**
	 *	@param		Entity_Download_Folder	$folder
	 *	@return		void
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function updateNumbers( Entity_Download_Folder $folder ): void
	{
		$path		= $this->getPathFromFolderId( $folder->downloadFolderId );
		$counts		= $this->countFilesAndFoldersInPath( $path, TRUE );
		$this->modelFolder->edit( $folder->downloadFolderId, [
			'nrFolders'	=> $counts->folders,
			'nrFiles'	=> $counts->files,
		] );
		$folder	= $this->modelFolder->get( $folder->downloadFolderId );
		if( $folder->parentId )
			$this->updateNumbers( $this->getFolder( $folder->parentId ) );
	}

	/**
	 *	@param		Entity_Download_Folder	$folder
	 *	@param		int						$type
	 *	@param		int						$diff
	 *	@return		void
	 */
	public function updateNumber( Entity_Download_Folder $folder, int $type, int $diff = 1 ): void
	{
		if( !in_array( $type, [self::TYPE_FOLDER, self::TYPE_FILE] ) )
			throw new InvalidArgumentException( 'Type must be folder or file' );
		do{
			$data	= match( $type ){
				self::TYPE_FOLDER	=> ['nrFolders' => $folder->nrFolders + $diff],
				self::TYPE_FILE		=> ['nrFiles' => $folder->nrFiles + $diff],
			};
			$this->editFolder( $folder, $data );
			$folder	= $this->getFolder( $folder->parentId );
		}
		while( NULL !== $folder );
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->modelFile	= new Model_Download_File( $this->env );
		$this->modelFolder	= new Model_Download_Folder( $this->env );
	}
}