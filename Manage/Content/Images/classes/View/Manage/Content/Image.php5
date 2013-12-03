<?php
class View_Manage_Content_Image extends CMF_Hydrogen_View{

	protected $path;

	public function __onInit(){
		$config				= $this->env->getConfig()->getAll( 'module.manage_content_images.', TRUE );
		$this->path			= $config->get( 'frontend.path' ).$config->get( 'path.images' );
		$this->extensions	= $config->get( 'extensions' );
	}

	public function addFolder(){}
	public function addImage(){}
	public function editFolder(){}
	public function editImage(){}
	public function index(){}


	protected function countFilesInFolder( $path ){
		$number	= 0;
		$index	= new DirectoryIterator( $path );
		foreach( $index as $entry )
			if( $entry->isFile() )
				if( preg_match( $this->extensions, $entry->getFilename() ) )
					$number++;
		return $number;
	}

	protected function listFolders( $currentPath ){
		$start	= microtime( TRUE );
		$list   = array();
		$folders	= $this->getData( 'folders' );
		foreach( $folders as $folder ){
			$name		= basename( $folder );
			$number		= $this->countFilesInFolder( $this->path.$folder );
			$attributes	= array( 'href' => './manage/content/image?path='.$folder );
			$label		= $folder.' <span class="pull-right badge badge">'.$number.'</span>';
		#	$label		= $folder.' <small class="muted" style="font-weight: normal">('.$number.')</small>';
			$link		= UI_HTML_Tag::create( 'a', $label, $attributes );
			$attributes	= array( 'class' => $folder == $currentPath ? "active" : NULL );
			$list[$folder]	= UI_HTML_Tag::create( 'li', $link, $attributes );
		}
		$time	= '<div class="label">'.round( ( microtime( TRUE ) - $start ) * 1000, 1 ).'ms</div>';
		if( $list )
			return UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'nav nav-pills nav-stacked' ) )/*.$time*/;
	}

	public function listImages( $path, $maxWidth, $maxHeight ){
		$list			= array();
		$index			= new DirectoryIterator( $this->path.$path );
		$thumbnailer	= new View_Helper_Thumbnailer( $this->env );
		$extensions		= array( "jpe", "jpeg", "jpg", "png", "gif" );
		foreach( $index as $entry ){
			if( !$entry->isFile() )
				continue;
			$extension	= strtolower( pathinfo( $entry->getFilename(), PATHINFO_EXTENSION ) );
			if( !in_array( $extension, $extensions ) )
				continue;
			$imagePath	= substr( $entry->getPathname(), strlen( $pathImages ) );
			$thumb		= $thumbnailer->get( $entry->getPathname(), $maxWidth, $maxHeight );
			$image		= UI_HTML_Tag::create( 'img', NULL, array( 'src' => $thumb ) );
			$label		= UI_HTML_Tag::create( 'div', $entry->getFilename() );
			$thumbnail	= UI_HTML_Tag::create( 'div', $image.$label );
			$list[]		= UI_HTML_Tag::create( 'li', $thumbnail, array( 'data-image' => addslashes( $imagePath ) ) );
		}
		if( $list )
			return UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'thumbs' ) );
	}
}
?>
