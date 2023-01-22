<?php

use CeusMedia\Bootstrap\Progress as ProgressBar;

/** @var int $total */
/** @var int $open */
/** @var int $negative */
/** @var int $positive */

$percentTested	= round( ( $positive + $negative ) / $total * 100, 2 );
$percentQuality	= $positive + $negative ? round( $positive / ( $positive + $negative ) * 100, 1 ) : 0;

$percentPositive	= round( $positive / $total * 100, 1 );
$percentNegative	= round( $negative / $total * 100, 1 );


$bar1	= new ProgressBar();
$bar1->addBar( $percentTested, ProgressBar::BAR_CLASS_INFO, $percentTested.'%' );

$bar2	= new ProgressBar( ProgressBar::CLASS_DANGER );
$bar2->addBar( $percentPositive, ProgressBar::BAR_CLASS_SUCCESS, $percentPositive.'%' );
$bar2->addBar( $percentNegative, ProgressBar::BAR_CLASS_DANGER, $percentNegative.'%' );

$bar3	= new ProgressBar( ProgressBar::CLASS_DANGER );
$bar3->addBar( $percentQuality, ProgressBar::BAR_CLASS_SUCCESS, $percentQuality.'%' );

	$content	= '
<div>
	<h4>Getestet</h4>
	'.$bar1.'
	<h4>Ergebnis</h4>
	'.$bar2.'
	<h4>Qualität <small class="muted">(Verhältnis erreichbarer und nicht erreichbarer Adressen zu einander)</small></h4>
	'.$bar3.'
	<dl class="dl-horizontal">
		<dt>Noch zu testen</dt>
		<dd>'.$open.'</dd>
		<dt>von insgesamt</dt>
		<dd>'.$total.'</dd>
		<dt>davon erreichbar</dt>
		<dd>'.$positive.'</dd>
		<dt>und NICHT erreichbar</dt>
		<dd>'.$negative.'</dd>
		<dt>Qualität</dt>
		<dd>'.( $positive + $negative ? round( $positive / ( $positive + $negative ) * 100, 1 ).'%' : '-' ).'</dd>
	</dl>
	<a href="./work/mail/check" class="btn btn-small"><i class="fa fa-fw fa-arrow-left"></i>&nbsp;zurück</a>
</div>
<script>
$(document).ready(function(){
	window.setTimeout(function(){
		document.location.reload();
	}, 12000);
})
</script>';

return '
<div class="content-panel">
	<h3>Stats</h3>
	<div class="content-panel-inner">
		'.$content.'
	</div>
</div>';
