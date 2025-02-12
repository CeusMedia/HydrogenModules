<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Bootstrap\Progress;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

/** @var Environment $env */
/** @var View $view */
/** @var object $words */
/** @var array $queues */
/** @var string $newsletterId */

$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] );
$iconRun		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-play'] );
$iconStop		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-stop'] );
$iconRefresh	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-refresh'] );

$list	= [];

$buttonRunDisabled	= HtmlTag::create( 'button', $iconRun.'&nbsp;starten&nbsp;', [
	'type'	=> 'button',
	'class'	=> 'btn btn-mini btn-success disabled',
] );
$buttonStopDisabled	= HtmlTag::create( 'a', $iconStop.'&nbsp;stoppen&nbsp;', [
	'type'	=> 'button',
	'class'	=> 'btn btn-mini btn-warning disabled',
] );
$buttonCancelDisabled	= HtmlTag::create( 'a', $iconCancel.'&nbsp;abbrechen&nbsp;', [
	'type'	=> 'button',
	'class'	=> 'btn btn-mini btn-danger disabled',
] );

foreach( $queues as $queue ){
//	print_m( $queue);die;
	$bar	= new Progress();
	$bar->addBar(
		round( ( $queue->countLettersByStatus[1] + $queue->countLettersByStatus[2] ) / $queue->countLetters * 100, 1 ),
		Progress::BAR_CLASS_SUCCESS
	);
	$bar->addBar(
		round( $queue->countLettersByStatus[0] / $queue->countLetters * 100, 1 ),
		Progress::BAR_CLASS_WARNING
	);
	$bar->addBar(
		round( ( $queue->countLettersByStatus[-1] + $queue->countLettersByStatus[-2] + $queue->countLettersByStatus[-3] ) / $queue->countLetters * 100, 1 ),
		Progress::BAR_CLASS_DANGER
	);

	$buttonRun	= HtmlTag::create( 'a', $iconRun.'&nbsp;starten&nbsp;', [
		'href'	=> '#',
		'class'	=> 'btn btn-mini btn-success',
	] );
	$buttonStop	= HtmlTag::create( 'a', $iconStop.'&nbsp;stoppen&nbsp;', [
		'href'	=> '#',
		'class'	=> 'btn btn-mini btn-warning',
	] );
	$buttonCancel	= HtmlTag::create( 'a', $iconCancel.'&nbsp;abbrechen&nbsp;', [
		'href'	=> '#',
		'class'	=> 'btn btn-mini btn-danger',
	] );

	$buttons	= [$buttonRunDisabled, $buttonStopDisabled, $buttonCancelDisabled];
	if( $queue->status == 0 )
		$buttons	= [$buttonRun, $buttonStopDisabled, $buttonCancel];
	if( $queue->status == 1 )
		$buttons	= [$buttonRunDisabled, $buttonStop, $buttonCancel];
	$buttons	= HtmlTag::create( 'div', $buttons, ['class' => 'btn-group'] );

	$creator	= '-';
	if( $queue->creatorId && $queue->creator ){
		$creator	= HtmlTag::create( 'abbr', $queue->creator->username, [
			'title'	=> $queue->creator->firstname.' '.$queue->creator->surname
		] );
	}

	$percent	= round( ( $queue->countLettersByStatus[1] + $queue->countLettersByStatus[2] ) / $queue->countLetters * 100, 1 ).'%';

	$list[]	= HtmlTag::create( 'tr', [
		HtmlTag::create( 'td', $queue->newsletterQueueId, ['class' => ''] ),
		HtmlTag::create( 'td', $queue->countLetters, ['class' => ''] ),
		HtmlTag::create( 'td', $words->queueStates[$queue->status], ['class' => ''] ),
		HtmlTag::create( 'td', $percent, ['class' => ''] ),
		HtmlTag::create( 'td', $bar->render(), ['class' => ''] ),
		HtmlTag::create( 'td', $creator ),
		HtmlTag::create( 'td', View_Helper_TimePhraser::convertStatic( $env, $queue->createdAt, TRUE ) ),
//		HtmlTag::create( 'td', $buttons, [] ),
	] );
}
$columnGroup	= HtmlElements::columnGroup( ['100px', '100px', '120px', '120px', '', '140px', '140px'/*, '260px'*/] );
$thead	= HtmlTag::create( 'thead', HtmlElements::TableHeads( [
	'Queue',
	'Empfänger',
	'Zustand',
	'Fortschritt',
	'',
	'Ersteller',
	'erstellt vor',
//	'Fortschritt',
] ) );
$tbody	= HtmlTag::create( 'tbody', $list );
$list	= HtmlTag::create( 'table', $columnGroup.$thead.$tbody, ['class' => 'table table-striped tabled-fixed'] );

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

$iconSend	= HtmlTag::create( 'i', '', ['class' => 'icon-envelope icon-white'] );
$iconRemove	= HtmlTag::create( 'i', '', ['class' => 'icon-remove icon-white'] );
$iconView	= HtmlTag::create( 'i', '', ['class' => 'icon-eye-open'] );

$labelLetterButtonSend		= $iconSend.' '.$words->edit->buttonQueueSend;
$labelLetterButtonRemove	= $iconRemove.' '.$words->edit->buttonQueueCancel;
$labelLetterButtonView		= $iconView.' '.$words->edit->buttonQueueView;
$table						= '<em><small class="muted">Keine offenen Vorgänge vorhanden.</small></em>';
if( $letterQueue ){
	$rows		= [];
	$rowColor	= 'warning';
	foreach( $letterQueue as $readerLetter ){
		if( $readerLetter->status != 0 )
			continue;
		$urlSend		= './work/newsletter/sendLetter/'.$readerLetter->newsletterReaderLetterId;
		$urlRemove		= './work/newsletter/dequeueLetter/'.$readerLetter->newsletterReaderLetterId;
		$urlView		= './work/newsletter/view/'.$readerLetter->newsletterReaderLetterId;
		$buttonSend		= '<a class="btn btn-small btn-success" href="'.$urlSend.'">'.$labelLetterButtonSend.'</a>';
		$buttonRemove	= '<a class="btn btn-small btn-danger" href="'.$urlRemove.'">'.$labelLetterButtonRemove.'</a>';
		$buttonView		= '<a class="btn btn-small" href="'.$urlView.'" target="_blank">'.$labelLetterButtonView.'</a>';
		$rows[]	= '<tr class="'.$rowColor.'"><td>'.implode( '</td><td>', [
			$readerLetter->reader->firstname.' '.$readerLetter->reader->surname,
			$readerLetter->reader->email,
			$words->letterStates[$readerLetter->status],
			$buttonView.' '.$buttonSend.' '.$buttonRemove
		] ).'</td></tr>';
	}
	$columns	= HtmlElements::ColumnGroup( "35%", "25%", "15%", "25%" );
	$thead		= '<thead><tr><th>Empfänger</th><th>E-Mail-Adresse</th><th>Zustand</th><th>Aktion</th></tr></thead>';
	$tbody		= HtmlTag::create( 'tbody', $rows );
	$table		= '<table class="table table-condensed">'.$columns.$thead.$tbody.'</table>';
}

return '
<h4>Offene Vorgänge</h4>
<p>Die folgende Liste zeigt die noch zu verschickenden Mails.</p>'.$table;
