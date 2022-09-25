<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$helper	= new View_Helper_Catalog( $env );

$list	= [];
foreach( $authors as $author ){
	$link	= $helper->renderAuthorLink( $author );
	$list[]	= HtmlTag::create( 'li', $link );
}
$list	= HtmlTag::create( 'ul', $list );

return '
<h3>Autoren</h3>
'.$list.'
';
?>