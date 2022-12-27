<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$changers	= [];
foreach( $issue->notes as $note )
	foreach( $note->changes as $change )
		if( $change->userId )
			if( !array_key_exists( $change->userId, $changers ) )
				$changers[$change->userId]	= $change->user;

foreach( $changers as $userId => $changer ){
	if( $changer ){
		$link	= HtmlTag::create( 'a', $changer->username, ['href' => './manage/user/edit/'.$userId] );
		$roled	= HtmlTag::create( 'span', $link, ['class' => 'role role'.$changer->roleId] );
		$changers[$userId]	= HtmlTag::create( 'li', $roled );
	}
}
$changers	= $changers ? HtmlTag::create( 'ul', join( $changers ), ['class' => 'list'] ) : "-";

$reporter	= '-';
if( $issue->reporter ){
	$reporter	= HtmlElements::Link( './manage/user/edit/'.$issue->reporter->userId, $issue->reporter->username );
	$reporter	= HtmlTag::create( 'span', $reporter, ['class' => 'role role'.$issue->reporter->roleId] );
}

$manager	= '-';
if( $issue->managerId ){
	$manager	= HtmlElements::Link( './maange/user/edit/'.$issue->manager->userId, $issue->manager->username );
	$manager	= HtmlTag::create( 'span', $manager, ['class' => 'role role'.$issue->manager->roleId] );
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
