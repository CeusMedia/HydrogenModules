<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\View\Helper\Abstraction;

class View_Helper_Navigation_Bootstrap_DropdownPillBar extends Abstraction
{
	protected string $current	= '';

	public function render( $scope = 0 ): string
	{
		$model		= new Model_Page( $this->env );
		$indices	= ['parentId' => 0, 'scope' => $scope];
		$pages		= $model->getAllByIndices( $indices, ['rank' => 'ASC'] );

		$linkMap	= [];
		foreach( $pages as $page )
			if( (int) $page->type === 2)
				$linkMap[strtolower( str_replace( '_', '/', $page->module ) )]	= $page->identifier;
			else
				$linkMap[$page->identifier]	= $page->identifier;
		$current	= self::calculateMatches( $linkMap, $this->current );
		if( array_key_exists( $current, $linkMap ) )
			$current	= $linkMap[$current];

		$list	= [];
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
					if( $current == $page->identifier.'/'.$subpage->identifier ){
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
				$class	= $current == $page->identifier ? 'active' : NULL;
				$href	= $page->identifier == "index" ? './' : './'.$page->identifier;
				$link	= HtmlTag::create( 'a', $page->title, ['href' => $href] );
				$list[]	= HtmlTag::create( 'li', $link, ['class' => $class] );
			}
		}
		$list	= HtmlTag::create( 'ul', $list, ['class' => "nav nav-pills"] );
		return HtmlTag::create( 'div', $list, ['id' => 'layout-nav-main'] );
	}

	public function setCurrent( string $path ): self
	{
		$this->current		= $path;
		return $this;
	}

	protected static function calculateMatches( $map, $current ): int|string|NULL
	{
		foreach( $map as $entry ){
			if( $entry->type === "menu" )
				self::calculateMatches( $entry->links, $current );
			else if( $entry->type === "link" )
				self::$matches[$entry->path]	= levenshtein( $current, $entry->path );
		}
		asort( self::$matches );
		$matches	= array_keys( self::$matches );
		return array_shift( $matches );
	}
}
