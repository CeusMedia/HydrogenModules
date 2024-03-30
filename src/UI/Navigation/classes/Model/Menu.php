<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\FS\File\JSON\Reader as JsonFileReader;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Environment\Resource\Acl\Abstraction as AclAbstraction;

class Model_Menu
{
	protected AclAbstraction $acl;
	protected ?object $current						= NULL;
	protected Environment $env;
	protected string $language;						//  @todo rename to current language or user language
	protected ?Logic_Localization $localization		= NULL;
	protected Dictionary $moduleConfig;
	protected array $pageMap						= [];
	protected array $pages							= [];
	protected array $scopes							= [];
	protected string $source;						//  @todo needed?
	protected ?string $userId						= NULL;
	protected bool $useAcl;							//  @todo needed?

	public static string $pathRequestKey			= "__path";			//  @todo get from env or router?

	public function __construct( Environment $env )
	{
		$this->env			= $env;
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.ui_navigation.', TRUE );
		$this->userId		= $this->env->getSession()->get( 'auth_user_id' );
		$this->language		= $this->env->getLanguage()->getLanguage();
		$this->useAcl		= $this->env->getModules()->has( 'Resource_Users' );
		$this->acl			= $this->env->getAcl();
		$this->source		= $this->moduleConfig->get( 'menu.source', '' );

		if( $this->env->getModules()->has( 'Resource_Localization' ) )
			$this->localization	= new Logic_Localization( $this->env );

		if( $this->source === "Database" && !$this->env->getModules()->has( 'Info_Pages' ) ){
			$this->env->getMessenger()->noteNotice( 'Navigation source "Database" is not available. Module "Info_Pages" is not installed. Falling back to navigation source "Config".' );
			$this->source	= "Config";
		}
		if( $this->source === "Config" && !file_exists( "config/pages.json" ) ){
			$this->env->getMessenger()->noteNotice( 'Navigation source "Config" is not available. File "config/pages.json" is not available. Falling back to navigation source "Modules".' );
			$this->source	= "Modules";
		}
		$this->readUserPages();
	}

	public function getCurrent()
	{
		return $this->current;
	}

	public function getPageMap(): array
	{
		return $this->pageMap;
	}

	public function getPages( $scope = NULL, bool $strict = TRUE ): array
	{
		if( is_null( $scope ) )
			return $this->pages;
		if( array_key_exists( $scope, $this->pages ) )
			return $this->pages[$scope];
		if( $strict )
			throw new OutOfRangeException( 'Invalid scope: '.$scope );
		return [];
	}

	public function getScopes(): array
	{
		return $this->scopes;
	}

	/**
	 *	Sets currently active path.
	 *	@access		public
	 *	@param		string		$path		Path to set as currently active
	 *	@return		self		This instance for method chaining
	 */
	public function setCurrent( string $path ): self
	{
//		$this->current	= $path;
		$this->identifyActive( $path );
		return $this;
	}

	//  --  PROTECTED  --  //

	/**
	 *	...
	 *	@access		public
	 *	@param		?string		$path		Currently requested path, auto-detected if not set
	 *	@return		string
	 */
	protected function identifyActive( ?string $path = NULL ): string
	{
		if( isset( $_REQUEST[self::$pathRequestKey] ) && $path === NULL )
			$path	= utf8_decode( $_REQUEST[self::$pathRequestKey] );
		$path		= $path ?: 'index';
		$matches	= [];																		//  empty array to regular matching
		$selected	= [];																		//  list of possibly selected links
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
				$selected[$page->path]	= $i / $pathLength;											//  qualification = number of matching characters relative to page link parts
		}
		arsort( $selected );																		//  sort link paths by its length, longest on top

		$paths	= array_keys( $selected );
		if( $paths && $first = array_shift( $paths ) ){
			$page		= $this->pageMap[$first];
			$this->pageMap[$first]->active	= TRUE;
			$this->current	= $this->pageMap[$first];
			if( $page->parent ){
				if( $this->pageMap[$page->parent]->type !== "item" )
					$this->pageMap[$page->parent]->active = TRUE;
			}
			return $page->path;																		//  return longest link path
		}
		return '';
	}

	protected function readUserPages(): void
	{
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

	protected function readUserPagesFromConfigFile(): void
	{
		$pagesFile		= $this->env->getPath( 'config' ).'pages.json';
		if( !file_exists( $pagesFile ) ){
			$message	= 'Page configuration file "%s" is not existing';
			throw new RuntimeException( sprintf( $message, $pagesFile ) );
		}
		$scopes			= JsonFileReader::load( $pagesFile );
		if( !is_object( $scopes ) ){
			$message	= 'Pages config file "%s" is outdated (not containing scopes)';
			throw new RuntimeException( sprintf( $message, $pagesFile ) );
		}
		$this->scopes		= array_keys( get_object_vars( $scopes ) );
		$this->pages		= [];
		$this->pageMap		= [];
		$isAuthenticated	= (bool) $this->userId;
		foreach( $scopes as $scope => $pages ){
			$this->pages[$scope]	= [];
			foreach( $pages as $pageId => $page ){
				if( isset( $page->disabled ) && !in_array( $page->disabled, ['no', FALSE] ) )
					continue;
				if( isset( $page->{"label@".$this->language} ) )
					$page->label	= $page->{"label@".$this->language};
				$item	= (object) array(
					'parent'	=> NULL,
					'type'		=> 'item',
					'scope'		=> $scope,
					'path'		=> $page->path,
					'link'		=> $page->link ?? $page->path,
					'label'		=> $page->label,
					'language'	=> $this->language,
					'rank'		=> $pageId,
//					'active'	=> $this->current == $page->path,
					'active'	=> FALSE,
					'icon'		=> $page->icon ?? NULL,
				);
				$subpages	= [];
				if( isset( $page->pages ) ){
					$item->type		= 'menu';
					$item->items	= [];
					foreach( $page->pages as $subpageId => $subpage ){
						if( isset( $subpage->disabled ) && !in_array( $subpage->disabled, ['no', FALSE] ) )
							continue;
						$free		= !isset( $subpage->access );
						$public		= !$free && $subpage->access == "public";
						$outside	= !$free && !$isAuthenticated && $subpage->access == "outside";
						$inside		= !$free && $isAuthenticated && $subpage->access == "inside";
//						$acl		= !$free && $subpage->access == "acl" && $this->env->getAcl()->has( $subpage->path );
						$acl		= FALSE;
						if( !$free && $subpage->access == "acl" ){
							$acl		= $this->acl->has( $subpage->path, 'index' );
							if( !$acl && ( $parts = preg_split( '/\//', $subpage->path ) ) ){
								$action		= array_pop( $parts );
								$acl		= $this->acl->has( join( '/', $parts ), $action );
							}
						}
						if( !( $public || $outside || $inside || $acl ) )
							continue;
						if( isset( $subpage->{"label@".$this->language} ) )
							$subpage->label	= $subpage->{"label@".$this->language};
						$subitem	= (object) array(
							'parent'	=> $item->path,
							'type'		=> 'item',
							'scope'		=> $scope,
							'path'		=> $subpage->path,
							'link'		=> $subpage->link ?? $subpage->path,
							'label'		=> $subpage->label,
							'language'	=> $this->language,
							'rank'		=> $subpageId,
//							'active'	=> $this->current == $page->path.'/'.$subpage->path,
							'active'	=> FALSE,
							'icon'		=> $subpage->icon ?? NULL,
							'chapter'	=> $subpage->chapter ?? '',
						);
						$subpages[]	= $subitem;
					}
				}
				$free		= !isset( $page->access );
				$public		= !$free && $page->access == "public";
				$outside	= !$free && !$isAuthenticated && $page->access == "outside";
				$inside		= !$free && $isAuthenticated && $page->access == "inside";
				$acl		= !$free && $page->access == "acl" && $this->acl->has( $page->path );
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

	/**
	 *	...
	 *	@access		protected
	 *	@todo		repair flag "active"
	 */
	protected function readUserPagesFromDatabase(): void
	{
		$model		= new Model_Page( $this->env );
		$scopes		= [
			0		=> 'main',
			1		=> 'footer',
			2		=> 'top',
		];
		$this->scopes		= array_values( $scopes );
		$this->pages		= [];
		$this->pageMap		= [];
		$isAuthenticated	= (bool) $this->userId;
		$subpages			= [];
		foreach( $scopes as $scopeId => $scope ){
			$this->pages[$scope]	= [];
			$pages		= $model->getAllByIndices( [
				'parentId'	=> 0,
				'scope'		=> $scopeId,
				'status'	=> '> 0',
			], ['rank' => 'ASC'] );
			foreach( $pages as $page ){
				$item	= (object) [
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
				];
				if( $this->localization ){
					$id	= 'page.'.$item->path.'-title';
					$item->label	= $this->localization->translate( $id, $item->label );
				}
				if( $page->type == 1 ){
					$item->type		= 'menu';
					$item->items	= [];
					$subpages		= $model->getAllByIndices( [
						'parentId'	=> $page->pageId,
						'scope'		=> 0,
						'status'	=> '> 0',
					], ['rank' => 'ASC'] );
					foreach( $subpages as $subpage ){
						if( $subpage->status < 1 )
							continue;
						$subitem	= (object) [
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
							'chapter'	=> $subpage->chapter ?? '',
						];
						if( $this->localization ){
							$id	= 'page.'.$subitem->path.'-title';
							$subitem->label	= $this->localization->translate( $id, $subitem->label );
						}
						if( $subpage->type == 2 ){
							$subpage->path	= $subpage->controller;
							$subpage->path	.= '_'.$subpage->action ? $subpage->action : 'index';
						}
						$public		= $subpage->access == "public";
						$outside	= !$isAuthenticated && $subpage->access == "outside";
						$inside		= $isAuthenticated && $subpage->access == "inside";
						$acl		= $subpage->access == "acl" && $this->acl->has( $subpage->path );

						if( !( $public || $outside || $inside || $acl ) )
							continue;
						$item->items[]	= $subitem;
						$this->pageMap[$page->identifier.'/'.$subpage->identifier]	= $subitem;
					}
				}
				$public		= $page->access == "public";
				$outside	= !$isAuthenticated && $page->access == "outside";
				$inside		= $isAuthenticated && $page->access == "inside";
				$acl		= $page->access == "acl" && $this->acl->has( $item->path );
				$menu		= $page->type == 1 && count( $subpages );
				if( !( $public || $outside || $inside || $acl || $menu ) )
					continue;
				$this->pages[$scope][]	= $item;
				$this->pageMap[$page->identifier]	= $item;
			}
		}
	}

	/**
	 *	...
	 *	@access		protected
	 *	@todo		repair flag "active"
	 */
	protected function readUserPagesFromModules(): void
	{
		$scopes			= ['main'];
		$this->scopes	= array_keys( $scopes );
		$this->pages	= [];
		$this->pageMap	= [];
		foreach( $scopes as $scope ){
			$this->pages[$scope]	= [];
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
						$right1	= (int) $this->acl->has( $controller.'_'.$action );
						$right2	= (int) $this->acl->has( $controller, $action );
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
						'link'		=> !empty( $link->link ) ? $link->link : $link->path,
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
}
