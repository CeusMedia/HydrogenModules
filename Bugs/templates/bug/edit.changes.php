<?php

if( !$bug->notes )
	return;
//	print_m( $bug->notes );
//	die;
$list	= array();
foreach( $bug->notes as $note ){
	
	$noteChanges	= array();
	foreach( $note->changes as $change ){
		$labelType	= UI_HTML_Tag::create( 'dt', $words['changes'][$change->type] );
		switch( $change->type ){
			case 4:
				$from	= UI_HTML_Tag::create( 'span', $words['types'][$change->from], array( 'class' => 'bug-type type-'.$change->from ) );
				$to		= UI_HTML_Tag::create( 'span', $words['types'][$change->to], array( 'class' => 'bug-type type-'.$change->to ) );
				$change	= $from." -> ".$to;
				break;
			case 5:
				$from	= UI_HTML_Tag::create( 'span', $words['severities'][$change->from], array( 'class' => 'bug-severity severity-'.$change->from ) );
				$to		= UI_HTML_Tag::create( 'span', $words['severities'][$change->to], array( 'class' => 'bug-severity severity-'.$change->to ) );
				$change	= $from." -> ".$to;
				break;
			case 6:
				$from	= UI_HTML_Tag::create( 'span', $words['priorities'][$change->from], array( 'class' => 'bug-priority priority-'.$change->from ) );
				$to		= UI_HTML_Tag::create( 'span', $words['priorities'][$change->to], array( 'class' => 'bug-priority priority-'.$change->to ) );
				$change	= $from." -> ".$to;
				break;
			case 7:
				$from	= UI_HTML_Tag::create( 'span', $words['states'][$change->from], array( 'class' => 'bug-status status-'.$change->from ) );
				$to		= UI_HTML_Tag::create( 'span', $words['states'][$change->to], array( 'class' => 'bug-status status-'.$change->to ) );
				$change	= $from." -> ".$to;
				break;
			case 8:
				$from	= UI_HTML_Tag::create( 'span', $change->from.'%', array( 'class' => 'bug-progress progress-'.( floor( $change->from / 25 ) * 25 ) ) );
				$to		= UI_HTML_Tag::create( 'span', $change->to.'%', array( 'class' => 'bug-progress progress-'.( floor( $change->to / 25 ) * 25 ) ) );
				$change	= $from." -> ".$to;
				break;
			default:
				$change	= 'unbekannt';
		}
		
		$change	= UI_HTML_Tag::create( 'dd', $change );
		$noteChanges[]	= $labelType.$change;	
	}
//	$noteChanges	= UI_HTML_Tag::create( 'dl', join( $noteChanges ) );
	$content	= '
<style>
#bug-change-list-note {
	float: left;
	width: 58%;
	margin-right: 2%;
	}
#bug-change-list-changes {
	float: left;
	width: 38%;
	margin-right: 2%;
	}
#bug-change-list-changes dl dt {
	width: 100px;
	}
</style>

<div id="bug-change-list-changes">
	<dl>
		<dt>Bearbeiter</dt>
		<dd>
			<span class="role role'.$note->user->roleId.'">
				<a href="./user/edit/'.$note->user->userId.'">
					'.$note->user->username.'
				</a>
			</span>
		</dd>
		<dt>Zeitpunkt</dt>
		<dd>
			<span class="bug-note-date">'.date( 'd.m.Y H:i:s', $note->timestamp ).'</span>
		</dd>
		'.join( $noteChanges ).'
	</dl>
</div>
<div id="bug-change-list-note">
	<div class="bug-note-content">'.( $note->note ? $note->note : '<em><small>Kein Kommentar.</small></em>' ).'</div>
</div>
<hr style="clear: left"/>
';
	
	
	$item	= UI_HTML_Tag::create( 'li', $content, array( 'class' => 'bug-note' ) );
	$list[]	= $item;
}
$list	= $list ? UI_HTML_Tag::create( 'ul', join( $list ), array( 'class' => 'list' ) ) : '';
	
return '
<fieldset>
	<legend>Entwicklung</legend>
	'.$list.'
</fieldset>
';
?>