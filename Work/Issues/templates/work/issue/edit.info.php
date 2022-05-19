<?php

$changers	= [];
foreach( $issue->notes as $note )
	foreach( $note->changes as $change )
		if( $change->userId )
			if( !array_key_exists( $change->userId, $changers ) )
				$changers[$change->userId]	= $change->user;

foreach( $changers as $userId => $changer ){
	if( $changer ){
		$link	= UI_HTML_Tag::create( 'a', $changer->username, array( 'href' => './manage/user/edit/'.$userId ) );
		$roled	= UI_HTML_Tag::create( 'span', $link, array( 'class' => 'role role'.$changer->roleId ) );
		$changers[$userId]	= UI_HTML_Tag::create( 'li', $roled );
	}
}
$changers	= $changers ? UI_HTML_Tag::create( 'ul', join( $changers ), array( 'class' => 'list' ) ) : "-";

$reporter	= '-';
if( $issue->reporter ){
	$reporter	= UI_HTML_Elements::Link( './manage/user/edit/'.$issue->reporter->userId, $issue->reporter->username );
	$reporter	= UI_HTML_Tag::create( 'span', $reporter, array( 'class' => 'role role'.$issue->reporter->roleId ) );
}

$manager	= '-';
if( $issue->managerId ){
	$manager	= UI_HTML_Elements::Link( './maange/user/edit/'.$issue->manager->userId, $issue->manager->username );
	$manager	= UI_HTML_Tag::create( 'span', $manager, array( 'class' => 'role role'.$issue->manager->roleId ) );
}

if( empty( $issue->project ) ){
	$issue->project	= (object) array(
		'status'	=> '',
		'title'		=> '<em><small class="muted">unbekannt</small></em>',
	);
}

return '
<div class="content-panel">
	<h3>Informationen</h3>
	<div class="content-panel-inner">
		<dl class="not-info not-list facts-vertical">
			<dt>'.$words['edit']['labelId'].'</dt>
			<dd><big>#'.$issue->issueId.'</big></dd>
			<dt>'.$words['edit']['labelProject'].'</dt>
			<dd><span class="project status'.$issue->project->status.'">'.$issue->project->title.'</span></dd>
			<dt>'.$words['edit']['labelType'].'</dt>
			<dd><span class="issue-type type-'.$issue->type.'">'.$words['types'][$issue->type].'</span></dd>
			<dt>'.$words['edit']['labelSeverity'].'</dt>
			<dd><span class="issue-severity severity-'.$issue->severity.'">'.$words['severities'][$issue->severity].'</span></dd>
			<dt>'.$words['edit']['labelStatus'].'</dt>
			<dd><span class="issue-status status-'.$issue->status.'">'.$words['states'][$issue->status].'</span></dd>
			<dt>'.$words['edit']['labelProgress'].'</dt>
			<dd><span class="issue-progress progress-'.( floor( $issue->progress / 25 ) * 25 ).'">'.$issue->progress.' %</span></dd>
			<dt>'.$words['edit']['labelCode'].'</dt>
			<dd><code>[issue:'.$issue->issueId.']</code></dd>
			<dt>'.$words['edit']['labelReporter'].'</dt>
			<dd>'.$reporter.'</dd>
			<dt>'.$words['edit']['labelManager'].'</dt>
			<dd>'.$manager.'</dd>
			<dt>'.$words['edit']['labelChanger'].'</dt>
			<dd>'.$changers.'</dd>
		</dl>
	</div>
</div>';
?>
