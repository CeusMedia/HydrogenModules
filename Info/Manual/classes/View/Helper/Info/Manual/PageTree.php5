<?php
class View_Helper_Info_Manual_PageTree{

	protected $activePageId	= 0;
	protected $pages		= array();
	protected $openParents	= array();
	protected $modelPage;

	public function __construct( CMF_Hydrogen_Environment $env ){
		$this->env			= $env;
		$this->modelPage	= new Model_Manual_Page( $env );
	}

	public function __toString(){
		return $this->render();
	}

	public function render(){
		$words	= $this->env->getLanguage()->getWords( 'info/manual' );

		if( $this->categoryId ){
			$conditions		= array(
				'status'			=> '>='.Model_Manual_Page::STATUS_NEW,
				'parentId'			=> 0,
				'manualCategoryId'	=> $this->categoryId,
			);
			$orders		= array( 'rank' => 'ASC' );
			$pages		= $this->modelPage->getAll( $conditions, $orders );
		}
		else if( $this->parentPageId ){
			$conditions		= array(
				'status'		=> '>='.Model_Manual_Page::STATUS_NEW,
				'parentId'		=> $this->parentPageId,
			);
			$orders		= array( 'rank' => 'ASC' );
			$pages		= $this->modelPage->getAll( $conditions, $orders );
		}
		else
			throw new RuntimeException( 'Neither category nor parent page set' );

		$tree		= $this->getPageTree( $pages );

		$tree		= $this->renderPageTree( $tree );
		$container	= UI_HTML_Tag::create( 'div', '', array( 'id' => 'page-tree' ) );
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
jQuery("#page-tree li.list-group-item").on("click", function(){
//	document.location.href=jQuery(this).children("a").prop("href");
})';
		$this->env->getPage()->js->addScriptOnReady( $script );
		return $container;



		$html		= $this->renderPageTree( $tree );
//		$container	= UI_HTML_Tag::create( 'div', $html, array( 'id' => 'page-tree' );
//		$this->env->getPage()->js->addScriptOnReady( 'jQuery("#page-tree").')
		return $container;
		print_m( $tree );die;

		$list	= array();
		foreach( $this->pages as $entry ){
			$link	= UI_HTML_Tag::create( 'a', $entry->title, array( 'href' => './info/manual/page/'.$entry->manualPageId.'-'.$this->urlencode( $entry->title ) ) );
			$class	= 'autocut '.( $this->activePageId == $entry->manualPageId ? 'active' : '' );
			$list[]	= UI_HTML_Tag::create( 'li', $link, array( 'class' => $class ) );
		}
		return UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'nav nav-pills nav-stacked' ) );
	}

	protected function renderPageTree( $tree ){
		$list	= array();
		foreach( $tree as $entry ){
			$sublist	= '';
			$link		= './info/manual/page/'.$entry->manualPageId.'-'.$this->urlencode( $entry->title );
			$children	= $this->renderPageTree( $entry->children );
			$icon		= 'fa fa-fw fa-minus';
			$icon		= 'fa fa-fw fa-chevron-down';
			$list[]	= (object) array(
				'text'	=> $entry->title,
				'href'	=> $link,
//				'icon'	=> $icon,
				'selectable'	=> false,
				'state'	=> (object) array(
					'expanded'	=> in_array( $entry->manualPageId, $this->openParents ),
					'selected'	=> $this->activePageId == $entry->manualPageId,
				),
				'nodes'	=> $children ? $children : NULL,
			);
		}
		return $list;


		$list	= array();
		foreach( $tree as $entry ){
			$sublist	= '';
			if( $entry->children )
				$sublist	= $this->renderPageTree( $entry->children );
			$link	= UI_HTML_Tag::create( 'a', $entry->title, array(
				'href'	=> './info/manual/page/'.$entry->manualPageId.'-'.$this->urlencode( $entry->title )
			) );
			$class	= 'autocut '.( $this->activePageId == $entry->manualPageId ? 'active' : '' );
			$list[]	= UI_HTML_Tag::create( 'li', $link.$sublist, array( 'class' => $class ) );
		}
		return UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'not-nav not-nav-pills not-nav-stacked' ) );
	}

	protected function getPageTree( $pages ){
		$tree	= array();
		foreach( $pages as $page ){
			$conditions		= array(
				'status'		=> '>='.Model_Manual_Page::STATUS_NEW,
				'parentId'		=> $page->manualPageId,
			);
			$orders		= array( 'rank' => 'ASC' );
			$children	= $this->modelPage->getAll( $conditions, $orders );
			$page->children	= $this->getPageTree( $children );
			$tree[]	= $page;
		}
		return $tree;
	}

	public function setActivePageId( $pageId ){
		$this->activePageId	= $pageId;
		$this->openParents	= array();
		$page	= $this->modelPage->get( $pageId );
		while( $page && $page->parentId ){
			$page	= $this->modelPage->get( $page->parentId );
			if( $page )
				$this->openParents[]	= $page->manualPageId;
		}
	}

	public function setCategoryId( $categoryId ){
		$this->categoryId	= $categoryId;
	}

	public function setParentPage( $pageId ){
		$this->parentPageId	= $parentPageId;
	}

	public function setPages( $pages ){
		$this->pages	= $pages;
	}

	protected function urlencode( $pageTitle ){
		return urlencode( $pageTitle );
	}
}
