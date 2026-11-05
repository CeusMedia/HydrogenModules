<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w	= (object) $words['index'];

if( $companies ){
	$list   = [];
	foreach( $companies as $company ){
		$link		= HtmlTag::create( 'a', sprintf( $w->labelInCity, $company->title, $company->city ), ['href' => './company/'.$company->companyId] );
		$list[]		= HtmlTag::create( 'li', $link );
	}
	$list	= HtmlTag::create( 'ul', $list );
}

return '
[Company::Index]
'.$list;
