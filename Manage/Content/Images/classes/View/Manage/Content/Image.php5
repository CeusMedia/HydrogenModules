<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\View;

class View_Manage_Content_Image extends View
{
	protected $path;
	protected $frontend;
	protected $moduleConfig;
	protected $extensions;

	public function addFolder()
	{
	}

	public function addImage()
	{
	}

	public function editFolder()
	{
	}

	public function editImage()
	{
	}

	public function index()
	{
	}

	public function listImages( $path, $maxWidth, $maxHeight )
	{
		$list			= [];
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
			$image		= HtmlTag::create( 'img', NULL, array( 'src' => $thumb ) );
			$label		= HtmlTag::create( 'div', $entry->getFilename() );
			$thumbnail	= HtmlTag::create( 'div', $image.$label );
			$key		= $entry->getFilename();
			$list[$key]	= HtmlTag::create( 'li', $thumbnail, array( 'data-image-hash' => addslashes( base64_encode( $imagePath ) ) ) );
		}
		natcasesort( $list );
		if( $list )
			return HtmlTag::create( 'ul', $list, array( 'class' => 'thumbs' ) );
	}

	protected function __onInit()
	{
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.manage_content_images.', TRUE );
		$this->frontend		= Logic_Frontend::getInstance( $this->env );
		$this->path			= $this->frontend->getPath().$this->moduleConfig->get( 'path.images' );
		$this->extensions	= preg_split( "/\s*,\s*/", $this->moduleConfig->get( 'extensions' ) );
	}

	protected function countFilesInFolder( string $path )
	{
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

	protected function listFolders( ?string $currentPath )
	{
		$words	= (object) $this->getWords( 'index' );
		$start	= microtime( TRUE );
		$list   = [];
		$folders	= $this->getData( 'folders' );
		foreach( $folders as $folder ){
			$name		= preg_replace( "/^\.\//", "", $folder );
			$name		= $name == "." ? '<small class="muted"><em>'.$words->labelRoot.'</em></small>' : $name;

			$number		= $this->countFilesInFolder( $this->path.$folder );
			$badge		= HtmlTag::create( 'span', $number, array( 'class' => 'badge badge-file-number' ) );
			$badge		= HtmlTag::create( 'small', '('.$number.')', array( 'class' => 'muted' ) );

			$label		= HtmlTag::create( 'span', $name.' '.$badge, array( 'class' => 'item-label autocut' ) );
			if( strlen( $folder ) > 45 )
				$label		= HtmlTag::create( 'small', $name.' '.$badge, array( 'class' => 'autocut' ) );
			$link		= HtmlTag::create( 'a', $label, array(
				'href'	=> './manage/content/image/'.base64_encode( $folder ),
//				'class'	=> 'autocut',
			) );
			$list[$folder]	= HtmlTag::create( 'li', $link, array(
				'class'	=> 'not-autocut '.( $folder == $currentPath ? "active" : NULL ),
				'title'	=> $folder,
			) );
		}
		$time	= '<div class="label">'.round( ( microtime( TRUE ) - $start ) * 1000, 1 ).'ms</div>';
		if( $list )
			return HtmlTag::create( 'ul', $list, array(
				'class'	=> 'nav nav-pills nav-stacked not-nav-bordered nav-resizing',
				'id'	=> 'list-folders',
		) );
	}
}
