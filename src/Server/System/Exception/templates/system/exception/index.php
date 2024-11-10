<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

/** @var Environment $env */
/** @var View_System_Exception $view */

$iconMore	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-info-circle'] );
$iconReload	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-refresh'] );
$iconReset	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );

$words		= $env->getLanguage()->getWords( 'server/system/exception' );

$buttonTryAgain	= '';
if( !empty( $exceptionUrl ) ){
	$buttonTryAgain	= HtmlTag::create( 'a', $iconReload.' '.$words['index']['buttonRetry'], [
		'href'	=> $exceptionUrl->get( TRUE ),
		'class'	=> 'btn',
	] );
}

$buttonReset	= HtmlTag::create( 'a', $iconReset.' '.$words['index']['buttonReset'], [
	'href'	=> './system/exception/reset',
	'class'	=> 'btn',
] );

extract( $view->populateTexts( ['top', 'bottom'], 'html/system/exception' ) );

$panelFacts	= '';
if( !empty( $exception ) ){
	$facts	= [];
	foreach( $words['facts'] as $key => $label ){
		if( property_exists( $exception, $key ) ){
			if( 'code' === $key && 0 === (int) $exception->code )
				continue;
			if( 'trace' === $key )
				continue;
			if( 'file' === $key )
				$exception->file	= HtmlTag::create( 'small', $exception->file );
	//			$exception->trace	= HtmlTag::create( 'kbd', nl2br( $exception->trace ) );
			$facts[]	= HtmlTag::create( 'dt', $label, ['class' => 'fact-'.$key] );
			$facts[]	= HtmlTag::create( 'dd', $exception->{$key}, ['class' => 'fact-'.$key] );
		}
	}

	$buttonMore	= HtmlTag::create( 'button', $iconMore.' '.$words['index-facts']['buttonShow'], [
		'type'		=> 'button',
		'id'		=> 'exception-facts-trigger',
		'onclick'	=> 'showExceptionFacts();',
		'class'		=> 'btn btn-mini',
	] );
	$panelFacts		= '
	<hr/>
	<div id="exception-facts" style="display: none">
		<h4>'.$words['index-facts']['heading'].'</h4>
		'.HtmlTag::create( 'dl', $facts, ['class' => 'dl-horizontal'] ).'
		<h4>'.$words['index-facts']['trace'].'</h4>
		'.HtmlTag::create( 'kbd', nl2br( $exception->trace ), ['style' => 'font-size: 10px; letter-spacing: -0.25px; line-height: 12px;'] ).'
	</div>
	'.$buttonMore.'
	<script>
	function showExceptionFacts(){
		jQuery("#exception-facts").show();
		jQuery("#exception-facts-trigger").hide();
	}
	</script>';
}

return '<h3>'.$words['index']['heading'].'</h3>
'.$textTop.'
<div class="not-btn-group">
	'.$buttonTryAgain.'
	'.$buttonReset.'
</div>
'.$panelFacts.'
'.$textBottom;
