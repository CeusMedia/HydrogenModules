<?php

$words	= $env->getLanguage()->getWords( 'manage/catalog/provision/product/license' );
$w		= (object) $words['index'];

$iconsStatus = array(
	-1	=> UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-remove' ) ),
	0	=> UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-pencil' ) ),
	1	=> UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-eye-close' ) ),
	2	=> UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-ok' ) ),
);

$list	= '<div class="muted"><em>'.$w->noEntries.'</em></div><br/>';
if( $licenses ){
	$list	= array();
	foreach( $licenses as $license ){
		$class		= isset( $licenseId ) && $licenseId == $license->productLicenseId ? 'active' : NULL;
		$label		= $license->title.' <small class="muted">('.$license->count.')</small>';
		$link		= UI_HTML_Tag::create( 'a', $iconsStatus[$license->status].'&nbsp;'.$label, array(
			'href'	=> './manage/catalog/provision/product/license/edit/'.$license->productLicenseId,
		) );
		$list[]		= UI_HTML_Tag::create( 'li', $link, array( 'class' => $class ) );
	}
	$list	= UI_HTML_Tag::create( 'ul', $list, array( 'class' => "nav nav-pills nav-stacked" ) );
}

$iconAdd	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-plus icon-white' ) );
$buttonAdd	= UI_HTML_Tag::create( 'a', $iconAdd.'&nbsp;'.$w->buttonAdd, array(
	'href'	=> './manage/catalog/provision/product/license/add/'.$product->productId,
	'class'	=> 'btn btn-success',
) );

return '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<div class="row-fluid">
			<div class="span12">
				'.$list.'
				<div class="buttonbar">
					'.$buttonAdd.'
				</div>
			</div>
		</div>
	</div>
</div>
';
