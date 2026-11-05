<?php

use CeusMedia\Bootstrap\Progress as ProgressBar;

/** @var int $total */
/** @var int $open */
/** @var int $negative */
/** @var int $positive */
/** @var array<string,array<string,string>> $words */

$w	= (object) $words['status'];

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
	<h4>'.$w->headTested.'</h4>
	'.$bar1.'
	<h4>'.$w->headResult.'</h4>
	'.$bar2.'
	<h4>'.$w->headQuality.' <small class="muted">'.$w->headQuality_suffix.'</small></h4>
	'.$bar3.'
	<dl class="dl-horizontal">
		<dt>'.$w->labelOpen.'</dt>
		<dd>'.$open.'</dd>
		<dt>'.$w->labelTotal.'</dt>
		<dd>'.$total.'</dd>
		<dt>'.$w->labelPositive.'</dt>
		<dd>'.$positive.'</dd>
		<dt>'.$w->labelNegative.'</dt>
		<dd>'.$negative.'</dd>
		<dt>'.$w->labelQuality.'</dt>
		<dd>'.( $positive + $negative ? round( $positive / ( $positive + $negative ) * 100, 1 ).'%' : '-' ).'</dd>
	</dl>
	<a href="./work/mail/check" class="btn btn-small"><i class="fa fa-fw fa-arrow-left"></i>&nbsp;'.$w->buttonBack.'</a>
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
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		'.$content.'
	</div>
</div>';
