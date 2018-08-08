<?php

$words		= $env->getLanguage()->getWords( 'server/system/exception' );

$facts	= array();
foreach( $words['facts'] as $key => $label ){
	if( property_exists( $exception, $key ) ){
		if( $key === "code" && (int) $exception->code === 0 )
			continue;
		if( $key === "trace" )
			$exception->trace	= UI_HTML_Tag::create( 'kbd', nl2br( $exception->trace ) );
		$facts[]	= UI_HTML_Tag::create( 'dt', $label );
		$facts[]	= UI_HTML_Tag::create( 'dd', $exception->{$key} );
	}
}
$facts	= UI_HTML_Tag::create( 'dl', $facts, array( 'class' => 'dl-horizontal' ) );

return '<h2>Ups!</h2>
<p>'.$words['index']['heading'].'</p>
<div id="exception-facts" style="display: none">
	'.$facts.'
</div>
<button type="button" id="exception-facts-trigger" onclick="showExceptionFacts();" class="btn btn-mini"><i class="fa fa-info-circle"></i> '.$words['exception']['buttonMore'].'</button>
<script>
function showExceptionFacts(){
	jQuery("#exception-facts").show();
	jQuery("#exception-facts-trigger").hide();
}
</script>';
?>
