<?php
class View_Helper_Navigation_Bootstrap_DropdownPillBar extends CMF_Hydrogen_View_Helper_Abstract{

	public function render( $scope = 0 ){
		$model		= new Model_Page( $this->env );
		$indices	= array( 'parentId' => 0, 'scope' => $scope );
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

	public function setCurrent( $path ){
		$this->current		= $path;
	}
}
?>

