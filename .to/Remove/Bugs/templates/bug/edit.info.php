<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$modelBugChange	= new Model_Bug_Change( $this->env );
$changers	= [];
$changes	= $modelBugChange->getAllByIndex( 'bugId', $bug->bugId );
foreach( $changes as $change ){
	if( !array_key_exists( $change->userId, $changers ) )
		if( $change->userId )
			$changers[$change->userId]	= $users[$change->userId];
}

foreach( $changers as $userId => $changer ){
	$link	= HtmlTag::create( 'a', $changer->username, ['href' => './user/edit/'.$userId] );
	$roled	= HtmlTag::create( 'span', $link, ['class' => 'role role'.$changer->roleId] );
	$changers[$userId]	= HtmlTag::create( 'li', $roled );
}
$changers	= $changers ? HtmlTag::create( 'ul', join( $changers ), ['class' => 'list'] ) : "-";

$reporter	= '-';
if( $bug->reporterId ){
	$reporter	= HtmlElements::Link( './user/edit/'.$bug->reporter->userId, $bug->reporter->username );
	$reporter	= HtmlTag::create( 'span', $reporter, ['class' => 'role role'.$bug->reporter->roleId] );
}

$manager	= '-';
if( $bug->managerId ){
	$manager	= HtmlElements::Link( './user/edit/'.$bug->manager->userId, $bug->manager->username );
	$manager	= HtmlTag::create( 'span', $manager, ['class' => 'role role'.$bug->manager->roleId] );
}

return '
<fieldset>
	<legend>Bug: Info</legend>
	<dl class="info list">
		<dt>'.$words['edit']['labelType'].'</dt>
		<dd><span class="bug-type type-'.$bug->type.'">'.$words['types'][$bug->type].'</span></dd>
		<dt>'.$words['edit']['labelSeverity'].'</dt>
		<dd><span class="bug-severity severity-'.$bug->severity.'">'.$words['severities'][$bug->severity].'</span></dd>
		<dt>'.$words['edit']['labelStatus'].'</dt>
		<dd><span class="bug-status status-'.$bug->status.'">'.$words['states'][$bug->status].'</span></dd>
		<dt>'.$words['edit']['labelProgress'].'</dt>
		<dd><span class="bug-progress progress-'.( floor( $bug->progress / 25 ) * 25 ).'">'.$bug->progress.' %</span></dd>
		<dt>'.$words['edit']['labelReporter'].'</dt>
		<dd>'.$reporter.'</dd>
		<dt>'.$words['edit']['labelManager'].'</dt>
		<dd>'.$manager.'</dd>
		<dt>'.$words['edit']['labelChanger'].'</dt>
		<dd>'.$changers.'</dd>
	</dl>
</fieldset>

'
?>