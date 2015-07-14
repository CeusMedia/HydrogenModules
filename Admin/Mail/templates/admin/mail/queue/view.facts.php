<?php

$helper	= new View_Helper_TimePhraser( $env );
$list	= array();
foreach( $mail as $key => $value ){
	if( in_array( $key, array( "object", "subject" ) ) )
		continue;
	if( preg_match( "/At$/", $key ) ){
		if( $value ){
			$date	= date( 'Y-m-d H:i:s', $value );
			$phrase	= $helper->convert( $value, TRUE, 'vor ' );
			$value	= $phrase.'&nbsp;<small class="muted">('.$date.')</small>';
		}
		else
			$value	= "-";
	}
	$list[]	= UI_HTML_Tag::create( 'dt', $key );
	$list[]	= UI_HTML_Tag::create( 'dd', $value.'&nbsp;' );
}
$list	= UI_HTML_Tag::create( 'dl', $list, array( 'class' => 'dl-horizontal' ) );

return '
<div class="content-panel">
	<h4>Fakten</h4>
	<div class="content-panel-inner">
		'.$list.'
	</div>
</div>';
?>
