<?php
/**
 *	Controller.
 *	@category		cmApps
 *	@package		Chat.Client.Controller
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 *	@version		$Id: Session.php 1593 2010-10-28 10:27:34Z christian.wuerker $
 */
/**
 *	Controller.
 *	@category		cmApps
 *	@package		Chat.Client.Controller
 *	@extends		CMF_Hydrogen_Controller
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 *	@version		$Id: Session.php 1593 2010-10-28 10:27:34Z christian.wuerker $
 */
class Controller_Lab_Session extends CMF_Hydrogen_Controller {
	public function index(){}
	public function reset(){
		$this->env->getSession()->clear();
		session_destroy();
		session_regenerate_id();
		$this->restart( './session' );
	}
}
?>