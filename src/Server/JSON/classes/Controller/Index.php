<?php
/**
 *	Controller to index server controllers and actions.
 *	@category		cmApps
 *	@package		Chat.Server.Controller
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2024 Ceus Media (https://ceusmedia.de/)
 */
/**
 *	Controller to index server controllers and actions.
 *	@category		cmApps
 *	@package		Chat.Server.Controller
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2024 Ceus Media (https://ceusmedia.de/)
 */
class Controller_Index extends Controller_Abstract
{
	/**
	 *	Just say "Hello".
	 *	@access		public
	 *	@param		string		$guestName		Name to greet
	 *	@return		string
	 */
	public function index( $guestName = NULL )
	{
		$guestName	= $guestName ? " ".$guestName : "";
		return "Hello".$guestName."!";
	}
}
