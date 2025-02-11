<?php

use CeusMedia\Common\ADT\URL;
use CeusMedia\Common\UI\HTML\Exception\View as ExceptionView;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

/** @var Environment $env */
/** @var View_System_Exception $view */
/** @var Error|Throwable|Exception|object $exception */
/** @var URL|string|NULL $exceptionUrl */

$iconReload	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-refresh'] );
$iconReset	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );

$words		= $env->getLanguage()->getWords( 'server/system/exception' );
$viewWords	= (object) $words['index'];

$buttonTryAgain	= '';
if( !empty( $exceptionUrl ) ){
	$url	= $exceptionUrl instanceof URL ? $exceptionUrl->get() : $exceptionUrl;
	$buttonTryAgain	= HtmlTag::create( 'a', $iconReload.' '.$viewWords->buttonRetry, [
		'href'	=> $url,
		'class'	=> 'btn',
	] );
}

$buttonReset	= HtmlTag::create( 'a', $iconReset.' '.$viewWords->buttonReset, [
	'href'	=> './system/exception/reset',
	'class'	=> 'btn',
] );

[$textTop, $textBottom] = array_values( $view->populateTexts( ['top', 'bottom'], 'html/system/exception' ) );

$showFacts	= 1 && !empty( $exception ) && ( $env->isInDevMode() || $env->isInTestMode() );

$panelFacts	= '';
if( $showFacts && NULL !== $exception ){
	if( $exception instanceof Throwable )
		$panelFacts     = ExceptionView::render( $exception );
	else
		$panelFacts		= renderFacts( $words, $exception );
}

return '<h3>'.$viewWords->heading.'</h3>
'.$textTop.'
<div class="not-btn-group">
	'.$buttonTryAgain.'
	'.$buttonReset.'
</div>
'.$panelFacts.'
'.$textBottom;


function renderFacts( array $words, object $exception ): string
{
	$iconMore	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-info-circle'] );
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

	return '
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