<?php

$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );
$iconRun		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-play' ) );
$iconStop		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-stop' ) );
$iconRefresh	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-refresh' ) );

$list	= array();

$buttonRunDisabled	= UI_HTML_Tag::create( 'button', $iconRun.'&nbsp;starten&nbsp;', array(
	'type'	=> 'button',
	'class'	=> 'btn btn-mini btn-success disabled',
) );
$buttonStopDisabled	= UI_HTML_Tag::create( 'a', $iconStop.'&nbsp;stoppen&nbsp;', array(
	'type'	=> 'button',
	'class'	=> 'btn btn-mini btn-warning disabled',
) );
$buttonCancelDisabled	= UI_HTML_Tag::create( 'a', $iconCancel.'&nbsp;abbrechen&nbsp;', array(
	'type'	=> 'button',
	'class'	=> 'btn btn-mini btn-danger disabled',
) );


foreach( $queues as $queue ){
//	print_m( $queue);die;
	$bar	= new \CeusMedia\Bootstrap\Progress();
	$bar->addBar(
		round( ( $queue->countLettersByStatus[1] + $queue->countLettersByStatus[2] ) / $queue->countLetters * 100, 1 ),
		\CeusMedia\Bootstrap\Progress::BAR_CLASS_SUCCESS
	);
	$bar->addBar(
		round( $queue->countLettersByStatus[0] / $queue->countLetters * 100, 1 ),
		\CeusMedia\Bootstrap\Progress::BAR_CLASS_WARNING
	);
	$bar->addBar(
		round( ( $queue->countLettersByStatus[-1] + $queue->countLettersByStatus[-2] + $queue->countLettersByStatus[-3] ) / $queue->countLetters * 100, 1 ),
		\CeusMedia\Bootstrap\Progress::BAR_CLASS_DANGER
	);

	$buttonRun	= UI_HTML_Tag::create( 'a', $iconRun.'&nbsp;starten&nbsp;', array(
		'href'	=> '#',
		'class'	=> 'btn btn-mini btn-success',
	) );
	$buttonStop	= UI_HTML_Tag::create( 'a', $iconStop.'&nbsp;stoppen&nbsp;', array(
		'href'	=> '#',
		'class'	=> 'btn btn-mini btn-warning',
	) );
	$buttonCancel	= UI_HTML_Tag::create( 'a', $iconCancel.'&nbsp;abbrechen&nbsp;', array(
		'href'	=> '#',
		'class'	=> 'btn btn-mini btn-danger',
	) );

	$buttons	= array( $buttonRunDisabled, $buttonStopDisabled, $buttonCancelDisabled );
	if( $queue->status == 0 )
		$buttons	= array( $buttonRun, $buttonStopDisabled, $buttonCancel );
	if( $queue->status == 1 )
		$buttons	= array( $buttonRunDisabled, $buttonStop, $buttonCancel );
	$buttons	= UI_HTML_Tag::create( 'div', $buttons, array( 'class' => 'btn-group' ) );

	$creator	= '-';
	if( $queue->creatorId && $queue->creator ){
		$creator	= UI_HTML_Tag::create( 'abbr', $queue->creator->username, array(
			'title'	=> $queue->creator->firstname.' '.$queue->creator->surname
		) );
	}

	$percent	= round( ( $queue->countLettersByStatus[1] + $queue->countLettersByStatus[2] ) / $queue->countLetters * 100, 1 ).'%';

	$list[]	= UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'td', $queue->newsletterQueueId, array() ),
		UI_HTML_Tag::create( 'td', $queue->countLetters, array() ),
		UI_HTML_Tag::create( 'td', $words->queueStates[$queue->status], array() ),
		UI_HTML_Tag::create( 'td', $percent, array() ),
		UI_HTML_Tag::create( 'td', $bar->render(), array() ),
		UI_HTML_Tag::create( 'td', $creator ),
		UI_HTML_Tag::create( 'td', View_Helper_TimePhraser::convertStatic( $env, $queue->createdAt, TRUE ) ),
//		UI_HTML_Tag::create( 'td', $buttons, array() ),
	) );
}
$columnGroup	= UI_HTML_Elements::columnGroup( array( '100px', '100px', '120px', '120px', '', '140px', '140px'/*, '260px'*/ ) );
$thead	= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array(
	'Queue',
	'Empfänger',
	'Zustand',
	'Fortschritt',
	'',
	'Ersteller',
	'erstellt vor',
//	'Fortschritt',
) ) );
$tbody	= UI_HTML_Tag::create( 'tbody', $list );
$list	= UI_HTML_Tag::create( 'table', $columnGroup.$thead.$tbody, array( 'class' => 'table table-striped tabled-fixed' ) );

$panelList	= '
<div class="content-panel">
	<h3>Sendevorgänge</h3>
	<div class="content-panel-inner">
		'.$list.'
		<div class="buttonbar">
			<a href="./work/newsletter/edit/'.$newsletterId.'" class="btn">'.$iconRefresh.'&nbsp;aktualisieren</a>
		</div>
	</div>
</div>';

return $panelList;

$labelLetterButtonSend		= '<i class="icon-envelope icon-white"></i> '.$words->edit->buttonQueueSend;
$labelLetterButtonRemove	= '<i class="icon-remove icon-white"></i> '.$words->edit->buttonQueueCancel;
$labelLetterButtonView		= '<i class="icon-eye-open"></i> '.$words->edit->buttonQueueView;
$table						= '<em><small class="muted">Keine offenen Vorgänge vorhanden.</small></em>';
if( $letterQueue ){
	$rows	= array();
	foreach( $letterQueue as $readerLetter ){
		if( $readerLetter->status != 0 )
			continue;
		$rowColor		= 'warning';
		$urlSend		= './work/newsletter/sendLetter/'.$readerLetter->newsletterReaderLetterId;
		$urlRemove		= './work/newsletter/dequeueLetter/'.$readerLetter->newsletterReaderLetterId;
		$urlView		= './work/newsletter/view/'.$readerLetter->newsletterReaderLetterId;
		$buttonSend		= '<a class="btn btn-small btn-success" href="'.$urlSend.'">'.$labelLetterButtonSend.'</a>';
		$buttonRemove	= '<a class="btn btn-small btn-danger" href="'.$urlRemove.'">'.$labelLetterButtonRemove.'</a>';
		$buttonView		= '<a class="btn btn-small" href="'.$urlView.'" target="_blank">'.$labelLetterButtonView.'</a>';
		$rows[]	= '<tr class="'.$rowColor.'"><td>'.implode( '</td><td>', array(
			$readerLetter->reader->firstname.' '.$readerLetter->reader->surname,
			$readerLetter->reader->email,
			$words->letterStates[$readerLetter->status],
			$buttonView.' '.$buttonSend.' '.$buttonRemove
		) ).'</td></tr>';
	}
	$columns	= UI_HTML_Elements::ColumnGroup( "35%", "25%", "15%", "25%" );
	$thead		= '<thead><tr><th>Empfänger</th><th>E-Mail-Adresse</th><th>Zustand</th><th>Aktion</th></tr></thead>';
	$tbody		= '<tbody>'.join( $rows ).'</tbody>';
	$table		= '<table class="table table-condensed">'.$columns.$thead.$tbody.'</table>';
}

return '
<h4>Offene Vorgänge</h4>
<p>Die folgende Liste zeigt die noch zu verschickenden Mails.</p>
'.$table.'
';
?>
