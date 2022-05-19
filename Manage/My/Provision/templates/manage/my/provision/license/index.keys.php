<?php

if( !$userLicensesWithNotAssignedKeys )
	return '';

$list	= [];
foreach( $userLicensesWithNotAssignedKeys as $userLicense ){
	$link	= UI_HTML_Tag::create( 'a', $userLicense->productLicense->title, array(
		'href'	=> './manage/my/provision/license/view/'.$userLicense->userLicenseId
	) );
	$label	= $link.'<br/>Produkt: '.$userLicense->product->title;
	$list[]	= UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'td', $label ),
		UI_HTML_Tag::create( 'td', $userLicense->productLicense->users.' vorhanden<br/>'.count( $userLicense->keys ).' nicht vergeben' ),
	) );
}
$colgroup	= UI_HTML_Elements::ColumnGroup( array( "", "25%" ) );
$heads	= UI_HTML_Elements::TableHeads( array( 'Lizenz', 'Schlüssel' ) );
$thead	= UI_HTML_Tag::create( 'thead', $heads );
$tbody	= UI_HTML_Tag::create( 'tbody', $list );
$list	= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table' ) );

return	'
<div class="content-panel">
	<h3>Noch nicht vergebene Schlüssel</h3>
	<div class="content-panel-inner">
		<div class="row-fluid">
			<div class="span12">
				'.$list.'
			</div>
		</div>
	</div>
</div>';
