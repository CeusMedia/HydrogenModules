<?php
/**
 *	Auth Controller.
 *	@category		cmApps
 *	@package		Chat.Client.Controller
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Ceus Media
 *	@version		$Id$
 */
/**
 *	Auth Controller.
 *	@category		cmApps
 *	@package		Chat.Client.Controller
 *	@extends		CMF_Hydrogen_Controller
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Ceus Media
 *	@version		$Id$
 */
class Controller_Info extends CMF_Hydrogen_Controller {

	public function index( $path = NULL, $path = NULL, $path = NULL, $path = NULL, $path = NULL ){
		$this->addData( 'fileName', join( '/', func_get_args() ) );
	}
}
?>