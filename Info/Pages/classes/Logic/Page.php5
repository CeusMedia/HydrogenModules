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

	protected $modelPage;

	public function __onInit(){
		$this->modelPage		= new Model_Page( $this->env );
	}

	/**
	 *	@todo		move "from path" to method hasPageByPath and make pathOrId to pageId
	 */
	public function getPage( $pathOrId ){
		if( preg_match( '/^[0-9]+$/', $pathOrId ) ){
			$page	= $this->modelPage->get( $pathOrId );
			if( !$page )
				return NULL;
			$way	= array( $page->identifier );
			$current	= $page;
			while( $current->parentId !== 0 ){
				$current	= $this->modelPage->get( $current->parentId );
				if( !$current )
					break;
				array_unshift( $way, $current->identifier );
			}
			$page->fullpath	= join( '/', $way );
			return $page;
		}
		return $this->modelPage->get( $pathOrId );
	}

	/**
	 *	Tries to resolves URI path and returns found page.
	 *	@access		public
	 *	@param		string		$path			Path to find page for
	 *	@return		object|null					Data object of found page or NULL if nothing found
	 *	@throws		InvalidArgumentException	if no or empty path is given, call atleast with path 'index'
	 */
	public function getPageFromPath( $path, $withParents = FALSE ){
		if( !strlen( trim( $path ) ) )
			throw new InvalidArgumentException( 'No path given' );
		if( ( $page = $this->modelPage->getByIndices( array( 'identifier' => $path ) ) ) )
			return $this->getPage( $page->pageId );
		$parts		= explode( '/', $path );
		$parentId	= 0;
		$parents	= array();
		$lastPage	= NULL;
		$way		= "";
		if( !$parts )
			return NULL;
		while( $part = array_shift( $parts ) ){
			$way		= $way ? $way.'/'.$part : $part;
			$indices	= array( 'parentId' => $parentId, 'identifier' => $part );
			$page		= $this->modelPage->getByIndices( $indices );
			if( !$page ){																			//  no page found for this identifier
				if( $lastPage && (int) $lastPage->type === Model_Page::TYPE_BRANCH )				//  last page is a module controller
					return $lastPage;																//  return this module controlled page
				return NULL;
			}
			$parentId	= $page->pageId;
			$page->fullpath	= $way;
			if( $parts )
				$parents[]	= $page;
			$lastPage	= $page;
			$lastPage->arguments	= $parts;
		}
		if( $withParents )
			$page->parents	= $parents;
		return $this->translatePage( $page );
	}

	public function getChildren( $pageId, $activeOnly = TRUE ){
		$page	= $this->modelPage->get( $pageId );
		if( !$page )
			throw new InvalidArgumentException( 'Invalid page ID given: '.$pageId );
		$indices	= array( 'parentId'	=> $pageId );
		if( $activeOnly )
			$indices['status']	= Model_Page::STATUS_VISIBLE;
		return $this->modelPage->getAllByIndices( $indices, array( 'rank' => 'ASC' ) );
	}

	/**
	 *	Tries to find page related to controller and returns found page.
	 *	@access		public
	 *	@param		string		$controllerName	Name of controller (Controller_Test -> Test)
	 *	@return		object|null					Data object of found page or NULL if nothing found
	 *	@throws		InvalidArgumentException	if no or empty module ID is given
	 */
	public function getPageFromController( $controllerName, $strict = TRUE ){
		if( !strlen( trim( $controllerName ) ) )
			throw new InvalidArgumentException( 'No controller name given' );
		$page	= $this->modelPage->getByIndex( 'controller', $controllerName );
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
	 *	@return		object|null					Data object of found page or NULL if nothing found
	 *	@throws		InvalidArgumentException	if no or empty module ID is given
	 */
	public function getPageFromControllerAction( $controllerName, $action, $strict = TRUE ){
		if( !strlen( trim( $controllerName ) ) )
			throw new InvalidArgumentException( 'No controller name given' );
		$page	= $this->modelPage->getByIndices( array(
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
	 *	Indicates wheter a page exists for an URI path or a page ID .
	 *	@access		public
	 *	@param		string		$pathOrId		Path or ID to find page for
	 *	@return		boolean
	 *	@throws		InvalidArgumentException	if no or empty path is given, call atleast with path 'index'
	 *	@todo		move "by path" to method hasPageByPath and make pathOrId to pageId
	 */
	public function hasPage( $pathOrId ){
		if( preg_match( '/^[0-9]+$/', $pathOrId ) )
			return (bool) $this->modelPage->get( $pathOrId );
		return (bool) $this->getPageFromPath( $pathOrId );
	}

	public function isAccessible( $page ){
		$isAuthenticated	= $this->env->getSession()->get( 'userId' );
		$public		= $page->access == "public";
		$outside	= !$isAuthenticated && $page->access == "outside";
		$inside		= $isAuthenticated && $page->access == "inside";
		return $public || $outside || $inside;
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
}
?>
