<?php
/**
 *	...
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013 Ceus Media
 *	@version		$Id$
 */
/**
 *	...
 *	@category		...
 *	@package		...
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013 Ceus Media
 *	@version		$Id$
 */
class Logic_Page extends CMF_Hydrogen_Logic{

	protected $app				= 'self';
	protected $model			= array();

	public function __onInit(){
	}

	public function getChildren( $pageId, $activeOnly = TRUE ){
		$page	= $this->getPageModel()->get( $pageId );
		if( !$page )
			throw new InvalidArgumentException( 'Invalid page ID given: '.$pageId );
		$indices	= array( 'parentId'	=> $pageId );
		if( $activeOnly )
			$indices['status']	= Model_Page::STATUS_VISIBLE;
		return $this->getPageModel()->getAllByIndices( $indices, array( 'rank' => 'ASC' ) );
	}

	/**
	 *	@todo		move "from path" to method hasPageByPath and make pathOrId to pageId
	 */
	public function getPage( $pageId, $strict = TRUE  ){
		if( !preg_match( '/^[0-9]+$/', $pageId ) )
			throw new RangeException( 'Given page is not an ID' );
		$page	= $this->getPageModel()->get( $pageId );
		if( !$page ){
			if( $strict )
				throw new RangeException( 'Given page is not an ID' );
			return NULL;
		}
/*		$way	= array( $page->identifier );
		$current	= $page;
		$parents	= array();
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
	 *	@return		object|null					Data object of found page or NULL if nothing found
	 *	@throws		InvalidArgumentException	if no or empty module ID is given
	 *	@todo		check if this is deprecated! why the hell get page from controller alone?
	 */
	public function getPageFromController( $controllerName, $strict = TRUE ){
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
	 *	@return		object|null					Data object of found page or NULL if nothing found
	 *	@throws		InvalidArgumentException	if no or empty module ID is given
	 */
	public function getPageFromControllerAction( $controllerName, $action, $strict = TRUE ){
		if( !strlen( trim( $controllerName ) ) )
			throw new InvalidArgumentException( 'No controller name given' );
		$page	= $this->getPageModel()->getByIndices( array(
			'controller'	=> $controllerName,
			'action'		=> $action,
		) );
		if( !$page ){
			if( !$strict )
				return NULL;
			throw new RangeException( 'No page set for controller action: '.$controllerName.':'.$action );
		}
		$page	= $this->getPage( $page->pageId );
		return $this->translatePage( $page );
	}

	/**
	 *	Tries to resolves URI path and returns found page.
	 *	@access		public
	 *	@param		string		$path			Path to find page for
	 *	@param		bool		$withParents	Flag: Returns page parents as well (default: no)
	 *	@return		object						Data object of found page or NULL if nothing found
	 *	@throws		RangeException				if path is not resolvable
	 *	@throws		RangeException				if path parent part is not resolvable
	 */
	public function getPageFromPath( $path, $withParents = FALSE ){
		$parents	= array();
		$path		= trim( $path, '/' );
		$page		= $this->getPageFromPathRecursive( $path, 0, $parents );
		if( $withParents ){
			foreach( $parents as $nr => $parent )
				$parents[$nr]	= $this->translatePage( $parent );											//  apply localization to page
			array_reverse( $parents );
			$page->parents	= $parents;
		}
		return $this->translatePage( $page );																//  return this module controlled page
	}

	/**
	 *	Tries to resolves URI path from current request and returns found page.
	 *	@access		public
	 *	@param		bool		$withParents	Flag: Returns page parents as well (default: no)
	 *	@param		boolean		$strict			Flag: throw exception on failure
	 *	@return		object|null					Data object of found page or NULL if nothing found
	 *	@throws		RuntimeException			if path is not resolvable
	 */
	public function getPageFromRequest( $withParents = FALSE, $strict = TRUE ){
		$request	= $this->env->getRequest();
		$path		= trim( $request->get( '__path' ), '/' );										//  get requested path
		$pagePath	= strlen( trim( $path ) ) ? trim( $path ) : 'index';							//  ensure page path is not empty
		try{
			$page	= $this->getPageFromPath( $pagePath, $withParents );								//  try to get page by called page path
			$request->set( 'arguments', $page->arguments );
			return $page;
		}
		catch( Exception $e ){
			if( $strict )
				throw new RuntimeException( 'Requested page is not resolvable', 0, $e );
			return NULL;
		}
	}

	/**
	 *	Indicates wheter a page exists for an URI path or a page ID .
	 *	@access		public
	 *	@param		string		$pathOrId		Path or ID to find page for
	 *	@return		boolean
	 *	@throws		InvalidArgumentException	if no or empty path is given, call atleast with path 'index'
	 *	@todo		move "by path" to method hasPageByPath and make pathOrId to pageId
	 */
	public function hasPage( $pathOrId ){
		if( preg_match( '/^[0-9]+$/', $pathOrId ) )
			return (bool) $this->getPageModel()->get( $pathOrId );
		return (bool) $this->getPageFromPath( $pathOrId );
	}

	public function hasPages( $visible = TRUE ){
		$indices	= array();
		$indices['status']	= $visible ? '>='.Model_Page::STATUS_VISIBLE : '>='.Model_Page::STATUS_HIDDEN;
		return $this->getPageModel()->count( $indices );
	}

	public function isAccessible( $page ){
		$isAuthenticated	= $this->env->getSession()->get( 'userId' );
		$public		= $page->access == "public";
		$outside	= !$isAuthenticated && $page->access == "outside";
		$inside		= $isAuthenticated && $page->access == "inside";
		return $public || $outside || $inside;
	}

	public function setApp( $app ){
		$this->app	= $app;
	}

	public function translatePage( $page ){
		if( !class_exists( 'Logic_Localization' ) )
			return $page;
		$localization		= new Logic_Localization( $this->env );
		$id	= 'page.'.$page->fullpath.'-title';
		$page->title	= $localization->translate( $id, $page->title );
		$id	= 'page.'.$page->fullpath.'-content';
		$page->content	= $localization->translate( $id, $page->content );
		return $page;
	}

	//  --  PROTECTED  --  //

	/**
	 *	...
	 *	@access		protected
	 *	@param		string		$path			Path to find page for
	 *	@param		integer		$parentPageId	Parent page ID to start with (default: 0)
	 *	@param		array		$parents		Flag: Returns page parents as well (default: no)
	 *	@return		object						Data object of found page or NULL if nothing found
	 *	@throws		RangeException				if path is not resolvable
	 */
	protected function getPageFromPathRecursive( $path, $parentPageId = 0, & $parents = array() ){
		$parts		= preg_split( '/\//', $path );
		$lastPage	= NULL;
		while( count( $parts ) ){
			$part		= array_shift( $parts );
			$indices	= array( 'identifier' => $part, 'parentId' => $parentPageId );
			$candidate	= $this->getPageModel()->getByIndices( $indices );
			if( $candidate ){
				$candidate->arguments	= array();
				$parentPageId			= $candidate->pageId;
				if( $lastPage )
					$parents[]			= $lastPage;
				$lastPage				= $candidate;
			}
			else{
				if( $lastPage )
					$lastPage->arguments[]	= $part;
				break;
			}
		}
		if( !$lastPage )
			throw new RangeException( 'Page with identifier "'.$path.'" is not resolvable' );
		return $lastPage;
	}

	protected function getPageModel(){
		if( !empty( $this->model[$this->app] ) )
			return $this->model[$this->app];
		$envManaged		= $this->env;
		if( $this->app === 'frontend' && class_exists( 'Logic_Frontend' ) ){
			$frontend	= Logic_Frontend::getInstance( $this->env );
			$envManaged	= $frontend->getEnv();
		}
		$source	= $envManaged->getModules( TRUE )->get( 'UI_Navigation' )->config['menu.source']->value;
		if( $source === 'Database' )
			$this->model[$this->app]	= new Model_Page( $envManaged );
		else if( $source === 'Config' )
			$this->model[$this->app]	= new Model_Config_Page( $envManaged );
		return $this->model[$this->app];
	}
}
