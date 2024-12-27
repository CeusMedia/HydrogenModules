<?php

//use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\View;

class View_Manage_Content_Image extends View
{
	protected string $path;
//	protected Logic_Frontend $frontend;
//	protected Dictionary $moduleConfig;
	protected array $extensions;

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


	public function listFolders( ?string $currentPath ): string
	{
		$words	= (object) $this->getWords( 'index' );
//		$start	= microtime( TRUE );
		$list   = [];
		$folders	= $this->getData( 'folders' );
		foreach( $folders as $folder ){
			$name		= preg_replace( "/^\.\//", "", $folder );
			$name		= $name == "." ? '<small class="muted"><em>'.$words->labelRoot.'</em></small>' : $name;

			$number		= $this->countFilesInFolder( $this->path.$folder );
//			$badge		= HtmlTag::create( 'span', $number, ['class' => 'badge badge-file-number'] );
			$badge		= HtmlTag::create( 'small', '('.$number.')', ['class' => 'muted'] );

			$label		= HtmlTag::create( 'span', $name.' '.$badge, ['class' => 'item-label autocut'] );
			if( strlen( $folder ) > 45 )
				$label		= HtmlTag::create( 'small', $name.' '.$badge, ['class' => 'autocut'] );
			$link		= HtmlTag::create( 'a', $label, array(
				'href'	=> './manage/content/image/'.base64_encode( $folder ),
//				'class'	=> 'autocut',
			) );
			$list[$folder]	= HtmlTag::create( 'li', $link, array(
				'class'	=> 'not-autocut '.( $folder == $currentPath ? "active" : NULL ),
				'title'	=> $folder,
			) );
		}
	//	$time	= '<div class="label">'.round( ( microtime( TRUE ) - $start ) * 1000, 1 ).'ms</div>';
		if( $list )
			return HtmlTag::create( 'ul', $list, [
				'class'	=> 'nav nav-pills nav-stacked not-nav-bordered nav-resizing',
				'id'	=> 'list-folders',
			] );
		return '';
	}

	public function listImages( string $path, ?int $maxWidth = NULL, ?int $maxHeight = NULL ): string
	{
		$list			= [];
		$index			= new DirectoryIterator( $this->path.$path );
		/** @var View_Helper_Thumbnailer $helper */
		$helper		= $this->getData( 'helperThumbnailer' );
		foreach( $index as $entry ){
			if( !$entry->isFile() )
				continue;
			$extension	= strtolower( pathinfo( $entry->getFilename(), PATHINFO_EXTENSION ) );
			if( !in_array( $extension, $this->extensions ) )
				continue;
			$imagePath	= substr( $entry->getPathname(), strlen( $this->path ) );
			$thumb		= $helper->get( $entry->getPathname(), $maxWidth, $maxHeight );
			$image		= HtmlTag::create( 'img', NULL, ['src' => $thumb] );
			$label		= HtmlTag::create( 'div', $entry->getFilename() );
			$thumbnail	= HtmlTag::create( 'div', $image.$label );
			$key		= $entry->getFilename();
			$list[$key]	= HtmlTag::create( 'li', $thumbnail, ['data-image-hash' => addslashes( base64_encode( $imagePath ) )] );
		}
		natcasesort( $list );
		return $list ? HtmlTag::create( 'ul', $list, ['class' => 'thumbs'] ) : '';
	}

	/**
	 *	@return		void
	 *	@throws	ReflectionException
	 */
	protected function __onInit(): void
	{
		$frontend		= Logic_Frontend::getInstance( $this->env );
		$moduleConfig	= $this->env->getConfig()->getAll( 'module.manage_content_images.', TRUE );

		$this->path			= $frontend->getPath().$moduleConfig->get( 'path.images' );
		$this->extensions	= preg_split( "/\s*,\s*/", $moduleConfig->get( 'extensions' ) );
	}

	protected function countFilesInFolder( string $path ): int
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
}
