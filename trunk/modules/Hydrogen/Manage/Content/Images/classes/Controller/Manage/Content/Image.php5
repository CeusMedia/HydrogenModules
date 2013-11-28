<?php
class Controller_Manage_Content_Image extends CMF_Hydrogen_Controller{

	protected $folders	= array();
	protected $path;

	public function __onInit(){
		$this->config		= $this->env->getConfig();
		$this->messenger	= $this->env->getMessenger();

		$config		= $this->config->getAll( 'module.manage_content_images.', TRUE );
		$pathIgnore	= trim( $config->get( 'path.ignore' ) );
		
		$this->path			= $config->get( 'frontend.path' ).$config->get( 'path.images' );
		if( !file_exists( $this->path ) ){
			$this->messenger->noteFailure( 'Der Bilderordner "'.$this->path.'" existiert nicht.' );
		}

		$this->folders	= array( '' => '.' );
		foreach( Folder_RecursiveLister::getFolderList( $this->path ) as $entry ){
			$path	= substr( $entry->getPathname(), strlen( $this->path ) );
			if( !( $pathIgnore && preg_match( $pathIgnore, $path ) ) )
				$this->folders[]	= './'.$path;
		}
		natcasesort( $this->folders );

		$path	= str_replace( "../", "", trim( $this->env->getRequest()->get( 'path' ) ) );
		$path	= strlen( trim( $path ) ) ? trim( $path ) : ".";
		$this->addData( 'path', $path );
		$this->addData( 'basePath', $this->path );
		$this->addData( 'folders', $this->folders );
#		$thumbnailer	= new View_Helper_Thumbnailer( $this->env );
#		$thumbnailer->optimize( $this->path );
	}

	static public function ___onTinyMCE_getImageList( $env, $context, $module, $arguments = array() ){
		self::___onTinyMCE_getLinkList( $env, $context, $module, array( 'hidePrefix' => TRUE ) );
	}

	static public function ___onTinyMCE_getLinkList( $env, $context, $module, $arguments = array() ){
		$config		= $env->getConfig()->getAll( 'module.manage_content_images.', TRUE );
		$pathFront	= trim( $config->get( 'frontend.path' ) );
		$pathImages	= trim( $config->get( 'path.images' ) );
		$pathIgnore	= trim( $config->get( 'path.ignore' ) );
		$hidePrefix	= !empty( $arguments['hidePrefix'] ) && $arguments['hidePrefix'];

		$words		= $env->getLanguage()->getWords( 'js/tinymce' );
		$prefixes	= (object) $words['link-prefixes'];
		$list		= array();
		$index		= new File_RecursiveRegexFilter( $pathFront.$pathImages, $config->get( 'extensions' ) );
		foreach( $index as $item ){
			$path	= substr( $item->getPathname(), strlen( $pathFront.$pathImages ) );
			if( $pathIgnore && preg_match( $pathIgnore, $path ) )
				continue;
			$parts	= explode( "/", $path );
			$file	= array_pop( $parts );
			$path	= implode( '/', array_slice( $parts, 1 ) );
			$label	= $path ? $path.'/'.$file : $file;
			$uri	= substr( $item->getPathname(), strlen( $pathFront ) );
			$list[$item->getPathname()]	= (object) array(
				'title'	=> $hidePrefix ? $label : $prefixes->image.$label,
				'url'	=> $uri,
			);
		}
		ksort( $list );
		$context->list	= array_merge( $context->list, array_values( $list ) );
	}		
	
	public function addFolder(){
		$request	= $this->env->getRequest();
		$messenger	= $this->env->getMessenger();
		if( $request->has( 'save' ) ){
			$folderPath	= trim( $request->get( 'path' ) );
			$folder		= trim( $request->get( 'folder' ) );
			$name		= trim( $request->get( 'name' ) );
			$folder		= str_replace( "../", "", $folder );							//  security
			$folder		= ( strlen( $folder ) && $folder != "." ) ? $folder.'/' : "";
			$name		= str_replace( "../", "", $name );								//  security
			if( !strlen( $name ) )
				$messenger->noteError( 'Der Name des Ordners fehlt.' );
			else{
				$target		= $this->path.$folder.$name;
				try{
					Folder_Editor::createFolder( $target, 0775 );
					$messenger->noteSuccess( 'Der Ordner "'.$folder.$name.'" wurde angelegt.' );
					$this->restart( './manage/content/image?path='.$folder.$name );
				}
				catch( Exception $e ){
					$messenger->noteFailure( $e->getMessage() );
				}
			}
		}
	}

	public function addImage(){
		$request	= $this->env->getRequest();
		$messenger	= $this->env->getMessenger();
		$path		= trim( $request->get( 'path' ) );
		$folder		= trim( $request->get( 'folder' ) );
		$file		= $request->get( 'file' );
		$folder		= str_replace( "../", "", $folder );				//  security
		$folder		= strlen( $folder ) ? $folder.'/' : "";
		$types		= array( IMAGETYPE_PNG, IMAGETYPE_JPEG );

		if( $request->has( 'save' ) ){
			if( $file['error'] ){
				$handler	= new Net_HTTP_UploadErrorHandler();
				$messenger->noteError( $handler->getErrorMessage( $file['error'] ) );
			}
			else{
				try{
					$image	= new UI_Image( $file['tmp_name'] );
					if( !in_array( $image->getType(), $types ) ){
						$messenger->noteError( 'Der Dateityp wird nicht unterstützt.' );
					}
					else{
						$target	= $this->path.$folder.$file['name'];
						if( !@move_uploaded_file( $file['tmp_name'], $target ) ){
							$messenger->noteFailure( 'Fehler beim Speichern der Datei. Bitte den Administrator informieren.' );
						}
						else{
							$messenger->noteSuccess( 'Die Bilddatei wurde hochgeladen.' );
							$this->restart( './manage/content/image?path='.$request->get( 'folder' ) );
						}
					}
				}
				catch( exception $e ){
					$messenger->noteError( 'Die hochgeladene Datei ist keine Bilddatei oder in einem unbekannten Format.' );
				}
			}
		}
	}

	public function editFolder(){
		$request	= $this->env->getRequest();
		$messenger	= $this->env->getMessenger();
		$folderPath	= trim( $request->get( 'path' ) );
		if( !file_exists( $this->path.$folderPath ) ){
			$messenger->noteError( 'Der Ordner existiert nicht.' );
			$this->restart( NULL, TRUE );
		}
		if( $request->has( 'save' ) ){
			$folder		= trim( $request->get( 'folder' ) );
			$name		= trim( $request->get( 'name' ) );
			$folder		= str_replace( "../", "", $folder );							//  security
			$folder		= ( strlen( $folder ) && $folder != "." ) ? $folder.'/' : "";
			$name		= str_replace( "../", "", $name );								//  security
			$source		= $this->path.$folderPath;
			if( !strlen( $name ) )
				$messenger->noteError( 'Der Name des Ordners fehlt.' );
			else{
				$target		= $this->path.$folder.$name;
				if( $source == $target ){
					$messenger->noteNotice( 'Keine Änderung vorgenommen.' );
					$this->restart( './manage/content/image?path='.$folderPath );
				}
				if( !file_exists( $this->path.$folder ) ){
					$messenger->noteError( 'Der Zielordner existiert nicht.' );
					$this->restart( './manage/content/image/editFolder?path='.$folderPath );
				}
				if( file_exists( $target ) ){
					$messenger->noteError( 'Ein Ordner mit diesem Namen existiert bereits.' );
					$this->restart( './manage/content/image/editFolder?path='.$folderPath );
				}
				if( @rename( $source, $target ) ){
					$messenger->noteSuccess( 'Der <abbr title="'.$folderPath.'">Ordner</abbr> wurde verschoben. Automatische Weiterleitung zum <abbr title="'.$folder.$name.'">neuen Ordner</abbr>.' );
					$thumbnailer	= new View_Helper_Thumbnailer( $this->env );
					$thumbnailer->uncacheFolder( $folderPath );
					$this->restart( './manage/content/image?path='.$folder.$name );
				}
				$messenger->noteFailure( 'Der Ordner "'.$folderPath.'" konnte nicht verschoben werden. Bitte den Administrator informieren.' );
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
		$request	= $this->env->getRequest();
		$messenger	= $this->env->getMessenger();
		$words		= $this->getWords();

		$imagePath	= $request->get( 'path' );
		if( !strlen( trim( $imagePath ) ) )
			$this->restart( NULL, TRUE );
		if( substr( $imagePath, 0, strlen( $this->path ) ) == $this->path )
			$imagePath	= substr( $imagePath, strlen( $this->path ) );
		$folderPath		= dirname( $imagePath ).'/';
		$imageName		= basename( $imagePath );
		if( !file_exists( $this->path.$imagePath ) ){
			$messenger->noteError( 'Die Bilddatei "'.$imagePath.'" existiert nicht. Weiterleitung zur Ordneransicht.' );
			$this->restart( './manage/content/image?path='.dirname( $imagePath ) );
		}
		if( $request->has( 'save' ) ){
			$folderPath		= trim( $request->get( 'folderpath' ) ).'/';
			$fileName		= trim( $request->get( 'filename' ) );
			if( $imagePath != $folderPath.$fileName ){
				$pathSource	= $this->path.$imagePath;
				$pathTarget	= $this->path.$folderPath.$fileName;
				rename( $pathSource, $pathTarget );
				$thumbnailer	= new View_Helper_Thumbnailer( $this->env );
				$thumbnailer->uncacheFile( $imagePath );
				$messenger->noteSuccess( 'Die Datei wurde verschoben.' );
				$this->restart( './manage/content/image/editImage?path='.$folderPath.$fileName );
			}
		}

		$image	= new UI_Image( $this->path.$imagePath );

		$this->addData( 'pathImages', $this->path );
		$this->addData( 'folderPath', $folderPath );
		$this->addData( 'imagePath', $imagePath );
		$this->addData( 'imageName', $imageName );
		$this->addData( 'imageWidth', $image->getWidth() );
		$this->addData( 'imageHeight', $image->getHeight() );
//		$this->addData( 'dimensions', 
		$this->addData( 'mimetype', image_type_to_mime_type( exif_imagetype( $this->path.$imagePath ) ) );
		$this->addData( 'filesize', filesize( $this->path.$imagePath ) );
		$this->addData( 'filetime', filemtime( $this->path.$imagePath ) );
		$this->addData( 'frontUrl', $this->env->getConfig()->get( 'module.manage_content_images.front.url' ) );
	}

	public function index(){
		$messenger	= $this->env->getMessenger();
		$folderPath	= $this->env->getRequest()->get( 'path' );
		$this->addData( 'folderPath', $folderPath );
		if( !file_exists( $this->path ) )
			return;
		if( !file_exists( $this->path.$folderPath ) ){
			$messenger->noteError( 'Die Ordner "'.$folderPath.'" existiert nicht. Weiterleitung zum höheren Ordner "'.dirname( $folderPath ).'".' );
			$this->restart( './manage/content/image?path='.dirname( $folderPath ) );
		}
	}

	public function removeFolder(){
		$messenger	= $this->env->getMessenger();
		$folderPath	= $this->env->getRequest()->get( 'path' );
		$contains	= 0;
		$index		= new DirectoryIterator( $this->path.$folderPath );
		foreach( $index as $entry )
			if( !$entry->isDot() )
				$contains++;
		if( !Folder_Editor::removeFolder( $this->path.$folderPath, TRUE ) ){
			$messenger->noteFailure( 'Der Ordner "'.$folderPath.'" konnte nicht entfernt werden. Bitte den Administrator informieren.' );
			$this->restart( './manage/content/image?path='.dirname( $folderPath ) );
		}
		$messenger->noteSuccess( 'Der Ordner "'.$folderPath.'" wurde entfernt.' );
		$this->restart( './manage/content/image?path='.dirname( $folderPath ) );
	}

	public function removeImage(){
		$messenger	= $this->env->getMessenger();
		$imagePath	= $this->env->getRequest()->get( 'path' );
		$imageName	= basename( $imagePath );
		if( !file_exists( $this->path.$imagePath ) ){
			$messenger->noteError( 'Die Bilddatei "'.$imagePath.'" existiert nicht. Weiterleitung zur Ordneransicht.' );
			$this->restart( './manage/content/image/editImage?path='.$imagePath );
		}
		unlink( $this->path.$imagePath );
		$messenger->noteSuccess( 'Die Bilddatei "'.$imageName.'" wurde entfernt.' );
		$this->restart( './manage/content/image?path='.dirname( $imagePath ) );
	}

	public function scale(){
		$request	= $this->env->getRequest();
		$messenger	= $this->env->getMessenger();
		$width		= (int) $request->get( 'width' );
		$height		= (int) $request->get( 'height' );
		$quality	= min( 100, max( 0, (int) $request->get( 'quality' ) ) ) / 2 + 50;
		$imagePath	= $request->get( 'path' );

		if( !file_exists( $this->path.$imagePath ) ){
			$messenger->noteError( 'Die Bilddatei "'.$imagePath.'" existiert nicht. Weiterleitung zur Ordneransicht.' );
			$this->restart( './manage/content/image?path='.dirname( $imagePath ) );
		}
		if( !$width || !$height ){
			$messenger->noteError( 'Das neue Bild muss mindestens 1 Pixel breit und hoch sein.' );
			$this->restart( './manage/content/image/editImage?path='.$imagePath );
		}
		$image		= new UI_Image( $this->path.$imagePath );
		if( $image->getWidth() === $width && $image->getHeight() ){
			$messenger->noteNotice( 'Keine Änderung vorgenommen.' );
			$this->restart( './manage/content/image/editImage?path='.$imagePath );
		}

		$targetPath	= $imagePath;
		if( $request->get( 'copy' ) ){
			$folderPath		= dirname( $imagePath ).'/';
			$imageName		= basename( $imagePath );
			$imageExt		= pathinfo( $imageName, PATHINFO_EXTENSION );
			$imageBase		= pathinfo( $imageName, PATHINFO_FILENAME );
			$targetPath	= $folderPath.$imageBase.'_'.$width.'x'.$height.'.'.$imageExt;;
		}
		$source			= $this->path.$imagePath;
		$target			= $this->path.$targetPath;
		$thumbnailer	= new UI_Image_ThumbnailCreator( $source, $target, $quality );
		$thumbnailer->thumbize( $width, $height );
		$messenger->noteSuccess( 'Das Bild wurde skaliert und unter "'.$targetPath.'" abgespeichert.' );
		$this->restart( './manage/content/image/editImage?path='.$imagePath );
	}

	public function view(){
		$imagePath	= $this->env->getRequest()->get( 'path' );
		if( !file_exists( $this->path.$imagePath ) )
			die( "Invalid request" );
		$image		= getimagesize( $this->path.$imagePath );
		$mimetype	= image_type_to_mime_type( $image[2] );
		$content	= base64_encode( file_get_contents( $this->path.$imagePath ) );
		$source		= "data:".$mimetype.";base64,".$content;
		$page		= new UI_HTML_PageFrame();
		$page->addBody( UI_HTML_Tag::create( 'img', NULL, array( 'src' => $source ) ) );
		print( $page->build() );
		exit;
	}
}
?>
