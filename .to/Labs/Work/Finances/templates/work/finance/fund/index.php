<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w			= (object) $words['index'];

$urlView1	= $env->getConfig()->get( 'module.work_funds.urlView' );
$urlView2	= 'http://www.finanzen.net/suchergebnis.asp?frmAktiensucheTextfeld=';

$total		= 0;
$rows		= [];
foreach( $funds as $fund ){
	$total	= $total + $fund->price->price * $fund->pieces;
	$date	= $fund->price->timestamp ? date( 'd.m.Y H:i', $fund->price->timestamp ) : '-';
	$value	= $fund->price->price ? number_format( $fund->price->price * $fund->pieces, 2, ',', '.' ) : '-';
	$price	= $fund->price->price ? number_format( $fund->price->price, 2, ',', '.' ) : '-';


	$icon1	= HtmlTag::create( 'img',NULL, ['src' => './images/fondsweb.de.ico'] );
	$link1	= HtmlTag::create( 'a', $icon1, ['href' => $urlView1.$fund->ISIN, 'class' => 'image', 'target' => '_blank'] );

	$icon2	= HtmlTag::create( 'img',NULL, ['src' => './images/finanzen.net.ico'] );
	$link2	= HtmlTag::create( 'a', $icon2, ['href' => $urlView2.$fund->ISIN, 'class' => 'image', 'target' => '_blank'] );

	$label	= HtmlTag::create( 'a', $fund->title, ['href' => './work/finance/fund/edit/'.$fund->fundId] );
	$row	= array(
		HtmlTag::create( 'td', $label ),
		HtmlTag::create( 'td', $fund->kag ),
		HtmlTag::create( 'td', $fund->ISIN.'&nbsp;'.$link1.'&nbsp;'.$link2 ),
		HtmlTag::create( 'td', $price.'&nbsp;'.$fund->currency, ['class' => 'currency'] ),
		HtmlTag::create( 'td', $value.'&nbsp;'.$fund->currency, ['class' => 'currency'] ),
		HtmlTag::create( 'td', $date ),
	);
	$rows[]	= HtmlTag::create( 'tr', $row );
}
$total		= number_format( $total, 2, ',', '.' );
$row	= array(
	HtmlTag::create( 'td', count( $funds ).' Fonts', ['colspan' => 4] ),
	HtmlTag::create( 'td', $total.'&nbsp;EUR', ['class' => 'currency'] ),
	HtmlTag::create( 'td', '' ),
);
$rows[]		= HtmlTag::create( 'tr', $row, ['class' => 'total'] );
$heads	= array(
	HtmlTag::create( 'th', $w->headTitle ),
	HtmlTag::create( 'th', $w->headKag ),
	HtmlTag::create( 'th', $w->headISIN ),
	HtmlTag::create( 'th', $w->headPrice, ['class' => 'currency'] ),
	HtmlTag::create( 'th', $w->headValue, ['class' => 'currency'] ),
	HtmlTag::create( 'th', $w->headTimestamp ),
);
$heads		= HtmlTag::create( 'tr', $heads );
$colgroup	= HtmlElements::ColumnGroup( '30%,20%,15%,10%,10%,15%' );
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
		'.HtmlElements::LinkButton( './work/finance/fund/add', $w->buttonAdd, 'button icon add' ).'
		'.HtmlElements::LinkButton( './work/finance/fund/requestPrices', $w->buttonUpdate, 'button icon reload refresh' ).'
	</div>
</fieldset>';
