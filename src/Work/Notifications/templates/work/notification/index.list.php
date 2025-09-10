<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Bootstrap\Icon;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var Entity_Notification_Message[] $messages */
/** @var array<string,array<string,int|float|string>> $words */

$w	= (object) $words['index'];

$table	= HtmlTag::create( 'div', 'Keine', ['class' => 'alert alert-info'] );

$statusClasses	= [
	Model_Notification_Message::STATUS_ABORTED	=> 'error',
	Model_Notification_Message::STATUS_NEW		=> 'info',
	Model_Notification_Message::STATUS_ACTIVE	=> 'warning',
	Model_Notification_Message::STATUS_SEEN		=> 'success',
];

if( [] !== $messages ){
	$rows	= [];
	foreach( $messages as $message ){
		$recipients	= $message->nrRecipients;
		$seen		= $message->nrRecipientsSeen;
		$ratio		= round( ( $message->nrRecipientsSeen / $message->nrRecipients ) * 100 ).'%';
		$link	= HtmlTag::create( 'a', $message->title, [
			'href'	=> './work/notification/edit/'.$message->notificationMessageId,
		 ] );
		$rows[]	= HtmlTag::create( 'tr', [
			HtmlTag::create( 'td', $link ),
			HtmlTag::create( 'td', $recipients ),
			HtmlTag::create( 'td', $seen ),
			HtmlTag::create( 'td', $ratio ),
			HtmlTag::create( 'td', $words['statuses'][(string) $message->status] ),
			HtmlTag::create( 'td', date( 'j.n.Y', strtotime( $message->dateStart ) ) ),
			HtmlTag::create( 'td', $message->dateEnd ? date( 'j.n.Y', strtotime( $message->dateEnd ) ) : '-' ),
		], ['class' => $statusClasses[$message->status]] );
	}
	$thead		= HtmlTag::create( 'thead', HtmlElements::TableHeads( [
		'Titel',
		'Empfänger',
		'Gelesen',
		'Leserate',
		'Zustand',
		'Start',
		'Ende',
	] ) );
	$tbody		= HtmlTag::create( 'tbody', $rows );
	$colgroup	= HtmlElements::ColumnGroup( ['', '10%', '10%', '10%', '10%', '10%', '10%'] );
	$table		= HtmlTag::create( 'table', [$colgroup, $thead, $tbody], ['class' => 'table table-bordered table-striped'] );
}

$iconAdd	= Icon::create( 'fa fa-plus' );

return HtmlTag::create( 'div', [
	HtmlTag::create( 'h3', $w->heading ),
	HtmlTag::create( 'div', [
		HtmlTag::create( 'div', [
			HtmlTag::create( 'div', [
				$table,
			], ['class' => 'span12'] )
		], ['class' => 'row-fluid'] ),
		HtmlTag::create( 'div', [
			HtmlTag::create( 'a', $iconAdd.'&nbsp;'.$w->buttonAdd, [
				'href'	=> './work/notification/add',
				'class' => 'btn btn-primary',
			] )
		], ['class' => 'buttonbar'] )
	], ['class' => 'content-panel-inner'] )
], ['class' => 'content-panel'] );

