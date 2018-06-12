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
class Logic_Page extends CMF_Hydrogen_Environment_Resource_Logic{

	protected $modelPage;

	public function __onInit(){
		$this->modelPage		= new Model_Page( $this->env );
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
		$parts		= explode( '/', $path );
		$parentId	= 0;
		$parents	= array();
		if( !$parts )
			return NULL;
		while( $part = array_shift( $parts ) ){
			$indices	= array( 'parentId' => $parentId, 'identifier' => $part );
			$page		= $this->modelPage->getByIndices( $indices );
			if( !$page )
				return NULL;
			$parentId	= $page->pageId;
			if( $parts )
				$parents[]	= $page;
		}
		if( $withParents )
			$page->parents	= $parents;
		return $page;
	}

	public function getChildren( $pageId, $activeOnly = TRUE ){
		$page	= $this->modelPage->get( $pageId );
		if( !$page )
			throw new InvalidArgumentException( 'Invalid page ID given: '.$pageId );
		$indices	= array( 'parentId'	=> $pageId );
		if( $activeOnly )
			$indices['status']	= 1;
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
		return $this->translatePage( $page );
	}

	/**
	 *	Indicates wheter a page exists for an URI path.
	 *	@access		public
	 *	@param		string		$path			Path to find page for
	 *	@return		boolean
	 *	@throws		InvalidArgumentException	if no or empty path is given, call atleast with path 'index'
	 */
	public function hasPage( $path ){
		return (bool) $this->getPageFromPath( $path );
	}

	public function isAccessible( $page ){
		$isAuthenticated	= $this->env->getSession()->get( 'userId' );
		$public		= $page->access == "public";
		$outside	= !$isAuthenticated && $page->access == "outside";
		$inside		= $isAuthenticated && $page->access == "inside";
		return $public || $outside || $inside;
	}
}
?>
