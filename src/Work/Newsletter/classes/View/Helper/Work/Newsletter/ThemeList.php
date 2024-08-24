<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Work_Newsletter_ThemeList
{
	protected Environment $env;
	protected Dictionary $moduleConfig;
	protected string $themePath;
	protected array $themes			= [];

	/**
	 *	@todo		clear theme path handling
	 */
	public function __construct( Environment $env )
	{
		$this->env	= $env;
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.work_newsletter.theme.', TRUE );
		$this->themePath	= $this->moduleConfig->get( 'path' );
		$this->themePath	= 'contents/themes/';
	}

	/**
	 *	@access		public
	 *	@return		string
	 */
	public function render(): string
	{
		$list	= [];
		foreach( $this->themes as $theme ){
			$list[]	= $this->renderItem( $theme );
		}
		return HtmlTag::create( 'ul', join( $list ), ['class' => 'thumbnails'] );
	}

	/**
	 *	Set path to newsletter themes.
	 *	@access		public
	 *	@param		string		$themePath		Path to themes
	 *	@return		self
	 */
	public function setThemePath( string $themePath ): self
	{
		$this->themePath	= $themePath;
		return $this;
	}

	/**
	 *	Set list of available newsletter themes.
	 *	@access		public
	 *	@param		array		$themes		List of available newsletter themes
	 *	@return		self
	 */
	public function setThemes( array $themes ): self
	{
		$this->themes	= $themes;
		return $this;
	}

	protected function renderItem( $theme ): string
	{
		return '<li style="width: 250px; display: inline-block; text-align: center;">
	<div class="thumbnail" style="background-color: white">
		<a href="'.$this->themePath.$theme->folder.'/template.png" class="fancybox-auto">
			'.HtmlTag::create( 'img', NULL, [
				'src'	=> $this->themePath.$theme->folder.'/template.png',
				'style'	=> 'height: 200px; border: 1px solid rgba(127, 127, 127, 0.5);',
				'alt'	=> htmlentities( $theme->name, ENT_QUOTES, 'UTF-8' ),
			], ['class' => 'img-polaroid'] ).'
		</a>
		<h4><a href="./work/newsletter/template/viewTheme/'.$theme->id.'">'.$theme->title.'</a></h4>
	</div>
</li>';
	}
}
