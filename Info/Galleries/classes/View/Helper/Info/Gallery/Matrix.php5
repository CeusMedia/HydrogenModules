<?php
class View_Helper_Info_Gallery_Matrix extends View_Helper_Info_Gallery{

	public function render(){
		$list		= array();
		$helper		= new View_Helper_Info_Gallery_Images( $this->env );
		foreach( $this->getGalleries() as $gallery ){
			$heading		= $gallery->title ? UI_HTML_Tag::create( 'h4', $gallery->title ) : "";
			$helper->setGallery( $gallery->galleryId );
			$images			= $helper->render();
			$description	= self::renderGalleryDescription( $this->env, $this, $gallery );
			$list[]			= $heading.$description.'<br/>'.$images;
		}
		return join( '<hr/>', $list );
	}
}
?>
