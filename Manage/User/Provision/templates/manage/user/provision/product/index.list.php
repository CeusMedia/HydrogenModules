<?php

$iconsStatus = array(
	-1	=> UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-remove' ) ),
	0	=> UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-pencil' ) ),
	1	=> UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-eye-close' ) ),
	2	=> UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-ok' ) ),
);

$list	= '<div class="muted"><em>Keine vorhanden.</em></div><br/>';
if( $licenses ){
	$list	= [];
	foreach( $licenses as $license ){
		$class		= isset( $licenseId ) && $licenseId == $license->productLicenseId ? 'active' : NULL;
		$label		= $license->license->title.' <small class="muted">()</small>';
		$link		= UI_HTML_Tag::create( 'a', $iconsStatus[$license->status].'&nbsp;'.$label, array(
//			'href'			=> '#modal-license-edit-'.$license->productLicenseId,
			'href'			=> './manage/catalog/provision/license/edit/'.$license->product->productId.'/'.$license->userLicenseId,
			'role'			=> 'button',
			'data-toggle'	=> 'modal',
		) );
		$class		= NULL;
		$list[]		= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $link ),
			UI_HTML_Tag::create( 'td', '-' ),
			UI_HTML_Tag::create( 'td', '--' ),
		), array( 'class' => $class ) );
	}
	$thead	= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array( 'a', 'b', 'c' ) ) );
	$tbody	= UI_HTML_Tag::create( 'tbody', $list );
	$list	= UI_HTML_Tag::create( 'table', $thead.$tbody, array( 'class' => "table table-striped" ) );
}

$iconAdd	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-plus icon-white' ) );
$buttonAdd	= UI_HTML_Tag::create( 'a', $iconAdd.'&nbsp;hinzufügen', array(
//	'href'	=> '#modal-license-add',
	'href'	=> './manage/catalog/provision/license/add/'.(int) $filterProductId.'/'.(int) $filterProductLicenseId,
	'role'	=> 'button',
	'class'	=> 'btn btn-success',
	'data-toggle'	=> 'modal',
) );

//$panelAdd	= $view->loadTemplateFile( 'manage/catalog/provision/product/license/add.php' );

//$pagination	= new \CeusMedia\Bootstrap\PageControl( './manage/license' );
//$pagination	= $pagination->render();
$pagination	= '';

return '
<div class="content-panel">
	<h3>Benutzer-Lizenzen</h3>
	<div class="content-panel-inner">
		<div class="row-fluid">
			<div class="span12">
				'.$list.'
				<div class="buttonbar">
					'.$pagination.'
					'.$buttonAdd.'
				</div>
			</div>
		</div>
	</div>
</div>
';
