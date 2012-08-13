<?php
class View_Gallery extends CMF_Hydrogen_View{

	public function __onInit(){
		$converters	= array(
			"formatText",
			"formatLinks",
			"formatImageSearch",
			"formatMapSearch",
			"formatCurrencies",
			"formatWikiLinks",
			"formatYoutubeLinks",
			"formatImdbLinks",
			"formatMapLinks",
			"formatBreaks",
			"formatCodeBlocks",
			"formatLists",
		);
		foreach( $converters as $converter )
			View_Helper_ContentConverter::register( "View_Helper_ContentConverter", $converter );
	}

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
		$words		= $this->getWords( 'feed' );
		$galleries	= $this->getData( 'galleries' );
		$path		= $this->getData( 'path' );
		$debug		= $this->getData( 'debug' );
		$config		= $this->env->getConfig();
		$baseUrl	= $config->get( 'app.base.url' );
		$channel	= array(
			'link'		=> $baseUrl.'gallery',
			'language'	=> $config->get( 'module.gallery_compact.feed.language' ),
			'generator'	=> 'cmClasses::XML_RSS_Builder/'.CMC_VERSION,
			'title'		=> $words->title,
		);
		if( $config->get( 'app.name' ) )
			$channel['title']	= $config->get( 'app.name' ).': '.$words->title;
		if( $words->description )
			$channel['description']	= $words->description;
		if( $words->category )
			$channel['category']	= $words->category;
		if( $words->copyright )
			$channel['copyright']	= $words->copyright;
		if( $config->get( 'module.gallery_compact.feed.editor' ) )
			$channel['managingEditor']	= $config->get( 'module.gallery_compact.feed.editor' );
		if( $config->get( 'app.email' ) ){
			$channel['webMaster']	= $config->get( 'app.email' );
			if( $config->get( 'app.author' ) )
				$channel['webMaster']	.=' ('.$config->get( 'app.author' ).')';
		}

		$feed		= new XML_RSS_Builder();
		$feed->setChannelData( $channel );
		foreach( $galleries as $gallery ){
			$uri	= $baseUrl.'gallery/index/'.str_replace( '%2F', '/', rawurlencode( $gallery->pathname ) );

			$content	= array_shift( explode( "\n", strip_tags( $gallery->content ) ) );
			$content	= View_Helper_ContentConverter::formatText( $this->env, $content );
			$content	= View_Helper_ContentConverter::formatLinks( $this->env, $content );
			$content	= View_Helper_ContentConverter::formatWikiLinks( $this->env, $content );
			$content	= View_Helper_ContentConverter::formatYoutubeLinks( $this->env, $content );
			$content	= View_Helper_ContentConverter::formatMapLinks( $this->env, $content );
			$content	= View_Helper_ContentConverter::formatMapSearch( $this->env, $content );
			$content	= View_Helper_ContentConverter::formatImageSearch( $this->env, $content );
			$content	= View_Helper_ContentConverter::formatImdbLinks( $this->env, $content );
			$content	= View_Helper_Blog::formatBlogLinks( $this->env, $content );
			if( $this->env->getModules()->has( 'Gallery_Compact' ) )
				$content	= View_Helper_Gallery::formatGalleryLinks( $this->env, $content );
			
			$data	= array(
				'title'			=> $gallery->label,
				'description'	=> $content,
				'guid'			=> $uri,
				'link'			=> $uri,
				'category'		=> 'Foto-Galerie',
				'source'		=> $baseUrl.'gallery/feed',
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
