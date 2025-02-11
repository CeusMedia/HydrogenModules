<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Info_Manual_PageTree
{
	protected Environment $env;
	protected Model_Manual_Page $modelPage;
	protected array $pages						= [];
	protected array $openParents				= [];
	protected int|string|NULL $activePageId				= NULL;
	protected int|string|NULL $parentPageId				= NULL;
	protected int|string|NULL $categoryId				= NULL;

	public function __construct( Environment $env )
	{
		$this->env			= $env;
		$this->modelPage	= new Model_Manual_Page( $env );
	}

	public function __toString(): string
	{
		return $this->render();
	}

	public function render(): string
	{
		$words	= $this->env->getLanguage()->getWords( 'info/manual' );

		if( $this->categoryId ){
			$conditions		= [
				'status'			=> '>= '.Model_Manual_Page::STATUS_NEW,
				'parentId'			=> 0,
				'manualCategoryId'	=> $this->categoryId,
			];
			$orders		= ['rank' => 'ASC'];
			$pages		= $this->modelPage->getAll( $conditions, $orders );
		}
		else if( $this->parentPageId ){
			$conditions		= [
				'status'		=> '>= '.Model_Manual_Page::STATUS_NEW,
				'parentId'		=> $this->parentPageId,
			];
			$orders		= ['rank' => 'ASC'];
			$pages		= $this->modelPage->getAll( $conditions, $orders );
		}
		else
			throw new RuntimeException( 'Neither category nor parent page set' );

		$tree		= $this->getPageTree( $pages );

		$tree		= $this->renderPageTree( $tree );
//print_m($tree);die;
		$container	= HtmlTag::create( 'div', '', ['id' => 'page-tree'] );
		$script		= '
jQuery("#page-tree").treeview({
	data: '.json_encode( $tree ).',
	enableLinks: true,
	showBorder: false,
	emptyIcon: "fa fa-fw fa-file-o",
	emptyIcon: "fa fa-fw fa-angle-right",
	expandIcon: "fa fa-fw fa-folder-o",
	collapseIcon: "fa fa-fw fa-folder-open-o",
	showIcon: true,
	onhoverColor: "transparent",
});
InfoManual.UI.Tree.init("#page-tree");';
		$this->env->getPage()->js->addScriptOnReady( $script );
		return $container;

/*
		$html		= $this->renderPageTree( $tree );
//		$container	= HtmlTag::create( 'div', $html, ['id' => 'page-tree'];
//		$this->env->getPage()->js->addScriptOnReady( 'jQuery("#page-tree").')
		return $container;
		print_m( $tree );die;

		$list	= [];
		foreach( $this->pages as $entry ){
			$link	= HtmlTag::create( 'a', $entry->title, ['href' => './info/manual/page/'.$entry->manualPageId.'-'.$this->urlencode( $entry->title] ) );
			$class	= 'autocut '.( $this->activePageId == $entry->manualPageId ? 'active' : '' );
			$list[]	= HtmlTag::create( 'li', $link, ['class' => $class] );
		}
		return HtmlTag::create( 'ul', $list, ['class' => 'nav nav-pills nav-stacked'] );*/
	}


	public function setActivePageId( int|string $pageId ): self
	{
		$this->activePageId	= $pageId;
		$this->openParents	= [];
		$page	= $this->modelPage->get( $pageId );
		while( $page && $page->parentId ){
			$page	= $this->modelPage->get( $page->parentId );
			if( $page )
				$this->openParents[]	= $page->manualPageId;
		}
		return $this;
	}

	public function setCategoryId( int|string $categoryId ): self
	{
		$this->categoryId	= $categoryId;
		return $this;
	}

	/**
	 *	@param		object[]		$pages
	 *	@return		self
	 */
	public function setPages( array $pages ): self
	{
		$this->pages	= $pages;
		return $this;
	}

	public function setParentPage( int|string $pageId ): self
	{
		$this->parentPageId		= $pageId;
		return $this;
	}

	//  --  PROTECTED  --  //

	protected function getPageTree( array $pages ): array
	{
		$tree	= [];
		foreach( $pages as $page ){
			$conditions		= [
				'status'		=> '>= '.Model_Manual_Page::STATUS_NEW,
				'parentId'		=> $page->manualPageId,
			];
			$orders			= ['rank' => 'ASC'];
			$children		= $this->modelPage->getAll( $conditions, $orders );
			$page->children	= $this->getPageTree( $children );
			$tree[]			= $page;
		}
		return $tree;
	}

	protected function renderPageTree( array $tree ): array
	{
		$session		= $this->env->getSession();
		$sessionPrefix	= 'filter_info_manual_';
		$categoryId		= $session->get( $sessionPrefix.'categoryId' );
		$sessionKeyOpen	= $sessionPrefix.'categoryId_'.$categoryId.'_openFolders';
		$openPages		= array_filter( explode( ',', $session->get( $sessionKeyOpen ) ) );
		$openPages		= array_merge( $openPages, $this->openParents );

		$list	= [];
		foreach( $tree as $entry ){
			$isOpen		= in_array( $entry->manualPageId, $openPages );

			$sublist	= '';
			$link		= './info/manual/page/'.$entry->manualPageId.'-'.$this->urlencode( $entry->title );
			$children	= $this->renderPageTree( $entry->children );
			$list[]	= (object) [
				'text'			=> $entry->title,
				'href'			=> $link,
				'selectable'	=> false,
				'state'			=> (object) [
					'expanded'	=> $isOpen,
					'selected'	=> $this->activePageId == $entry->manualPageId,
				],
				'color'			=> '!inherit',
//				'data'			=> ['pageId' => $entry->manualPageId],				//  not working with this version of bootstrap-treeview
//				'tags'			=> ['pageId:'.$entry->manualPageId],					//  not working with this version of bootstrap-treeview
				'nodes'			=> $children ?: NULL,
			];
		}
		return $list;

/*		$list	= [];
		foreach( $tree as $entry ){
			$sublist	= '';
			if( $entry->children )
				$sublist	= $this->renderPageTree( $entry->children );
			$link	= HtmlTag::create( 'a', $entry->title, [
				'href'	=> './info/manual/page/'.$entry->manualPageId.'-'.$this->urlencode( $entry->title )
			] );
			$class	= 'autocut '.( $this->activePageId == $entry->manualPageId ? 'active' : '' );
			$list[]	= HtmlTag::create( 'li', $link.$sublist, ['class' => $class] );
		}
		return HtmlTag::create( 'ul', $list, ['class' => 'not-nav not-nav-pills not-nav-stacked'] );*/
	}

	protected function urlencode( string $pageTitle ): string
	{
		return urlencode( $pageTitle );
	}
}
