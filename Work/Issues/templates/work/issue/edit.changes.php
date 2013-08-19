<?php

if( !$issue->notes )
	return;
//	print_m( $issue->notes );
//	die;
$list	= array();
foreach( $issue->notes as $note ){
	$noteChanges	= array();
	foreach( $note->changes as $change ){
		$labelType	= UI_HTML_Tag::create( 'dt', $words['changes'][$change->type] );
		switch( $change->type ){
			case 4:
				$from	= UI_HTML_Tag::create( 'span', $words['types'][$change->from], array( 'class' => 'issue-type type-'.$change->from ) );
				$to		= UI_HTML_Tag::create( 'span', $words['types'][$change->to], array( 'class' => 'issue-type type-'.$change->to ) );
				$change	= $from." -> ".$to;
				break;
			case 5:
				$from	= UI_HTML_Tag::create( 'span', $words['severities'][$change->from], array( 'class' => 'issue-severity severity-'.$change->from ) );
				$to		= UI_HTML_Tag::create( 'span', $words['severities'][$change->to], array( 'class' => 'issue-severity severity-'.$change->to ) );
				$change	= $from." -> ".$to;
				break;
			case 6:
				$from	= UI_HTML_Tag::create( 'span', $words['priorities'][$change->from], array( 'class' => 'issue-priority priority-'.$change->from ) );
				$to		= UI_HTML_Tag::create( 'span', $words['priorities'][$change->to], array( 'class' => 'issue-priority priority-'.$change->to ) );
				$change	= $from." -> ".$to;
				break;
			case 7:
				$from	= UI_HTML_Tag::create( 'span', $words['states'][$change->from], array( 'class' => 'issue-status status-'.$change->from ) );
				$to		= UI_HTML_Tag::create( 'span', $words['states'][$change->to], array( 'class' => 'issue-status status-'.$change->to ) );
				$change	= $from." -> ".$to;
				break;
			case 8:
				$from	= UI_HTML_Tag::create( 'span', $change->from.'%', array( 'class' => 'issue-progress progress-'.( floor( $change->from / 25 ) * 25 ) ) );
				$to		= UI_HTML_Tag::create( 'span', $change->to.'%', array( 'class' => 'issue-progress progress-'.( floor( $change->to / 25 ) * 25 ) ) );
				$change	= $from." -> ".$to;
				break;
			default:
				$change	= 'unbekannt';
		}

		$change	= UI_HTML_Tag::create( 'dd', $change );
		$noteChanges[]	= $labelType.$change;
	}
//	$noteChanges	= UI_HTML_Tag::create( 'dl', join( $noteChanges ) );
	$manager	= '-';
	if( $note->user ){
		$manager	= '<a href="./manage/user/edit/'.$note->user->userId.'">'.$note->user->username.'</a>';
		$manager	= '<span class="role role'.$note->user->roleId.'">'.$manager.'</span>';
	}

	$noteText	= nl2br( $note->note );
	if( $env->getModules()->has( 'UI_Helper_Content' ) )
		$noteText	= View_Helper_ContentConverter::render( $env, $note->note );

	$content	= '
<div id="issue-change-list-changes">
	<dl>
		<dt>Bearbeiter</dt>
		<dd>
			'.$manager.'
		</dd>
		<dt>Zeitpunkt</dt>
		<dd>
			<span class="issue-note-date">'.date( 'd.m.Y H:i:s', $note->timestamp ).'</span>
		</dd>
		'.join( $noteChanges ).'
	</dl>
</div>
<div id="issue-change-list-note">
	<div class="issue-note-content">'.( $note->note ? $noteText : '<em><small>Kein Kommentar.</small></em>' ).'<br/></div>
</div>
<div class="column-clear"></div>
<hr/>';

	$item	= UI_HTML_Tag::create( 'li', $content, array( 'class' => 'issue-note' ) );
	$list[]	= $item;
}
$list	= $list ? UI_HTML_Tag::create( 'ul', join( $list ), array( 'class' => '-list unstyled' ) ) : '';

return '
<style>
#issue-change-list-note {
	float: left;
	width: 58%;
	margin-right: 2%;
	}
#issue-change-list-changes {
	float: left;
	width: 38%;
	margin-right: 2%;
	}
#issue-change-list-changes dl dt {
	width: 100px;
	}
</style>
<fieldset>
	<legend>Entwicklung</legend>
	'.$list.'
</fieldset>
';
?>
