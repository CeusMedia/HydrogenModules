<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class View_Helper_Info_Gallery_Images extends View_Helper_Info_Gallery
{
	protected int|string|NULL $galleryId	= NULL;
	protected ?object $gallery				= NULL;

	public function render(): string
	{
		$list	= [];
		$images	= $this->getGalleryImages( $this->galleryId );
		foreach( $images as $image ){
			$thumb	= HtmlTag::create( 'img', NULL, array(
				'src'	=> $this->baseFilePath.$this->gallery->path.'/thumbs/'.rawurlencode( $image->filename ),
				'class'	=> $this->moduleConfig->get( 'gallery.thumb.class'),
				'alt'	=> htmlspecialchars( $image->title, ENT_QUOTES, 'UTF-8' ),
			) );
			$link	= HtmlTag::create( 'a', $thumb, array(
				'href'			=> $this->baseFilePath.$this->gallery->path.'/'.rawurlencode( $image->filename ),
				'class'			=> $this->getThumbnailLinkClass( View_Helper_Info_Gallery::SCOPE_IMAGE ),
				'rel'			=> 'gallery-'.$this->galleryId,
				'title'			=> htmlspecialchars( $image->title, ENT_QUOTES, 'UTF-8' ),
				'data-fancybox'	=> 'gallery',
				'data-type'		=> 'image',
				'data-caption'	=> htmlspecialchars( $image->title, ENT_QUOTES, 'UTF-8' ),
			) );
			$list[]	= HtmlTag::create( 'li', $link );
		}
		return HtmlTag::create( 'ul', $list, ['class' => 'thumbnails equalize-auto'] );
	}

	/**
	 *	@param		int|string		$galleryId
	 *	@return		self
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function setGallery( int|string $galleryId ): self
	{
		$this->galleryId	= $galleryId;
		$this->gallery		= $this->modelGallery->get( $galleryId );

		$parts	= [];
		foreach( explode( '/', $this->gallery->path ) as $part )
			$parts[]	= rawurlencode( $part );
		$this->gallery->path = join( '/', $parts );
		return $this;
	}
}
