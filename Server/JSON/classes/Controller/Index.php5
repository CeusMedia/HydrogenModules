<?php
/**
 *	Controller to index server controllers and actions.
 *	@category		cmApps
 *	@package		Chat.Server.Controller
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 *	@version		$Id: Index.php5 3022 2012-06-26 20:08:10Z christian.wuerker $
 */
/**
 *	Controller to index server controllers and actions.
 *	@category		cmApps
 *	@package		Chat.Server.Controller
 *	@extends		Controller_Abstract
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 *	@version		$Id: Index.php5 3022 2012-06-26 20:08:10Z christian.wuerker $
 */
class Controller_Index extends Controller_Abstract {

	/**	@var		Environment		$env		Environment instance */
	protected $env;

	/**
	 *	Just say "Hello".
	 *	@access		public
	 *	@param		string		$guestName		Name to greet
	 *	@return		string
	 */
	public function index( $guestName = NULL ) {
		$guestName	= $guestName ? " ".$guestName : "";
		return "Hello".$guestName."!";
	}
}
?>