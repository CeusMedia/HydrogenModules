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
		$model		= new Model_Page( $this->env );
		$parts		= explode( '/', $path );
		$parentId	= 0;
		$parents	= array();
		if( !$parts )
			return NULL;
		while( $part = array_shift( $parts ) ){
			$indices	= array( 'parentId' => $parentId, 'identifier' => $part );
			$page		= $model->getByIndices( $indices );
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

	/**
	 *	Indicates wheter a page exists for an URI path.
	 *	@access		public
	 *	@param		string		$path			Path to find page for
	 *	@return		boolean
	 */
	public function hasPage( $path ){
		$model		= new Model_Page( $this->env );
		return (bool) $this->getPageFromPath( $path );
	}
}
?>
