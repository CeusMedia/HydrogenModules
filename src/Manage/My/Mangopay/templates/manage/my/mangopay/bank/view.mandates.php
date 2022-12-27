<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$statuses	= array(
	'CREATED'		=> 'beantragt',
	'SUBMITTED'		=> 'erteilt',
	'ACTIVE'		=> 'bestätigt',
	'FAILED'		=> 'abgebrochen',
);

$iconAdd		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-plus'] );
$iconDownload	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-download'] );

$countActive	= 0;

$list	= HtmlTag::create( 'div', 'Keine vorhanden.', ['class' => 'alert alert-info'] );
if( $mandates ){
	$list	= [];
	foreach( $mandates as $mandate ){
		$status	= HtmlTag::create( 'span', $statuses[$mandate->Status], ['class' => 'label'] );
		$buttonDocument	= HtmlTag::create( 'a', $iconDownload.' Dokument', array(
			'href'		=> $mandate->DocumentURL,
			'target'	=> '_blank',
			'class'		=> 'btn btn-small'
		) );
		$buttonRevoke	= HtmlTag::create( 'button', $iconRevoke.' entziehen', array(
			'type'		=> 'button',
			'disabled'	=> 'disabld',
			'class'		=> 'btn btn-danger btn-small',
		) );

		if( $mandate->Status === 'SUBMITTED' || $mandate->Status === 'ACTIVE' ){
			$countActive++;
			$buttonRevoke	= HtmlTag::create( 'a', $iconRevoke.' entziehen', array(
				'href'		=> './manage/my/mangopay/bank/mandate/revoke/'.$mandate->Id,
				'class'		=> 'btn btn-danger btn-small',
			) );
		}
		$list[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $status ),
			HtmlTag::create( 'td', date( 'Y-m-d H:i', $mandate->CreationDate ) ),
			HtmlTag::create( 'td', $buttonDocument.' '.$buttonRevoke ),
		) );
	}

	$thead	= HtmlTag::create( 'thead', HtmlElements::TableHeads( ['Zustand', 'existiert seit', 'Aktionen'] ) );
	$tbody	= HtmlTag::create( 'tbody', $list );
	$list	= HtmlTag::create( 'table', $tbody, ['class' => 'tabe table-fixed'] );
}

$buttonAdd	= HtmlTag::create( 'a', $iconAdd.' Mandate erstellen', array(
	'href'	=> './manage/my/mangopay/bank/mandate/'.$bankAccountId,
	'class'	=> 'btn btn-success',
) );
if( $countActive ){
	$buttonAdd	= HtmlTag::create( 'button', $iconAdd.' Mandate erstellen', array(
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
