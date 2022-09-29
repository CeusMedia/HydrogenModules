<?php

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */
/** @var object[] $payins */

$colors		= array(
	Model_Mangopay_Payin::STATUS_CREATED	=> 'label-info',
	Model_Mangopay_Payin::STATUS_FAILED		=> 'label-important',
	Model_Mangopay_Payin::STATUS_SUCCEEDED	=> 'label-success',
);


$helperMoney	= new View_Helper_Mangopay_Entity_Money( $env );
$helperMoney->setFormat( View_Helper_Mangopay_Entity_Money::FORMAT_AMOUNT_SPACE_CURRENCY );
$helperMoney->setNumberFormat( View_Helper_Mangopay_Entity_Money::NUMBER_FORMAT_COMMA );

$list		= HtmlTag::create( 'div', 'Keine gefunden.', array( 'class' => 'alert alert-info' ) );

if( $payins ){
	$list	= [];
	foreach( $payins as $item ){
		$resource	= Model_Mangopay_Payin::getLatestResourceFromPayinData( $item->data );
	//	print_m( $resource );die;
		$link		= HtmlTag::create( 'a', $item->payinId, array( 'href' => './admin/payment/mangopay/payin/view/'.$item->payinId ) );
		$status		= Model_Mangopay_Payin::getStatusLabel( $item->status );
		$status		= HtmlTag::create( 'label', $status, array( 'class' => 'label '.$colors[$item->status] ) );
		$fromUser	= HtmlTag::create( 'tt', $item->user->FirstName.' '.$item->user->LastName );
		$tags		= HtmlTag::create( 'div', array(
			HtmlTag::create( 'label', $resource->Nature, array( 'class' => 'label' ) ).' ',
			HtmlTag::create( 'label', $resource->ExecutionType, array( 'class' => 'label' ) ).' ',
			HtmlTag::create( 'label', Model_Mangopay_Payin::getTypeLabel( $item->type ), array( 'class' => 'label' ) ).' ',
		) );
		$list[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $link ),
			HtmlTag::create( 'td', $fromUser ),
			HtmlTag::create( 'td', $tags ),
			HtmlTag::create( 'td', $helperMoney->setAmount( $item->amount * 100 )->setCurrency( $item->currency ), array( 'style' => 'text-align: right' ) ),
			HtmlTag::create( 'td', $status ),
/*			HtmlTag::create( 'td', date( 'Y-m-d H:i:s', $item->createdAt ) ),*/
			HtmlTag::create( 'td', HtmlTag::create( 'small', date( 'Y-m-d H:i:s', $item->modifiedAt ) ) ),
		) );
	}
	$colgroup	= HtmlElements::ColumnGroup( array( '50', '', '', '100px', '100px', '140px' ) );
	$thead	= HtmlTag::create( 'thead', HtmlElements::TableHeads( array(
		'#',
		'Person',
		'Einordnung',
		'Betrag',
		'Zustand',
		'Datum',
	) ) );
	$tbody	= HtmlTag::create( 'tbody', $list );
	$list	= HtmlTag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-fixed table-condensed' ) );
}

$buttonbar	= '';
$pagination	= new \CeusMedia\Bootstrap\PageControl( 'admin/payment/mangopay/payin', $page, $pages );
if( $pages > 1 )
	$buttonbar	= HtmlTag::create( 'div', array(
		$pagination,
	), array( 'class' => 'buttonbar' ) );

$tabs	= View_Admin_Payment_Mangopay::renderTabs( $env, 'payin' );

return $tabs.HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', 'Payins' ),
	HtmlTag::create( 'div', array(
		$list,
		$buttonbar,
	), array( 'class' => 'content-panel-inner' ) )
), array( 'class' => 'content-panel' ) );
