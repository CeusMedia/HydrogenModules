<?php

$rows	= array();
foreach( $issues as $issue ){
	$reporter	= '';
	$manager	= '';
	if( $issue->reporterId ){
		$link		= UI_HTML_Tag::create( 'a', $users[$issue->reporterId]->username, array( 'href' => './manage/user/edit/'.$issue->reporterId ) );
		$reporter	= UI_HTML_Tag::create( 'span', $link, array( 'class' => 'role role'.$users[$issue->reporterId]->roleId ) );
	}
	if( $issue->managerId ){
		$link		= UI_HTML_Tag::create( 'a', $users[$issue->managerId]->username, array( 'href' => './manage/user/edit/'.$issue->managerId ) );
		$manager	= UI_HTML_Tag::create( 'span', $link, array( 'class' => 'role role'.$users[$issue->managerId]->roleId ) );
	}
	$notes		= count( $issue->notes );
	$changes	= count( $issue->changes );
	$changes	= ( $notes || $changes ) ? ' mit '.$changes.' Veränderung(en) und '.$notes.' Notiz(en)' : '';
	$link		= UI_HTML_Elements::Link( './work/issue/edit/'.$issue->issueId, $issue->title, 'issue-title' );
	$type		= UI_HTML_Tag::create( 'span', $words['types'][$issue->type], array( 'class' => 'issue-type type-'.$issue->type ) );
	$severity	= UI_HTML_Tag::create( 'span', $words['severities'][$issue->severity], array( 'class' => 'issue-severity severity-'.$issue->severity ) );
	$priority	= UI_HTML_Tag::create( 'span', $words['priorities'][$issue->priority], array( 'class' => 'issue-priority priority-'.$issue->priority ) );
	$status		= UI_HTML_Tag::create( 'span', $words['states'][$issue->status], array( 'class' => 'issue-status status-'.$issue->status ) );
	$progress	= $issue->progress ? UI_HTML_Tag::create( 'span', $issue->progress.'%', array( 'class' => 'issue-progress progress-'.( floor( $issue->progress / 25 ) * 25 ) ) ) : "-"; 
	$createdAt	= date( 'd.m.Y H:i:s', $issue->createdAt );
	$modifiedAt	= $issue->modifiedAt ? date( 'd.m.Y H:i:s', $issue->modifiedAt ) : "-";
	$rows[]	= '
<tr>
	<td>'.$link.'<br/>'.$changes.'</td>
	<td>'.$type.'<br/>'.$priority.'</td>
	<td>'.$status.'<br/>'.$progress.'</td>
	<td>'.$reporter.'<br/>'.$manager.'</td>
	<td><small>'.$createdAt.'</small><br/><small>'.$modifiedAt.'</small></td>
</tr>';
}
$pagination	= new View_Helper_Pagination();
$pagination	= $pagination->render( './work/issue', $number, 10, $page );

return '
<fieldset id="issue-list">
	<legend>Einträge ('.$number.' von '.$total.')</legend>
	<table class="table table-condensed">
		<colgroup>
			<col width="47%"/>
			<col width="10%"/>
			<col width="12%"/>
			<col width="18%"/>
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
	'.$pagination.'
	<div class="buttonbar">
		<a class="btn btn-small btn-success" href="./work/issue/add"><i class="icon-plus icon-white"></i> neuer Eintrag</a>
	</div>
</fieldset>
';
?>
