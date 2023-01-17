<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconsStatus = array(
	-1	=> HtmlTag::create( 'i', '', ['class' => 'icon-remove'] ),
	0	=> HtmlTag::create( 'i', '', ['class' => 'icon-pencil'] ),
	1	=> HtmlTag::create( 'i', '', ['class' => 'icon-eye-close'] ),
	2	=> HtmlTag::create( 'i', '', ['class' => 'icon-ok'] ),
);

$list	= '<div class="muted"><em>Keine vorhanden.</em></div><br/>';
if( $licenses ){
	$list	= [];
	foreach( $licenses as $license ){
		$class		= isset( $licenseId ) && $licenseId == $license->productLicenseId ? 'active' : NULL;
		$label		= $license->license->title.' <small class="muted">()</small>';
		$link		= HtmlTag::create( 'a', $iconsStatus[$license->status].'&nbsp;'.$label, [
//			'href'			=> '#modal-license-edit-'.$license->productLicenseId,
			'href'			=> './manage/catalog/provision/license/edit/'.$license->product->productId.'/'.$license->userLicenseId,
			'role'			=> 'button',
			'data-toggle'	=> 'modal',
		] );
		$class		= NULL;
		$list[]		= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $link ),
			HtmlTag::create( 'td', '-' ),
			HtmlTag::create( 'td', '--' ),
		), ['class' => $class] );
	}
	$thead	= HtmlTag::create( 'thead', HtmlElements::TableHeads( ['a', 'b', 'c'] ) );
	$tbody	= HtmlTag::create( 'tbody', $list );
	$list	= HtmlTag::create( 'table', $thead.$tbody, ['class' => "table table-striped"] );
}

$iconAdd	= HtmlTag::create( 'i', '', ['class' => 'icon-plus icon-white'] );
$buttonAdd	= HtmlTag::create( 'a', $iconAdd.'&nbsp;hinzufügen', array(
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
