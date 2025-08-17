<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\FS\File\Reader as FileReader;
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
		$this->moduleConfig		= $this->env->getConfig()->getAll( 'module.work_newsletter.', TRUE );

		$resourceModuleConfig	= $this->env->getConfig()->getAll( 'module.resource_newsletter.', TRUE );
		$this->themePath		= $resourceModuleConfig->get( 'path.themes' );
	}

	/**
	 *	@access		public
	 *	@return		string
	 */
	public function render(): string
	{
		$list	= [];
		foreach( $this->themes as $theme )
			$list[]	= $this->renderItem( $theme );
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
		$base64	= base64_encode( FileReader::load( $this->themePath.$theme->folder.'/template.png' ) );
		$image	= HtmlTag::create( 'img', NULL, [
			'src'	=> 'data:image/jpeg;base64,'.$base64,
			'style'	=> 'height: 200px; border: 1px solid rgba(127, 127, 127, 0.5);',
			'alt'	=> htmlentities( $theme->title, ENT_QUOTES, 'UTF-8' ),
		], ['class' => 'img-polaroid'] );
		$linkedImage	= HtmlTag::create( 'a', $image, [
			'href'	=> 'data:image/jpeg;base64,'.$base64,
			'class'	=> 'fancybox-auto',
		] );
		$linkedTitle	= HtmlTag::create( 'a', $theme->title, [
			'href'	=> './work/newsletter/template/viewTheme/'.$theme->id,
		] );
		$thumbnail	= HtmlTag::create( 'div', $linkedImage.'<h4>'.$linkedTitle.'</h4>', [
			'class'	=> 'thumbnail',
			'style'	=> 'background-color: white',
		] );
		return HtmlTag::create( 'li', $thumbnail , [
			'style'	=> 'width: 250px; display: inline-block; text-align: center;',
		] );
	}
}
