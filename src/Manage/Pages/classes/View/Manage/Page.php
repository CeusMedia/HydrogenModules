<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\View;

class View_Manage_Page extends View
{
	public function add(): void
	{
	}

	public function edit(): void
	{
		$captain	= $this->env->getCaptain();
		$captain->disableHook( 'View', 'onRenderContent' );
	}

	public function index(): void
	{
	}

	/**
	 *	@param		Entity_Page[]	$tree
	 *	@param		$currentPageId
	 *	@return		string
	 */
	public function renderTree( array $tree, $currentPageId = NULL ): string
	{
		$app	= $this->getData( 'app' );
		$source	= $this->getData( 'source' );

		$isSelfApp		= 'self' === $app;
		$isFrontendApp	= 'frontend' === $app;
		$isFromConfig	= 'Config' === $source;
		$isFromDatabase	= 'Database' === $source;

		$list	= [];
		foreach( $tree as $item ){
			$sublist	= '';
			if( [] !== $item->pages ){
				$sublist	= [];
				foreach( $item->pages as $subitem ){
					$classes	= [];
					if( $currentPageId && $currentPageId == $subitem->pageId )
						$classes[]	= 'active';
					if( $subitem->status < Model_Page::STATUS_VISIBLE || $item->status < Model_Page::STATUS_VISIBLE )
						$classes[]	= 'disabled';
					if( $subitem->status < Model_Page::STATUS_HIDDEN )
						$subitem->title	= '<span style="text-decoration: line-through;">' .$subitem->title. '</span>';
					$url	= './manage/page/edit/'.$subitem->pageId;
					$label	= $this->getPageIcon( $subitem ).' <small>'.$subitem->title.'</small>';
					$link	= HtmlTag::create( 'a', $label, ['href' => $url, 'class' => 'autocut'] );
					$sublist[]	= HtmlTag::create( 'li', $link, [
						'class'			=> join( ' ', $classes ),
						'data-page-id'	=> $subitem->pageId,
					] );
				}
				if( $sublist )
					$sublist	= HtmlTag::create( 'ul', $sublist, ['class' => 'nav nav-pills nav-stacked'] );
				else
					$sublist	= '';
			}
			$classes	= ['autocut'];
			if( $currentPageId && $currentPageId == $item->pageId )
				$classes[]	= 'active';
			if( $item->status < Model_Page::STATUS_VISIBLE )
				$classes[]	= 'disabled';
			if( $item->status < Model_Page::STATUS_HIDDEN )
				$item->title	= '<span style="text-decoration: line-through;">' .$item->title. '</span>';
			$url	= './manage/page/edit/'.$item->pageId;
			$label	= $this->getPageIcon( $item ).' '.$item->title;
			$link	= HtmlTag::create( 'a', $label, ['href' => $url] );
			$list[]	= HtmlTag::create( 'li', $link.$sublist, [
				'class'			=> join( ' ', $classes ),
				'data-page-id'	=> $item->pageId,
			] );
		}
		if( $list )
			return HtmlTag::create( 'ul', $list, ['class' => 'nav nav-pills nav-stacked'] );
		$words	= (object) $this->env->getLanguage()->getWords( 'manage/page' )['tree'];
		return '<div class="muted"><small><em>'.$words->no_entries.'</em></small></div><br/>';
	}

	protected function __onInit(): void
	{
//		$page	= $this->env->getPage();
	}

	protected function getPageIcon( Entity_Page $page ): string
	{
		return match( $page->type ){
			0		=> '<i class="fa fa-fw fa-file-text-o"></i>',
			1		=> '<i class="fa fa-fw fa-chevron-down"></i>',
			2		=> '<i class="fa fa-fw fa-plug"></i>',
			3		=> '<i class="fa fa-fw fa-puzzle-piece"></i>',
			default	=> '',
		};
	}

	public function renderTabs( array $labels, array $templates, $current ): string
	{
		/** @var Entity_Page $page */
		$page	= $this->getData( 'page' );
		$app	= $this->getData( 'app' );
		$source	= $this->getData( 'source' );
		$meta	= $this->getData( 'appHasMetaModule' );

		$isSelfApp		= 'self' === $app;
		$isFrontendApp	= 'frontend' === $app;
		$isFromConfig	= 'Config' === $source;
//		$isFromDatabase	= 'Database' === $source;
//		$isFromModules	= 'Modules' === $source;

		$listTabs	= [];
		$listPanes	= [];
		foreach( $labels as $tabKey => $label ){
			$isPage		= Model_Page::TYPE_CONTENT === $page->type;
			$isBranch	= Model_Page::TYPE_BRANCH === $page->type;
			$isModule	= Model_Page::TYPE_MODULE === $page->type;
			$disabled	= FALSE;
			$attributes		= ['href' => '#tab-'.$tabKey, 'data-toggle' => 'tab'];
			switch( $tabKey ){
				case 'content':
					$disabled	= $isBranch || $isModule || $isFromConfig;
					break;
				case 'meta':
					$disabled	= !$meta;
					break;
			}
			$link			= HtmlTag::create( 'a', $label, $attributes );
			$isActive		= $tabKey == $current;
			$class			= $isActive ? "active" : '';
			$class			.= $disabled ? ' disabled' : '';
			$attributes		= ['id' => 'page-editor-tab-'.$tabKey, 'class' => $class];
			$listTabs[]		= HtmlTag::create( 'li', $link, $attributes );
			$paneContent	= $this->loadTemplateFile( 'manage/page/'.$templates[$tabKey], [], FALSE );
			$attributes		= ['id' => 'tab-'.$tabKey, 'class' => $isActive ? 'tab-pane active' : 'tab-pane'];
			$listPanes[]	= HtmlTag::create( 'div', $paneContent, $attributes );
		}
		$listTabs	= HtmlTag::create( 'ul', $listTabs, ['class' => "nav nav-tabs"] );
		$listPanes	= HtmlTag::create( 'div', $listPanes, ['class' => 'tab-content'] );
		$attributes	= ['class' => 'tabbable', 'id' => 'tabs-page-editor'];
		return HtmlTag::create( 'div', $listTabs.$listPanes, $attributes );
	}
}
