<?php
/**
 *	View.
 *	@category		cmApps
 *	@package		Chat.Client.View
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 *	@version		$Id: Session.php 1502 2010-10-10 17:11:17Z christian.wuerker $
 */
/**
 *	View.
 *	@category		cmApps
 *	@package		Chat.Client.View
 *	@extends		CMF_Hydrogen_View
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 *	@version		$Id: Session.php 1502 2010-10-10 17:11:17Z christian.wuerker $
 */
class View_Lab_Session extends CMF_Hydrogen_View {
	public function index() {
		ob_start();
		print_m( $_SESSION );
		$s	=  ob_get_clean();
		return '<dl>
			<dt>Session Name</dt>
			<dd>'.session_name().'</dd>
			<dt>Session ID</dt>
			<dd>'.session_id().'</dd>
			<dt>Session Content</dt>
			<dd>'.$s.'</dd>
		</dl>'
		.UI_HTML_Elements::LinkButton( './lab/session/reset', 'reset' );
		
	}
}
?>