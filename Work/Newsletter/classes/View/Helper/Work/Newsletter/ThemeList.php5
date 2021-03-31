<?php
class View_Helper_Work_Newsletter_ThemeList
{
	protected $env;
	protected $moduleConfig;
	protected $themePath;
	protected $themes			= array();

	/**
	 *	@todo		clear theme path handling
	 */
	public function __construct( CMF_Hydrogen_Environment $env )
	{
		$this->env	= $env;
		$this->moduleConfig	= $this->env->config->getAll( 'module.work_newsletter.theme.', TRUE );
		$this->themePath	= $this->moduleConfig->get( 'path' );
		$this->themePath	= 'contents/themes/';
	}

	/**
	 *	@access		public
	 *	@return		string
	 */
	public function render(): string
	{
		$list	= array();
		foreach( $this->themes as $theme ){
			$list[]	= $this->renderItem( $theme );
		}
		return \UI_HTML_Tag::create( 'ul', join( $list ), array( 'class' => 'thumbnails' ) );
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
			<img src="'.$this->themePath.$theme->folder.'/template.png" style="height: 200px; border: 1px solid rgba(127, 127, 127, 0.5);" data-class="img-polaroid"/>
		</a>
		<h4><a href="./work/newsletter/template/viewTheme/'.$theme->id.'">'.$theme->title.'</a></h4>
	</div>
</li>';
	}
}
