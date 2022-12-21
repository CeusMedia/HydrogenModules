<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Info_Manual_PageTree
{
	protected $env;
	protected $modelPage;
	protected $pages			= [];
	protected $activePageId		= 0;
	protected $openParents		= [];

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
			$conditions		= array(
				'status'			=> '>= '.Model_Manual_Page::STATUS_NEW,
				'parentId'			=> 0,
				'manualCategoryId'	=> $this->categoryId,
			);
			$orders		= ['rank' => 'ASC'];
			$pages		= $this->modelPage->getAll( $conditions, $orders );
		}
		else if( $this->parentPageId ){
			$conditions		= array(
				'status'		=> '>= '.Model_Manual_Page::STATUS_NEW,
				'parentId'		=> $this->parentPageId,
			);
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


	public function setActivePageId( $pageId ): self
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

	public function setCategoryId( $categoryId ): self
	{
		$this->categoryId	= $categoryId;
		return $this;
	}

	public function setPages( $pages ): self
	{
		$this->pages	= $pages;
		return $this;
	}

	public function setParentPage( $pageId ): self
	{
		$this->parentPageId	= $parentPageId;
		return $this;
	}

	//  --  PROTECTED  --  //

	protected function getPageTree( array $pages ): array
	{
		$tree	= [];
		foreach( $pages as $page ){
			$conditions		= array(
				'status'		=> '>= '.Model_Manual_Page::STATUS_NEW,
				'parentId'		=> $page->manualPageId,
			);
			$orders		= ['rank' => 'ASC'];
			$children	= $this->modelPage->getAll( $conditions, $orders );
			$page->children	= $this->getPageTree( $children );
			$tree[]	= $page;
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

/*print_m($entry);
print_m($session->getAll());
print_m($categoryId);
print_m($sessionKeyOpen);
print_m($session->get( $sessionKeyOpen ));
print_m($openPages);
print_m($isOpen);
die;*/

			$sublist	= '';
			$link		= './info/manual/page/'.$entry->manualPageId.'-'.$this->urlencode( $entry->title );
			$children	= $this->renderPageTree( $entry->children );
			$list[]	= (object) array(
				'text'			=> $entry->title,
				'href'			=> $link,
				'selectable'	=> false,
				'state'			=> (object) array(
					'expanded'	=> $isOpen,
					'selected'	=> $this->activePageId == $entry->manualPageId,
				),
				'color'			=> '!inherit',
//				'data'			=> ['pageId' => $entry->manualPageId],				//  not working with this version of bootstrap-treeview
//				'tags'			=> ['pageId:'.$entry->manualPageId],					//  not working with this version of bootstrap-treeview
				'nodes'			=> $children ? $children : NULL,
			);
		}
		return $list;

/*		$list	= [];
		foreach( $tree as $entry ){
			$sublist	= '';
			if( $entry->children )
				$sublist	= $this->renderPageTree( $entry->children );
			$link	= HtmlTag::create( 'a', $entry->title, array(
				'href'	=> './info/manual/page/'.$entry->manualPageId.'-'.$this->urlencode( $entry->title )
			) );
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
