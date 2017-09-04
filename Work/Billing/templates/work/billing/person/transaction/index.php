<?php
$optYear	= array(
	''	=> '- alle -',
);
$optYear[date( "Y" )]	= date( "Y" );
$optYear[date( "Y" )-1]	= date( "Y" )-1;
$optYear[date( "Y" )-2]	= date( "Y" )-2;
$optYear	= UI_HTML_Elements::Options( $optYear, $filterYear );

$optMonth	= array(
	''		=> '- alle -',
	'01'	=> 'Januar',
	'02'	=> 'Februar',
	'03'	=> 'März',
	'04'	=> 'April',
	'05'	=> 'Mai',
	'06'	=> 'Juni',
	'07'	=> 'Juli',
	'08'	=> 'August',
	'09'	=> 'September',
	'10'	=> 'Oktober',
	'11'	=> 'November',
	'12'	=> 'Dezember',
);
$optMonth	= UI_HTML_Elements::Options( $optMonth, $filterMonth );


$helper	= new View_Work_Billing_Helper_Transactions( $env );
$helper->setTransactions( $transactions );
$helper->setMode( View_Work_Billing_Helper_Transactions::MODE_PERSON );
$helper->setFilterUrl( './work/billing/person/transaction/filter/'.$person->personId );
$helper->setFilterPrefix( $filterSessionPrefix );
$panelTransactions	= $helper->render();

$tabs	= View_Work_Billing_Person::renderTabs( $env, $personId, 1 );

return '<h2 class="autocut"><span class="muted">Person</span> '.$person->firstname.' '.$person->surname.'</h2>
'.$tabs.'
'.$panelTransactions;
?>
