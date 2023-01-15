<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Resource\Captain as CaptainResource;
use CeusMedia\HydrogenFramework\Environment\Resource\Page as PageResource;
use CeusMedia\HydrogenFramework\View;

class View_Manage_Content_Style extends View
{
	protected PageResource $page;

	public function index(): void
	{
		$file	= $this->getData( 'file' );
		if( $file ){
			$this->page->js->addScriptOnReady( 'ModuleManageStyle.init("'.$file.'");' );
		}
	}

	/**
	 *	@param		string[]		$files
	 *	@param		string|NULL		$currentFile
	 *	@return		string
	 */
	public function listFiles( array $files, ?string $currentFile = NULL ): string
	{
		$words		= (object) $this->getWords( 'index.filter' );
		$list	= [];
		if( 0 === count( $files ) )
			return '<div class="muted"><em>'.$words->noEntries.'</em></div>';
		foreach( $files as $file ){
			$name	= pathinfo( $file, PATHINFO_FILENAME );
			$ext	= pathinfo( $file, PATHINFO_EXTENSION );
			$label	= $name.'<small class="muted">.'.$ext.'</small>';
			$class	= $file == $currentFile ? 'active' : NULL;
			$link	= HtmlTag::create( 'a', $label, array(
				'href'	=> './manage/content/style/'.$file,
				'class'	=> 'autocut',
				'title'	=> htmlentities( $file, ENT_QUOTES, 'UTF-8' ),
			) );
			$list[]	= HtmlTag::create( 'li', $link, ['class' => $class] );
		}
		return HtmlTag::create( 'ul', $list, array(
			'class'	=> 'nav nav-pills nav-stacked'
		) );
	}

	protected function __onInit(): void
	{
		$this->page	= $this->env->getPage();
		$pathJs	= $this->env->getConfig()->get( 'path.scripts' );
		$this->page->js->addUrl( $pathJs.'module.manage.content.style.js', CaptainResource::LEVEL_HIGH );
		$this->page->css->theme->addUrl( 'module.manage.content.style.css' );
	}
}
