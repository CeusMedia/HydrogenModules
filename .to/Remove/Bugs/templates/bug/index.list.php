<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$rows	= [];
foreach( $bugs as $bug ){
	
	$reporter	= '';
	$manager	= '';
	if( $bug->reporterId ){
		$link		= HtmlTag::create( 'a', $users[$bug->reporterId]->username, ['href' => './user/edit/'.$bug->reporterId] );
		$reporter	= HtmlTag::create( 'span', $link, ['class' => 'role role'.$users[$bug->reporterId]->roleId] );
	}
	if( $bug->managerId ){
		$link		= HtmlTag::create( 'a', $users[$bug->managerId]->username, ['href' => './user/edit/'.$bug->managerId] );
		$manager	= HtmlTag::create( 'span', $link, ['class' => 'role role'.$users[$bug->managerId]->roleId] );
	}
	$notes		= count( $bug->notes );
	$changes	= count( $bug->changes );
	$changes	= ( $notes || $changes ) ? ' mit '.$changes.' Veränderung(en) und '.$notes.' Notiz(en)' : '';
	$link		= HtmlElements::Link( './bug/edit/'.$bug->bugId, $bug->title, 'bug-title' );
	$type		= HtmlTag::create( 'span', $words['types'][$bug->type], ['class' => 'bug-type type-'.$bug->type] );
	$severity	= HtmlTag::create( 'span', $words['severities'][$bug->severity], ['class' => 'bug-severity severity-'.$bug->severity] );
	$priority	= HtmlTag::create( 'span', $words['priorities'][$bug->priority], ['class' => 'bug-priority priority-'.$bug->priority] );
	$status		= HtmlTag::create( 'span', $words['states'][$bug->status], ['class' => 'bug-status status-'.$bug->status] );
	$progress	= $bug->progress ? HtmlTag::create( 'span', $bug->progress.'%', array( 'class' => 'bug-progress progress-'.( floor( $bug->progress / 25 ) * 25 ) ) ) : "-"; 
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
		'.HtmlElements::LinkButton( './bug/add', 'neuer Eintrag', 'button add' ).'
	</div>
</fieldset>
';
?>
