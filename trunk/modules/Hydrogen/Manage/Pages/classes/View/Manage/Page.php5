<?php
class View_Manage_Page extends CMF_Hydrogen_View{

	public function __onInit(){
//		$page	= $this->env->getPage();
	}

	public function add(){}

	public function edit(){}

	public function index(){}

	protected function getPageIcon( $page ){
		switch( $page->type ){
			case 0:
				return '<i class="icon-leaf"></i>';
			case 1:
				return '<i class="icon-chevron-down"></i>';
			case 2:
				return '<i class="icon-fire"></i>';
		}
	}

	public function renderTree( $tree, $currentPage = NULL ){
		$list	= array();
		foreach( $tree as $item ){
			$sublist	= array();
			foreach( $item->subpages as $subitem ){
				$classes	= array();
				if( $currentPage && $currentPage->pageId == $subitem->pageId )
					$classes[]	= 'active';
				if( $subitem->status == 0 )
					$classes[]	= 'disabled';
				$url	= './manage/page/edit/'.$subitem->pageId;
				$label	= $this->getPageIcon( $subitem ).' <small>'.$subitem->title.'</small>';
				$link	= UI_HTML_Tag::create( 'a', $label, array( 'href' => $url ) );
				$sublist[]	= UI_HTML_Tag::create( 'li', $link, array( 'class' => join( ' ', $classes ) ) );
			}
			if( $sublist )
				$sublist	= UI_HTML_Tag::create( 'ul', $sublist, array( 'class' => 'nav nav-pills nav-stacked' ) );
			else
				$sublist	= '';
			$classes	= array();
			if( $currentPage && $currentPage->pageId == $item->pageId )
				$classes[]	= 'active';
			if( $item->status == 0 )
				$classes[]	= 'disabled';
			$url	= './manage/page/edit/'.$item->pageId;
			$label	= $this->getPageIcon( $item ).' '.$item->title;
			$link	= UI_HTML_Tag::create( 'a', $label, array( 'href' => $url ) );
			$list[]	= UI_HTML_Tag::create( 'li', $link.$sublist, array( 'class' => join( ' ', $classes ) ) );
		}
		return UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'nav nav-pills nav-stacked' ) );
	}
}
?>
