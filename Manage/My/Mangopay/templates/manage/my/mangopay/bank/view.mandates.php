<?php

$statuses	= array(
	'CREATED'		=> 'beantragt',
	'SUBMITTED'		=> 'erteilt',
	'ACTIVE'		=> 'bestätigt',
	'FAILED'		=> 'abgebrochen',
);

$iconAdd		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) );
$iconDownload	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-download' ) );

$countActive	= 0;

$list	= UI_HTML_Tag::create( 'div', 'Keine vorhanden.', array( 'class' => 'alert alert-info' ) );
if( $mandates ){
	$list	= [];
	foreach( $mandates as $mandate ){
		$status	= UI_HTML_Tag::create( 'span', $statuses[$mandate->Status], array( 'class' => 'label' ) );
		$buttonDocument	= UI_HTML_Tag::create( 'a', $iconDownload.' Dokument', array(
			'href'		=> $mandate->DocumentURL,
			'target'	=> '_blank',
			'class'		=> 'btn btn-small'
		) );
		$buttonRevoke	= UI_HTML_Tag::create( 'button', $iconRevoke.' entziehen', array(
			'type'		=> 'button',
			'disabled'	=> 'disabld',
			'class'		=> 'btn btn-danger btn-small',
		) );

		if( $mandate->Status === 'SUBMITTED' || $mandate->Status === 'ACTIVE' ){
			$countActive++;
			$buttonRevoke	= UI_HTML_Tag::create( 'a', $iconRevoke.' entziehen', array(
				'href'		=> './manage/my/mangopay/bank/mandate/revoke/'.$mandate->Id,
				'class'		=> 'btn btn-danger btn-small',
			) );
		}
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $status ),
			UI_HTML_Tag::create( 'td', date( 'Y-m-d H:i', $mandate->CreationDate ) ),
			UI_HTML_Tag::create( 'td', $buttonDocument.' '.$buttonRevoke ),
		) );
	}

	$thead	= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array( 'Zustand', 'existiert seit', 'Aktionen' ) ) );
	$tbody	= UI_HTML_Tag::create( 'tbody', $list );
	$list	= UI_HTML_Tag::create( 'table', $tbody, array( 'class' => 'tabe table-fixed' ) );
}

$buttonAdd	= UI_HTML_Tag::create( 'a', $iconAdd.' Mandate erstellen', array(
	'href'	=> './manage/my/mangopay/bank/mandate/'.$bankAccountId,
	'class'	=> 'btn btn-success',
) );
if( $countActive ){
	$buttonAdd	= UI_HTML_Tag::create( 'button', $iconAdd.' Mandate erstellen', array(
		'type'		=> 'button',
		'class'		=> 'btn btn-success',
		'disabled'	=> 'disabled',
	) );
}

return '
<div class="content-panel panel-mangopay-view" id="panel-mangopay-card-view">
	<h3>Lastschriftmandate</h3>
	<div class="content-panel-inner">
		'.$list.'
		<div class="buttonbar">
			'.$buttonAdd.'
		</div>
	</div>
</div>';
?>
