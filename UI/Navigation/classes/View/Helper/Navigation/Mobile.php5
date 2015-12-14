<?php
class View_Helper_Navigation_Mobile{

	protected $env;
	protected $menu;
	protected $inverse			= FALSE;
	protected $linksToSkip		= array();
	protected $scope			= 'main';

	public function __construct( $env, Model_Menu $menu ){
		$this->env		= $env;
		$this->menu		= $menu;
	}

	/**
	 *	@todo 		kriss: remove after abstract interface and abstract of Hydrogen view helper are updated
	 */
	public function __toString(){
		return $this->render();
	}

	public function render(){
		$list	= array();
		foreach( $this->menu->getPages( $this->scope, FALSE ) as $page ){
			if( $page->type == 'menu' ){
				$sublist	= array();
				foreach( $page->items as $subpage ){
					$class		= $subpage->active ? 'Selected' : NULL;
					$href		= './'.$subpage->link;
					$link		= UI_HTML_Tag::create( 'a', $this->renderLabelWithIcon( $subpage ), array( 'href' => $href ) );
					$sublist[]	= UI_HTML_Tag::create( 'li', $link, array( 'class' => $class ) );
				}
				$class		= $page->active ? 'Selected' : NULL;
				$sublist	= UI_HTML_Tag::create( 'ul', $sublist, array( 'class' => '' ) );
				$link		= UI_HTML_Tag::create( 'span', $this->renderLabelWithIcon( $page )/*, array( 'href' => '#' )*/ );
				$list[]	= UI_HTML_Tag::create( 'li', $link.$sublist, array( 'class' => $class ) );
			}
			else{
				if( in_array( $page->path, $this->linksToSkip ) )
					continue;
				$class	= $page->active ? 'Selected' : NULL;
				$href	= $page->path == "index" ? './' : './'.$page->link;
				$link	= UI_HTML_Tag::create( 'a', self::renderLabelWithIcon( $page ), array( 'href' => $href ) );
				$list[]	= UI_HTML_Tag::create( 'li', $link, array( 'class' => $class ) );
			}
		}
		$list	= UI_HTML_Tag::create( 'ul', $list, array( "class" => 'mm-listview' ) );
		return UI_HTML_Tag::create( 'div', $list, array( 'id' => "menu", 'class' => "mm-hidden" ) );
	}

	protected function renderLabelWithIcon( $entry ){
		if( !isset( $entry->icon ) )
			return $entry->label;
		$class	= $entry->icon;
		if( !preg_match( "/^fa/", $entry->icon ) )
			$class	= 'icon-'.$class.( $this->inverse ? ' icon-white' : '' );
		$icon   = UI_HTML_Tag::create( 'i', '', array( 'class' => $class ) );
		return $icon.'&nbsp;'.$entry->label;
	}

	public function setInverse( $boolean = NULL ){
		$this->inverse	= (boolean) $boolean;
	}

	public function setLinksToSkip( $links ){
		$this->linksToSkip	= $links;
	}

	public function setScope( $scope ){
		$this->scope	= $scope;
	}
}
?>
