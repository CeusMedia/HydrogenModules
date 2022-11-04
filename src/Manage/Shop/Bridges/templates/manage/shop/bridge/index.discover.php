<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

if( !$discovered )
	return '';

$list	= [];
foreach( $discovered as $bridge ){
	$link	= HtmlTag::create( 'a', '<i class="icon-plus"></i> '.$bridge->title, array(
		'href'	=> './manage/shop/bridge/add?'.http_build_query( (array) $bridge ),
		'class'	=> 'btn btn-small',
	) );
	$list[]	= HtmlTag::create( 'li', $link );
}
$list	= HtmlTag::create( 'ul', $list, ['class' => 'unstyled not-nav not-nav-pills nav-stacked'] );

return '
<div class="content-panel">
	<h3>Discovered Bridges</h3>
	<div class="content-panel.inner">
		'.$list.'
	</div>
</div>';
?>
