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

$helper	= new View_Helper_Map( $env );
$map	= $helper->build( $branch->latitude, $branch->longitude, $branch->title, 'border' );

return '
<h3>[Company_Branch::View('.$branchId.')]</h3>
<hr/>
<div class="row-fluid">
	<div class="span6">
		<h4>'.$w->headingBranch.'</h4>
		<dl class="dl-horizontal">
			<dt>'.$w->labelAddress.'</dt>
			<dd>'.$branch->street.' '.$branch->number.'<br/>'.$branch->postcode.' '.$branch->city.'</dd>
		</dl>
//		'.print_m( $branch, NULL, NULL, TRUE ).'
	</div>
	<div class="span6 branch">
		'.$map.'
	</div>
</div>
<div class="row-fluid">
	<div class="span6">
		<h4>'.$w->headingCompany.'</h4>
		'.print_m( $company, NULL, NULL, TRUE ).'
	</div>
</div>
<hr/>
'.$listBranches.'
<style>
.branch .UI_Map {
	height: 400px;
	}
</style>

';
