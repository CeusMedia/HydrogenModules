<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */

//$helper	= new View_Helper_Work_Time_Timer( $env );
//print_m( $helper->getRegisteredModules() );die;

$w			= (object) $words['edit-related'];

$listRelated	= HtmlTag::create( 'div', $w->noRelatedModule, ['class' => 'alert alert-notice'] );
if( $timer->module && $timer->moduleId ){
	$helperShortList    = new View_Helper_Work_Time_ShortList( $env );
	//$helperShortList->setStatus( [2] );
	$helperShortList->setModule( $timer->module );
	$helperShortList->setModuleId( $timer->moduleId );
	//$helperShortList->setRelationId( $timer->moduleId );
	$helperShortList->setButtons( ['view'/*, 'edit'*/] );
	$listRelated	= $helperShortList->render();
	if( !$listRelated )
		$listRelated	= HtmlTag::create( 'div', $w->noRelations, ['class' => 'alert alert-notice'] );

	$heading	= sprintf( $w->heading, $timer->relationTitle );
}

return '
<div class="content-panel">
	<h3 class="autocut">'.$w->heading.'</h3>
	<div class="content-panel-inner">
		'.$listRelated.'
	</div>
</div>';
