<?php

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */
/** @var object[] $modules */

/*  --  MODULE TABLE  --  */
$list	= [];
foreach( $modules as $moduleId => $module ){
	$attributes	= array(
		'class'		=> 'module available',
		'title'		=> $module->description,
		'href'		=> './admin/module/view/'.$moduleId
	);
	$link		= HtmlTag::create( 'a', $module->title, $attributes );
	$type		= '<span class="module-type type-'.$module->type.'">'.$words['types'][(int) $module->type].'</span>';
	$class		= 'module available type-'.$module->type;
	$version	= $module->version;
	if( $module->versionInstalled && $module->versionAvailable && $module->versionInstalled != $module->versionAvailable ){
		if( $module->versionInstalled < $module->versionAvailable )
			$version	= $module->versionInstalled.' <small>(verfügbar: '.$module->versionAvailable.')</small>';
		else
			$version	= $module->versionInstalled.' / '.$module->versionAvailable;
	}
	$version	= '<span class="module-version">'.$version.'</span>';
	$list[]		= '<tr class="'.$class.'"><td>'.$link.'</td><td>'.$type.'</td><td>'.$version.'</td></tr>';
}
$heads		= array( $words['index']['headTitle'], $words['index']['headType'], $words['index']['headVersion'] );
$heads		= HtmlElements::TableHeads( $heads );
$listAll	= '<table class="modules all">'.$heads.join( $list ).'</table>';


/*  --  AVAILABLE  --  */
/*$list	= [];
foreach( $modulesAvailable as $moduleId => $module ){
	$attributes	= array(
		'class'		=> 'module available',
		'title'		=> $module->description,
		'href'		=> './admin/module/view/'.$moduleId
	);
	$link	= HtmlTag::create( 'a', $module->title, $attributes );
	$list[]	= '<li class="module available">'.$link.'</li>';
}
$listAvailable	= '<ul class="modules available">'.join( $list ).'</ul>';
*/

/*  --  INSTALLED  --  */
/*$list	= [];
foreach( $modulesInstalled as $moduleId => $module ){
	$attributes	= array(
		'class'		=> 'module installed',
		'title'		=> $module->description,
		'href'		=> './admin/module/view/'.$moduleId
	);
	$link	= HtmlTag::create( 'a', $module->title, $attributes );
	$list[]	= '<li class="module installed">'.$link.'</li>';
}
$listInstalled	= '<ul class="modules installed">'.join( $list ).'</ul>';
*/

/*  --  NOT INSTALLED  --  */
/*$list	= [];
foreach( $modulesNotInstalled as $moduleId => $module ){
	$attributes	= array(
		'class'		=> 'module',
		'title'		=> $module->description,
		'href'		=> './admin/module/view/'.$moduleId
	);
	$link	= HtmlTag::create( 'a', $module->title, $attributes );
	$list[]	= '<li class="module">'.$link.'</li>';
}
$listNotInstalled	= '<ul class="modules">'.join( $list ).'</ul>';
*/

return '
<div>
	<h2>'.$words['index']['heading'].'</h2>
	<fieldset>
		<legend>'.$words['index']['legend'].'</legend>
	'.$listAll.'
	</fieldset>
<!--	<h3>Verfügbar</h3>
	'./*$listAvailable.*/'
	<h3>Installiert</h3>
	'./*$listInstalled.*/'
	<h3>Nicht installiert</h3>
	'./*$listNotInstalled.*/'-->
</div>';
