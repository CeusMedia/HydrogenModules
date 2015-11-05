<?php
class View_Helper_Navigation_Dropdown{

	protected $env;
	protected $menu;

	public function __construct( $env, Model_Menu $menu ){
		$this->env		= $env;
		$this->menu		= $menu;
	}

	public function render( $scope, $style = NULL ){
		$listClass	= 'nav';
		if( strtolower( $style ) == "pills" )
			$listClass	.= ' nav-pills';

		$list	= array();
		foreach( $this->menu->getPages( $scope, FALSE ) as $page ){
			if( $page->type == 'menu' ){
				$sublist	= array();
				foreach( $page->items as $subpage ){
					$class		= $subpage->active ? 'active' : NULL;
					$href		= './'.$subpage->link;
//					$link		= UI_HTML_Tag::create( 'a', $subpage->label, array( 'href' => $href ) );
					$link		= UI_HTML_Tag::create( 'a', self::renderLabelWithIcon( $subpage ), array( 'href' => $href ) );
					$sublist[]	= UI_HTML_Tag::create( 'li', $link, array( 'class' => $class ) );
				}
				$class		= $page->active ? 'dropdown active' : 'dropdown';
				$sublist	= UI_HTML_Tag::create( 'ul', $sublist, array( 'class' => 'dropdown-menu' ) );
				$title		= $page->label.' <b class="caret"></b>';
				$link	= UI_HTML_Tag::create( 'a', $title, array( 'href' => '#', 'class' => 'dropdown-toggle', 'data-toggle' => 'dropdown' ) );
				$list[]	= UI_HTML_Tag::create( 'li', $link.$sublist, array( 'class' => $class ) );
			}
			else{
				$class	= $page->active ? 'active' : NULL;
				$href	= $page->path == "index" ? './' : './'.$page->link;
//				$link	= UI_HTML_Tag::create( 'a', $page->label, array( 'href' => $href ) );
				$link	= UI_HTML_Tag::create( 'a', self::renderLabelWithIcon( $page ), array( 'href' => $href ) );
				$list[]	= UI_HTML_Tag::create( 'li', $link, array( 'class' => $class ) );
			}
		}
		return UI_HTML_Tag::create( 'ul', $list, array( "class" => $listClass ) );
	}

	static protected function renderLabelWithIcon( $entry ){
		if( !isset( $entry->icon ) )
			return $entry->label;
		$icon   = UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-'.$entry->icon ) );
		return $icon.'&nbsp;'.$entry->label;
	}
}
?>
