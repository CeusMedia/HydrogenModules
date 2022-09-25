<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

//print_m( $bridges );die;

$list	= [];
foreach( $bridges as $bridge ){
	$label	= $bridge->title ? $bridge->title : $bridge->class;
	$link	= HtmlTag::create( 'a', $label, array(
		'href'	=> './manage/shop/bridge/edit/'.$bridge->bridgeId,
	) );
	$class	= ( isset( $bridgeId) && $bridgeId === $bridge->bridgeId ) ? 'active' : NULL;
	$list[]	= HtmlTag::create( 'li', $link, array( 'class' => $class ) );
}
$table	= HtmlTag::create( 'ul', $list, array( 'class' => 'nav nav-pills nav-stacked' ) );

return '
<div class="content-panel">
	<h3>Shop Bridges</h3>
	<div class="content-panel-inner">
		'.$table.'
		<div class="buttonbar">
			<a href="./manage/shop/bridge/add" class="btn btn-small btn-success"><i class="icon-plus icon-white"></i> add</a>
		</div>
	</div>
</div>';
?>
