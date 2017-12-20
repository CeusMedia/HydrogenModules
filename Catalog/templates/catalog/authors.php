<?php
$helper	= new View_Helper_Catalog( $env );


$list	= array();
foreach( $authors as $author ){
	$link	= $helper->renderAuthorLink( $author );
	$list[]	= UI_HTML_Tag::create( 'li', $link );
}
$list	= UI_HTML_Tag::create( 'ul', $list );

return '
<h3>Autoren</h3>
'.$list.'
';
?>