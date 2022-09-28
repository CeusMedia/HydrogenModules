<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\View\Helper\Abstraction;

/**
 *	@todo		check if needed. seems to be no used yet or anymore. UI_Navigation has same function, but this is usable stand-alone.
 */
class View_Helper_Navigation_Pages_Navbar extends Abstraction
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
		$list	= [];
		foreach( $pages as $page ){
			if( $page->status < 1 )
				continue;
			if( $page->type == 1 ){
				$found		= FALSE;
				$sublist	= [];
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
					$link	= HtmlTag::create( 'a', $subpage->title, array( 'href' => $href ) );
					$sublist[]	= HtmlTag::create( 'li', $link, array( 'class' => $class ) );
				}
				$class		= $found ? 'dropdown active' : 'dropdown';
				$sublist	= HtmlTag::create( 'ul', $sublist, array( 'class' => 'dropdown-menu' ) );
				$title		= $page->title.' <b class="caret"></b>';
				$link	= HtmlTag::create( 'a', $title, array( 'href' => '#', 'class' => 'dropdown-toggle', 'data-toggle' => 'dropdown' ) );
				$list[]	= HtmlTag::create( 'li', $link.$sublist, array( 'class' => $class ) );
			}
			else{
				$class	= $this->current == $page->identifier ? 'active' : NULL;
				$href	= $page->identifier == "index" ? './' : './'.$page->identifier;
				$link	= HtmlTag::create( 'a', $page->title, array( 'href' => $href ) );
				$list[]	= HtmlTag::create( 'li', $link, array( 'class' => $class ) );
			}
		}
		$list	= HtmlTag::create( 'ul', $list, array( 'class' => "nav nav-pills" ) );
		return HtmlTag::create( 'div', $list, array( 'id' => 'layout-nav-main' ) );
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
