<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

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

$iconBack		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );

$a->volume		= $category->volume ? $w->volume."&nbsp;".$category->volume : "";
$position		= $helper->renderPositionFromArticle( $article );

$panelDetails	= $this->loadTemplateFile( 'catalog/bookstore/article/details.php' );
$panelOrder		= $this->loadTemplateFile( 'catalog/bookstore/article/order.php' );
$panelRelations	= $this->loadTemplateFile( 'catalog/bookstore/article/relations.php' );

$linkBack		= '';
if( isset( $from ) && strlen( $from ) )
	$linkBack		= HtmlTag::create( 'a', $iconBack.' zurück', array(
		'href'	=> './'.$from,
		'class'	=> 'btn btn-small',
	) );

return '
<div class="article">
	<h2>'.$w->heading.'</h2>
	'.$position.'
	'.( $linkBack ? $linkBack.'<br/>' : '' ).'
	<br/>
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
