<?php


/*  --  MODULE TABLE  --  */
$list	= array();
foreach( $modules as $moduleId => $module ){
	$attributes	= array(
		'class'		=> 'module available',
		'title'		=> $module->description,
		'href'		=> './admin/module/view/'.$moduleId
	);
	$link		= UI_HTML_Tag::create( 'a', $module->title, $attributes );
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
$heads		= UI_HTML_Elements::TableHeads( $heads );
$listAll	= '<table class="modules available">'.$heads.join( $list ).'</table>';


/*  --  AVAILABLE  --  */
/*$list	= array();
foreach( $modulesAvailable as $moduleId => $module ){
	$attributes	= array(
		'class'		=> 'module available',
		'title'		=> $module->description,
		'href'		=> './admin/module/view/'.$moduleId
	);
	$link	= UI_HTML_Tag::create( 'a', $module->title, $attributes );
	$list[]	= '<li class="module available">'.$link.'</li>';
}
$listAvailable	= '<ul class="modules available">'.join( $list ).'</ul>';
*/

/*  --  INSTALLED  --  */
/*$list	= array();
foreach( $modulesInstalled as $moduleId => $module ){
	$attributes	= array(
		'class'		=> 'module installed',
		'title'		=> $module->description,
		'href'		=> './admin/module/view/'.$moduleId
	);
	$link	= UI_HTML_Tag::create( 'a', $module->title, $attributes );
	$list[]	= '<li class="module installed">'.$link.'</li>';
}
$listInstalled	= '<ul class="modules installed">'.join( $list ).'</ul>';
*/

/*  --  NOT INSTALLED  --  */
/*$list	= array();
foreach( $modulesNotInstalled as $moduleId => $module ){
	$attributes	= array(
		'class'		=> 'module',
		'title'		=> $module->description,
		'href'		=> './admin/module/view/'.$moduleId
	);
	$link	= UI_HTML_Tag::create( 'a', $module->title, $attributes );
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
?>
