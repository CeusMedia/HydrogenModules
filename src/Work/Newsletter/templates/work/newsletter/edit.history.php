<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

/** @var Environment $env */
/** @var View $view */
/** @var object $words */
/** @var bool $tabbedLinks */
/** @var array $letterHistory */
/** @var string $frontendUrl */
/** @var int $nrLetters */

$rowColors	= [
	-1	=> 'error',
	1	=> 'warning',
	2	=> 'success',
];

$iconView		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-eye'] );
$iconRemove		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] );
$iconRefresh	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-refresh'] );
$iconSend		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-envelope'] );

$labelLetterButtonRemove	= $iconRemove.' '.$words->edit->buttonQueueRemove;
$labelLetterButtonRetry		= $iconRefresh.' '.$words->edit->buttonQueueRetry;
$labelLetterButtonView		= $iconView.' '.$words->edit->buttonQueueView;
$labelLetterButtonSend		= $iconSend.' '.$words->edit->buttonQueueAgain;

if( $nrLetters > 50 && [] === $letterHistory ){
	return '<div class="alert alert-info"><em class="not-muted">Der Newsletter wurde an '.$nrLetters.' Empfänger verschickt. Die Liste ist zu groß, um hier angezeigt zu werden.</em></div>';
}
else if( [] !== $letterHistory ){
	$rows	= [];
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
			case Model_Newsletter_Reader_Letter::STATUS_FAILED:
//				$buttonSend		= '<button disabled="disabled" class="btn btn-small btn-primary" type="button" onclick="document.location.href=\'./work/newsletter/retryLetter/'.$readerLetter->newsletterReaderLetterId.'\';">'.$labelLetterButtonRetry.'</button>';
				break;
			case Model_Newsletter_Reader_Letter::STATUS_SENT:
				$buttonRemove	= '';
				break;
			case Model_Newsletter_Reader_Letter::STATUS_OPENED:
				$buttonSend		= '';
				$buttonRemove	= '';
				break;
		}
		$rows[]	= '<tr class="'.$rowColor.'"><td>'.implode( '</td><td>', [
			HtmlTag::create( 'a', $readerLetter->reader->firstname.' '.$readerLetter->reader->surname, ['href' => $urlReader] ),
			$readerLetter->reader->email,
			$words->letterStates[$readerLetter->status],
			$buttonView.' '.$buttonSend.' '.$buttonRemove
		] ).'</td></tr>';
	}
	$columns	= HtmlElements::ColumnGroup( "25%", "30%", "15%", "30%" );
	$thead		= '<thead><tr><th>Empfänger</th><th>E-Mail-Adresse</th><th>Zustand</th><th>Aktion</th></tr></thead>';
	$tbody		= '<tbody>'.join( $rows ).'</tbody>';
	$table		= '<table class="table table-condensed">'.$columns.$thead.$tbody.'</table>';
}
else
	$table		= '<em><small class="muted">Nichts versendet bisher.</small></em>';

return '
<div class="content-panel">
	<h3>Versendete E-Mails</h3>
	<div class="content-panel-inner">
		'.$table.'
	</div>
</div>';
