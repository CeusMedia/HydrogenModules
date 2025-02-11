<?php
/**
 *	...
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013-2024 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\HydrogenFramework\Logic;
use CeusMedia\HydrogenFramework\Environment\Exception as EnvironmentException;

/**
 *	...
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013-2024 Ceus Media (https://ceusmedia.de/)
 */
class Logic_Page extends Logic
{
	protected string $app			= 'self';
	protected array $model			= [];

	/**
	 *	Decorates page with its parent pages by adding a list of parent pages to the given page object.
	 *	@access		public
	 *	@param		Entity_Page		$page		Page data object to set list of parent pages to
	 *	@return		array			List of parent pages, added to given page object
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 *	@throws		ReflectionException
	 *	@throws		EnvironmentException
	 */
	public function decoratePathWithParents( Entity_Page $page ): array
	{
		$model		= $this->getPageModel();
		$current	= $page;
		$list		= [];
		while( $current->parentId ){
			$candidate	= $model->get( $current->parentId );
			if( !$candidate )
				throw new DomainException( vsprintf(
					'Page %d relates to invalid parent page ID %d',
					array( $current->pageId, $current->parentId )
				) );
//			$current->parent	= $candidate;
//			$candidate->child	= $current;
			$list[]		= $candidate;
			$current	= $candidate;
		}
		$list	= array_reverse( $list );
		$page->parents	= $list;
		return $list;
	}

	/**
	 *	@param		int|string		$pageId
	 *	@param		bool			$activeOnly
	 *	@return		array
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 *	@throws		ReflectionException
	 *	@throws		EnvironmentException
	 */
	public function getChildren( int|string $pageId, bool $activeOnly = TRUE ): array
	{
		$page	= $this->getPageModel()->get( $pageId );
		if( !$page )
			throw new InvalidArgumentException( 'Invalid page ID given: '.$pageId );
		$indices	= ['parentId'	=> $pageId];
		if( $activeOnly )
			$indices['status']	= Model_Page::STATUS_VISIBLE;
		return $this->getPageModel()->getAllByIndices( $indices, ['rank' => 'ASC'] );
	}

	/**
	 *	Return page of type component by full path, if available.
	 *	@access		public
	 *	@param		string			$path		Full path to get component for
	 *	@return		?Entity_Page				Page data object if available or NULL of strict is disabled
	 *	@throws		InvalidArgumentException	if given path is not a string or empty
	 *	@throws		RangeException				if no page object found for by fullpath in strict mode
	 *	@throws		ReflectionException
	 *	@throws		EnvironmentException
	 */
	public function getComponentFromPath( string $path, bool $strict = TRUE ): ?Entity_Page
	{
		if( !strlen( trim( $path ) ) )
			throw new InvalidArgumentException( 'No path given' );
		$page	= $this->getPageModel()->getByIndices( [
			'type'		=> Model_Page::TYPE_COMPONENT,
			'fullpath'	=> $path
		] );
		if( !$page ){
			if( !$strict )
				return NULL;
			throw new RangeException( 'No component set for path: '.$path );
		}
		return $page;
	}

	/**
	 *	@param		int|string		$pageId
	 *	@param		bool			$strict		Flag: throw exception on miss, default: yes
	 *	@return		?Entity_Page
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 *	@throws		ReflectionException
	 *	@throws		EnvironmentException
	 *	@todo		move "from path" to method hasPageByPath and make pathOrId to pageId
	 */
	public function getPage( int|string $pageId, bool $strict = TRUE  ): ?Entity_Page
	{
		if( !preg_match( '/^[0-9]+$/', $pageId ) )
			throw new RangeException( 'Given page is not an ID' );
		$page	= $this->getPageModel()->get( $pageId );
		if( NULL === $page ){
			if( $strict )
				throw new RangeException( 'Given page is not an ID' );
			return NULL;
		}
/*		$way	= [$page->identifier];
		$current	= $page;
		$parents	= [];
		while( $current->parentId !== 0 ){
			$current	= $this->getPageModel()->get( $current->parentId );
			if( !$current )
				break;
			$parents[]	= $current;
			array_unshift( $way, $current->identifier );
		}
		$page->fullpath	= join( '/', $way );
		$page->parents	= $parents;*/
		return $page;
	}

	/**
	 *	Tries to find page related to controller and returns found page.
	 *	@access		public
	 *	@param		string		$controllerName	Name of controller (Controller_Test -> Test)
	 *	@param		boolean		$strict			Flag: throw exceptions on failure
	 *	@return		?Entity_Page				Data object of found page or NULL if nothing found
	 *	@throws		InvalidArgumentException	if no or empty module ID is given
	 *	@todo		check if this is deprecated! why the hell get page from controller alone?
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 *	@throws		EnvironmentException
	 */
	public function getPageFromController( string $controllerName, bool $strict = TRUE ): ?Entity_Page
	{
		if( !strlen( trim( $controllerName ) ) )
			throw new InvalidArgumentException( 'No controller name given' );
		$page	= $this->getPageModel()->getByIndex( 'controller', $controllerName );
		if( !$page ){
			if( !$strict )
				return NULL;
			throw new RangeException( 'No page set for controller: '.$controllerName );
		}
		$page	= $this->getPage( $page->pageId );
		return $this->translatePage( $page );
	}

	/**
	 *	Tries to find page related to controller and returns found page.
	 *	@access		public
	 *	@param		string		$controllerName	Name of controller (Controller_Test -> Test)
	 *	@param		string		$action			Name of controller action
	 *	@param		boolean		$strict			Flag: throw exceptions on failure
	 *	@return		?Entity_Page				Data object of found page or NULL if nothing found
	 *	@throws		InvalidArgumentException	if no or empty module ID is given
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 *	@throws		EnvironmentException
	 */
	public function getPageFromControllerAction( string $controllerName, string $action, bool $strict = TRUE ): ?Entity_Page
	{
		if( !strlen( trim( $controllerName ) ) )
			throw new InvalidArgumentException( 'No controller name given' );
		$page	= $this->getPageModel()->getByIndices( [
			'controller'	=> $controllerName,
			'action'		=> $action,
		] );
		if( !$page ){
			if( !$strict )
				return NULL;
			throw new RangeException( 'No page set for controller action: '.$controllerName.':'.$action );
		}
		$page	= $this->getPage( $page->pageId );
		$page->dispatcher	= (object) [
			'type'		=> 'module',
			'module'	=> 'Resource_Page',
			'strategy'	=> 'controller_action',
		];
		return $this->translatePage( $page );
	}

	/**
	 *	Tries to resolve URI path and returns found page.
	 *	@access		public
	 *	@param		string		$path			Path to find page for
	 *	@param		bool		$withParents	Flag: Returns page parents as well (default: no)
	 *	@return		?Entity_Page				Data object of found page or NULL if nothing found
	 *	@throws		RangeException				if path is not resolvable
	 *	@throws		RangeException				if path parent part is not resolvable
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function getPageFromPath( string $path, bool $withParents = FALSE, bool $strict = TRUE ): ?Entity_Page
	{
		$path		= trim( $path, '/' );
		try{
			$page	= $this->getPageFromPathRecursive( $path );
			if( $withParents ){
				$this->decoratePathWithParents( $page );
				foreach( $page->parents as $nr => $parent )
					$page->parents[$nr]	= $this->translatePage( $parent );							//  apply localization to page
			}
			return $this->translatePage( $page );													//  return this module controlled page
		}
		catch( Exception $e ){
			if( $strict )
				throw new RuntimeException( 'Requested page is not resolvable ('.$e->getMessage().')', 0, $e );
			return NULL;
		}
	}

	/**
	 *	Tries to resolve URI path from current request and returns found page.
	 *	@access		public
	 *	@param		bool		$withParents	Flag: Returns page parents as well (default: no)
	 *	@param		boolean		$strict			Flag: throw exception on failure
	 *	@return		?Entity_Page				Data object of found page or NULL if nothing found
	 *	@throws		RuntimeException			if path is not resolvable
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function getPageFromRequest( bool $withParents = FALSE, bool $strict = TRUE ): ?Entity_Page
	{
		$request	= $this->env->getRequest();
		$path		= trim( $request->get( '__path', '' ), '/' );									//  get requested path
		$pagePath	= strlen( trim( $path ) ) ? trim( $path ) : 'index';							//  ensure page path is not empty
		try{
			return $this->getPageFromPath( $pagePath, $withParents );						// try to get page by called page path
		}
		catch( Exception $e ){
			if( $strict )
				throw new RuntimeException( 'Requested page is not resolvable', 0, $e );
			return NULL;
		}
	}

	/**
	 *	Indicates whether a page exists for a URI path or a page ID .
	 *	@access		public
	 *	@param		int|string		$pathOrId		Path or ID to find page for
	 *	@return		boolean
	 *	@throws		InvalidArgumentException	if no or empty path is given, call at least with path 'index'
	 *	@todo		move "by path" to method hasPageByPath and make pathOrId to pageId
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 *	@throws		ReflectionException
	 *	@throws		EnvironmentException
	 */
	public function hasPage( int|string $pathOrId ): bool
	{
		if( preg_match( '/^[0-9]+$/', $pathOrId ) )
			return (bool) $this->getPageModel()->get( $pathOrId );
		return (bool) $this->getPageFromPath( $pathOrId );
	}

	/**
	 *	@param		bool		$visible
	 *	@return		bool
	 *	@throws		ReflectionException
	 *	@throws		EnvironmentException
	 */
	public function hasPages( bool $visible = TRUE ): bool
	{
		$minimumStatus	= $visible ? Model_Page::STATUS_VISIBLE : Model_Page::STATUS_HIDDEN;
		$indices		= ['status' => '>= '.$minimumStatus];
		return $this->getPageModel()->count( $indices );
	}

	/**
	 *	@param		Entity_Page		$page
	 *	@return		bool
	 */
	public function isAccessible( Entity_Page $page ): bool
	{
		$isAuthenticated	= $this->env->getSession()->get( 'auth_user_id' );
		$hasRight			= FALSE;
		if( Model_Page::TYPE_MODULE === $page->type && 'acl' === $page->access )
			$hasRight	= $this->env->getAcl()->has( $page->controller, $page->action ?: 'index' );
		$public		= 'public' === $page->access;
		$outside	= !$isAuthenticated && 'outside' === $page->access;
		$inside		= $isAuthenticated && 'inside' === $page->access;
		return $public || $outside || $inside || $hasRight;
	}

	/**
	 *	@param		string		$app
	 *	@return		self
	 */
	public function setApp( string $app ): self
	{
		$this->app	= $app;
		return $this;
	}

	/**
	 *	@param		Entity_Page		$page
	 *	@return		Entity_Page
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function translatePage( Entity_Page $page ): Entity_Page
	{
		if( !class_exists( 'Logic_Localization' ) )
			return $page;
		$localization		= new Logic_Localization( $this->env );
		$id	= 'page.'.$page->fullpath.'-title';
		$page->title	= $localization->translate( $id, $page->title );
		$id	= 'page.'.$page->fullpath.'-content';
		$page->content	= $localization->translate( $id, $page->content );
		return $page;
	}

	/**
	 *	@param		int|string		$pageId
	 *	@param		string|NULL		$parentPath
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 *	@throws		ReflectionException
	 *	@throws		EnvironmentException
	 */
	public function updateFullpath( int|string $pageId, string $parentPath = NULL ): void
	{
		$model	= $this->getPageModel();
		$page	= $model->get( $pageId );
		if( !$parentPath ){
			$parentPath	= '';
			$parent	= $page;
			while( $parent->parentId ){
				$parent	= $model->get( $page->parentId );
				$parentPath	= $parent->identifier.'/'.$parentPath;
			}
		}
		$model->edit( $pageId, [
			'fullpath'		=> $parentPath.$page->identifier,
			'modifiedAt'	=> time(),
		] );
		if( Model_Page::TYPE_BRANCH === (int) $page->type )
			foreach( $model->getAllByIndex( 'parentId', $pageId ) as $subpage )
				$this->updateFullpath( $subpage->pageId, $parentPath.$page->identifier.'/' );
	}

	//  --  PROTECTED  --  //

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 *	@throws		ReflectionException
	 *	@throws		EnvironmentException
	 */
	protected function __onInit(): void
	{
		$moduleNav	= $this->env->getModules()->get( 'UI_Navigation', TRUE, FALSE );
		if( !$moduleNav || 'Database' !== $moduleNav->config['menu.source']->value )
			return;
		$model	= $this->getPageModel();
		foreach( $model->getAllByIndex( 'fullpath', '' ) as $page ){
			$way	= '';
			$parent	= $page;
			while( $parent->parentId ){
				$parent	= $model->get( $page->parentId );
				$way	.= $parent->identifier.'/';
			}
			$model->edit( $page->pageId, ['fullpath' => $way.$page->identifier] );
		}
	}

	/**
	 *	Tries to find page for given URL path with several strategies.
	 *	Otherwise, returns NULL or throws exception if strict mode is on.
	 *	Attention: Pages to be found must be at least hidden (but not disabled) and of type content or module.
	 *
	 *	@access		protected
	 *	@param		string		$path			Path to find page for
	 *	@param		integer		$parentPageId	Parent page ID to start with (default: 0)
	 *	@param		boolean		$strict			Flag: strict mode - throw exceptions
	 *	@return		?Entity_Page				Data object of found page or NULL if nothing found and not in strict mode
	 *	@throws		RangeException				if path is not resolvable and strict mode is on
	 *	@todo		remove strategies absolute_backward and relative_forward since both are buggy
	 *	@throws		ReflectionException
	 *	@throws		EnvironmentException
	 */
	protected function getPageFromPathRecursive( string $path, int $parentPageId = 0, bool $strict = TRUE ): ?Entity_Page
	{
		$model	= $this->getPageModel();
		$parts	= preg_split( '/\//', $path );
		$indices	= [																		//  basic indices to find page
			'type'		=> [Model_Page::TYPE_CONTENT, Model_Page::TYPE_MODULE],				//  ... being of page type content or module
			'status'	=> [Model_Page::STATUS_HIDDEN, Model_Page::STATUS_VISIBLE],			//  ... being visible or hidden, but not disabled
		];
		$dispatcher	= [
			'type'		=> 'module',
			'module'	=> 'Resource_Page',
		];

		/**
		 *	Strategy: fullpath_backward
		 *
		 *	Tries to find page by comparing requested path with fullpath of page models.
		 *	Reduces requested path backwards until matching a fullpath.
		 *	Found page:
		 *	 - can be, of course, at every level - parent ID does not matter.
		 *	 - must be of type Model_Page::TYPE_CONTENT or TYPE_MODULE
		 *	 - must be visible or at least hidden
		 */
		for( $i=count( $parts ); $i>0; $i-- ){														//  backward resolution
			$candidate	= $model->getByIndices( array_merge( $indices, [							//  try to find page ...
				'fullpath'		=> join( '/', array_slice( $parts, 0, $i ) ),						//  ... having this full path
			] ) );
			if( $candidate ){																		//  page found
				$candidate->arguments	= array_slice( $parts, $i );								//  set cut path parts as action arguments
				$candidate->dispatcher	= (object) array_merge( $dispatcher, [
					'strategy'	=> 'fullpath_backward',
				] );
				return $candidate;
			}
		}

		/**
		 *	Strategy: absolute_backward
		 *
		 *	Tries to find page by identifier, also supporting identifier to contain slashes.
		 *	This is a fix for strategy relative_forward.
		 *	Reduces requested path backwards until matching an identifier.
		 *	Found page:
		 *	 - must be in root, so having no parent
		 *	 - must be of type Model_Page::TYPE_CONTENT or TYPE_MODULE
		 *	 - must be visible or at least hidden
		 *	Problem: Does not work for pages in deeper levels
		 */
		for( $i=count( $parts ); $i>0; $i-- ){														//  absolute and backward resolution
			$candidate	= $model->getByIndices( array_merge( $indices, [							//  try to find page ...
				'parentId'		=> 0,																//  ... with no parent --> absolute
				'identifier'	=> join( '/', array_slice( $parts, 0, $i ) ),						//  ... having this sub path
			] ) );
			if( $candidate ){																		//  page found
				$candidate->arguments	= array_slice( $parts, $i );								//  set cut path parts as action arguments
				$candidate->dispatcher	= (object) array_merge( $dispatcher, [
					'strategy'	=> 'absolute_backward',
				] );
				return $candidate;
			}
		}

		/**
		 *	Strategy: relative_forward
		 *
		 *	Iterates pages recursive by parents starting from top while each path part matches a page identifier.
		 *	Returns deepest found page, that
		 *	 - must be of type Model_Page::TYPE_CONTENT or TYPE_MODULE
		 *	 - must be visible or at least hidden
		 *
		 *	Problem: Does not work if page identifier contains a slash, e.g. is like abc/def
		 */
		$lastPage	= NULL;
		while( count( $parts ) ){																	//  relative and forward resolution
			$part		= array_shift( $parts );												//  take next path part
			$candidate	= $model->getByIndices( array_merge( $indices, [							//  try to find page ...
				'parentId'		=> $parentPageId,
				'identifier'	=> $part,
			] ) );
			if( !$candidate )
				break;
			$candidate->arguments	= $parts;
			$candidate->dispatcher	= (object) array_merge( $dispatcher, [
				'strategy'	=> 'relative_forward',
			] );
			$parentPageId			= $candidate->pageId;
			$lastPage				= $candidate;
		}
		if( $lastPage )
			return $lastPage;

		if( $strict )
			throw new RangeException( 'Page with identifier "'.$path.'" is not resolvable' );
		return NULL;
	}

	/**
	 *	@return		Model_Config_Page|Model_Module_Page|Model_Page
	 *	@throws		ReflectionException
	 *	@throws		EnvironmentException
	 */
	protected function getPageModel(): Model_Page|Model_Module_Page|Model_Config_Page
	{
		if( !empty( $this->model[$this->app] ) )
			return $this->model[$this->app];
		$envManaged		= $this->env;
		if( $this->app === 'frontend' && class_exists( 'Logic_Frontend' ) ){
			$frontend	= Logic_Frontend::getInstance( $this->env );
			$envManaged	= $frontend->getEnv();
		}
		$source	= $envManaged->getModules()->get( 'UI_Navigation' )->config['menu.source']->value;
		if( $source === 'Database' )
			$this->model[$this->app]	= new Model_Page( $envManaged );
		else if( $source === 'Config' )
			$this->model[$this->app]	= new Model_Config_Page( $envManaged );
		else if( $source === 'Modules' )
			$this->model[$this->app]	= new Model_Module_Page( $envManaged );
		return $this->model[$this->app];
	}
}
