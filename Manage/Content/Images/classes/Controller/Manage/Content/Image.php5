<?php
class Controller_Manage_Content_Image extends CMF_Hydrogen_Controller{

	protected $extensions;
	protected $folders			= array();
	protected $frontend;
	protected $messenger;
	protected $moduleConfig;
	protected $path;
	protected $request;
	protected $thumbnailer;

	static protected $cacheImageList	= array();

	public function __onInit(){
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
		$this->frontend		= Logic_Frontend::getInstance( $this->env );
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.manage_content_images.', TRUE );
		$this->extensions	= preg_split( "/\s*,\s*/", $this->moduleConfig->get( 'extensions' ) );
		$pathIgnore			= trim( $this->moduleConfig->get( 'path.ignore' ) );

		$words				= (object) $this->getWords( 'msg' );
		$this->path			= $this->frontend->getPath().$this->moduleConfig->get( 'path.images' );
		$this->uri			= $this->frontend->getUri().$this->moduleConfig->get( 'path.images' );
		if( $this->moduleConfig->get( 'path.images' ) === "auto" ){
			$this->path	= $this->frontend->getPath( 'images' );
		}
		if( !file_exists( $this->path ) ){
			$this->messenger->noteFailure( $words->errorBasePathInvalid, $this->path );
		}
		else{
			$this->folders	= array( '' => '.' );
			foreach( Folder_RecursiveLister::getFolderList( $this->path ) as $entry ){
				$path	= substr( $entry->getPathname(), strlen( $this->path ) );
				if( !( $pathIgnore && preg_match( $pathIgnore, $path ) ) )
					$this->folders[]	= './'.$path;
			}
			natcasesort( $this->folders );
		}

		$this->thumbnailer	= new View_Helper_Thumbnailer( $this->env, 120, 80, "config/" );
		$this->thumbnailer->optimize( $this->path );

		$path	= str_replace( "../", "", trim( $this->env->getRequest()->get( 'path' ) ) );
		$path	= strlen( trim( $path ) ) ? trim( $path ) : ".";
		$this->addData( 'path', $path );
		$this->addData( 'basePath', $this->path );
		$this->addData( 'folders', $this->folders );
		$this->addData( 'extensions', $this->extensions );
		$this->addData( 'frontend', $this->frontend );
		$this->addData( 'moduleConfig', $this->moduleConfig );
		$this->addData( 'helperThumbnailer', $this->thumbnailer );
	}

	static protected function getImageList( $env ){
		$cache		= $env->getCache();
		if( $list = $env->cache->get( 'ManageContentImages.list.static' ) )
			return $list;
		$frontend		= Logic_Frontend::getInstance( $env );
		$moduleConfig	= $env->getConfig()->getAll( 'module.manage_content_images.', TRUE );
		$pathImages		= $frontend->getPath().$moduleConfig->get( 'path.images' );
		$pathIgnore		= trim( $moduleConfig->get( 'path.ignore' ) );
		$extensions		= preg_split( "/\s*,\s*/", $moduleConfig->get( 'extensions' ) );
		$list			= array();

		$regexExt	= "/\.(".join( "|", $extensions ).")$/i";
		$index		= new File_RecursiveRegexFilter( $pathImages, $regexExt );
		foreach( $index as $item ){
			$path	= substr( $item->getPathname(), strlen( $pathImages ) );
			if( $pathIgnore && preg_match( $pathIgnore, $path ) )
				continue;
			$parts	= explode( "/", $path );
			$level	= count( $parts );
			$file	= array_pop( $parts );
//			$path	= implode( '/', array_slice( $parts, 1 ) );
			$path	= implode( '/', $parts );
			$label	= $path ? $path.'/'.$file : $file;
			$uri	= substr( $item->getPathname(), strlen( $frontend->getPath() ) );
			$key	= $level.'_'.str_replace( "/", "_", strtolower( $label ) );
			$list[$key]	= (object) array( 'label' => $label, 'uri' => $uri );
		}
		ksort( $list );
		$env->getCache()->set( 'ManageContentImages.list.static', $list );
		return $list;
	}

	static public function ___onTinyMCE_getImageList( $env, $context, $module, $arguments = array() ){
		self::___onTinyMCE_getLinkList( $env, $context, $module, array( 'hidePrefix' => TRUE ) );
	}

	static public function ___onTinyMCE_getLinkList( $env, $context, $module, $arguments = array() ){
		$moduleConfig	= $env->getConfig()->getAll( 'module.manage_content_images.', TRUE );
		$frontend		= Logic_Frontend::getInstance( $env );
		$pathFront		= trim( $frontend->getPath() );
		$pathImages		= trim( $moduleConfig->get( 'path.images' ) );
		$pathIgnore		= trim( $moduleConfig->get( 'path.ignore' ) );
		$hidePrefix		= 1 || !empty( $arguments['hidePrefix'] ) && $arguments['hidePrefix'];

		$words		= $env->getLanguage()->getWords( 'js/tinymce' );
		$prefixes	= (object) $words['link-prefixes'];
//		$label		= $prefixes->image;
		$list		= array();
		$index		= self::getImageList( $env );
		foreach( $index as $item ){
			$list[]	= (object) array(
				'title'	=> $hidePrefix ? $item->label : $prefixes->image.$item->label,
				'value'	=> $item->uri,
			);
		}
		$list	= array( (object) array(
			'title'	=> $prefixes->image,
			'menu'	=> array_values( $list ),
		) );
//		$context->list	= array_merge( $context->list, array_values( $list ) );
		$context->list	= array_merge( $context->list, $list );
	}

	public function addFolder(){
		if( $this->request->has( 'save' ) ){
			$words		= (object) $this->getWords( 'msg' );
			$folderPath	= trim( $this->request->get( 'path' ) );
			$folder		= trim( $this->request->get( 'folder' ) );
			$name		= trim( $this->request->get( 'name' ) );
			$folder		= str_replace( "../", "", $folder );							//  security
			$folder		= ( strlen( $folder ) && $folder != "." ) ? $folder.'/' : "";
			$name		= str_replace( "../", "", $name );								//  security
			if( !strlen( $name ) )
				$this->messenger->noteError( $words->errorFolderNameMissing );
			else{
				$target		= $this->path.$folder.$name;
				try{
					Folder_Editor::createFolder( $target, 0775 );
					$this->env->getCache()->remove( 'ManageContentImages.list.static' );
					$this->messenger->noteSuccess( $words->successFolderCreated, $folder.$name );
					$this->restart( '?path='.$folder.$name, TRUE );
				}
				catch( Exception $e ){
					$this->messenger->noteFailure( $e->getMessage() );
				}
			}
		}
		$this->addData( 'imagePath', trim( $this->request->get( 'path' ) ) );
	}

	public function addImage(){
		$path		= trim( $this->request->get( 'path' ) );
		$folder		= trim( $this->request->get( 'folder' ) );
		$file		= $this->request->get( 'file' );
		$folder		= str_replace( "../", "", $folder );				//  security
		$folder		= strlen( $folder ) ? $folder.'/' : "";
		$types		= array( IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF );
		$words		= (object) $this->getWords( 'msg' );

		if( $this->request->has( 'save' ) ){
			if( $file['error'] ){
				$handler	= new Net_HTTP_UploadErrorHandler();
				$this->messenger->noteError( $handler->getErrorMessage( $file['error'] ) );
			}
			else{
				try{
					$image	= new UI_Image( $file['tmp_name'] );
					$type	= $image->getType();
					if( !in_array( $type, $types ) ){
						$this->messenger->noteError( $words->errorTypeNotSupported, $type );
					}
					else{
						$target	= $this->path.$folder.$file['name'];
						if( !@move_uploaded_file( $file['tmp_name'], $target ) ){
							$this->messenger->noteFailure( $words->errorUploadFailed );
						}
						else{
							$this->env->getCache()->remove( 'ManageContentImages.list.static' );
							$this->messenger->noteSuccess( $words->successImageAdded, $file['name'] );				//  @todo apply security!
							$this->restart( '?path='.$this->request->get( 'folder' ), TRUE );
						}
					}
				}
				catch( exception $e ){
					$this->messenger->noteError( $words->errorNoSupportedImage, $file['name'] );
				}
			}
		}
		$this->addData( 'imagePath', trim( $this->request->get( 'path' ) ) );
	}

	protected function checkFile( $filePath ){
		if( !file_exists( $this->path.$filePath ) ){
			$words		= (object) $this->getWords( 'msg' );
			$this->messenger->noteError( $words->errorImageNotExisting, $filePath );
			$this->restart( '?path='.dirname( $filePath ), TRUE );
		}
	}

	protected function checkFolder( $folderPath ){
		if( !file_exists( $this->path.$folderPath ) ){
			$words		= (object) $this->getWords( 'msg' );
			$this->messenger->noteError( $words->errorFolderNotExisting, $folderPath );
			$this->restart( NULL, TRUE );
		}
	}

	public function editFolder(){
		$words		= (object) $this->getWords( 'msg' );
		$folderPath	= trim( $this->request->get( 'path' ) );
		$this->checkFolder( $folderPath );
		if( $this->request->has( 'save' ) ){
			$folder		= trim( $this->request->get( 'folder' ) );
			$name		= trim( $this->request->get( 'name' ) );
			$folder		= str_replace( "../", "", $folder );							//  security
			$folder		= ( strlen( $folder ) && $folder != "." ) ? $folder.'/' : "";
			$name		= str_replace( "../", "", $name );								//  security
			$source		= $this->path.$folderPath;
			if( !strlen( $name ) )
				$this->messenger->noteError( $words->errorFolderNameMissing );
			else{
				$target		= $this->path.$folder.$name;
				if( $source == $target ){
					$this->messenger->noteNotice( $words->noticeNoChanges );
					$this->restart( '?path='.$folderPath, TRUE );
				}
				if( !file_exists( $this->path.$folder ) ){
					$this->messenger->noteError( $words->errorTargetFolderInvalid );
					$this->restart( 'editFolder?path='.$folderPath, TRUE );
				}
				if( file_exists( $target ) ){
					$this->messenger->noteError( $words->errorTargetFolderExisting, $folder.$name );
					$this->restart( 'editFolder?path='.$folderPath, TRUE );
				}
				if( @rename( $source, $target ) ){
					$this->messenger->noteSuccess( $words->successFolderMoved, $folderPath, $folder.$name );
					$this->thumbnailer->uncacheFolder( $folderPath );
					$this->env->getCache()->remove( 'ManageContentImages.list.static' );
					$this->restart( '?path='.$folder.$name, TRUE );
				}
				$this->messenger->noteFailure( $words->errorMovingFolderFailed, $folderPath );
			}
		}
		$this->addData( 'folderPath', dirname( $folderPath ) );
		$this->addData( 'folderName', basename( $folderPath ) );
	}

	/**
	 *	...
	 *	@access		public
	 *	@return		void
	 */
	public function editImage(){
		$words		= (object) $this->getWords( 'msg' );

		$imagePath	= $this->request->get( 'path' );
		if( !strlen( trim( $imagePath ) ) )
			$this->restart( NULL, TRUE );
//		if( substr( $imagePath, 0, strlen( $this->path ) ) == $this->path )
//			$imagePath	= substr( $imagePath, strlen( $this->path ) );
		$imageFolder	= dirname( $imagePath ).'/';
		$imageName		= basename( $imagePath );
		if( !file_exists( $this->path.$imagePath ) ){
			$this->messenger->noteError( $words->errorImageNotExisting, $imagePath );
			$this->restart( '?path='.dirname( $imagePath ), TRUE );
		}
		if( $this->request->has( 'save' ) ){
			$folderPath		= trim( $this->request->get( 'folderpath' ) ).'/';
			$fileName		= trim( $this->request->get( 'filename' ) );
			if( $imagePath === $folderPath.$fileName )
				$this->messenger->noteNotice( $words->noticeNoChanges );
			else{
				$pathSource	= $this->path.$imagePath;
				$pathTarget	= $this->path.$folderPath.$fileName;
				rename( $pathSource, $pathTarget );
				$this->thumbnailer->uncacheFile( $pathSource );
				$this->env->getCache()->remove( 'ManageContentImages.list.static' );
				if( $imageName !== $fileName && $imageFolder !== $folderPath )			//  both name and folder changed
					$this->messenger->noteSuccess( $words->successImageRenamedAndMoved, $imageName, $fileName, $folderPath );
				else if( $imageName !== $fileName )										//  only name changed
					$this->messenger->noteSuccess( $words->successImageRenamed, $imageName, $fileName );
				else if( $imageFolder !== $folderPath )									//  only folder changed
					$this->messenger->noteSuccess( $words->successImageMoved, $fileName, $folderPath );
				$this->restart( 'editImage?path='.$folderPath.$fileName, TRUE );
			}
		}

		$image	= new UI_Image( $this->path.$imagePath );

		$this->addData( 'frontend', $this->frontend );
		$this->addData( 'pathImages', $this->path );
		$this->addData( 'imageFolder', $imageFolder );
		$this->addData( 'imagePath', $imagePath );
		$this->addData( 'imageName', $imageName );
		$this->addData( 'imageWidth', $image->getWidth() );
		$this->addData( 'imageHeight', $image->getHeight() );
		$this->addData( 'imageUri', $this->uri.preg_replace( "@^\.\/@", "", $imagePath ) );
		$this->addData( 'imageMimeType', image_type_to_mime_type( exif_imagetype( $this->path.$imagePath ) ) );
		$this->addData( 'imageFileSize', filesize( $this->path.$imagePath ) );
		$this->addData( 'imageFileTime', filemtime( $this->path.$imagePath ) );
		$this->addData( 'imageMegaPixels', round( $image->getWidth() * $image->getHeight() / 1024 / 1024, 1 ) );
	}

	public function index(){
		$words		= (object) $this->getWords( 'msg' );
		$folderPath	= $this->env->getRequest()->get( 'path' );
		$this->addData( 'folderPath', $folderPath );
		if( !file_exists( $this->path ) )
			return;
		if( !file_exists( $this->path.$folderPath ) ){
			$this->messenger->noteError( $words->errorPathNotExisting, $folderPath, dirname( $folderPath ) );
			$this->restart( 'path='.dirname( $folderPath ), TRUE );
		}
	}

	public function removeFolder(){
		$words		= (object) $this->getWords( 'msg' );
		$folderPath	= $this->env->getRequest()->get( 'path' );
		$this->checkFolder( $folderPath );
		$contains	= 0;
		$index		= new DirectoryIterator( $this->path.$folderPath );
		foreach( $index as $entry )
			if( !$entry->isDot() )
				$contains++;
		if( !Folder_Editor::removeFolder( $this->path.$folderPath, TRUE ) ){
			$this->messenger->noteFailure( $words->errorRemovingFolderFailed, $folderPath );
		}
		else{
			$this->messenger->noteSuccess( $words->successFolderRemoved, $folderPath );
		}
		$this->restart( '?path='.dirname( $folderPath ), TRUE );
	}

	public function removeImage(){
		$words		= (object) $this->getWords( 'msg' );
		$imagePath	= $this->env->getRequest()->get( 'path' );
		$imageName	= basename( $imagePath );
		$this->checkFile( $imagePath );
		if( !@unlink( $this->path.$imagePath ) ){
			$this->messenger->noteError( $words->errorRemovingImageFailed, $imagePath );
		}
		else{
			$this->env->getCache()->remove( 'ManageContentImages.list.static' );
			$this->thumbnailer->uncacheFile( $imagePath );
			$this->messenger->noteSuccess( $words->successImageRemoved, $imageName );
		}
		$this->restart( '?path='.dirname( $imagePath ), TRUE );
	}

	public function scale(){
		$words		= (object) $this->getWords( 'msg' );
		$width		= (int) $this->request->get( 'width' );
		$height		= (int) $this->request->get( 'height' );
		$quality	= min( 100, max( 0, (int) $this->request->get( 'quality' ) ) ) / 2 + 50;
		$imagePath	= $this->request->get( 'path' );

		$this->checkFile( $imagePath );

		if( $width * $height === 0 ){
			$this->messenger->noteError( $words->errorImageDimensionsInvalid );
			$this->restart( 'editImage?path='.$imagePath, TRUE );
		}
		$image		= new UI_Image( $this->path.$imagePath );
		if( $image->getWidth() === $width && $image->getHeight() ){
			$this->messenger->noteNotice( $words->noticeNoChanges );
			$this->restart( 'editImage?path='.$imagePath, TRUE );
		}

		$targetPath	= $imagePath;
		if( $this->request->get( 'copy' ) ){
			$folderPath		= dirname( $imagePath ).'/';
			$imageName		= basename( $imagePath );
			$imageExt		= pathinfo( $imageName, PATHINFO_EXTENSION );
			$imageBase		= pathinfo( $imageName, PATHINFO_FILENAME );
			$targetName		= $imageBase.'_'.$width.'x'.$height.'.'.$imageExt;;
			$targetPath		= $folderPath.$targetName;
		}
		$source			= $this->path.$imagePath;
		$target			= $this->path.$targetPath;
		$thumbnailer	= new UI_Image_ThumbnailCreator( $source, $target, $quality );
		$thumbnailer->thumbize( $width, $height );
		$this->env->getCache()->remove( 'ManageContentImages.list.static' );
		$this->messenger->noteSuccess( $words->successImageScaled, $targetName );
		$this->restart( 'editImage?path='.$imagePath, TRUE );
	}

	public function view( $embededInHtml = FALSE ){
		$imagePath	= $this->env->getRequest()->get( 'path' );
		if( !file_exists( $this->path.$imagePath ) ){
			Net_HTTP_Status::sendHeader( 400 );                                           //  send HTTP status code header
			die( "Invalid request" );
		}
		$image		= getimagesize( $this->path.$imagePath );
		$mimetype	= image_type_to_mime_type( $image[2] );
		if( $embededInHtml ){
			$content	= base64_encode( file_get_contents( $this->path.$imagePath ) );
			$source		= "data:".$mimetype.";base64,".$content;
			$page		= new UI_HTML_PageFrame();
			$page->addBody( UI_HTML_Tag::create( 'img', NULL, array( 'src' => $source ) ) );
			print( $page->build() );
			exit;
		}
		header( 'Content-Type: '.$mimetype );
		print( File_Reader::load( $this->path.$imagePath ) );
		exit;
	}
}
?>
