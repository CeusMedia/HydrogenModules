<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\FS\File\JSON\Reader as JsonFileReader;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Environment\Resource\Acl\Abstraction as AclAbstraction;

class Model_Menu
{
	public static string $pathRequestKey			= '__path';			//  @todo get from env or router?

	public const SOURCE_DATABASE					= 'database';
	public const SOURCE_CONFIG						= 'config';
	public const SOURCE_MODULES						= 'modules';
	public const SOURCES							= [
		self::SOURCE_CONFIG,
		self::SOURCE_DATABASE,
		self::SOURCE_MODULES,
	];

	protected AclAbstraction $acl;
	protected ?object $current						= NULL;
	protected Environment $env;
	protected string $language;						//  @todo rename to current language or user language
	protected ?Logic_Localization $localization		= NULL;
	protected Dictionary $moduleConfig;
	/** @var array<string,Entity_Menu_Item> $pageMap */
	protected array $pageMap						= [];
	/** @var array<string,array<int,Entity_Menu_Item>> $pages */
	protected array $pages							= [];
	protected array $scopes							= [];

	/** @var string $source */
	protected string $source;						//  @todo needed?
	protected ?string $userId						= NULL;
	protected object $msg;

	protected bool $useAcl;							//  @todo needed?

	public function __construct( Environment $env )
	{
		$this->env			= $env;
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.ui_navigation.', TRUE );
		$this->userId		= $this->env->getSession()->get( 'auth_user_id' );
		$this->language		= $this->env->getLanguage()->getLanguage();
		$this->useAcl		= $this->env->getModules()->has( 'Resource_Users' );
		$this->acl			= $this->env->getAcl();
		$this->source		= strtolower( $this->moduleConfig->get( 'menu.source', '' ) );
		$this->msg			= (object) $this->env->getLanguage()->getWords( 'ui/menu' )['msg'];

		if( $this->env->getModules()->has( 'Resource_Localization' ) )
			$this->localization	= new Logic_Localization( $this->env );

		if( self::SOURCE_DATABASE === $this->source && !$this->env->getModules()->has( 'Resource_Pages' ) ){
			$this->env->getMessenger()->noteNotice( $this->msg->errorSourceDatabaseNotAvailable );
			$this->source	= self::SOURCE_CONFIG;
		}
		if( self::SOURCE_CONFIG === $this->source && !file_exists( 'config/pages.json' ) ){
			$this->env->getMessenger()->noteNotice( $this->msg->errorSourceConfigNotAvailable );
			$this->source	= self::SOURCE_MODULES;
		}
		$this->readUserPages();
	}

	public function getCurrent(): ?object
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

	protected function hasAccessToConfigPageLevel1( object $page, array $subpages ): bool
	{
		$isAuthenticated	= (bool) $this->userId;
		$free		= !isset( $page->access );
		$public		= !$free && 'public' === $page->access;
		$outside	= !$free && 'outside' === $page->access && !$isAuthenticated;
		$inside		= !$free && 'inside' === $page->access && $isAuthenticated;
		$acl		= !$free && $isAuthenticated && 'acl' === $page->access && $this->acl->has( $page->path );
		$menu		= isset( $page->pages ) && [] !== $page->pages && [] !== $subpages;
		return $public || $outside || $inside || $acl || $menu;
	}

	protected function hasAccessToConfigPageLevelX( object $subpage ): bool
	{
		$isAuthenticated	= (bool) $this->userId;
		$free		= !isset( $subpage->access );
		$public		= !$free && 'public' === $subpage->access;
		$outside	= !$free && 'outside' === $subpage->access && !$isAuthenticated;
		$inside		= !$free && 'inside' === $subpage->access && $isAuthenticated;
		$acl		= FALSE;
		if( !$free && $isAuthenticated && 'acl' === $subpage->access ){
			$acl		= $this->acl->has( $subpage->path, 'index' );
			if( !$acl && ( $parts = preg_split( '/\//', $subpage->path ) ) ){
				$action		= array_pop( $parts );
				$acl		= $this->acl->has( join( '/', $parts ), $action );
			}
		}
		return $public || $outside || $inside || $acl;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		?string		$path		Currently requested path, auto-detected if not set
	 *	@return		string
	 */
	protected function identifyActive( ?string $path = NULL ): string
	{
		if( isset( $_REQUEST[self::$pathRequestKey] ) && $path === NULL )
			$path	= @utf8_decode( $_REQUEST[self::$pathRequestKey] );
		$path		= $path ?: 'index';
		$selected	= [];																			//  list of possibly selected links
		foreach( $this->pageMap as $pagePath => $page ){											//  iterate link map
			$page->active = FALSE;
			if( $pagePath == $path ){																//  page path matches requested path
				$selected[$pagePath]	= strlen( $path );											//  note page with highest conformity (longest match length)
				break;
			}
			$pathLength	= min( 1, strlen( $path ) );

			$parts	= explode( '/', $page->link );											//  parts of menu page link
			for( $i=0; $i<strlen( $path ); $i++ ){													//  iterate requested path
				if( !isset( $page->link[$i] ) ){													//  menu page link is finished
					if( $path[$i] === "/" )															//  but path goes on
						$i	+= 3;																	//  add bonus to rank of this page with if rest of path is action, only
					break;																			//  break scan here
				}
				if( $path[$i] !== $page->link[$i] )													//  requested path and menu page path are not matching anymore
					break;																			//  break scan here
			}
			if( $i )
				$selected[$page->path]	= $i / $pathLength;											//  qualification = number of matching characters relative to page link parts
		}
		arsort( $selected );																	//  sort link paths by its length, longest on top

		$paths	= array_keys( $selected );
		if( $paths && $first = array_shift( $paths ) ){
			$page	= $this->pageMap[$first];
			$this->pageMap[$first]->active	= TRUE;
			$this->current	= $this->pageMap[$first];
			if( $page->parent ){
				if( Entity_Menu_Item::TYPE_ITEM !== $this->pageMap[$page->parent]->type )
					$this->pageMap[$page->parent]->active = TRUE;
			}
			return $page->path;																		//  return longest link path
		}
		return '';
	}

	/**
	 *	@return		void
	 *	@throws		OutOfRangeException		if defined source is not one of [Config, Database, Modules]
	 */
	protected function readUserPages(): void
	{
		if( !in_array( $this->source, self::SOURCES, TRUE ) )
			throw new OutOfRangeException( 'Invalid source: '.$this->source );

		match( $this->source ){
			self::SOURCE_MODULES	=> $this->readUserPagesFromModules(),
			self::SOURCE_CONFIG		=> $this->readUserPagesFromConfigFile(),
			self::SOURCE_DATABASE	=> $this->readUserPagesFromDatabase(),
		};
		$this->identifyActive();
	}

	protected function readUserPagesFromConfigFile(): void
	{
		$pagesFile		= $this->env->getPath( 'config' ).'pages.json';
		if( !file_exists( $pagesFile ) )
			throw new RuntimeException( sprintf( $this->msg->errorConfigFileNotExisting, $pagesFile ) );

		$scopes			= JsonFileReader::load( $pagesFile );
		if( !is_object( $scopes ) )
			throw new RuntimeException( sprintf( $this->msg->errorConfigFileMissingScopes, $pagesFile ) );

		$this->scopes		= array_keys( get_object_vars( $scopes ) );
		$this->pages		= [];
		$this->pageMap		= [];
		foreach( $scopes as $scope => $pages ){
			$this->pages[$scope]	= [];
			foreach( $pages as $pageId => $page ){
				if( isset( $page->disabled ) && !in_array( $page->disabled, ['no', FALSE] ) )
					continue;
				if( isset( $page->{"label@".$this->language} ) )
					$page->label	= $page->{"label@".$this->language};
				$item	= Entity_Menu_Item::fromArray( [] );
				$item->scope		= $scope;
				$item->path			= $page->path;
				$item->link			= $page->link ?? $page->path;
				$item->label		= $page->label;
				$item->language		= $this->language;
				$item->rank			= $pageId;
//				$item->active		= $this->current == $page->path;
				$item->icon			= $page->icon ?? NULL;

				$subpages	= [];
				if( isset( $page->pages ) ){
					$item->type		= Entity_Menu_Item::TYPE_MENU;
					$item->items	= [];
					foreach( $page->pages as $subpageId => $subpage ){
						if( isset( $subpage->disabled ) && !in_array( $subpage->disabled, ['no', FALSE] ) )
							continue;
						if( !$this->hasAccessToConfigPageLevelX( $subpage ) )
							continue;
						if( isset( $subpage->{"label@".$this->language} ) )
							$subpage->label	= $subpage->{"label@".$this->language};
						$subitem	= Entity_Menu_Item::fromArray( [] );
						$subitem->parent	= $item->path;
						$subitem->scope		= $scope;
						$subitem->path		= $subpage->path;
						$subitem->link		= $subpage->link ?? $subpage->path;
						$subitem->label		= $subpage->label;
						$subitem->language	= $this->language;
						$subitem->rank		= $subpageId;
//						$subitem->active	= $this->current == $page->path.'/'.$subpage->path;
						$subitem->icon		= $subpage->icon ?? NULL;
						$subitem->chapter	= $subpage->chapter ?? '';

						$subpages[]	= $subitem;
					}
				}
				if( !$this->hasAccessToConfigPageLevel1( $page, $subpages ) )
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
		$model		= new Model_Page_ByDatabase( $this->env );
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
			/** @var Entity_Page[] $pages */
			$pages		= $model->getAllByIndices( [
				'parentId'	=> 0,
				'scope'		=> $scopeId,
				'status'	=> '>= '.Model_Page_ByDatabase::STATUS_VISIBLE,
			], ['rank' => 'ASC'] );
			foreach( $pages as $page ){
				$item	= new Entity_Menu_Item();
				$item->scope	= $scope;
				$item->path		= $page->identifier;
				$item->link		= $page->identifier;
				$item->label	= $page->title;
				$item->language	= $this->language;
				$item->rank		= $page->rank;
//				$item->active	= $this->current == $page->identifier;
				$item->active	= FALSE;
				$item->icon		= @$page->icon;

				if( $this->localization ){
					$id	= 'page.'.$item->path.'-title';
					$item->label	= $this->localization->translate( $id, $item->label );
				}
				if( Model_Page_ByDatabase::TYPE_BRANCH === $page->type ){
					$item->type		= Entity_Menu_Item::TYPE_MENU;
					$item->items	= [];
					/** @var Entity_Page[] $subpages */
					$subpages		= $model->getAllByIndices( [
						'parentId'	=> $page->pageId,
						'scope'		=> 0,
						'status'	=> '> 0',
					], ['rank' => 'ASC'] );
					foreach( $subpages as $subpage ){
						if( $subpage->status < 1 )
							continue;
						$subitem	= new Entity_Menu_Item();
//						$subitem->parent	= $item;
						$subitem->parent	= $page->identifier;
						$subitem->scope		= $scope;
						$subitem->path		= $page->identifier.'/'.$subpage->identifier;
						$subitem->link		= $page->identifier.'/'.$subpage->identifier;
						$subitem->label		= $subpage->title;
						$subitem->language	= $this->language;
						$subitem->rank		= $subpage->rank;
//						$subitem->active	= $this->current == $page->identifier.'/'.$subpage->identifier;
						$subitem->icon		= @$subpage->icon;
						$subitem->chapter	= $subpage->chapter ?? '';

						if( $this->localization ){
							$id	= 'page.'.$subitem->path.'-title';
							$subitem->label	= $this->localization->translate( $id, $subitem->label );
						}
						if( Model_Page_ByDatabase::TYPE_MODULE === $subpage->type ){
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
				$public		= Model_Page_ByDatabase::ACCESS_PUBLIC === $page->access;
				$outside	= Model_Page_ByDatabase::ACCESS_OUTSIDE === $page->access && !$isAuthenticated;
				$inside		= Model_Page_ByDatabase::ACCESS_INSIDE === $page->access && $isAuthenticated;
				$acl		= Model_Page_ByDatabase::ACCESS_ACL === $page->access && $this->acl->has( $item->path );
				$menu		= Model_Page_ByDatabase::TYPE_BRANCH === $page->type && count( $subpages );
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
			/** @var Environment\Resource\Module\Definition $module */
			foreach( $this->env->getModules()->getAll() as $module ){
				foreach( $module->links as $link ){
					if( $link->language && $link->language != $this->language )
						continue;
					$link->scope	= $link->scope ?? 'main';
					if( $link->scope !== $scope )
						continue;
					if( $link->access == 'none' )
						continue;
					if( !strlen( $link->label ) )
						continue;
	#				if( isset( $linkMap[$link->path] ) )												//  link has been added already
	#					continue;
					if( 'inside' === $link->access && !$this->userId )											//  @todo	not needed anymore?
						continue;
					if( 'outside' === $link->access && $this->userId )											//  @todo	not needed anymore?
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
					$item	= new Entity_Menu_Item();
					$item->scope		= $scope;
					$item->path			= $link->path;
					$item->link			= !empty( $link->link ) ? $link->link : $link->path;
					$item->label		= $link->label;
					$item->language		= $this->language;
					$item->rank			= $link->rank;
//					$item->active		= $this->current == $link->path,
					$this->pages[$scope][$rank]	= $item;
					$this->pageMap[$link->path]	= $item;
				}
			}
			ksort( $this->pages[$scope] );
			$this->pages[$scope]	= array_values( $this->pages[$scope] );
		}
	}
}
