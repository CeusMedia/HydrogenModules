<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;

$wf		= (object) $words['index'];

$list	= '<em>'.$wf->empty.'</em>';

if( $servers ){
	$list	= [];
	foreach( $servers as $server ){
		$label	= $server->title;
		$label	= HtmlElements::Link( './admin/server/edit/'.$server->serverId, $label );
		$list[]	= '<tr><td>'.$label.'</td></tr>';
	}
	$list	= '<table>'.join( $list ).'</table>';
}

return '
<fieldset>
	<legend class="icon server">'.$wf->legend.'</legend>
	'.$list.'
	<div class="buttonbar">
		'.HtmlElements::LinkButton( './admin/server/add', $wf->buttonAdd, 'button add' ).'
	</div>
</fieldset>';
?>