<?php
$rowColors	= array(
	-1	=> 'error',
	1	=> 'warning',
	2	=> 'success',
);
$labelLetterButtonSend		= '<i class="icon-envelope icon-white"></i> '.$words->edit->buttonQueueAgain;
$labelLetterButtonRemove	= '<i class="icon-remove icon-white"></i> '.$words->edit->buttonQueueRemove;
$labelLetterButtonView		= '<i class="icon-eye-open"></i> '.$words->edit->buttonQueueView;
$labelLetterButtonRetry		= '<i class="icon-refresh icon-white"></i> '.$words->edit->buttonQueueRetry;
$table						= '<em><small class="muted">Nichts versendet bisher.</small></em>';
if( $letterHistory ){
	$rows	= array();
	foreach( $letterHistory as $readerLetter ){
		if( $readerLetter->status == 0 )
			continue;
		$rowColor		= $rowColors[$readerLetter->status];
		$urlReader		= './work/newsletter/reader/edit/'.$readerLetter->newsletterReaderId;
		$urlSend		= './work/newsletter/sendLetter/'.$readerLetter->newsletterReaderLetterId;
		$urlRemove		= './work/newsletter/dequeueLetter/'.$readerLetter->newsletterReaderLetterId;
		$urlView		= $frontendUrl.'info/newsletter/view/'.$readerLetter->newsletterReaderLetterId.'?dry';
		$buttonSend		= '<button class="btn btn-small btn-primary" type="button" onclick="if(confirm(\''.$words->edit->buttonQueueAgainConfirm.'\'))document.location.href=\''.$urlSend.'\';">'.$labelLetterButtonSend.'</button>';
		$buttonRemove	= '<a class="btn btn-small btn-danger" href="'.$urlRemove.'">'.$labelLetterButtonRemove.'</a>';
		$buttonView		= '<a class="btn btn-small" href="'.$urlView.'" target="_blank">'.$labelLetterButtonView.'</a>';
		switch( $readerLetter->status ){
			case -1:
				$buttonSend		= '<button disabled="disabled" class="btn btn-small btn-primary" type="button" onclick="document.location.href=\'./work/newsletter/retryLetter/'.$readerLetter->newsletterReaderLetterId.'\';">'.$labelLetterButtonRetry.'</button>';
				break;
			case 1:
				$buttonRemove	= "";
				break;
			case 2:
				$buttonSend	= "";
				$buttonRemove	= "";
				break;
		}
		$rows[]	= '<tr class="'.$rowColor.'"><td>'.implode( '</td><td>', array(
			UI_HTML_Tag::create( 'a', $readerLetter->reader->firstname.' '.$readerLetter->reader->surname, array( 'href' => $urlReader ) ),
			$readerLetter->reader->email,
			$words->letterStates[$readerLetter->status],
			$buttonView.' '.$buttonSend.' '.$buttonRemove
		) ).'</td></tr>';
	}
	$columns	= UI_HTML_Elements::ColumnGroup( "25%", "30%", "20%", "25%" );
	$thead		= '<thead><tr><th>Empfänger</th><th>E-Mail-Adresse</th><th>Zustand</th><th>Aktion</th></tr></thead>';
	$tbody		= '<tbody>'.join( $rows ).'</tbody>';
	$table		= '<table class="table table-condensed">'.$columns.$thead.$tbody.'</table>';
}

return '
<div class="content-panel">
	<h3>Versendete E-Mails</h3>
	<div class="content-panel-inner">
		'.$table.'
	</div>
</div>';
?>
