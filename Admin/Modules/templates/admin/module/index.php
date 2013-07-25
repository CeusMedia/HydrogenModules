<?php

$panelFilter	= $this->loadTemplateFile( 'admin/module/index.filter.php' );

$pagination	= '';
if( $modulesTotal > $limit ){
	$pagination	= new UI_HTML_Pagination();
	$pagination->setOption( 'uri', './admin/module' );
	$pagination	= $pagination->build( $modulesTotal, $limit, $offset );
}

/*  --  MODULE TABLE  --  */
$list	= array();
foreach( $modules as $moduleId => $module ){
	$descLines	= explode( "\n", $module->description );
	$abstract	= $descLines[0];
	$attributes	= array(
		'class'		=> 'module available',
		'title'		=> htmlentities( $abstract,ENT_QUOTES, 'UTF-8' ),
		'href'		=> './admin/module/viewer/index/'.$moduleId
	);

	$icon		= '';
	if( !empty( $module->icon ) ){
		$image	= UI_HTML_Elements::Image( $module->icon, htmlentities( $module->title, ENT_QUOTES, 'UTF-8' ) );
		$icon	= UI_HTML_Tag::create( 'a', $image, array( 'class' => 'image' ) );
	}
	$icon		= '<div style="width: 16px; height: 16px; float: left; display: block">'.$icon.'</div>';

	$category	= $module->category;
	$abstract	= strlen( $abstract ) ? $abstract : '&nbsp;';
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
	$list[]		= '<tr class="'.$class.'">
		<td>'.$icon.'&nbsp;'.$link.'<br/><small class="shorten">'.$abstract.'</small></td>
		<td>'.$type.'</td>
		<td>'.$version.'</td>
		<td>'.$category.'</td>
	</tr>';
}
$heads		= array(
	$words['index']['headTitle'],
	$words['index']['headType'],
	$words['index']['headVersion'],
	$words['index']['headCategory'],
);
$heads		= UI_HTML_Elements::TableHeads( $heads );
$colGroup	= UI_HTML_Elements::ColumnGroup( "58%,14%,8%,20%" );
$listAll	= '<table class="modules all" style="table-layout: fixed">
	'.$colGroup.'
	'.$heads.'
	'.join( $list ).'
</table>';


function renderPagesIndicator( $count, $total, $offset, $template = '%1$s - %2$s / %3$s' ){
	$nrFrom		= 1 + (int) $offset;
	$nrTo		= $count + (int) $offset;
	$nrTotal	= $total;
	$spanFrom	= UI_HTML_Tag::create( 'span', $nrFrom, array( 'class' => 'pages-from' ) );
	$spanTo		= UI_HTML_Tag::create( 'span', $nrTo, array( 'class' => 'pages-to' ) );
	$spanTotal	= UI_HTML_Tag::create( 'span', $nrTotal, array( 'class' => 'pages-total' ) );
	$line		= sprintf( $template, $spanFrom, $spanTo, $spanTotal );
	return UI_HTML_Tag::create( 'div', $line, array( 'class' => 'pages-indicator' ) );
}

$template	= '%1$s bis %2$s von %3$s';
$count		= count( $modules );
$pages		= renderPagesIndicator( $count, $modulesTotal, $offset, $template );

return '
<style>
.pages-indicator {
	float: right;
	padding: 4px;
	}
.pages-indicator .pages-from,
.pages-indicator .pages-to,
.pages-indicator .pages-total {
	font-weight: bold;
	}
table.modules.all tr td {
	border-bottom: 1px solid #DFDFDF;
	}
table.modules.all tr:last-child td {
	border-bottom: 0px;
	}
table {
	border: 0px;
	}
</style>
<div>
	<div class="column-left-25">
		'.$panelFilter.'
	</div>
	<div class="column-right-75">
		<fieldset>
			<legend class="module">Module</legend>
			'.$listAll.'
			'.$pages.'
			'.$pagination.'
		</fieldset>
	</div>
</div>';
?>
