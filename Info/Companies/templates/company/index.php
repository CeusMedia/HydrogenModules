<?php

if( $companies ){
	$list   = array();
	foreach( $companies as $company ){
		$link		= UI_HTML_Tag::create( 'a', $company->title.' in '.$company->city, array( 'href' => './company/'.$company->companyId ) );
		$list[]		= UI_HTML_Tag::create( 'li', $link );
	}
	$list	= UI_HTML_Tag::create( 'ul', $list );
}

return '
[Company::Index]
'.$list;
