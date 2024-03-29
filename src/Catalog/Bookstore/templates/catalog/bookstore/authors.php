<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var Environment $env */
/** @var object[] $authors */

$helper	= new View_Helper_Catalog_Bookstore( $env );

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
