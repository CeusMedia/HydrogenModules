<?php
try{
if( !$issue->notes )
	return;
$modelUser	= new Model_User( $env );

$list	= array();
foreach( $issue->notes as $note ){
	$noteChanges	= array();
	foreach( $note->changes as $change ){
		$labelType	= UI_HTML_Tag::create( 'dt', $words['changes'][$change->type] );
		switch( $change->type ){
			case 1:
			case 2:
				$from	= UI_HTML_Tag::create( 'small', 'unbekannt', array( 'class' => 'muted' ) );
				$to		= UI_HTML_Tag::create( 'small', 'unbekannt', array( 'class' => 'muted' ) );
				if( $change->from ){
					$from	= $modelUser->get( $change->from )->username;
					$from	= UI_HTML_Tag::create( 'a', $from, array( 'href' => './user/view/'.$change->from ) );
					$from	= UI_HTML_Tag::create( 'span', $from, array( 'class' => 'issue-user' ) );
				}
				if( $change->to ){
					$to		= $modelUser->get( $change->to )->username;
					$to		= UI_HTML_Tag::create( 'a', $to, array( 'href' => './user/view/'.$change->from ) );
					$to		= UI_HTML_Tag::create( 'span', $to, array( 'class' => 'issue-user' ) );
				}
				$change	= $from." -> ".$to;
				break;
			case 3:
				$logic	= new Logic_Project( $this->env );
				$from	= UI_HTML_Tag::create( 'small', 'unbekannt', array( 'class' => 'muted' ) );
				$to		= UI_HTML_Tag::create( 'small', 'unbekannt', array( 'class' => 'muted' ) );
				if( $change->from )
					$from	= UI_HTML_Tag::create( 'span', $logic->get( $change->from )->title, array( 'class' => '' ) );
				if( $change->to )
					$to		= UI_HTML_Tag::create( 'span', $logic->get( $change->to )->title, array( 'class' => '' ) );
				$change	= $from." -> ".$to;
				break;
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

	$noteText	= '<em><small class="muted">Kein Kommentar.</small></em>';
	if( trim( $note->note ) ){
		if( $env->getModules()->has( 'UI_Markdown' ) )
			$noteText	= View_Helper_Markdown::transformStatic( $env, $note->note );
		else if( $env->getModules()->has( 'UI_Helper_Content' ) )
			$noteText	= View_Helper_ContentConverter::render( $env, $note->note );
		else
			$noteText	= nl2br( $note->note );
	}
	$facts	= UI_HTML_Tag::create( 'dl', '
		<dt>Bearbeiter</dt>
		<dd>
			'.$manager.'
		</dd>
		<dt>Zeitpunkt</dt>
		<dd>
			<span class="issue-note-date">'.date( 'd.m.Y H:i:s', $note->timestamp ).'</span>
		</dd>
		'.join( $noteChanges )
	);
	$facts	= UI_HTML_Tag::create( 'div', $facts, array( 'class' => 'span6', 'id' => 'issue-change-list-facts' ) );
	$note	= UI_HTML_Tag::create( 'div', $noteText, array( 'class' => 'issue-change-list-note-content' ) );
	$note	= UI_HTML_Tag::create( 'div', $note, array( 'class' => 'span6', 'id' => 'issue-change-list-note' ) );
	$item	= UI_HTML_Tag::create( 'div', array( $facts, $note, '<br/>' ), array( 'class' => 'issue-note row-fluid' ) );
	$list[]	= UI_HTML_Tag::create( 'tr', UI_HTML_Tag::create( 'td', $item ) );
}
$list	= UI_HTML_Tag::create( 'table', $list, array( 'class' => 'table table-striped' ) );


return '
<style>
#issue-change-list-facts dl dt {
	width: 100px;
	}
</style>
<div class="content-panel">
	<h3>Entwicklung</h3>
	<div class="content-panel-inner">
		'.$list.'
	</div>
</div>';
}
catch( Exception $e ){
	UI_HTML_Exception_Page::display( $e);
}
?>
