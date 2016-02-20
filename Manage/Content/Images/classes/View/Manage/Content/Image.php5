<?php
class View_Manage_Content_Image extends CMF_Hydrogen_View{

	protected $path;
	protected $frontend;
	protected $moduleConfig;
	protected $extensions;

	public function __onInit(){
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.manage_content_images.', TRUE );
		$this->frontend		= Logic_Frontend::getInstance( $this->env );
		$this->path			= $this->frontend->getPath().$this->moduleConfig->get( 'path.images' );
		$this->extensions	= preg_split( "/\s*,\s*/", $this->moduleConfig->get( 'extensions' ) );
	}

	public function addFolder(){}
	public function addImage(){}
	public function editFolder(){}
	public function editImage(){}
	public function index(){}

	protected function countFilesInFolder( $path ){
		$number	= 0;
		$index	= new DirectoryIterator( $path );													//  create folder lister
		foreach( $index as $entry ){																//  iterate folder entries
			if( !$entry->isDir() && $entry->isFile() ){												//  only if entry is a file
				$extension	= strtolower( pathinfo( $entry->getFilename(), PATHINFO_EXTENSION ) );	//  get lowercased file extension
				$number		+= in_array( $extension, $this->extensions ) ? 1 : 0;					//  count file if extension is allowed
			}
		}
		return $number;
	}

	protected function listFolders( $currentPath ){
		$words	= (object) $this->getWords( 'index' );
		$start	= microtime( TRUE );
		$list   = array();
		$folders	= $this->getData( 'folders' );
		foreach( $folders as $folder ){
			$name		= preg_replace( "/^\.\//", "", $folder );
			$name		= $name == "." ? '<small class="muted"><em>'.$words->labelRoot.'</em></small>' : $name;

			$number		= $this->countFilesInFolder( $this->path.$folder );
			$badge		= UI_HTML_Tag::create( 'span', $number, array( 'class' => 'badge badge-file-number' ) );
			$badge		= UI_HTML_Tag::create( 'small', '('.$number.')', array( 'class' => 'muted' ) );

			$label		= UI_HTML_Tag::create( 'span', $name.' '.$badge, array( 'class' => 'item-label autocut' ) );
			if( strlen( $folder ) > 45 )
				$label		= UI_HTML_Tag::create( 'small', $name.' '.$badge, array( 'class' => 'autocut' ) );
			$link		= UI_HTML_Tag::create( 'a', $label, array(
				'href'	=> './manage/content/image/'.base64_encode( $folder ),
//				'class'	=> 'autocut',
			) );
			$list[$folder]	= UI_HTML_Tag::create( 'li', $link, array(
				'class'	=> 'not-autocut '.( $folder == $currentPath ? "active" : NULL ),
				'title'	=> $folder,
			) );
		}
		$time	= '<div class="label">'.round( ( microtime( TRUE ) - $start ) * 1000, 1 ).'ms</div>';
		if( $list )
			return UI_HTML_Tag::create( 'ul', $list, array(
				'class'	=> 'nav nav-pills nav-stacked nav-bordered nav-resizing',
				'id'	=> 'list-folders',
		) );
	}

	public function listImages( $path, $maxWidth, $maxHeight ){
		$list			= array();
		$index			= new DirectoryIterator( $this->path.$path );
		$thumbnailer	= $this->getData( 'helperThumbnailer' );
		foreach( $index as $entry ){
			if( !$entry->isFile() )
				continue;
			$extension	= strtolower( pathinfo( $entry->getFilename(), PATHINFO_EXTENSION ) );
			if( !in_array( $extension, $this->extensions ) )
				continue;
			$imagePath	= substr( $entry->getPathname(), strlen( $this->path ) );
			$thumb		= $thumbnailer->get( $entry->getPathname(), $maxWidth, $maxHeight );
			$image		= UI_HTML_Tag::create( 'img', NULL, array( 'src' => $thumb ) );
			$label		= UI_HTML_Tag::create( 'div', $entry->getFilename() );
			$thumbnail	= UI_HTML_Tag::create( 'div', $image.$label );
			$key		= $entry->getFilename();
			$list[$key]	= UI_HTML_Tag::create( 'li', $thumbnail, array( 'data-image-hash' => addslashes( base64_encode( $imagePath ) ) ) );
		}
		natcasesort( $list );
		if( $list )
			return UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'thumbs' ) );
	}
}
?>
