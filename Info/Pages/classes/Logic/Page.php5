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
		$lastPage	= NULL;
		$way		= "";
		if( !$parts )
			return NULL;
		while( $part = array_shift( $parts ) ){
			$way		= $way ? $way.'/'.$part : $part;
			$indices	= array( 'parentId' => $parentId, 'identifier' => $part );
			$page		= $this->modelPage->getByIndices( $indices );
			if( !$page ){																			//  no page found for this identifier
				if( $lastPage && $lastPage->type == 2 ){											//  last page is a module controller
					return $lastPage;																//  return this module controlled page
				}
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
		return $page;
	}

	public function getChildren( $pageId, $activeOnly = TRUE ){
		$page	= $this->modelPage->get( $pageId );
		if( !$page )
			throw new InvalidArgumentException( 'Invalid page ID given: '.$pageId );
		$indices	= array( 'parentId'	=> $pageId );
		if( $activeOnly )
			$indices['status']	= 1;
		return $this->modelPage->getAllByIndices( $indices );
	}

	/**
	 *	Tries to find page related to module and returns found page.
	 *	@access		public
	 *	@param		string		$module			ID of module to find related page for
	 *	@return		object|null					Data object of found page or NULL if nothing found
	 *	@throws		InvalidArgumentException	if no or empty module ID is given
	 */
	public function getPageFromModule( $module ){
		if( !strlen( trim( $module ) ) )
			throw new InvalidArgumentException( 'No module ID given' );
		$page	= $this->modelPage->getByIndex( 'module', $module );
		return $page ? $page : NULL;
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
}
?>
