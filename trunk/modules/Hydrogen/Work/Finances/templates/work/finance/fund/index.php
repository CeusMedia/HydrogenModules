<?php
$w			= (object) $words['index'];

$urlView1	= $env->getConfig()->get( 'module.work_funds.urlView' );
$urlView2	= 'http://www.finanzen.net/suchergebnis.asp?frmAktiensucheTextfeld=';

$total		= 0;
$rows		= array();
foreach( $funds as $fund ){
	$total	= $total + $fund->price->price * $fund->pieces;
	$date	= $fund->price->timestamp ? date( 'd.m.Y H:i', $fund->price->timestamp ) : '-';
	$value	= $fund->price->price ? number_format( $fund->price->price * $fund->pieces, 2, ',', '.' ) : '-';
	$price	= $fund->price->price ? number_format( $fund->price->price, 2, ',', '.' ) : '-';
	

	$icon1	= UI_HTML_Tag::create( 'img',NULL, array( 'src' => './images/fondsweb.de.ico' ) );
	$link1	= UI_HTML_Tag::create( 'a', $icon1, array( 'href' => $urlView1.$fund->ISIN, 'class' => 'image', 'target' => '_blank' ) );

	$icon2	= UI_HTML_Tag::create( 'img',NULL, array( 'src' => './images/finanzen.net.ico' ) );
	$link2	= UI_HTML_Tag::create( 'a', $icon2, array( 'href' => $urlView2.$fund->ISIN, 'class' => 'image', 'target' => '_blank' ) );
	
	$label	= UI_HTML_Tag::create( 'a', $fund->title, array( 'href' => './work/finance/fund/edit/'.$fund->fundId ) );
	$row	= array(
		UI_HTML_Tag::create( 'td', $label ),
		UI_HTML_Tag::create( 'td', $fund->kag ),
		UI_HTML_Tag::create( 'td', $fund->ISIN.'&nbsp;'.$link1.'&nbsp;'.$link2 ),
		UI_HTML_Tag::create( 'td', $price.'&nbsp;'.$fund->currency, array( 'class' => 'currency' ) ),
		UI_HTML_Tag::create( 'td', $value.'&nbsp;'.$fund->currency, array( 'class' => 'currency' ) ),
		UI_HTML_Tag::create( 'td', $date ),
	);
	$rows[]	= UI_HTML_Tag::create( 'tr', $row );
}
$total		= number_format( $total, 2, ',', '.' );
$row	= array(
	UI_HTML_Tag::create( 'td', count( $funds ).' Fonts', array( 'colspan' => 4 ) ),
	UI_HTML_Tag::create( 'td', $total.'&nbsp;EUR', array( 'class' => 'currency' ) ),
	UI_HTML_Tag::create( 'td', '' ),
);
$rows[]		= UI_HTML_Tag::create( 'tr', $row, array( 'class' => 'total' ) );
$heads	= array(
	UI_HTML_Tag::create( 'th', $w->headTitle ),
	UI_HTML_Tag::create( 'th', $w->headKag ),
	UI_HTML_Tag::create( 'th', $w->headISIN ),
	UI_HTML_Tag::create( 'th', $w->headPrice, array( 'class' => 'currency' ) ),
	UI_HTML_Tag::create( 'th', $w->headValue, array( 'class' => 'currency' ) ),
	UI_HTML_Tag::create( 'th', $w->headTimestamp ),
);
$heads		= UI_HTML_Tag::create( 'tr', $heads );
$colgroup	= UI_HTML_Elements::ColumnGroup( '30%,20%,15%,10%,10%,15%' );
$table		= '<table class="list">'.$colgroup.$heads.join( $rows ).'</table>';
return '<style>
table tr.total td {
	font-weight: bold;
	border-top: 1px solid gray;
	}
table tr .currency {
	text-align: right;
	}
</style>
<fieldset>
	<legend>Fonds</legend>
'.$table.'
	<div class="buttonbar">
		'.UI_HTML_Elements::LinkButton( './work/finance/fund/add', $w->buttonAdd, 'button icon add' ).'
		'.UI_HTML_Elements::LinkButton( './work/finance/fund/requestPrices', $w->buttonUpdate, 'button icon reload refresh' ).'
	</div>
</fieldset>
';
?>