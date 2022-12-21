<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

if( !$userLicensesWithNotAssignedKeys )
	return '';

$list	= [];
foreach( $userLicensesWithNotAssignedKeys as $userLicense ){
	$link	= HtmlTag::create( 'a', $userLicense->productLicense->title, array(
		'href'	=> './manage/my/provision/license/view/'.$userLicense->userLicenseId
	) );
	$label	= $link.'<br/>Produkt: '.$userLicense->product->title;
	$list[]	= HtmlTag::create( 'tr', array(
		HtmlTag::create( 'td', $label ),
		HtmlTag::create( 'td', $userLicense->productLicense->users.' vorhanden<br/>'.count( $userLicense->keys ).' nicht vergeben' ),
	) );
}
$colgroup	= HtmlElements::ColumnGroup( ["", "25%"] );
$heads	= HtmlElements::TableHeads( ['Lizenz', 'Schlüssel'] );
$thead	= HtmlTag::create( 'thead', $heads );
$tbody	= HtmlTag::create( 'tbody', $list );
$list	= HtmlTag::create( 'table', $colgroup.$thead.$tbody, ['class' => 'table'] );

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
