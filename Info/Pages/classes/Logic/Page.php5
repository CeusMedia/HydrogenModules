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

	public function getPageFromPath( $path ){
		$model		= new Model_Page( $this->env );
		$parts		= explode( '/', $path );
		$parentId	= 0;
		$found		= TRUE;
		while( $part = array_shift( $parts ) ){
			$indices	= array( 'parentId' => $parentId, 'identifier' => $part );
			$page		= $model->getByIndices( $indices );
			if( !$page )
				return NULL;
			$parentId	= $page->pageId;
		}
		return $page;
	}

	public function hasPage( $path ){
		$model		= new Model_Page( $this->env );
		return (bool) $this->getPageFromPath( $path );
	}
}
?>
