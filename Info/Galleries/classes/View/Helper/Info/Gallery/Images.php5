<?php
class View_Helper_Info_Gallery_Images extends View_Helper_Info_Gallery{

	public function render(){
		$list	= array();
		$images	= $this->getGalleryImages( $this->galleryId );
		foreach( $images as $image ){
			$thumb	= UI_HTML_Tag::create( 'img', NULL, array(
				'src'	=> $this->baseFilePath.$this->gallery->path.'/thumbs/'.rawurlencode( $image->filename ),
				'class'	=> $this->moduleConfig->get( 'gallery.thumb.class'),
				'alt'	=> htmlspecialchars( $image->title, ENT_QUOTES, 'UTF-8' ),
			) );
			$link	= UI_HTML_Tag::create( 'a', $thumb, array(
				'href'			=> $this->baseFilePath.$this->gallery->path.'/'.rawurlencode( $image->filename ),
				'class'			=> $this->getThumbnailLinkClass( View_Helper_Info_Gallery::SCOPE_IMAGE ),
				'rel'			=> 'gallery-'.$this->galleryId,
				'title'			=> htmlspecialchars( $image->title, ENT_QUOTES, 'UTF-8' ),
				'data-fancybox'	=> 'gallery',
				'data-type'		=> 'image',
				'data-caption'	=> htmlspecialchars( $image->title, ENT_QUOTES, 'UTF-8' ),
			) );
			$list[]	= UI_HTML_Tag::create( 'li', $link );
		}
		return UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'thumbnails equalize-auto' ) );
	}

	public function setGallery( $galleryId ){
		$this->galleryId	= $galleryId;
		$this->gallery		= $this->modelGallery->get( $galleryId );

		$parts	= array();
		foreach( preg_split( '@/@', $this->gallery->path ) as $part )
			$parts[]	= rawurlencode( $part );
		$this->gallery->path = join( '/', $parts );

	}
}
?>
