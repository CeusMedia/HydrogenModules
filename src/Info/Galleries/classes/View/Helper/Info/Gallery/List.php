<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class View_Helper_Info_Gallery_List extends View_Helper_Info_Gallery
{
	protected ?string $baseUriPath		= NULL;

	/**
	 *	@return		string
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function render(): string
	{
		$list		= [];
		$words		= $this->env->getLanguage()->getWords( 'info/gallery' );
		foreach( $this->getGalleries() as $gallery ){
			$urlGallery		= self::getGalleryUrl( $gallery, $this->baseUriPath );
			$heading		= $gallery->title ? HtmlTag::create( 'h4', $gallery->title ) : "";
			$heading		= HtmlTag::create( 'a', $heading, ['href' => $urlGallery] );
			$description	= self::renderGalleryDescription( $this->env, $this, $gallery );
			$image			= $this->renderGalleryImage( $gallery->galleryId );
			$button			= HtmlTag::create( 'a', $words['index']['buttonGallery'], [
				'href'	=> $urlGallery,
				'class'	=> 'btn not-btn-primary',
			] );
			$item	= '<div class="row-fluid"><div class="span3">'.$image.'</div><div class="span9">'.$heading.$description.'<br/>'.$button.'</div></div>';
			$list[]	= $item;
		}
		return join( '<hr/>', $list );
	}

	public function setBaseUriPath( string $path ): self
	{
		$this->baseUriPath	= $path;
		return $this;
	}

	//  --  PROTECTED  --  //

	/**
	 *	@param		int|string		$galleryId
	 *	@return		string
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function renderGalleryImage( int|string $galleryId ): string
	{
		$gallery		= $this->modelGallery->get( $galleryId );
		$images			= $this->getGalleryImages( $galleryId );
		if( !count( $images ) )
			return '';

		$thumb	= HtmlTag::create( 'img', NULL, [
			'src'	=> $this->baseFilePath.$gallery->path.'/thumbs/'.$images[0]->filename,
			'class'	=> $this->moduleConfig->get( 'index.thumb.class'),
			'alt'	=> htmlspecialchars( $images[0]->title, ENT_QUOTES, 'UTF-8' ),
		] );
		return HtmlTag::create( 'a', $thumb, [
			'href'	=> self::getGalleryUrl( $gallery, $this->baseUriPath ),
//			'class'	=> $this->getThumbnailLinkClass( View_Helper_Info_Gallery::SCOPE_GALLERY ),
			'title'	=> htmlspecialchars( $gallery->title, ENT_QUOTES, 'UTF-8' ),
		] );
	}
}
