<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Net\HTTP\Download as HttpDownload;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger;

class Controller_Info_File extends Controller
{
	/**	@var	Messenger								$messenger	*/
	protected Messenger $messenger;

	/**	@var	Logic_Download							$logic				Logic class for file and folder management */
	protected Logic_Download $logic;

	/**	@var	Dictionary								$options			Module configuration object */
	protected Dictionary $options;

	/**	@var	string									$path				Base path to files */
	protected string $path;

	/**	@var	HttpRequest								$request			Object to map request parameters */
	protected HttpRequest $request;

	/**	@var	array									$rights				List of access rights of current user */
	protected array $rights		= [];

	protected object $messages;

	/**
	 *	@param		int|string|NULL		$folderId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function addFolder( int|string|NULL $folderId = NULL ): void
	{
		$path		= $this->logic->getPathFromFolderId( $folderId );
		$folder		= trim( $this->request->get( 'folder' ) );
		if( preg_match( "/[\/\?:]/", $folder) ){
			$this->messenger->noteError( 'Folgende Zeichen sind in Ordnernamen nicht erlaubt: / : ?' );
			$url	= ( $folderId ?: NULL ).'?input_folder='.rawurlencode( $folder );
			$this->restart( $url, TRUE );
		}
		if( file_exists( $this->path.$path.$folder ) ){
			$this->messenger->noteError( sprintf( 'Ein Eintrag <small>(ein Ordner oder eine Datei)</small> mit dem Namen "%s" existiert in diesem Ordner bereits.', $folder ) );
			$this->restart( $folderId.'?input_folder='.rawurlencode( $folder ), TRUE );
		}
		$this->logic->addFolder( $folder, $folderId );
		$this->messenger->noteSuccess( 'Ordner <b>"%s"</b> hinzugefügt.', $folder );
		$this->restart( 'index/'.$folderId, TRUE );
	}

	/**
	 *	@param		int|string		$fileId
	 *	@return		never
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function deliver( int|string $fileId ): never
	{
		$file		= $this->logic->getFile( $fileId );
		if( !$file ){
			$this->messenger->noteError( 'Invalid file ID: %s', $fileId );
			$this->restart( NULL, TRUE );
		}
		$path	= getCwd().'/'.$this->logic->getPathFromFolderId( $file->downloadFolderId, TRUE );
		if( !file_exists( $path.$file->title ) ){
			$this->messenger->noteError( 'Die Datei wurde nicht am Speicherort gefunden. Bitte informieren Sie den Administrator!' );
			$this->restart( 'index/'.$file->downloadFolderId, TRUE );
		}
		$mimeType	= mime_content_type( $path.$file->title );
		header( 'Content-Type: '.$mimeType );
		header( 'Content-Length: '.filesize( $path.$file->title ) );
		header( 'Content-Disposition: inline; filename="'.addslashes( $file->title ).'"' );
		$fp = @fopen( $path.$file->title, "rb" );
		if( !$fp )
			header("HTTP/1.0 500 Internal Server Error");
		fpassthru( $fp );
		exit;
	}

	/**
	 *	@param		int|string		$fileId
	 *	@return		never
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function download( int|string $fileId ): never
	{
		$file		= $this->logic->getFile( $fileId );
		if( !$file ){
			$this->messenger->noteError( 'Invalid file ID: %s', $fileId );
			$this->restart( NULL, TRUE );
		}
		$path	= $this->logic->getPathFromFolderId( $file->downloadFolderId, TRUE );
		if( !file_exists( $path.$file->title ) ){
			$this->messenger->noteError( 'Die Datei wurde nicht am Speicherort gefunden. Bitte informieren Sie den Administrator!' );
			$this->restart( 'index/'.$file->downloadFolderId, TRUE );
		}
		$this->logic->makeDownloadCount( $fileId );
		HttpDownload::sendFile( $path.$file->title );
		exit;
	}

	/**
	 *	@param		int|string		$fileId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function editFile( int|string $fileId ): void
	{
		$file		= $this->logic->getFile( $fileId );
		if( !$file ){
			$this->messenger->noteError( 'Invalid file ID: %s', $fileId );
			$this->restart( NULL, TRUE );
		}
		if( $this->request->getMethod()->isPost() && $this->request->has( 'save' ) ){

			$title		= $this->request->get( 'title' );
			$folderId	= $this->request->get( 'folderId' );
			if( $title != $file->title ){
				$this->logic->renameFile( $fileId, $title );
			}
			if( $folderId != $file->downloadFolderId ){
				if( !$this->logic->folderPathExists( $folderId ) ){
					$this->messenger->noteError( 'Target folder is not existing' );
					$this->restart( 'editFile/'.$fileId, TRUE );
				}
				$this->logic->moveFile( $fileId, $folderId );
			}
			$this->restart( 'index/'.$file->downloadFolderId, TRUE );
		}
		$this->addData( 'file', $file );
		$this->addData( 'folderId', -1 );//(int) $file->downloadFolderId );
		$this->addData( 'folderPath', $this->logic->getPathFromFolderId( $file->downloadFolderId ) );
		$this->addData( 'folders', $this->logic->listFolderNested() );
		$this->addData( 'rights', $this->rights );
	}

	/**
	 *	@param		int|string		$folderId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function editFolder( int|string $folderId ): void
	{
		$folder		= $this->logic->getFolder( $folderId );
		if( !$folder ){
			$this->messenger->noteError( 'Invalid folder ID: %s', $folderId );
			$this->restart( NULL, TRUE );
		}
		if( $this->request->getMethod()->isPost() && $this->request->has( 'save' ) ){
			$title		= $this->request->get( 'title' );
			$parentId	= $this->request->get( 'parentId' );
			if( $title != $folder->title ){
				$this->logic->renameFolder( $folderId, $title );
			}
			if( $parentId != $folder->parentId ){
				if( !$this->logic->folderPathExists( $parentId ) ){
					$this->messenger->noteError( 'Target folder is not existing' );
					$this->restart( 'editFolder/'.$folderId, TRUE );
				}
				$this->logic->moveFolder( $folderId, $parentId );
			}
			$this->restart( 'index/'.$folder->parentId, TRUE );
		}
		$files	= $this->logic->findFiles( ['downloadFolderId' => $folderId], ['title' => 'ASC'] );

		$this->addData( 'folder', $folder );
		$this->addData( 'folderPath', $this->logic->getPathFromFolderId( $folder->parentId ) );
		$this->addData( 'folders', $this->logic->listFolderNested( 0, $folderId ) );
		$this->addData( 'rights', $this->rights );
		$this->addData( 'files', $files );
	}

	/**
	 *	@param		int|string|NULL		$folderId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function index( int|string|NULL $folderId = NULL ): void
	{
		$search		= trim( $this->request->get( 'search' ) );
		$folderId	= (int) $folderId;

		$folders	= [];
		if( $search ){
			$conditions	= ['title' => '%'.$search.'%'];
			if( $folderId ){
				$folderIds	= $this->logic->getNestedFolderIds( $folderId );
				array_unshift( $folderIds, $folderId );
				$conditions['downloadFolderId']	= $folderIds;
			}
			$orders		= ['title' => 'ASC'];
			$limits		= [];
			$files		= $this->logic->findFiles( $conditions, $orders, $limits );
		}
		else{
			$orders		= ['rank' => 'ASC'];
			$orders		= ['title' => 'ASC'];
			if( $folderId ){
				$folder		= $this->logic->getFolder( $folderId );
				if( !$folder ){
					$this->messenger->noteError( sprintf( 'Invalid folder ID: %s', $folderId ) );
					$this->restart( NULL, TRUE );
				}
				$this->addData( 'folder', $folder );
			}
			$files		= $this->logic->findFiles( ['downloadFolderId' => $folderId], $orders );
			$folders	= $this->logic->findFolders( ['parentId' => $folderId], ['title' => 'ASC'] );
		}

		$this->addData( 'files', $files );
		$this->addData( 'folders', $folders );
		$this->addData( 'folderId', $folderId );
		$this->addData( 'pathBase', $this->path );
		$this->addData( 'folderPath', $this->logic->getPathFromFolderId( $folderId ) );
		$this->addData( 'rights', $this->rights );
		$this->addData( 'search', $this->request->get( 'search' ) );
	}

	/**
	 *	@param		int|string		$folderId
	 *	@param		$downwards
	 *	@return		void
	 */
	public function rankFolder( int|string $folderId, $downwards = NULL ): void
	{
		$words		= (object) $this->getWords( 'msg' );
		$direction	= ('' !== $downwards ?? '' ) ? +1 : -1;
		if( !( $folder = $this->logic->getFolder( (int) $folderId ) ) )
			$this->messenger->noteError( $words->errorInvalidFolderId, $folderId );
		else{
			$this->logic->rankFolder( $folderId, $direction );
		}
		$this->restart( 'index/'.$folder->parentId, TRUE );
	}

	/**
	 *	@param		int|string		$fileId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function remove( int|string $fileId ): void
	{
		$file		= $this->logic->getFile( $fileId );
		if( !$file ){
			$this->messenger->noteError( 'Invalid file ID: %s', $fileId );
			$this->restart( NULL, TRUE );
		}
		$this->logic->removeFile( $fileId );
		$this->messenger->noteSuccess( 'Datei <b>"%s"</b> entfernt.', $file->title );
		$this->restart( 'index/'.$file->downloadFolderId, TRUE );
	}

	/**
	 *	@param		int|string		$folderId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function removeFolder( int|string $folderId ): void
	{
		if( $folderId ){
			$folder		= $this->logic->getFolder( $folderId );
			if( !$folder ){
				$this->messenger->noteError( sprintf( 'Invalid folder ID: %s', $folderId ) );
			}
			else{
				$hasFiles	= $this->logic->countFilesInFolder( $folderId );
				$hasFolders	= $this->logic->countFoldersInFolder( $folderId );
				if( $hasFiles || $hasFolders ){
					$this->messenger->noteError( 'Der Ordner <b>"%s"</b> ist nicht leer und kann daher nicht entfernt werden.', $folder->title );
				}
				else{
					$this->logic->removeFolder( $folderId );
				}
				$this->restart( $folder->parentId ? 'index/'.$folder->parentId : '', TRUE );
			}
		}
		$this->restart( NULL, TRUE );
	}

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function scan(): void
	{
		$statsImport	= (object) ['folders' => [], 'files' => []];
		$statsClean		= (object) ['folders' => [], 'files' => []];
		$this->logic->scanRecursive( 0, '', $statsImport );
		$this->logic->cleanRecursive( 0, '', $statsClean );

		$addedSomething		= count( $statsImport->folders ) + count( $statsImport->folders ) > 0;
		$removedSomething	= count( $statsClean->folders ) + count( $statsClean->folders ) > 0;
		if( $addedSomething || $removedSomething ){
			if( $addedSomething ){
				$list	= [];
				foreach( $statsImport->files as $file ){
					$path	= HtmlTag::create( 'small', $file->path, ['class' => "muted"] );
					$list[]	= HtmlTag::create( 'li', $path.$file->title );
				}
				$list	= HtmlTag::create( 'ul', $list );
				$this->messenger->noteNotice( $this->messages->infoScanFoundSomething, count( $statsImport->folders ), count( $statsImport->files ), $list );
			}
			if( $removedSomething ){
				$list	= [];
				foreach( $statsClean->files as $file ){
					$path	= HtmlTag::create( 'small', $file->path, ['class' => "muted"] );
					$list[]	= HtmlTag::create( 'li', $path.$file->title );
				}
				$list	= HtmlTag::create( 'ul', $list );
				$this->messenger->noteNotice( $this->messages->infoScanRemovedSomething, count( $statsClean->folders ), count( $statsClean->files ), $list );
			}
		}
		else
			$this->messenger->noteNotice( $this->messages->infoScanNoChanges );
		$this->restart( NULL, TRUE );
	}

	/**
	 *	@param		int|string|NULL		$folderId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function upload( int|string $folderId = NULL ): void
	{
		if( !in_array( 'upload', $this->rights ) )
			$this->restart( NULL, TRUE );
		if( $this->request->has( 'save' ) ){
			$upload	= (object) $this->request->get( 'upload' );
			$logicUpload	= new Logic_Upload( $this->env );
			try{
				$description	= $this->request->get( 'description', '' );
				$logicUpload->setUpload( $upload );
				$this->logic->addFileFromUpload( $logicUpload, $folderId, $description );
				$this->messenger->noteSuccess( 'Datei "%s" hochgeladen.', $upload->name );
			}
			catch( Exception $e ){
				$helperError	= new View_Helper_UploadError( $this->env );
				$helperError->setUpload( $logicUpload );
				$message	= $helperError->render();
				$this->messenger->noteError( $message ?: $e->getMessage() );
			}
		}
		$this->restart( 'index/'.$folderId, TRUE );
	}

	/**
	 *	@param		int|string|NULL		$fileId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function view( int|string|NULL $fileId = NULL ): void
	{
		$file		= $this->logic->getFile( $fileId );
		if( !$file ){
			$this->messenger->noteError( 'Invalid file ID: %s', $fileId );
			$this->restart( NULL, TRUE );
		}
		$path	= $this->logic->getPathFromFolderId( $file->downloadFolderId, TRUE );
		$this->addData( 'file', $file );
		$this->addData( 'path', $path );
		$this->addData( 'rights', $this->rights );
		$this->addData( 'filesize', filesize( $path.$file->title ) );
		$this->addData( 'type', pathinfo( $file->title, PATHINFO_EXTENSION ) );
		$this->addData( 'mimeType', mime_content_type( $path.$file->title ) );
	}

	//  --  PROTECTED  --  //

	/**
	 *	@return		void
	 */
	protected function __onInit(): void
	{
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
		$this->options		= $this->env->getConfig()->getAll( 'module.info_files.', TRUE );
		$this->path			= $this->options->get( 'path' );
		$this->logic		= new Logic_Download( $this->env, $this->path );
		$this->rights		= $this->env->getAcl()->index( 'info/file' );
		$this->messages		= (object) $this->getWords( 'msg' );
	}

	protected function checkFolder( int|string $folderId ): bool
	{
		if( (int) $folderId > 0 ){
			$folder		= $this->logic->getFolder( $folderId );
			if( $folder && file_exists( $this->path.$folder->title ) )
				return TRUE;
			if( !$folder )
				$this->messenger->noteError( 'Invalid folder with ID %s', $folderId );
			else if( file_exists( $this->path.$folder->title ) )
				$this->messenger->noteError( 'Folder %s is not existing', $folder->title );
		}
		else{
			if( file_exists( $this->path ) )
				return TRUE;
			$this->messenger->noteError( 'Base folder %s is not existing', $this->path );
		}
		return FALSE;
	}
}
