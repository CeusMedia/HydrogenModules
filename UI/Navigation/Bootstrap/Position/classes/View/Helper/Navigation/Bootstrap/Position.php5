<?php
class View_Helper_Navigation_Bootstrap_Position extends CMF_Hydrogen_View_Helper_Abstract {

	protected $divider			= '&nbsp;/&nbsp;';
	protected $hasPageSupport	= FALSE;
	protected $moduleConfig;
	protected $moduleId			= "UI_Navigation_Bootstrap_Position";
	protected $labelHome		= "Home";

	public function __construct( $env ){
		$this->setEnv( $env );
		$this->hasPageSupport	= $this->env->getModules()->has( 'Info_Pages' );
		$moduleConfigKey		= 'module.'.strtolower( $this->moduleId ).'.';
		$this->moduleConfig		= $this->env->getConfig()->getAll( $moduleConfigKey, TRUE );
	}

	public function render(){
		if( 0 && !$this->moduleConfig->get( 'enabled' ) )
			return;
		if( !$this->hasPageSupport){
			$this->env->getMessenger()->noteFailure( 'Module "UI:Navigation:Bootstrap:Position" needs module "Info:Pages".' );
			return;
		}
		$itemList		= array();
		$model			= new Model_Menu( $this->env );
		$pageMap		= $model->getPageMap();
		if( ( $currentPage = $model->getCurrent() ) ){
			$path	= $currentPage->path;
			while( $path && isset( $pageMap[$path] ) && $page = $pageMap[$path] ){
				$item	= (object) array(
					'label'		=> $page->label,
					'link'		=> NULL,
					'current'	=> FALSE,
				);
				if( $page->path === $currentPage->path ){
					$item->current = TRUE;
				}
				if( $page->type === "menu" ){
				}
				else if( $page->type === "item" ){
					if( !$item->current )
						$item->link = './'.$page->path;
				}
				array_unshift( $itemList, $item );
				$parts	= explode( "/", $path );
				$path	= implode( "/", array_slice( $parts, 0, count( $parts ) - 1 ) );
			}
			array_unshift( $itemList, (object) array(
				'label'		=> $this->labelHome,
				'link'		=> './',
			) );
		}
		else {
			if( !$this->moduleConfig->get( 'showOnHome' ) )
				return;
			array_unshift( $itemList, (object) array(
				'label'		=> $this->labelHome,
				'link'		=> NULL,
			) );
		}
		$barList	= array();
		foreach( $itemList as $entry ){
			$label	= $entry->label;
			if( isset( $entry->link ) )
				$label	= UI_HTML_Tag::create( 'a', $label, array( 'href' => $entry->link ) );
			$barList[]	= UI_HTML_Tag::create( 'span', $label, array(
				'class'	=> 'position-bar-path-list-item',
			'href'	=> '#',
			) );
		}
		$divider	= UI_HTML_Tag::create( 'span', $this->divider, array( 'class' => 'position-bar-path-divider' ) );
		$barList	= implode( $divider, $barList );
		$bar		= UI_HTML_Tag::create( 'span', $barList, array( 'class' => 'position-bar-path-list' ) );
		$label		= UI_HTML_Tag::create( 'span', 'Position', array( 'class' => 'position-bar-label' ) );

		$content	= UI_HTML_Tag::create( 'div', $label.$bar, array( 'class' => 'position-bar' ) );
		return $content;
	}

	public function setPathDivider( $string ){
		$this->divider		= $string;
	}

	public function setHomeLabel( $string ){
		$this->labelHome	= $string;
	}
}
?>
