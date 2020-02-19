<?php

$iconMore	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-info-circle' ) );
$iconReload	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-refresh' ) );
$iconReset	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );

$words		= $env->getLanguage()->getWords( 'server/system/exception' );

$buttonTryAgain	= '';
if( !empty( $exceptionUrl ) ){
	$buttonTryAgain	= UI_HTML_Tag::create( 'a', $iconReload.' '.$words['index']['buttonRetry'], array(
		'href'	=> $exceptionUrl->get( TRUE ),
		'class'	=> 'btn',
	) );
}

$buttonReset	= UI_HTML_Tag::create( 'a', $iconReset.' '.$words['index']['buttonReset'], array(
	'href'	=> './system/exception/reset',
	'class'	=> 'btn',
) );

extract( $view->populateTexts( array( 'top', 'bottom' ), 'html/system/exception' ) );

$showFacts	= 1 && !empty( $exception );

$panelFacts	= '';
if( $showFacts ){
	$facts	= array();
	foreach( $words['facts'] as $key => $label ){
		if( property_exists( $exception, $key ) ){
			if( $key === "code" && (int) $exception->code === 0 )
				continue;
			if( $key === "trace" )
				continue;
			if( $key === "file" )
				$exception->file	= UI_HTML_Tag::create( 'small', $exception->file );
	//			$exception->trace	= UI_HTML_Tag::create( 'kbd', nl2br( $exception->trace ) );
			$facts[]	= UI_HTML_Tag::create( 'dt', $label, array( 'class' => 'fact-'.$key ) );
			$facts[]	= UI_HTML_Tag::create( 'dd', $exception->{$key}, array( 'class' => 'fact-'.$key ) );
		}
	}
	$facts	= UI_HTML_Tag::create( 'dl', $facts, array( 'class' => 'dl-horizontal' ) );
	$buttonMore	= UI_HTML_Tag::create( 'button', $iconMore.' '.$words['index-facts']['buttonShow'], array(
		'type'		=> 'button',
		'id'		=> 'exception-facts-trigger',
		'onclick'	=> 'showExceptionFacts();',
		'class'		=> 'btn btn-mini',
	) );
	$panelFacts		= '
	<hr/>
	<div id="exception-facts" style="display: none">
		<h4>'.$words['index-facts']['heading'].'</h4>
		'.$facts.'
		<h4>'.$words['index-facts']['trace'].'</h4>
		'.UI_HTML_Tag::create( 'kbd', nl2br( $exception->trace ), array( 'style' => 'font-size: 10px; letter-spacing: -0.25px; line-height: 12px;' ) ).'
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
?>
