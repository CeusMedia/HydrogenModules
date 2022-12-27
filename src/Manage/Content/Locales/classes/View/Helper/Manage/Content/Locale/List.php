<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class View_Helper_Manage_Content_Locale_List{

	protected $current;
	protected $env;
	protected $files;
	protected $folder;
	protected $language;

	public function __construct( $env ){
		$this->env		= $env;
	}

	public function render(){
		$filterPrefix	= Controller_Manage_Content_Locale::$filterPrefix;
		if( !$this->folder )
 			throw new RuntimeException( 'No folder set' );
		if( !$this->language )
 			throw new RuntimeException( 'No language set' );
//		if( !$this->folders )
// 			throw new RuntimeException( 'No folders set' );
		$showEmpty	= $this->env->getSession()->get( $filterPrefix.'empty' );
		$list		= [];
		$lastPath	= NULL;
		$iconFolder	= HtmlTag::create( 'i', '', ['class' => 'icon-chevron-down'] );
		$iconFile	= HtmlTag::create( 'i', '', ['class' => 'icon-file'] );
		foreach( $this->files as $filePath => $file ){
			if( !$showEmpty && !$file->size )
				continue;
			$url	= './manage/content/locale/edit/'.$this->folder.'/'.$this->language.'/'.base64_encode( $file->pathName );
			$pathName	= dirname( $file->pathName );
			if( $pathName !== $lastPath ){
				$path	= $iconFolder.' '.$pathName;
				$list[]	= HtmlTag::create( 'li', $path, ['class' => 'folder'] );
				$lastPath	= $pathName;
			}
			$class		= $file->size ? NULL : 'empty';
			$fileExt	= HtmlTag::create( 'small', '.'.$file->extension, ['class' => 'muted'] );
			$fileBase	= $file->baseName;
			$link	= HtmlTag::create( 'a', $fileBase.$fileExt, array(
				'href'	=> $url,
				'class'	=> $class
			) );
			$class	= $this->current == $file->pathName ? "active" : "";
			$list[]	= HtmlTag::create( 'li', $link, ['class' => $class] );
		}
		$attributes	= ['class' => 'nav nav-pills nav-stacked boxed', 'id' => 'list-files', 'style' => 'display: none'];
		$list	= HtmlTag::create( 'ul', $list, $attributes );
		return $list;
	}

	public function setCurrent( $current ){
		$this->current	= $current;
	}

	public function setFiles( $files ){
		$this->files	= $files;
		ksort( $this->files, SORT_FLAG_CASE | SORT_NATURAL );
	}

	public function setFolder( $folder ){
		$this->folder	= $folder;
	}

	public function setLanguage( $language ){
		$this->language	= $language;
	}
}
