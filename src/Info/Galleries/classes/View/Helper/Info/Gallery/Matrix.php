<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class View_Helper_Info_Gallery_Matrix extends View_Helper_Info_Gallery
{
	/**
	 *	@return		string
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function render(): string
	{
		$list		= [];
		$helper		= new View_Helper_Info_Gallery_Images( $this->env );
		foreach( $this->getGalleries() as $gallery ){
			$heading		= $gallery->title ? HtmlTag::create( 'h4', $gallery->title ) : "";
			$helper->setGallery( $gallery->galleryId );
			$images			= $helper->render();
			$description	= self::renderGalleryDescription( $this->env, $this, $gallery );
			$list[]			= $heading.$description.'<br/>'.$images;
		}
		return join( '<hr/>', $list );
	}
}
