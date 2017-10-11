<?php

$colors		= array(
	Model_Mangopay_Payin::STATUS_CREATED	=> 'label-info',
	Model_Mangopay_Payin::STATUS_FAILED		=> 'label-important',
	Model_Mangopay_Payin::STATUS_SUCCEEDED	=> 'label-success',
);


$helperMoney	= new View_Helper_Mangopay_Entity_Money( $env );
$helperMoney->setFormat( View_Helper_Mangopay_Entity_Money::FORMAT_AMOUNT_SPACE_CURRENCY );
$helperMoney->setNumberFormat( View_Helper_Mangopay_Entity_Money::NUMBER_FORMAT_COMMA );

$list		= UI_HTML_Tag::create( 'div', 'Keine gefunden.', array( 'class' => 'alert alert-info' ) );

if( $payins ){
	$list	= array();
	foreach( $payins as $item ){
		$resource	= Model_Mangopay_Payin::getLatestResourceFromPayinData( $item->data );
	//	print_m( $resource );die;
		$link		= UI_HTML_Tag::create( 'a', $item->payinId, array( 'href' => './mangopay/payin/view/'.$item->payinId ) );
		$status		= Model_Mangopay_Payin::getStatusLabel( $item->status );
		$status		= UI_HTML_Tag::create( 'label', $status, array( 'class' => 'label '.$colors[$item->status] ) );
		$fromUser	= UI_HTML_Tag::create( 'tt', $item->user->FirstName.' '.$item->user->LastName );
		$tags		= UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'label', $resource->Nature, array( 'class' => 'label' ) ).' ',
			UI_HTML_Tag::create( 'label', $resource->ExecutionType, array( 'class' => 'label' ) ).' ',
			UI_HTML_Tag::create( 'label', Model_Mangopay_Payin::getTypeLabel( $item->type ), array( 'class' => 'label' ) ).' ',
		) );
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $link ),
			UI_HTML_Tag::create( 'td', $fromUser ),
			UI_HTML_Tag::create( 'td', $tags ),
			UI_HTML_Tag::create( 'td', $helperMoney->setAmount( $item->amount )->setCurrency( $item->currency ), array( 'style' => 'text-align: right' ) ),
			UI_HTML_Tag::create( 'td', $status ),
/*			UI_HTML_Tag::create( 'td', date( 'Y-m-d H:i:s', $item->createdAt ) ),*/
			UI_HTML_Tag::create( 'td', UI_HTML_Tag::create( 'small', date( 'Y-m-d H:i:s', $item->modifiedAt ) ) ),
		) );
	}
	$colgroup	= UI_HTML_Elements::ColumnGroup( array( '50', '', '', '100px', '100px', '140px' ) );
	$thead	= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array(
		'#',
		'Person',
		'Einordnung',
		'Betrag',
		'Zustand',
		'Datum',
	) ) );
	$tbody	= UI_HTML_Tag::create( 'tbody', $list );
	$list	= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-fixed table-condensed' ) );
}

$buttonbar	= '';
$pagination	= new \CeusMedia\Bootstrap\PageControl( 'mangopay/payin', $page, $pages );
if( $pages > 1 )
	$buttonbar	= UI_HTML_Tag::create( 'div', array(
		$pagination,
	), array( 'class' => 'buttonbar' ) );

return UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'Payins' ),
	UI_HTML_Tag::create( 'div', array(
		$list,
		$buttonbar,
	), array( 'class' => 'content-panel-inner' ) )
), array( 'class' => 'content-panel' ) );
