<?php
/**
 *	Template for Univerlag Frontend.
 *	@package		Univerlag.templates.article
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 *	@since			20.11.2005
 *	@version		3.0
 */

$a			= clone( $article );
$w			= (object) $words['article'];
$w->isn		= $a->series ? $w->issn : $w->isbn;
$helper		= new View_Helper_Catalog_Bookstore( $env );

$a->volume		= $category->volume ? $w->volume."&nbsp;".$category->volume : "";
$position		= $helper->renderPositionFromArticle( $article );

$panelDetails	= $this->loadTemplateFile( 'catalog/bookstore/article/details.php' );
$panelOrder		= $this->loadTemplateFile( 'catalog/bookstore/article/order.php' );
$panelRelations	= $this->loadTemplateFile( 'catalog/bookstore/article/relations.php' );

return '
<div class="article">
	<h2>'.$w->heading.'</h2>
	'.$position.'<br/>
	<div class="volume">'.$a->volume.'</div>
	<h4>'.$a->title.'</h4>
	<div class="subtitle">'.$a->subtitle.'</div>
	<br/>
	'.$panelDetails.'
	'.$panelOrder.'
	<br/>
	'.$panelRelations.'
</div>';


?>
