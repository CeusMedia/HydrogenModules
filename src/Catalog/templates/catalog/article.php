<?php
/**
 *	Template for Univerlag Frontend.
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 */

use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var array $words */
/** @var object $article */
/** @var object $category */

$a			= clone( $article );
$w			= (object) $words['article'];
$w->isn		= $a->series ? $w->issn : $w->isbn;
$helper		= new View_Helper_Catalog( $env );

$a->title		= View_Helper_Text::applyFormat( $a->title );
$a->subtitle	= View_Helper_Text::applyFormat( $a->subtitle );
$a->volume		= $category->volume ? $w->volume."&nbsp;".$category->volume : "";
$position		= $helper->renderPositionFromArticle( $article );

$panelDetails	= $this->loadTemplateFile( 'catalog/article/details.php' );
$panelOrder		= $this->loadTemplateFile( 'catalog/article/order.php' );
$panelRelations	= $this->loadTemplateFile( 'catalog/article/relations.php' );

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
