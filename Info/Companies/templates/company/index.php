<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

if( $companies ){
	$list   = [];
	foreach( $companies as $company ){
		$link		= HtmlTag::create( 'a', $company->title.' in '.$company->city, array( 'href' => './company/'.$company->companyId ) );
		$list[]		= HtmlTag::create( 'li', $link );
	}
	$list	= HtmlTag::create( 'ul', $list );
}

return '
[Company::Index]
'.$list;
