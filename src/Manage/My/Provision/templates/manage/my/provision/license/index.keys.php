<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w	= (object) $words['index.keys'];

if( !$userLicensesWithNotAssignedKeys )
	return '';

$list	= [];
foreach( $userLicensesWithNotAssignedKeys as $userLicense ){
	$link	= HtmlTag::create( 'a', $userLicense->productLicense->title, [
		'href'	=> './manage/my/provision/license/view/'.$userLicense->userLicenseId
	] );
	$label	= $link.'<br/>'.sprintf( $w->labelProduct, $userLicense->product->title );
	$list[]	= HtmlTag::create( 'tr', array(
		HtmlTag::create( 'td', $label ),
		HtmlTag::create( 'td', sprintf( $w->textKeysAvailable, $userLicense->productLicense->users ).'<br/>'.sprintf( $w->textKeysNotAssigned, count( $userLicense->keys ) ) ),
	) );
}
$colgroup	= HtmlElements::ColumnGroup( ["", "25%"] );
$heads	= HtmlElements::TableHeads( [$w->headLicense, $w->headKeys] );
$thead	= HtmlTag::create( 'thead', $heads );
$tbody	= HtmlTag::create( 'tbody', $list );
$list	= HtmlTag::create( 'table', $colgroup.$thead.$tbody, ['class' => 'table'] );

return	'
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<div class="row-fluid">
			<div class="span12">
				'.$list.'
			</div>
		</div>
	</div>
</div>';
