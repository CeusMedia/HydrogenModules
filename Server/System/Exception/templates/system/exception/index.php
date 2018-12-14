<?php

$words		= $env->getLanguage()->getWords( 'server/system/exception' );

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

$buttonTryAgain	= '';
if( isset( $exceptionUrl ) ){
	$buttonTryAgain	= UI_HTML_Tag::create( 'a', 'nochmal probieren', array(
		'href'	=> $exceptionUrl->get( TRUE ),
		'class'	=> 'btn',
	) );
}

extract( $view->populateTexts( array( 'top', 'bottom' ), 'html/system/exception' ) );

return '<h3>'.$words['index']['heading'].'</h3>
'.$textTop.'
<!--<p>'.$words['index']['heading'].'</p>-->
<div>
	'.$buttonTryAgain.'
</div>
<hr/>
<div id="exception-facts" style="display: none">
	<h4>Fehlermeldung</h4>
	'.$facts.'
	<h4>Aufrufstapel</h4>
	'.UI_HTML_Tag::create( 'kbd', nl2br( $exception->trace ), array( 'style' => 'font-size: 10px; letter-spacing: -0.25px; line-height: 12px;' ) ).'
</div>
<button type="button" id="exception-facts-trigger" onclick="showExceptionFacts();" class="btn btn-mini"><i class="fa fa-info-circle"></i> '.$words['index']['buttonMore'].'</button>
<script>
function showExceptionFacts(){
	jQuery("#exception-facts").show();
	jQuery("#exception-facts-trigger").hide();
}
</script>
<style>
#exception-facts dl dd {
	margin-left: 120px;
}
#exception-facts dl dt {
	width: 110px;
}
#exception-facts dl dd {
	margin-left: 120px;
}
#exception-facts dl .fact-file,
#exception-facts dl .fact-line {
	font-size: 0.85em;
}
s</style>'.$textBottom;
?>
