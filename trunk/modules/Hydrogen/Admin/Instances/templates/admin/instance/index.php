<?php

$rows	= array();
foreach( $instances as $instance ){
	$link	= UI_HTML_Elements::Link( './admin/instance/edit/'.$instance->id, $instance->title );
	$rows[$instance->title]	= '<tr><td>'.$link.'</td><td>'.$instance->path.'</td><td></td></tr>';
}
ksort( $rows );

$panelList	= '
<fieldset>
	<legend>Instanzen</legend>
	<table>
		<tr><th>Instanz</th><th>Pfad</th><th></th></tr>
		'.join( $rows ).'
	</table>
	'.UI_HTML_Elements::LinkButton( './admin/instance/add', 'neue Instanz', 'button add' ).'
</fieldset>
';

return '
<div class="column-left-75">
	'.$panelList.'
</div>
';

?>