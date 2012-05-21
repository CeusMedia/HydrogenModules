<?php
$wf		= (object) $words['index'];

$list	= '<em>'.$wf->empty.'</em>';

if( $servers ){
	$list	= array();
	foreach( $servers as $server ){
		$label	= $server->title;
		$label	= UI_HTML_Elements::Link( './admin/server/edit/'.$server->serverId, $label );
		$list[]	= '<tr><td>'.$label.'</td></tr>';
	}
	$list	= '<table>'.join( $list ).'</table>';
}

return '
<fieldset>
	<legend class="icon server">'.$wf->legend.'</legend>
	'.$list.'
	<div class="buttonbar">
		'.UI_HTML_Elements::LinkButton( './admin/server/add', $wf->buttonAdd, 'button add' ).'
	</div>
</fieldset>';
?>