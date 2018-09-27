<?php
class View_Helper_Navigation_Index{

	protected $env;
	protected $menu;
	protected $scope			= 'main';
	protected $linksToSkip		= array();

	/**
	 *	Constructur.
	 *	@throws		RuntimeException	if module UI_Navigation is not installed
	 */
	public function __construct( $env ){
		$this->env	= $env;
		if( !$this->env->getModules()->has( 'UI_Navigation' ) )
			throw new RuntimeException( 'Module "UI_Navigation" is required' );
		$this->menu	= new Model_Menu( $this->env );
	}

	public function render(){
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
		return UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'unstyled nav-index' ) );
	}

	protected function renderItem( $page ){
		$link	= $this->renderItemLink( $page );
		return UI_HTML_Tag::create( 'li', $link, array( 'class' => 'nav-index-topic-item' ) );
	}

	protected function renderItemLink( $page ){
		$href		= $page->path == "index" ? './' : './'.$page->link;
		$icon		= $page->icon ? UI_HTML_Tag::create( 'i', '', array( 'class' => $page->icon ) ).'&nbsp;' : '';
		$title		= $icon.$page->label;
		$link		= UI_HTML_Tag::create( 'a', $title, array( 'href' => $href, 'class' => 'btn btn-large btn-block nav-index-topic-item-link' ) );
		return $link;
	}

	protected function renderTopicHeadingItem( $page ){
		$icon		= $page->icon ? UI_HTML_Tag::create( 'i', '', array( 'class' => $page->icon ) ).'&nbsp;' : '';
		$heading	= UI_HTML_Tag::create( 'div', $icon.$page->label, array( 'class' => 'nav-index-topic-heading' ) );
		return UI_HTML_Tag::create( 'li', $heading, array( 'class' => 'nav-index-topic' ) );
	}

	public function setLinksToSkip( $linksToSkip ){
		$this->linksToSkip	= $linksToSkip;
	}

	public function setScope( $scope ){
		$this->scope		= $scope;
	}
}
