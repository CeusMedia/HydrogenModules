<?php

$rows	= array();
foreach( $bugs as $bug ){
	
	$reporter	= '';
	$manager	= '';
	if( $bug->reporterId ){
		$link		= UI_HTML_Tag::create( 'a', $users[$bug->reporterId]->username, array( 'href' => './user/edit/'.$bug->reporterId ) );
		$reporter	= UI_HTML_Tag::create( 'span', $link, array( 'class' => 'role role'.$users[$bug->reporterId]->roleId ) );
	}
	if( $bug->managerId ){
		$link		= UI_HTML_Tag::create( 'a', $users[$bug->managerId]->username, array( 'href' => './user/edit/'.$bug->managerId ) );
		$manager	= UI_HTML_Tag::create( 'span', $link, array( 'class' => 'role role'.$users[$bug->managerId]->roleId ) );
	}
	$notes		= count( $bug->notes );
	$changes	= count( $bug->changes );
	$changes	= ( $notes || $changes ) ? ' mit '.$changes.' Veränderung(en) und '.$notes.' Notiz(en)' : '';
	$link		= UI_HTML_Elements::Link( './labs/bug/edit/'.$bug->bugId, $bug->title, 'bug-title' );
	$type		= UI_HTML_Tag::create( 'span', $words['types'][$bug->type], array( 'class' => 'bug-type type-'.$bug->type ) );
	$severity	= UI_HTML_Tag::create( 'span', $words['severities'][$bug->severity], array( 'class' => 'bug-severity severity-'.$bug->severity ) );
	$priority	= UI_HTML_Tag::create( 'span', $words['priorities'][$bug->priority], array( 'class' => 'bug-priority priority-'.$bug->priority ) );
	$status		= UI_HTML_Tag::create( 'span', $words['states'][$bug->status], array( 'class' => 'bug-status status-'.$bug->status ) );
	$progress	= $bug->progress ? UI_HTML_Tag::create( 'span', $bug->progress.'%', array( 'class' => 'bug-progress progress-'.( floor( $bug->progress / 25 ) * 25 ) ) ) : "-"; 
	$createdAt	= date( 'd.m.Y H:i:s', $bug->createdAt );
	$modifiedAt	= $bug->modifiedAt ? date( 'd.m.Y H:i:s', $bug->modifiedAt ) : "-";
	$rows[]	= '
<tr>
	<td>'.$link.'<br/>'.$type.$changes.'</td>
	<td>'.$priority.'<br/>'.$severity.'</td>
	<td>'.$status.'<br/>'.$progress.'</td>
	<td>'.$reporter.'<br/>'.$manager.'</td>
	<td><small>'.$createdAt.'</small><br/><small>'.$modifiedAt.'</small></td>
</tr>';
}

return '
<fieldset id="bug-list">
	<legend>Fehler</legend>
	<table>
		<colgroup>
			<col width="53%"/>
			<col width="10%"/>
			<col width="12%"/>
			<col width="12%"/>
			<col width="13%"/>
		</colgroup>
		<tr>
			<th>Kurzbeschreibung / <br/>Veränderungen</th>
			<th>Typ / Schweregrad</th>
			<th>Zustand / Fortschritt</th>
			<th>Reporter / Manager</th>
			<th>gemeldet / bearbeitet</th>
		</tr>
		'.join( $rows ).'
	</table>
	<div class="buttonbar">
		'.UI_HTML_Elements::LinkButton( './labs/bug/add', 'neuer Eintrag', 'add' ).'
	</div>
</fieldset>
';
?>
