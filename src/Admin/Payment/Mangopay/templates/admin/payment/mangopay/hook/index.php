<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

//return print_m( $hooks, NULL, NULL, TRUE );

/*print_m( $hooks );
print_m( $eventTypes );
print_m( $hookedEventTypes );
die;*/


$statuses	= array(
	Model_Mangopay_Event::STATUS_RECEIVED	=> 'RECEIVED',
	Model_Mangopay_Event::STATUS_FAILED		=> 'FAILED',
	Model_Mangopay_Event::STATUS_HANDLED	=> 'HANDLED',
	Model_Mangopay_Event::STATUS_CLOSED		=> 'CLOSED',
);
$colors		= array(
	'ENABLED'	=> 'label-success',
	'DISABLED'	=> 'label-warning',
	'VALID'		=> 'label-info',
	'INVALID'	=> 'label-important',
);


$list	= [];
foreach( $eventTypes as $topic => $types ){
	$sublist	= [];
	foreach( $types as $type ){
		if( !array_key_exists( $type, $hookedEventTypes ) )
			continue;
		$hook	= $hookedEventTypes[$type];
		$labelType	= substr( $type, strlen( $topic ) );
		$labelType	= ucwords( strtolower( str_replace( '_', ' ', $labelType ) ) );
		$labelTag	= HtmlTag::create( 'small', $hook->Tag ? '('.$hook->Tag.')' : '', ['class' => 'muted'] );
		$link		= HtmlTag::create( 'a', $labelType.' '.$labelTag, ['href' => './admin/payment/mangopay/hook/view/'.$hook->Id] );

		$labelUrl	= HtmlTag::create( 'small', $hook->Url, ['class' => 'not-muted'] );
		$sublist[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $link ),
			HtmlTag::create( 'td', $labelUrl ),
			HtmlTag::create( 'td', HtmlTag::create( 'label', $hook->Status, ['class' => 'label '.$colors[$hook->Status]] ) ),
			HtmlTag::create( 'td', HtmlTag::create( 'label', $hook->Validity, ['class' => 'label '.$colors[$hook->Validity]] ) ),
		) );
	}
	if( $sublist ){
		$topic	= HtmlTag::create( 'h4', $topic );
		$list[]	= HtmlTag::create( 'tr', HtmlTag::create( 'td', $topic, ['colspan' => 4] ) );
		$list[]	= $sublist;
	}
}
$tbody	= HtmlTag::create( 'tbody', $list );
$colgroup	= HtmlElements::ColumnGroup( ['30%', '', '90', '80'] );
$list	= HtmlTag::create( 'table', $colgroup.$tbody, ['class' => 'table table-fixed table-condensed'] );

$tabs	= View_Admin_Payment_Mangopay::renderTabs( $env, 'hook' );

return $tabs.HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', 'Hooks' ),
	HtmlTag::create( 'div', array(
		$list,
		HtmlTag::create( 'div', array(
			HtmlTag::create( 'a', '<i class="fa fa-fw fa-plus"></i> neuer Hook', ['disabled' => 'disabled', 'href' => './admin/payment/mangopay/hook/add', 'class' => 'btn btn-success btn-small'] ),
			' ',
			HtmlTag::create( 'a', '<i class="fa fa-fw fa-cogs"></i> URL für Hooks setzen', ['href' => './admin/payment/mangopay/hook/apply', 'class' => 'btn btn-small'] ),

		), ['class' => 'buttonbar'] )
	), ['class' => 'content-panel-inner'] )
), ['class' => 'content-panel'] );
?>
