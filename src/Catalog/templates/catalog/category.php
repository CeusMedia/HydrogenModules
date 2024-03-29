<?php
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var array $words */
/** @var object $category */

$w				= (object) $words['category'];
$language		= $env->getLanguage()->getLanguage();
$helper			= new View_Helper_Catalog( $env );

$position		= $helper->renderPositionFromCategory( $category );

$heading		= ( $category->children && !$category->parentId ) ? $w->single : NULL;
$articleList	= $helper->renderCategory( $category/*, $heading*/ );

$children		= "";
if( $category->children ){
	$children	= [];
	foreach( $category->children as $child ){
		$children[]	= $helper->renderCategory( $child, TRUE );
	}
	$children	= join( $children );
}

return '
<h2>'.$w->heading.'</h2>
'.$position.'
'.$articleList.'
'.$children.'
';
