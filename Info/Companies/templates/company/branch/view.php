<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$listBranches	="";
if( $branches ){
	$list	= [];
	foreach( $branches as $entry){
		$label	= $entry->title.' in '.$entry->city;
		$link	= HtmlTag::create( 'a', $label, array( 'href' => './company/branch/'.$entry->branchId, 'class' => '' ) );
		$list[]	= HtmlTag::create( 'li', $link, array( 'class' => '' ) );
	}
	$listBranches	= '
		<h4>Branches of Company</h4>
		'.HtmlTag::create( 'ul', $list, array( 'class' => '' ) ).'
	';
}

$helper	= new View_Helper_Map( $env );
$map	= $helper->build( $branch->latitude, $branch->longitude, $branch->title, 'border' );

return '
<h3>[Company_Branch::View('.$branchId.')]</h3>
<hr/>
<div class="row-fluid">
	<div class="span6">
		<h4>Branch</h4>
		<dl class="dl-horizontal">
			<dt>Anschrift</dt>
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
		<h4>Company of Branch</h4>
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
