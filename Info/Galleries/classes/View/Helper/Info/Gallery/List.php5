<?php
class View_Helper_Info_Gallery_List extends View_Helper_Info_Gallery{

	public function render(){
		$list		= array();
		$words		= $this->env->getLanguage()->getWords( 'info/gallery' );
		foreach( $this->getGalleries() as $gallery ){
			$urlGallery		= self::getGalleryUrl( $gallery, $this->baseUriPath );
			$heading		= $gallery->title ? UI_HTML_Tag::create( 'h4', $gallery->title ) : "";
			$heading		= UI_HTML_Tag::create( 'a', $heading, array( 'href' => $urlGallery ) );
			$description	= self::renderGalleryDescription( $this->env, $this, $gallery );
			$image			= $this->renderGalleryImage( $gallery->galleryId );
			$button			= UI_HTML_Tag::create( 'a', $words['index']['buttonGallery'], array(
				'href'	=> $urlGallery,
				'class'	=> 'btn not-btn-primary',
			) );
			$item	= '<div class="row-fluid"><div class="span3">'.$image.'</div><div class="span9">'.$heading.$description.'<br/>'.$button.'</div></div>';
			$list[]	= $item;
		}
		return join( '<hr/>', $list );
	}

	protected function renderGalleryImage( $galleryId ){
		$gallery		= $this->modelGallery->get( $galleryId );
		$images			= $this->getGalleryImages( $galleryId );
		if( !count( $images ) )
			return;

		$thumb	= UI_HTML_Tag::create( 'img', NULL, array(
			'src'	=> $this->baseFilePath.$gallery->path.'/thumbs/'.$images[0]->filename,
			'class'	=> $this->moduleConfig->get( 'index.thumb.class'),
			'alt'	=> htmlspecialchars( $images[0]->title, ENT_QUOTES, 'UTF-8' ),
		) );
		$link	= UI_HTML_Tag::create( 'a', $thumb, array(
			'href'	=> self::getGalleryUrl( $gallery, $this->baseUriPath ),
//			'class'	=> $this->getThumbnailLinkClass( View_Helper_Info_Gallery::SCOPE_GALLERY ),
			'title'	=> htmlspecialchars( $gallery->title, ENT_QUOTES, 'UTF-8' ),
		) );
		return $link;
	}

	public function setBaseUriPath( $path ){
		$this->baseUriPath	= $path;
	}
}
?>
