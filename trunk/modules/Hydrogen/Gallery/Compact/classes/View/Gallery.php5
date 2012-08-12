<?php
class View_Gallery extends CMF_Hydrogen_View{

	/**
	 *	@deprecated	not used anymore
	 *	@todo		remove
	 */
	protected function buildActions( $source ){
		$actions	= array();

		$icon		= UI_HTML_Tag::create( 'img', NULL, array( 'src' => '//icons.ceusmedia.com/famfamfam/silk/disk.png' ) );
		$action		= UI_HTML_Tag::create( 'a', $icon, array( 'href' => './gallery/download/'.$source ) );
		$actions[]	= UI_HTML_Tag::create( 'span', $action );

		$icon		= UI_HTML_Tag::create( 'img', NULL, array( 'src' => '//icons.ceusmedia.com/famfamfam/silk/information.png' ) );
		$action		= UI_HTML_Tag::create( 'a', $icon, array( 'href' => './gallery/info/'.$source, 'class' => 'thickbox' ) );
		$actions[]	= UI_HTML_Tag::create( 'span', $action );

		$actions	= implode( '', $actions );
		return $actions;
	}
	
	public function feed(){
		$galleries	= $this->getData( 'galleries' );
		$path		= $this->getData( 'path' );
		$debug		= $this->getData( 'debug' );
		$config		= $this->env->getConfig();
		$baseUrl	= $config->get( 'app.base.url' );
		$channel	= array(
			'title'			=> $config->get( 'app.name' ).': Galerien',
			'description'	=> 'Aktuelle Galerien von iamkriss.net',
			'link'			=> $baseUrl.'gallery/'
		);
		$feed		= new XML_RSS_Builder();
		$feed->setChannelData( $channel );
		foreach( $galleries as $gallery ){
			$uri	= $baseUrl.'gallery/'.$gallery->pathname;
			$data	= array(
				'title'			=> $gallery->label,
		//		'description'	=> array_shift( explode( "\n", strip_tags( $article->content ) ) ),
				'guid'			=> $uri,
				'link'			=> $uri,
			);
			if( $gallery->timestamp )
				$data['pubDate']	= date( "r", (double) $gallery->timestamp );
			$feed->addItem( $data );
		}
		$rss	= $feed->build( 'utf-8', '0.92' );
		if( $debug ){
			xmp( $rss );
			die;
		}
		header( 'Content-type: application/rss+xml' );
		print( $rss );
		exit;
	}

	public function index(){
		$config	= $this->env->getConfig()->getAll( 'module.gallery_compact.' );
		extract( $this->getData() );
		foreach( $files as $entry ){
			if( preg_match( '/\.(small|medium)\.(jpg|jpeg|jpe|png|gif)$/i', $entry->getFilename() ) )
				continue;

			$fileName	= $path.$source.$entry->getFilename();
			if( !file_exists( $fileName ) )
				throw new Exception( 'No image file' );

			$data		= pathinfo( $source.$entry->getFilename() );
			$fileSmall	= $path.$data['dirname'].'/'.$data['filename'].'.small.'.$data['extension'];
			$fileMedium	= $path.$data['dirname'].'/'.$data['filename'].'.medium.'.$data['extension'];
			$fileInfo	= getimagesize( $fileName );
			if( !$fileInfo )
				throw new Exception( 'Invalid file: '.$source.$entry->getFilename() );

			if( !file_exists( $fileSmall ) ){
				$angle	= 0;
				try{
					$exif	= new UI_Image_Exif( $fileName );
					switch( $exif->get( 'Orientation' ) ){
						case 1:
							break;
						case 3:
							$angle	= 180;
							break;
						case 6:
							$angle	= 270;
							break;
						case 8:
							$angle	= 90;
							break;
					}
				}
				catch( Exception $e ){}

				try{
					copy( $fileName, $fileSmall );
					if( $angle )
						UI_Image_Rotator::rotateImage( $fileSmall, $angle );

					if( $fileInfo[0] > $config['thumb.width'] || $fileInfo[1] > $config['thumb.height'] ){
						$creator	= new UI_Image_ThumbnailCreator( $fileSmall, $fileSmall, $config['thumb.quality'] );
						$creator->thumbizeByLimit( $config['thumb.width'], $config['thumb.height'] );
						if( $fileInfo[0] > $config['image.width'] || $fileInfo[1] > $config['image.height'] ){
							copy( $fileName, $fileMedium );
							if( $angle )
								UI_Image_Rotator::rotateImage( $fileMedium, $angle );
							$creator	= new UI_Image_ThumbnailCreator( $fileMedium, $fileMedium, $config['image.quality'] );
							$creator->thumbizeByLimit( $config['image.width'], $config['image.height'] );
						}
					}
				}
				catch( Exception $e ){
					throw new Exception( 'Image import failed: '.$e->getMessage() );
				}
			}
		}
		$fileBottom	= 'html/gallery/index.bottom.html';
		if( !$source )
			$this->setData( array( 'textBottom' => $this->loadContentFile( $fileBottom ) ) );

		$data		= $this->env->getConfig()->getAll( 'module.gallery_compact.license.' );
		$this->addData( 'license', $this->loadContentFile( 'html/gallery/license.html', $data ) );
	}

	/**
	 *	Display EXIF Data of Image.
	 *	@access		public
	 *	@return		void
	 */
	public function info(){
		$keys	= array( 'info.top', 'info.bottom', 'info.info' );
		$path	= 'html/gallery/';
		$data		= $this->env->getConfig()->getAll( 'module.gallery_compact.license.' );
		$this->addData( 'text', $this->populateTexts( $keys, $path ) );
		$this->addData( 'license', $this->loadContentFile( $path.'license.html', $data ) );
	}
}
?>
