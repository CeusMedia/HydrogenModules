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

$w			= (object) $words->edit_history;

$labelLetterButtonRemove	= $iconRemove.' '.$words->edit->buttonQueueRemove;
$labelLetterButtonRetry		= $iconRefresh.' '.$words->edit->buttonQueueRetry;
$labelLetterButtonView		= $iconView.' '.$words->edit->buttonQueueView;
$labelLetterButtonSend		= $iconSend.' '.$words->edit->buttonQueueAgain;

if( $nrLetters > 50 && [] === $letterHistory ){
	return '<div class="alert alert-info"><em class="not-muted">'.sprintf( $w->alertTooMany, $nrLetters ).'</em></div>';
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
		$buttonSend		= '<button class="btn btn-small btn-primary" type="button" onclick="if(confirm(\''.htmlspecialchars( addslashes( $words->edit->buttonQueueAgainConfirm ), ENT_QUOTES, 'UTF-8' ).'\'))document.location.href=\''.$urlSend.'\';">'.$labelLetterButtonSend.'</button>';
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
	$thead		= '<thead><tr><th>'.$w->columnReader.'</th><th>'.$w->columnEmail.'</th><th>'.$w->columnStatus.'</th><th>'.$w->columnAction.'</th></tr></thead>';
	$tbody		= '<tbody>'.join( $rows ).'</tbody>';
	$table		= '<table class="table table-condensed">'.$columns.$thead.$tbody.'</table>';
}
else
	$table		= '<em><small class="muted">'.$w->empty.'</small></em>';

return '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		'.$table.'
	</div>
</div>';
