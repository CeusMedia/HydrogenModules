<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

if( !$bug->notes )
	return;
//	print_m( $bug->notes );
//	die;
$list	= [];
foreach( $bug->notes as $note ){
	
	$noteChanges	= [];
	foreach( $note->changes as $change ){
		$labelType	= HtmlTag::create( 'dt', $words['changes'][$change->type] );
		switch( $change->type ){
			case 4:
				$from	= HtmlTag::create( 'span', $words['types'][$change->from], ['class' => 'bug-type type-'.$change->from] );
				$to		= HtmlTag::create( 'span', $words['types'][$change->to], ['class' => 'bug-type type-'.$change->to] );
				$change	= $from." -> ".$to;
				break;
			case 5:
				$from	= HtmlTag::create( 'span', $words['severities'][$change->from], ['class' => 'bug-severity severity-'.$change->from] );
				$to		= HtmlTag::create( 'span', $words['severities'][$change->to], ['class' => 'bug-severity severity-'.$change->to] );
				$change	= $from." -> ".$to;
				break;
			case 6:
				$from	= HtmlTag::create( 'span', $words['priorities'][$change->from], ['class' => 'bug-priority priority-'.$change->from] );
				$to		= HtmlTag::create( 'span', $words['priorities'][$change->to], ['class' => 'bug-priority priority-'.$change->to] );
				$change	= $from." -> ".$to;
				break;
			case 7:
				$from	= HtmlTag::create( 'span', $words['states'][$change->from], ['class' => 'bug-status status-'.$change->from] );
				$to		= HtmlTag::create( 'span', $words['states'][$change->to], ['class' => 'bug-status status-'.$change->to] );
				$change	= $from." -> ".$to;
				break;
			case 8:
				$from	= HtmlTag::create( 'span', $change->from.'%', array( 'class' => 'bug-progress progress-'.( floor( $change->from / 25 ) * 25 ) ) );
				$to		= HtmlTag::create( 'span', $change->to.'%', array( 'class' => 'bug-progress progress-'.( floor( $change->to / 25 ) * 25 ) ) );
				$change	= $from." -> ".$to;
				break;
			default:
				$change	= 'unbekannt';
		}
		
		$change	= HtmlTag::create( 'dd', $change );
		$noteChanges[]	= $labelType.$change;	
	}

	$user	= '-';
	if( $note->userId ){
		$user	= HtmlElements::Link( './user/edit/'.$note->userId, $note->user->username );
		$user	= HtmlTag::create( 'span', $user, ['class' => 'role role'.$note->user->roleId] );
	}

//	$noteChanges	= HtmlTag::create( 'dl', join( $noteChanges ) );
	$content	= '
<div id="bug-change-list-changes">
	<dl class="clearfloat">
		<dt>Bearbeiter</dt>
		<dd>'.$user.'</dd>
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
<div style="clear: left"></div>
';
	
	
	$item	= HtmlTag::create( 'li', $content, ['class' => 'bug-note'] );
	$list[]	= $item;
}
$list	= $list ? HtmlTag::create( 'ul', join( $list ), ['class' => 'list'] ) : '';
	
return '
<fieldset>
	<legend>Entwicklung</legend>
	'.$list.'
</fieldset>
';
?>