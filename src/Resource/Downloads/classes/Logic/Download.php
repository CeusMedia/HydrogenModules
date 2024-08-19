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
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function addFileFromUpload( Logic_Upload $logicUpload, int|string $folderId = 0, ?string $description = NULL ): void
	{
		$logicUpload->checkSize( Logic_Upload::getMaxUploadSize(), TRUE );
//		$logicUpload->checkVirus( TRUE );
		$targetFile	= $this->getPathFromFolderId( $folderId, TRUE ).$logicUpload->getFileName();
		$logicUpload->saveTo( $targetFile );
		$this->modelFile->add( [
			'downloadFolderId'	=> $folderId,
			'rank'				=> $this->countFilesInFolder( $folderId ),
			'size'				=> $logicUpload->getFileSize(),
			'title'				=> $logicUpload->getFileName(),
			'description'		=> $description,
			'uploadedAt'		=> time()
		] );
		$this->updateNumber( $folderId, 'file' );
	}

	/**
	 *	@param		string 			$folder
	 *	@param		int|string		$parentId
	 *	@param		int				$type
	 *	@return		string
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function addFolder( string $folder, int|string $parentId = 0, int $type = 0 ): string
	{
		$path		= $this->getPathFromFolderId( $parentId );
		FolderEditor::createFolder( $this->path.$path.$folder );
		$newId	= $this->modelFolder->add( [
			'parentId'	=> (int) $parentId,
			'rank'		=> $this->countFoldersInFolder( $parentId ),
			'type'		=> $type,
			'title'		=> $folder,
			'createdAt'	=> time(),
		] );
		$this->updateNumber( $parentId, 'folder' );
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
				$stats->folders[]	= (object) ['title' => $folder->title, 'path' => $path];
			}
		}
		foreach( $files as $file ){
			if( !file_exists( $this->path.$path.$file->title ) ){
				$this->modelFile->remove( $file->downloadFileId );
				$stats->files[]	= (object) ['title' => $file->title, 'path' => $path];
			}
		}
	}

	/**
	 *	@param		int|string		$folderId
	 *	@return		int
	 */
	public function countFilesInFolder( int|string $folderId ): int
	{
		return $this->modelFile->count( ['downloadFolderId' => $folderId] );
	}

	/**
	 *	@param		int|string		$folderId
	 *	@return		int
	 */
	public function countFoldersInFolder(int|string $folderId ): int
	{
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
		return (object) ['folders' => $folders, 'files' => $files];
	}

	/**
	 *	@param		int|string		$fileId
	 *	@param		array			$data
	 *	@return		bool|NULL
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function editFile( int|string $fileId, array $data ): ?bool
	{
		return (bool) $this->modelFile->edit( $fileId, array_merge( $data, [
			'modifiedAt' => time()
		] ) );
	}

	/**
	 *	@param		int|string		$folderId
	 *	@param		array			$data
	 *	@return		bool|NULL
	 */
	public function editFolder( int|string $folderId, array $data ): ?bool
	{
		try{
			return (bool) $this->modelFolder->edit( $folderId, array_merge( $data, [
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
	 *	@return		array
	 */
	public function findFiles( array $conditions, array $orders = [], array $limits = [] ): array
	{
		return $this->modelFile->getAll( $conditions, $orders, $limits );
	}

	/**
	 *	@param		array		$conditions
	 *	@param		array		$orders
	 *	@param		array		$limits
	 *	@return		array
	 */
	public function findFolders( array $conditions, array $orders = [], array $limits = [] ): array
	{
		return $this->modelFolder->getAll( $conditions, $orders, $limits );
	}

	/**
	 *	@param		int|string		$folderId
	 *	@return		bool
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function folderPathExists( int|string $folderId ): bool
	{
		return file_exists( $this->getPathFromFolderId( $folderId, TRUE ) );
	}

	/**
	 *	@param		int|string		$fileId
	 *	@return		object|NULL
	 */
	public function getFile( int|string $fileId ): ?object
	{
		try{
			return $this->modelFile->get( $fileId );
		}
		catch( SimpleCacheInvalidArgumentException ){
		}
		return NULL;
	}

	/**
	 *	@param		int|string		$folderId
	 *	@return		object|NULL
	 */
	public function getFolder( int|string $folderId ): ?object
	{
		try{
			return $this->modelFolder->get( $folderId );
		}
		catch( SimpleCacheInvalidArgumentException  ){
		}
		return NULL;
	}

	/**
	 *	@param		int|string		$folderId
	 *	@param		bool			$withBasePath
	 *	@return		string
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function getPathFromFolderId( int|string $folderId, bool $withBasePath = FALSE ): string
	{
		$path	= '';
		while( $folderId ){
			$folder	= $this->modelFolder->get( $folderId );
			if( !$folder )
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
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function getStepsFromFolderId( int|string $folderId ): array
	{
		$steps		= [];
		while( $folderId ){
			$folder	= $this->modelFolder->get( $folderId );
			if( !$folder )
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
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function makeDownloadCount( int|string $fileId ): void
	{
		$file	= $this->getFile( $fileId );
		$this->modelFile->edit( $fileId, [
			'nrDownloads'	=> $file->nrDownloads + 1,
			'downloadedAt'	=> time(),
		] );
	}

	/**
	 *	@param		int|string		$fileId
	 *	@param		int|string		$folderId
	 *	@return		void
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function moveFile( int|string $fileId, int|string $folderId ): void
	{
		$file		= $this->getFile( $fileId );
		$path		= $this->getPathFromFolderId( $file->downloadFolderId, TRUE );
		$pathTarget	= $this->getPathFromFolderId( $folderId, TRUE );
		$editor		= new FileEditor( $path.$file->title );
		$editor->rename( $pathTarget.$file->title );
		$this->editFile( $fileId, ['downloadFolderId' => $folderId] );
		$this->updateNumbers( $file->downloadFolderId );
		$this->updateNumbers( $folderId );
	}

	/**
	 *	@param		int|string		$folderId
	 *	@param		int|string		$parentId
	 *	@return		void
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function moveFolder( int|string $folderId, int|string $parentId ): void
	{
		$folder		= $this->getFolder( $folderId );
		$path		= $this->getPathFromFolderId( $folder->parentId, TRUE );
		$pathTarget	= $this->getPathFromFolderId( $parentId, TRUE );
		$editor		= new FolderEditor( $path.$folder->title );
		$editor->move( $pathTarget );
		$this->updateNumbers( $folder->parentId );
		$this->updateNumbers( $parentId );
		$this->editFolder( $folderId, ['parentId' => $parentId] );
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
			$this->editFolder( $folderId, ['rank' => $rank] );
			$this->editFolder( $next->downloadFolderId, ['rank' => $folder->rank] );
		}
	}

	/**
	 *	@param		int|string		$fileId
	 *	@return		void
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function removeFile( int|string $fileId ): void
	{
		$file	= $this->getFile( $fileId );
		$path	= $this->path;
		if( $file->downloadFolderId )
			$path	= $this->getPathFromFolderId( $file->downloadFolderId, TRUE );
		@unlink( $path.$file->title );
		$this->modelFile->remove( $fileId );
		$this->updateNumber( $file->downloadFolderId, 'file', -1 );

	}

	/**
	 *	@param		int|string		$fileId
	 *	@param		string			$title
	 *	@return		void
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function renameFile( int|string $fileId, string $title ): void
	{
		$file	= $this->getFile( $fileId );
		$path	= $this->getPathFromFolderId( $file->downloadFolderId, TRUE );
		$editor	= new FileEditor( $path.$file->title );
		$editor->rename( $path.$title );
		$this->editFile( $fileId, ['title' => $title ] );
	}

	/**
	 *	@param		int|string		$folderId
	 *	@param		string			$title
	 *	@return		void
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function renameFolder( int|string $folderId, string $title ): void
	{
		$folder		= $this->getFolder( $folderId );
		$path		= $this->getPathFromFolderId( $folder->parentId, TRUE );
		$editor	= new FolderEditor( $path.$folder->title );
		$editor->rename( $path.$title );
		$this->editFolder( $folderId, ['title' => $title] );
	}

	/**
	 *	@param		int|string		$folderId
	 *	@return		void
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function removeFolder( int|string $folderId ): void
	{
		$folder	= $this->getFolder( $folderId );
		rmdir( $this->getPathFromFolderId( $folderId, TRUE ) );
		$this->modelFolder->remove( $folderId );
		$this->updateNumber( $folder->parentId, 'folder', -1 );
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
					$this->updateNumber( $parentId, 'folder' );
					$stats->folders[]	= (object) ['title' => $entryName, 'path' => $path];
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
					$this->updateNumber( $parentId, 'file' );
					$stats->files[]	= (object) ['title' => $entryName, 'path' => $path];
				}
			}
		}
	}

	/**
	 *	@param		int|string		$folderId
	 *	@return		void
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function updateNumbers( int|string $folderId ): void
	{
		if( $folderId ){
			$path		= $this->getPathFromFolderId( $folderId );
			$counts		= $this->countFilesAndFoldersInPath( $path, TRUE );
			$this->modelFolder->edit( $folderId, [
				'nrFolders'	=> $counts->folders,
				'nrFiles'	=> $counts->files,
			] );
			$folder	= $this->modelFolder->get( $folderId );
			if( $folder->parentId )
				$this->updateNumbers( $folder->parentId );
		}
	}

	/**
	 *	@param		int|string		$folderId
	 *	@param		string			$type
	 *	@param		int				$diff
	 *	@return		void
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function updateNumber( int|string $folderId, string $type, int $diff = 1 ): void
	{
		if( !in_array( $type, ['folder', 'file'] ) )
			throw new InvalidArgumentException( 'Type must be folder or file' );
		while( $folderId ){
			$folder	= $this->modelFolder->get( $folderId );
			if( !$folder )
				throw new RuntimeException( 'Invalid folder ID: %s', $folderId );
			$data	= match( $type ){
				'folder'	=> ['nrFolders' => $folder->nrFolders + $diff],
				'file'		=> ['nrFiles' => $folder->nrFiles + $diff],
			};
			$this->editFolder( $folderId, $data );
			$folderId	= $folder->parentId;
		}
	}

	protected function __onInit(): void
	{
		$this->modelFile	= new Model_Download_File( $this->env );
		$this->modelFolder	= new Model_Download_Folder( $this->env );
	}
}