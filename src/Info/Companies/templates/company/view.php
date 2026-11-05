<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w	= (object) $words['view'];

$listBranches	="";
if( $branches ){
	$list	= [];
	foreach( $branches as $entry){
		$label	= sprintf( $w->labelInCity, $entry->title, $entry->city );
		$link	= HtmlTag::create( 'a', $label, ['href' => './company/branch/'.$entry->branchId, 'class' => ''] );
		$list[]	= HtmlTag::create( 'li', $link, ['class' => ''] );
	}
	$listBranches	= '
		<h4>'.$w->headingBranches.'</h4>
		'.HtmlTag::create( 'ul', $list, ['class' => ''] ).'
	';
}

return '
[Company::View('.$companyId.')]
<hr/>
<div class="row-fluid">
	<div class="span6">
		<h4>'.$w->headingCompany.'</h4>
		'.print_m( $company, NULL, NULL, TRUE ).'
	</div>
	<div class="span6">
		'.$listBranches.'
	</div>
</div>
<hr/>
';
