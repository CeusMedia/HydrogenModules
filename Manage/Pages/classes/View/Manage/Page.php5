<?php
class View_Manage_Page extends CMF_Hydrogen_View{

	public function __onInit(){
//		$page	= $this->env->getPage();
	}

	public function add(){}

	public function edit(){
		$captain	= $this->env->getCaptain();
		$captain->disableHook( 'View', 'onRenderContent' );
	}

	public function index(){}

	protected function getPageIcon( $page ){
		switch( $page->type ){
			case 0:
				return '<i class="fa fa-fw fa-file-text-o"></i>';
			case 1:
				return '<i class="fa fa-fw fa-chevron-down"></i>';
			case 2:
				return '<i class="fa fa-fw fa-plug"></i>';
			case 3:
				return '<i class="fa fa-fw fa-puzzle-piece"></i>';
		}
	}

	protected function renderTabs( $labels, $templates, $current ){
		$page	= $this->getData( 'page' );
		$app	= $this->getData( 'app' );
		$source	= $this->getData( 'source' );

		$isSelfApp		= $app === 'self';
		$isFrontendApp	= $app === 'frontend';
		$isFromConfig	= $source === 'Config';
		$isFromDatabase	= $source === 'Database';

		$listTabs	= array();
		$listPanes	= array();
		foreach( $labels as $tabKey => $label ){
			$isPage		= (int) $page->type === Model_Page::TYPE_BRANCH;
			$isBranch	= (int) $page->type === Model_Page::TYPE_BRANCH;
			$isModule	= (int) $page->type === Model_Page::TYPE_MODULE;
			$disabled	= FALSE;
			$attributes		= array( 'href' => '#tab-'.$tabKey, 'data-toggle' => 'tab' );
			switch( $tabKey ){
				case 'content':
					$disabled	= $isBranch || $isModule || $isFromConfig;
					break;
			}
			$link			= UI_HTML_Tag::create( 'a', $label, $attributes );
			$isActive		= $tabKey == $current;
			$class			= $isActive ? "active" : '';
			$class			.= $disabled ? ' disabled' : '';
			$attributes		= array( 'id' => 'page-editor-tab-'.$tabKey, 'class' => $class );
			$listTabs[]		= UI_HTML_Tag::create( 'li', $link, $attributes );
			$paneContent	= $this->loadTemplateFile( 'manage/page/'.$templates[$tabKey], array(), FALSE );
			$attributes		= array( 'id' => 'tab-'.$tabKey, 'class' => $isActive ? 'tab-pane active' : 'tab-pane' );
			$listPanes[]	= UI_HTML_Tag::create( 'div', $paneContent, $attributes );
		}
		$listTabs	= UI_HTML_Tag::create( 'ul', $listTabs, array( 'class' => "nav nav-tabs" ) );
		$listPanes	= UI_HTML_Tag::create( 'div', $listPanes, array( 'class' => 'tab-content' ) );
		$attributes	= array( 'class' => 'tabbable', 'id' => 'tabs-page-editor' );
		return UI_HTML_Tag::create( 'div', $listTabs.$listPanes, $attributes );
	}

	public function renderTree( $tree, $currentPageId = NULL ){
		$list	= array();
		foreach( $tree as $item ){
			$sublist	= array();
			foreach( $item->subpages as $subitem ){
				$classes	= array();
				if( $currentPageId && $currentPageId == $subitem->pageId )
					$classes[]	= 'active';
				if( $subitem->status < Model_Page::STATUS_VISIBLE || $item->status < Model_Page::STATUS_VISIBLE )
					$classes[]	= 'disabled';
				if( $subitem->status < Model_Page::STATUS_HIDDEN )
					$subitem->title	= '<strike>'.$subitem->title.'</strike>';
				$url	= './manage/page/edit/'.$subitem->pageId;
				$label	= $this->getPageIcon( $subitem ).' <small>'.$subitem->title.'</small>';
				$link	= UI_HTML_Tag::create( 'a', $label, array( 'href' => $url, 'class' => 'autocut' ) );
				$sublist[]	= UI_HTML_Tag::create( 'li', $link, array(
					'class'			=> join( ' ', $classes ),
					'data-page-id'	=> $subitem->pageId,
				) );
			}
			if( $sublist )
				$sublist	= UI_HTML_Tag::create( 'ul', $sublist, array( 'class' => 'nav nav-pills nav-stacked' ) );
			else
				$sublist	= '';
			$classes	= array( 'autocut' );
			if( $currentPageId && $currentPageId == $item->pageId )
				$classes[]	= 'active';
			if( $item->status < Model_Page::STATUS_VISIBLE )
				$classes[]	= 'disabled';
			if( $item->status < Model_Page::STATUS_HIDDEN )
				$item->title	= '<strike>'.$item->title.'</strike>';
			$url	= './manage/page/edit/'.$item->pageId;
			$label	= $this->getPageIcon( $item ).' '.$item->title;
			$link	= UI_HTML_Tag::create( 'a', $label, array( 'href' => $url ) );
			$list[]	= UI_HTML_Tag::create( 'li', $link.$sublist, array(
				'class'			=> join( ' ', $classes ),
				'data-page-id'	=> $item->pageId,
			) );
		}
		if( $list )
			return UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'nav nav-pills nav-stacked' ) );
		$words	= (object) $this->env->getLanguage()->getWords( 'manage/page' )['tree'];
		return '<div class="muted"><small><em>'.$words->no_entries.'</em></small></div><br/>';
	}
}
?>
