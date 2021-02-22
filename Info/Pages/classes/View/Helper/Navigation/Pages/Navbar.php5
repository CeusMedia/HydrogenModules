<?php
/**
 *	@todo		check if needed. seems to be no used yet or anymore. UI_Navigation has same function, but this is usable stand-alone.
 */
class View_Helper_Navigation_Pages_Navbar extends CMF_Hydrogen_View_Helper_Abstract
{
	protected $current;
	protected $scopeId		= 0;

	public function render(): string
	{
		$model		= new Model_Page( $this->env );
		$indices	= array(
			'parentId'	=> 0,
			'scope'		=> $this->scopeId,
		);
		$pages		= $model->getAllByIndices( $indices, array( 'rank' => 'ASC' ) );
		$list	= array();
		foreach( $pages as $page ){
			if( $page->status < 1 )
				continue;
			if( $page->type == 1 ){
				$found		= FALSE;
				$sublist	= array();
				$indices	= array( 'parentId' => $page->pageId, 'scope' => 0 );
				$subpages	= $model->getAllByIndices( $indices, array( 'rank' => 'ASC' ) );
				foreach( $subpages as $subpage ){
					if( $subpage->status == 0 )
						continue;
					$class	= NULL;
					if( $this->current == $page->identifier.'/'.$subpage->identifier ){
						$class	= 'active';
						$found	= TRUE;
					}
					$href	= './'.$page->identifier.'/'.$subpage->identifier;
					$link	= UI_HTML_Tag::create( 'a', $subpage->title, array( 'href' => $href ) );
					$sublist[]	= UI_HTML_Tag::create( 'li', $link, array( 'class' => $class ) );
				}
				$class		= $found ? 'dropdown active' : 'dropdown';
				$sublist	= UI_HTML_Tag::create( 'ul', $sublist, array( 'class' => 'dropdown-menu' ) );
				$title		= $page->title.' <b class="caret"></b>';
				$link	= UI_HTML_Tag::create( 'a', $title, array( 'href' => '#', 'class' => 'dropdown-toggle', 'data-toggle' => 'dropdown' ) );
				$list[]	= UI_HTML_Tag::create( 'li', $link.$sublist, array( 'class' => $class ) );
			}
			else{
				$class	= $this->current == $page->identifier ? 'active' : NULL;
				$href	= $page->identifier == "index" ? './' : './'.$page->identifier;
				$link	= UI_HTML_Tag::create( 'a', $page->title, array( 'href' => $href ) );
				$list[]	= UI_HTML_Tag::create( 'li', $link, array( 'class' => $class ) );
			}
		}
		$list	= UI_HTML_Tag::create( 'ul', $list, array( 'class' => "nav nav-pills" ) );
		return UI_HTML_Tag::create( 'div', $list, array( 'id' => 'layout-nav-main' ) );
	}

	public function setCurrent( string $current ): self
	{
		$this->current		= $current;
		return $this;
	}

	public function setScopeId( $scopeId ): self
	{
		$this->scopeId		= $scopeId;
		return $this;
	}
}
