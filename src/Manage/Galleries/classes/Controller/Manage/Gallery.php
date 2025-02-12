<?php

use CeusMedia\Common\FS\Folder\Editor as FolderEditor;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\Common\Net\HTTP\UploadErrorHandler;
use CeusMedia\Common\UI\Image\Exif as ImageExif;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

class Controller_Manage_Gallery extends Controller
{
	protected HttpRequest $request;
	protected MessengerResource $messenger;
	protected Logic_Frontend $frontend;
	protected Model_Gallery $modelGallery;
	protected Model_Gallery_Image $modelImage;
	protected string $baseUri;

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function add(): void
	{
		$words		= (object) $this->getWords( 'msg' );
		if( $this->request->has( 'save' ) ){
			$data				= $this->request->getAll();
			$data['status']		= 0;
			$data['timestamp']	= time();
			if( !strlen( trim( $data['path'] ) ) ){
				$this->messenger->noteError(  $words->errorPathEmpty );
				$this->restart( 'add?rank='.$data['rank'], TRUE );
			}
			FolderEditor::createFolder( $this->getPath( (object) $data ), 0777 );
			FolderEditor::createFolder( $this->getPath( (object) $data, TRUE ), 0777 );
			$galleryId		= $this->modelGallery->add( $data );
			$this->messenger->noteSuccess( $words->successGalleryAdded );
			$this->restart( './manage/gallery/edit/'.$galleryId );
		}
		$latestRank	= 0;
		$latest	= $this->modelGallery->getAll( [], ['rank' => 'DESC'], [0, 1] );
		if( $latest ){
			$latest	= array_pop( $latest );
			$latestRank	= $latest->rank;
		}
		$rank	= $this->request->has( 'rank' ) ? $this->request->get( 'rank' ) : $latestRank + 1;
		$gallery	= (object) array(
			'status'		=> (int) $this->request->get( 'status' ),
			'rank'			=> (int) $rank,
			'path'			=> $this->request->get( 'path' ),
			'title'			=> $this->request->get( 'title' ),
			'description'	=> $this->request->get( 'description' ),
		);
		$this->addData( 'gallery', $gallery );
	}

	/**
	 *	@param		string		$galleryId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function addImage( string $galleryId ): void
	{
		$gallery	= $this->getGallery( $galleryId );
		$file		= $this->request->get( 'file' );
		$words		= $this->getWords();
		$sizes		= $this->moduleConfig->getAll( 'image.size.', TRUE );
		$upload		= new Logic_Upload( $this->env );
		if( $file && $file['error'] != 0 ){
			$handler	= new UploadErrorHandler();
			$handler->setMessages( $words['msgErrorUpload'] );
			$handler->handleErrorCode( $file['error'] );
		}
		else{
			try{
				$upload->setUpload( $file );
				$maxSize	= max( 1, min( 20, $this->moduleConfig->get( 'image.size.file' ) ) );
				$mimeTypes	= preg_split( "/, ?/", $this->moduleConfig->get( 'image.types' ) );
				if( !$upload->checkSize( $maxSize * 1024 * 1024 ) ){
					$this->messenger->noteError( $words['msg']['errorUploadTooLarge'] );
				}
				else if( !$upload->checkMimeType( $mimeTypes ) ){
					$this->messenger->noteError( $words['msg']['errorUploadWrongMimeType'] );
				}
				else if( !$upload->checkIsImage() ){
					$this->messenger->noteError( $words['msg']['errorUploadWrongFileType'] );
				}
				else{
					$target		= $this->getPath( $galleryId ).$file['name'];
					$thumb		= $this->getPath( $galleryId, TRUE ).$file['name'];
					if( file_exists( $target ) ){
						$this->messenger->noteError( $words['msg']['errorImageAlreadyExists'] );
					}
					else{
						$upload->scaleImage( $target, $sizes->get( 'x' ), $sizes->get( 'y' ) );
						$upload->scaleImage( $thumb, $sizes->get( 'thumb.x' ), $sizes->get( 'thumb.y' ) );

						$title		= trim( $this->request->get( 'title' ) );
						if( !strlen( $title )  ){
							$upload->saveTo( '_test_' );
							$exif		= new ImageExif( '_test_' );
							if( strlen( trim( $exif->get( 'ImageDescription' ) ) ) )
								$title	= $exif->get( 'ImageDescription' );
						}

						$data		= array(
							'galleryId'	=> $galleryId,
							'rank'		=> $this->request->get( 'rank' ),
							'filename'	=> $file['name'],
							'title'		=> $title,
							'timestamp'	=> time(),
						);
						$imageId	= $this->modelImage->add( $data );
						$this->messenger->noteSuccess( $words['msg']['successImageAdded'] );
					}


				}
			}
			catch( RuntimeException $e ){
				$this->messenger->noteError( $e->getMessage() );
			}
		}
		$this->restart( 'edit/'.$galleryId, TRUE );
	}

	public function ajaxSetTab(): void
	{
		$session	= $this->env->getSession();
		$session->set( 'module.manage_galleries.tab', (int) $this->request->get( 'tab' ) );
		exit;
	}

	/**
	 *	@param		string		$galleryId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function edit( string $galleryId ): void
	{
		$words		= (object) $this->getWords( 'msg' );
		$gallery	= $this->getGallery( $galleryId );
		if( $this->request->has( 'save' ) ){
			$old	= $this->getGallery( $galleryId );
			$data	= $this->request->getAll();
			if( !strlen( trim( $data['path'] ) ) ){
				$this->messenger->noteError(  $words->errorPathEmpty );
				$this->restart( 'edit/'.$galleryId, TRUE );
			}
			$data['timestamp']		= time();
			$this->modelGallery->edit( $galleryId, $data, FALSE );
			if( $old->path !== $data['path'] )
				rename( $this->baseUri.$old->path, $this->getPath( $galleryId ) );
			$this->messenger->noteSuccess( $words->successGallerySaved );
			$this->restart( 'edit/'.$galleryId, TRUE );
		}


		$lastRank	= 0;
		$latest	= $this->modelImage->getAllByIndex( 'galleryId', $galleryId, ['rank' => 'DESC'], [0, 1] );
		if( $latest ){
			$latest	= array_pop( $latest );
			$lastRank	= $latest->rank;
		}

		$this->addData( 'images', $this->modelImage->getAllByIndex( 'galleryId', $galleryId, ['rank' => 'ASC'] ) );
		$this->addData( 'gallery', $gallery );
		$this->addData( 'galleryId', $galleryId );
		$this->addData( 'sizes', $this->env->getConfig()->getAll( 'module.manage_galleries.image.size.' ) );

		$this->addData( 'nextRank', $lastRank + 1 );

	}

	/**
	 *	@param		string		$imageId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function editImage( string $imageId ): void
	{
		$words	= (object) $this->getWords( 'msg' );
		$image	= $this->getImage( $imageId );
		$data	= $this->request->getAll();
		$this->modelImage->edit( $imageId, $data );
		$this->messenger->noteSuccess( $words->successImageSaved );
		$this->restart( 'edit/'.$image->galleryId, TRUE );
	}

	public function index()
	{
	}

	/**
	 *	@param		string		$galleryId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function remove( string $galleryId ): void
	{
		$gallery	= $this->getGallery( $galleryId );
		$words		= (object) $this->getWords( 'msg' );
		foreach( $this->modelGallery->getAllByIndex( 'galleryId', $galleryId ) as $image ){
			if( file_exists( $this->getPath( $gallery ).$image->filename ) )
				unlink( $this->getPath( $gallery ).$image->filename );
			if( file_exists( $this->getPath( $gallery, TRUE ).$image->filename ) )
				unlink( $this->getPath( $gallery, TRUE ).$image->filename );
			$this->modelGallery->remove( $image->galleryImageId );
		}
		if( file_exists( $this->getPath( $gallery, TRUE ) ) )
			FolderEditor::removeFolder( $this->getPath( $gallery, TRUE ) );
		if( file_exists( $this->getPath( $gallery ) ) )
			FolderEditor::removeFolder( $this->getPath( $gallery ) );
		$this->modelGallery->remove( $galleryId );
		$this->messenger->noteSuccess( $words->successGalleryRemoved );
		$this->restart( NULL, TRUE );
	}

	/**
	 *	@param		string		$imageId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function removeImage( string $imageId ): void
	{
		$image		= $this->getImage( $imageId );
		$path		= $this->getPath( $image->galleryId );
		$words		= (object) $this->getWords( 'msg' );

		@unlink( $this->getPath( $image->galleryId ).$image->filename );
		@unlink( $this->getPath( $image->galleryId, TRUE ).$image->filename );
		$this->modelImage->remove( $imageId );
		$this->messenger->noteSuccess( $words->successImageRemoved );
		$this->restart( 'edit/'.$image->galleryId, TRUE );
	}

	/**
	 *	@return		void
	 */
	protected function __onInit(): void
	{
		$this->modelGallery	= new Model_Gallery( $this->env );
		$this->modelImage	= new Model_Gallery_Image( $this->env );
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
		$this->frontend		= Logic_Frontend::getInstance( $this->env );
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.manage_galleries.', TRUE );
		$this->baseUri		= $this->frontend->getPath( 'images' ).$this->moduleConfig->get( 'image.path' );

		if( !$this->env->getSession()->get( 'module.manage_galleries.tab' ) )
			$this->env->getSession()->set( 'module.manage_galleries.tab', 1 );

		$this->addData( 'baseUri', $this->baseUri );
		$this->addData( 'frontend', $this->frontend );
		$this->addData( 'moduleConfig', $this->moduleConfig );

		$order		= $this->moduleConfig->get( 'sort.by' );
		$direction	= $this->moduleConfig->get( 'sort.direction' );
		$this->addData( 'galleries', $this->modelGallery->getAll( [], [$order => $direction] ) );

//		$this->baseUri		= '../images/gallery/';
	}

	/**
	 *	@param		string $galleryId
	 *	@return		object|NULL
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function getGallery( string $galleryId ): ?object
	{
		$words		= (object) $this->getWords( 'msg' );
		if( strlen( trim( $galleryId ) ) && (int) $galleryId ){
			if( ( $gallery = $this->modelGallery->get( (int) $galleryId ) ) )
				return $gallery;
			else
				$this->messenger->noteError( $words->errorGalleryIdEmpty );
		}
		else
			$this->messenger->noteError( $words->errorGalleryIdInvalid );
		$this->restart( NULL, TRUE );
		return NULL;
	}

	/**
	 *	@param		string		$imageId
	 *	@return		object|NULL
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function getImage( string $imageId ): ?object
	{
		$words		= (object) $this->getWords( 'msg' );
		if( strlen( trim( $imageId ) ) && (int) $imageId ){
			if( $image = $this->modelImage->get( (int) $imageId ) )
				return $image;
			$this->messenger->noteError( $words->errorImageIdEmpty );
		}
		else
			$this->messenger->noteError( $words->errorImageIdInvalid );
		$this->restart( NULL, TRUE );
		return NULL;
	}

	/**
	 *	@param		object|int|string		$gallery
	 *	@param		bool					$thumbs
	 *	@return		string
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function getPath( object|int|string $gallery, bool $thumbs = FALSE ): string
	{
		if( is_int( $gallery ) || is_string( $gallery ) )
			$gallery	= $this->getGallery( (int) $gallery );
		if( !is_object( $gallery ) )
			throw new InvalidArgumentException( 'Neither gallery object nor gallery ID given' );
		$path	= $gallery->path."/".( $thumbs ? "thumbs/" : "" );
		return $this->baseUri.$path;
	}
}
