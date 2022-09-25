<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$words	= $env->getLanguage()->getWords( 'manage/catalog/provision/product/license' );
$w		= (object) $words['index'];

$iconsStatus = array(
	-1	=> HtmlTag::create( 'i', '', array( 'class' => 'icon-remove' ) ),
	0	=> HtmlTag::create( 'i', '', array( 'class' => 'icon-pencil' ) ),
	1	=> HtmlTag::create( 'i', '', array( 'class' => 'icon-eye-close' ) ),
	2	=> HtmlTag::create( 'i', '', array( 'class' => 'icon-ok' ) ),
);

$list	= '<div class="muted"><em>'.$w->noEntries.'</em></div><br/>';
if( $licenses ){
	$list	= [];
	foreach( $licenses as $license ){
		$class		= isset( $licenseId ) && $licenseId == $license->productLicenseId ? 'active' : NULL;
		$label		= $license->title.' <small class="muted">('.$license->count.')</small>';
		$link		= HtmlTag::create( 'a', $iconsStatus[$license->status].'&nbsp;'.$label, array(
			'href'	=> './manage/catalog/provision/product/license/edit/'.$license->productLicenseId,
		) );
		$list[]		= HtmlTag::create( 'li', $link, array( 'class' => $class ) );
	}
	$list	= HtmlTag::create( 'ul', $list, array( 'class' => "nav nav-pills nav-stacked" ) );
}

$iconAdd	= HtmlTag::create( 'i', '', array( 'class' => 'icon-plus icon-white' ) );
$buttonAdd	= HtmlTag::create( 'a', $iconAdd.'&nbsp;'.$w->buttonAdd, array(
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
