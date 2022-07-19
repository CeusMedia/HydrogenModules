<?php
/**
 *	View.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 */

use CeusMedia\HydrogenFramework\View;

/**
 *	View.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 */
class View_Lab_Session extends View
{
	public function index()
	{
		$sessionTree	= print_m( $_SESSION, NULL, NULL, TRUE );
		$buttonReset	= UI_HTML_Tag::create( 'a', 'reset', [ 'class' => 'btn btn-inverse', 'href' => './lab/session/reset' ] );

		return '<dl>
			<dt>Session Name</dt>
			<dd>'.session_name().'</dd>
			<dt>Session ID</dt>
			<dd>'.session_id().'</dd>
			<dt>Session Content</dt>
			<dd>'.$sessionTree.'</dd>
		</dl>'.$buttonReset.'<div style="clear: both"></div>';
	}
}
