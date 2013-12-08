<?php
/**
 *	Abstract Controller for JSON server.
 * 
 *	Copyright (c) 2010-2013 Christian Würker (ceusmedia.de)
 *
 *	This program is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, either version 3 of the License, or
 *	(at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU General Public License for more details.
 *
 *	You should have received a copy of the GNU General Public License
 *	along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *	@category		cmApps
 *	@package		Chat.Server.Controller
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2013 Ceus Media <ceusmedia.de>
 *	@version		$Id: Abstract.php5 3022 2012-06-26 20:08:10Z christian.wuerker $
 */
/**
 *	Abstract Controller for JSON server.
 *	@category		cmApps
 *	@package		Chat.Server.Controller
 *	@extends		CMF_Hydrogen_Controller
 *	@uses			Logic_Chat
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2013 Ceus Media <ceusmedia.de>
 *	@version		$Id: Abstract.php5 3022 2012-06-26 20:08:10Z christian.wuerker $
 */
class Controller_Abstract extends CMF_Hydrogen_Controller {

	protected function getViewObject( $controller ){
	}

	protected function logException( Exception $exception ) {
		UI_HTML_Exception_Page::display( $exception );
		exit;
	}
}
?>