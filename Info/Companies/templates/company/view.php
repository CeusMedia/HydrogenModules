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

return '
[Company::View('.$companyId.')]
<hr/>
<div class="row-fluid">
	<div class="span6">
		<h4>Company of Branch</h4>
		'.print_m( $company, NULL, NULL, TRUE ).'
	</div>
	<div class="span6">
		'.$listBranches.'
	</div>
</div>
<hr/>
';
