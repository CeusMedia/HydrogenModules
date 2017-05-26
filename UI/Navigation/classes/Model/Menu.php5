<?php
class Model_Menu {

	protected $pages	= array();
	protected $pageMap	= array();
	protected $current	= NULL;
	public static $pathRequestKey		= "__path";

	public function __construct( $env ){
		$this->env			= $env;
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.ui_navigation.', TRUE );
		$this->userId		= $env->getSession()->get( 'userId' );
		$this->language		= $env->getLanguage()->getLanguage();
		$this->useAcl		= $env->getModules()->has( 'Resource_Users' );
		$this->source		= $this->moduleConfig->get( 'menu.source' );
		if( $this->source === "Database" && !$env->getModules()->has( 'Info_Pages' ) ){
			$this->env->getMessenger()->noteNotice( 'Navigation source "Database" is not available. Module "Info_Pages" is not installed. Falling back to navigation source "Config".' );
			$this->source	= "Config";
		}
		if( $this->source === "Config" && !file_exists( "config/pages.json" ) ){
			$this->env->getMessenger()->noteNotice( 'Navigation source "Config" is not available. File "config/pages.json" is not available. Falling back to navigation source "Modules".' );
			$this->source	= "Modules";
		}
		$this->readUserPages();
	}

	public function getCurrent(){
		return $this->current;
	}

	public function getPages( $scope = NULL, $strict = TRUE ){
		if( is_null( $scope ) )
			return $this->pages;
		if( array_key_exists( $scope, $this->pages ) )
			return $this->pages[$scope];
		if( $strict )
			throw new OutOfRangeException( 'Invalid scope: '.$scope );
		return array();
	}

	public function getPageMap(){
		return $this->pageMap;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		string		$current		Currently requested path, autodetected if not set
	 *	@return		string
	 */
	protected function identifyActive( $path = NULL ){
		if( isset( $_REQUEST[self::$pathRequestKey] ) && $path === NULL )
			$path	= utf8_decode( $_REQUEST[self::$pathRequestKey] );
		$path		= $path ? $path : 'index';
		$matches	= array();																		//  empty array to regular matching
		$selected	= array();																		//  list of possibly selected links
		foreach( $this->pageMap as $pagePath => $page ){											//  iterate link map
			$page->active = FALSE;
			if( $pagePath == $path ){																//  page path matches requested path
				$selected[$pagePath]	= strlen( $path );											//  note page with highest conformity (longest match length)
				break;
			}
			$pathLength	= min( 1, strlen( $path ) );

			$parts	= explode( '/', $page->link );													//  parts of menu page link
			for( $i=0; $i<strlen( $path ); $i++ ){													//  iterate requested path
				if( !isset( $page->link[$i] ) ){													//  menu page link is finished
					if( $path[$i] === "/" )															//  but path goes on
						$i	+= 3;																	//  add bonus to rank of this page with if rest of path is action, only
					break;																			//  break scan here
				}
				if( $path[$i] !== $page->link[$i] )													//  requested path and menu page path are not matching anymore
					break;																			//  break scan here
			}
			if($i)
				$selected[$page->path]	= $i / $pathLength;												//  qualification = number of matching characters relative to page link parts
		}
		arsort( $selected );																		//  sort link paths by its length, longest on top
//print_m( $selected );die;
		$paths	= array_keys( $selected );
		if( $paths && $first = array_shift( $paths ) ){
			$page		= $this->pageMap[$first];
			$this->pageMap[$first]->active	= TRUE;
			$this->current	= $this->pageMap[$first];
			if( $page->parent )
				$this->pageMap[$page->parent]->active = TRUE;
			return $page->path;																		//  return longest link path
		}
		return '';
	}

	protected function readUserPages(){
		switch( $this->source ){
			case 'Modules':
				$this->readUserPagesFromModules();
				break;
			case 'Config':
				$this->readUserPagesFromConfigFile();
				break;
			case 'Database':
				$this->readUserPagesFromDatabase();
				break;
			default:
				throw new OutOfRangeException( 'Invalid source: '.$this->source );
		}
		$this->identifyActive();
	}

	protected function readUserPagesFromConfigFile(){
		$pagesFile	= 'config/pages.json';
		if( !file_exists( $pagesFile ) )
			throw new RuntimeException( 'Page configuration file "'.$pagesFile.'" is not existing' );

		$scopes				= FS_File_JSON_Reader::load( $pagesFile );
		$this->pages		= array();
		$this->pageMap		= array();
		$isAuthenticated	= (bool) $this->userId;
		foreach( $scopes as $scope => $pages ){
			$this->pages[$scope]	= array();
			foreach( $pages as $pageId => $page ){
				if( isset( $page->disabled ) && !in_array( $page->disabled, array( 'no', FALSE ) ) )
					continue;
				if( isset( $page->{"label@".$this->language} ) )
					$page->label	= $page->{"label@".$this->language};
				$item	= (object) array(
					'parent'	=> NULL,
					'type'		=> 'item',
					'scope'		=> $scope,
					'path'		=> $page->path,
					'link'		=> isset( $page->link ) ? $page->link : $page->path,
					'label'		=> $page->label,
					'language'	=> $this->language,
					'rank'		=> $pageId,
//					'active'	=> $this->current == $page->path,
					'active'	=> FALSE,
					'icon'		=> isset( $page->icon ) ? $page->icon : NULL,
				);
				$subpages	= array();
				if( isset( $page->pages ) ){
					$item->type		= 'menu';
					$item->items	= array();
					foreach( $page->pages as $subpageId => $subpage ){
						if( isset( $subpage->disabled ) && !in_array( $subpage->disabled, array( 'no', FALSE ) ) )
							continue;
						$free		= !isset( $subpage->access );
						$public		= !$free && $subpage->access == "public";
						$outside	= !$free && !$isAuthenticated && $subpage->access == "outside";
						$inside		= !$free && $isAuthenticated && $subpage->access == "inside";
						$acl		= !$free && $subpage->access == "acl" && $this->env->getAcl()->has( $subpage->path );
						if( !( $public || $outside || $inside || $acl ) )
							continue;
						if( isset( $subpage->{"label@".$this->language} ) )
							$subpage->label	= $subpage->{"label@".$this->language};
						$subitem	= (object) array(
							'parent'	=> $item->path,
							'type'		=> 'item',
							'scope'		=> $scope,
							'path'		=> $subpage->path,
							'link'		=> isset( $subpage->link ) ? $subpage->link : $subpage->path,
							'label'		=> $subpage->label,
							'language'	=> $this->language,
							'rank'		=> $subpageId,
//							'active'	=> $this->current == $page->path.'/'.$subpage->path,
							'active'	=> FALSE,
							'icon'		=> isset( $subpage->icon ) ? $subpage->icon : NULL,
						);
						$subpages[]	= $subitem;
					}
				}
				$free		= !isset( $page->access );
				$public		= !$free && $page->access == "public";
				$outside	= !$free && !$isAuthenticated && $page->access == "outside";
				$inside		= !$free && $isAuthenticated && $page->access == "inside";
				$acl		= !$free && $page->access == "acl" && $this->env->getAcl()->has( $page->path );
				$menu		= isset( $page->pages ) && count( $page->pages ) && $subpages;
				if( !( $public || $outside || $inside || $acl || $menu ) )
					continue;
				foreach( $subpages as $subitem ){
					$item->items[]	= $subitem;
					$this->pageMap[$subitem->path]	= $subitem;
				}
				$this->pages[$scope][]	= $item;
				$this->pageMap[$page->path]	= $item;
			}
		}
	}

	protected function readUserPagesFromDatabase(){
		$model		= new Model_Page( $this->env );
		$scopes		= array(
			0		=> 'main',
			1		=> 'footer',
		);
		$this->pages		= array();
		$this->pageMap		= array();
		foreach( $scopes as $scopeId => $scope ){
			$this->pages[$scope]	= array();
			$pages		= $model->getAllByIndices( array(
				'parentId'	=> 0,
				'scope'		=> $scopeId,
				'status'	=> '>0',
			), array( 'rank' => 'ASC' ) );
			foreach( $pages as $page ){
				$item	= (object) array(
					'parent'	=> NULL,
					'type'		=> 'item',
					'scope'		=> $scope,
					'path'		=> $page->identifier,
					'link'		=> $page->identifier,
					'label'		=> $page->title,
					'language'	=> $this->language,
					'rank'		=> $page->rank,
//					'active'	=> $this->current == $page->identifier,
					'active'	=> FALSE,
					'icon'		=> @$page->icon,
				);
				if( $page->type == 1 ){
					$item->type		= 'menu';
					$item->items	= array();
					$subpages		= $model->getAllByIndices( array(
						'parentId'	=> $page->pageId,
						'scope'		=> 0,
						'status'	=> '>0',
					), array( 'rank' => 'ASC' ) );
					foreach( $subpages as $subpage ){
						if( $subpage->status < 1 )
							continue;
						$subitem	= (object) array(
//							'parent'	=> $item,
							'parent'	=> $page->identifier,
							'type'		=> 'item',
							'scope'		=> $scope,
							'path'		=> $page->identifier.'/'.$subpage->identifier,
							'link'		=> $page->identifier.'/'.$subpage->identifier,
							'label'		=> $subpage->title,
							'language'	=> $this->language,
							'rank'		=> $subpage->rank,
//							'active'	=> $this->current == $page->identifier.'/'.$subpage->identifier,
							'active'	=> FALSE,
							'icon'		=> @$subpage->icon,
						);
						$item->items[]	= $subitem;
						$this->pageMap[$page->identifier.'/'.$subpage->identifier]	= $subitem;
					}
				}
				$this->pages[$scope][]	= $item;
				$this->pageMap[$page->identifier]	= $item;
			}
		}
	}

	protected function readUserPagesFromModules(){
		$scopes			= array( 'main' );
		$acl			= $this->env->getAcl();
		$this->pages	= array();
		$this->pageMap	= array();
		foreach( $scopes as $scope ){
			$this->pages[$scope]	= array();
			foreach( $this->env->getModules()->getAll() as $module ){
				foreach( $module->links as $link ){
					if( $link->language && $link->language != $this->language )
						continue;
					$link->scope	= isset( $link->scope ) ? $link->scope : 'main';
					if( $link->scope !== $scope )
						continue;
					if( $link->access == 'none' )
						continue;
					if( !strlen( $link->label ) )
						continue;
	#				if( isset( $linkMap[$link->path] ) )												//  link has been added already
	#					continue;
					if( $link->access == 'inside' && !$this->userId )											//  @todo	not needed anymore?
						continue;
					if( $link->access == 'outside' && $this->userId )											//  @todo	not needed anymore?
						continue;
					$pathParts	= explode( '/', $link->path );
					$action		= array_pop( $pathParts );
					$controller	= implode( '_', $pathParts );
					if( $this->useAcl ){
						$right1	= (int) $acl->has( $controller.'_'.$action );
						$right2	= (int) $acl->has( $controller, $action );
						if( !( $right1 + $right2 ) )
							continue;
					}
					$rank	= strlen( $link->rank ) ? $link->rank : 50;
					$rank	= str_pad( $rank, 3, "0", STR_PAD_LEFT );
					$rank	.= "_".str_pad( count( $this->pages[$scope] ), 2, "0", STR_PAD_LEFT );
					$item	= (object) array(
						'parent'	=> NULL,
						'type'		=> 'item',
						'scope'		=> $scope,
						'path'		=> $link->path,
						'link'		=> is_string( $link->link ) ? $link->link : $link->path,
						'label'		=> $link->label,
						'language'	=> $this->language,
						'rank'		=> $link->rank,
//						'active'	=> $this->current == $link->path,
						'active'	=> FALSE,
					);
					$this->pages[$scope][$rank]	= $item;
					$this->pageMap[$link->path]	= $item;
				}
			}
			ksort( $this->pages[$scope] );
			$this->pages[$scope]	= array_values( $this->pages[$scope] );
		}
	}

	public function setCurrent( $path ){
//		$this->current	= $path;
		$this->identifyActive( $path );
	}
}
?>
