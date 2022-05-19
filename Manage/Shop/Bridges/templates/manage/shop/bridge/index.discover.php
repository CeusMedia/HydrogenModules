<?php

if( !$discovered )
	return '';

$list	= [];
foreach( $discovered as $bridge ){
	$link	= new UI_HTML_Tag( 'a', '<i class="icon-plus"></i> '.$bridge->title, array(
		'href'	=> './manage/shop/bridge/add?'.http_build_query( (array) $bridge ),
		'class'	=> 'btn btn-small',
	) );
	$list[]	= new UI_HTML_Tag( 'li', $link );
}
$list	= new UI_HTML_Tag( 'ul', $list, array( 'class' => 'unstyled not-nav not-nav-pills nav-stacked' ) );

return '
<div class="content-panel">
	<h3>Discovered Bridges</h3>
	<div class="content-panel.inner">
		'.$list.'
	</div>
</div>';
?>
