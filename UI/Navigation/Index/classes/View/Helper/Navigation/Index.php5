<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class View_Helper_Navigation_Index
{
	protected $env;
	protected $menu;
	protected $scope			= 'main';
	protected $linksToSkip		= [];

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment	Environment instance
	 *	@throws		RuntimeException	if module UI_Navigation is not installed
	 */
	public function __construct( $env )
	{
		$this->env	= $env;
		if( !$this->env->getModules()->has( 'UI_Navigation' ) )
			throw new RuntimeException( 'Module "UI_Navigation" is required' );
		$this->menu	= new Model_Menu( $this->env );
	}

	/**
	 *	Renders nav index of all known and accessible pages of currently selected scope.
	 *	Pages will be filtered by accessibility by current user, if enabled.
	 *	@access		public
	 *	@return		string		Rendered index
	 */
	public function render(): string
	{
		$list	= [];
		$pages	= $this->menu->getPages( $this->scope, FALSE );
		foreach( $pages as $page ){
			if( in_array( $page->path, $this->linksToSkip ) )
				continue;
			if( $page->type == 'menu' ){
				if( !$page->items )
					continue;
				$list[]	= $this->renderTopicHeadingItem( $page );
				foreach( $page->items as $subpage )
					if( !in_array( $subpage->path, $this->linksToSkip ) )
						$list[]		= $this->renderItem( $subpage );
			}
			else
				$list[]		= $this->renderItem( $page );
		}
		return HtmlTag::create( 'ul', $list, array( 'class' => 'unstyled nav-index' ) );
	}

	/**
	 *	Set list of paths to skip on rendering.
	 *	@access		public
	 *	@param		array		$linksToSkip		List of paths to skip on rendering
	 *	@return		self		This instance for chainability
	 *	@throws		InvalidArgumentException	if given argument is not an array
	 */
	public function setLinksToSkip( array $linksToSkip ): self
	{
		if( !is_array( $linksToSkip ) )
			throw new InvalidArgumentException( 'Must be an array' );
		$this->linksToSkip	= $linksToSkip;
		return $this;
	}

	/**
	 *	Sets menu scope to render index for.
	 *	@access		public
	 *	@param		string		Menu scope to render index for
	 *	@return		self		This instance for chainability
	 *	@throws		RangeException		if scope is not known
	 */
	public function setScope( string $scope ): self
	{
		if( !in_array( $scope, $this->menu->getScopes() ) )
			throw new RangeException( 'Invalid scope' );
		$this->scope		= $scope;
		return $this;
	}

	/**
	 *	Renders nav index list item of a menu page.
	 *	@access		protected
	 *	@param		object		$page		Page object to render list item for
	 *	@return		string
	 *	@todo		add page type check
	 */
	protected function renderItem( $page ): string
	{
//		if( $page->type !== '...' )
//			return;
		$href		= $page->path == "index" ? './' : './'.$page->link;
		$icon		= $page->icon ? HtmlTag::create( 'i', '', array( 'class' => $page->icon ) ).'&nbsp;' : '';
		$link		= HtmlTag::create( 'a', $icon.$page->label, array(
			'href'	=> $href,
			'class'	=> 'btn btn-large btn-block nav-index-topic-item-link'
		) );
		return HtmlTag::create( 'li', $link, array( 'class' => 'nav-index-topic-item' ) );
	}

	/**
	 *	Renders list item containing heading of a menu page.
	 *	@access		protected
	 *	@param		object		$page		Page object to render topic list item for
	 *	@return		string		List item containing heading of menu page, if page is of type menu
	 *	@todo		add page type check
	 */
	protected function renderTopicHeadingItem( $page ): string
	{
//		if( $page->type !== 'menu' )
//			return;
		$icon		= $page->icon ? HtmlTag::create( 'i', '', array( 'class' => $page->icon ) ).'&nbsp;' : '';
		$heading	= HtmlTag::create( 'div', $icon.$page->label, array( 'class' => 'nav-index-topic-heading' ) );
		return HtmlTag::create( 'li', $heading, array( 'class' => 'nav-index-topic' ) );
	}
}
