<?php
/**
 *	View.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2024 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\View;

/**
 *	View.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2024 Ceus Media (https://ceusmedia.de/)
 */
class View_Lab_Session extends View
{
	public function index()
	{
		$sessionTree	= print_m( $_SESSION, NULL, NULL, TRUE );
		$buttonReset	= HtmlTag::create( 'a', 'reset', [ 'class' => 'btn btn-inverse', 'href' => './lab/session/reset' ] );

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
