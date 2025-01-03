<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\View\Helper\Abstraction;

/**
 *	@todo		check if needed. seems to be no used yet or anymore. UI_Navigation has same function, but this is usable stand-alone.
 */
class View_Helper_Navigation_Pages_Navbar extends Abstraction
{
	protected ?string $current			= NULL;
	protected int|string $scopeId		= '0';

	/**
	 *	@return		string
	 *	@throws		ReflectionException
	 */
	public function render(): string
	{
		$model		= new Model_Page( $this->env );
		$indices	= [
			'parentId'	=> 0,
			'scope'		=> $this->scopeId,
		];
		$pages		= $model->getAllByIndices( $indices, ['rank' => 'ASC'] );
		$list		= [];
		foreach( $pages as $page ){
			if( $page->status < 1 )
				continue;
			if( $page->type == 1 ){
				$found		= FALSE;
				$sublist	= [];
				$indices	= ['parentId' => $page->pageId, 'scope' => 0];
				$subpages	= $model->getAllByIndices( $indices, ['rank' => 'ASC'] );
				foreach( $subpages as $subpage ){
					if( $subpage->status == 0 )
						continue;
					$class	= NULL;
					if( $this->current == $page->identifier.'/'.$subpage->identifier ){
						$class	= 'active';
						$found	= TRUE;
					}
					$href	= './'.$page->identifier.'/'.$subpage->identifier;
					$link	= HtmlTag::create( 'a', $subpage->title, ['href' => $href] );
					$sublist[]	= HtmlTag::create( 'li', $link, ['class' => $class] );
				}
				$class		= $found ? 'dropdown active' : 'dropdown';
				$sublist	= HtmlTag::create( 'ul', $sublist, ['class' => 'dropdown-menu'] );
				$title		= $page->title.' <b class="caret"></b>';
				$link	= HtmlTag::create( 'a', $title, ['href' => '#', 'class' => 'dropdown-toggle', 'data-toggle' => 'dropdown'] );
				$list[]	= HtmlTag::create( 'li', $link.$sublist, ['class' => $class] );
			}
			else{
				$class	= $this->current == $page->identifier ? 'active' : NULL;
				$href	= $page->identifier == "index" ? './' : './'.$page->identifier;
				$link	= HtmlTag::create( 'a', $page->title, ['href' => $href] );
				$list[]	= HtmlTag::create( 'li', $link, ['class' => $class] );
			}
		}
		$list	= HtmlTag::create( 'ul', $list, ['class' => "nav nav-pills"] );
		return HtmlTag::create( 'div', $list, ['id' => 'layout-nav-main'] );
	}

	/**
	 *	@param		string		$current
	 *	@return		self
	 */
	public function setCurrent( string $current ): self
	{
		$this->current		= $current;
		return $this;
	}

	/**
	 *	@param		int|string		$scopeId
	 *	@return		self
	 */
	public function setScopeId( int|string $scopeId ): self
	{
		$this->scopeId		= $scopeId;
		return $this;
	}
}
