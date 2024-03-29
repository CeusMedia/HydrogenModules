<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;

$panelFilter	= $this->loadTemplateFile( 'admin/module/index.filter.php' );

$pagination	= '';
if( $modulesTotal > $limit ){
/*	$pagination	= new UI_HTML_Pagination();
	$pagination->setOption( 'uri', './admin/module' );
	$pagination	= $pagination->build( $modulesTotal, $limit, $offset );*/
	$pagination	= new \CeusMedia\Bootstrap\PageControl( './admin/module', $page, ceil( $modulesTotal / $limit ) );
}

/*  --  MODULE TABLE  --  */
$list	= [];
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
		$image	= HtmlElements::Image( $module->icon, htmlentities( $module->title, ENT_QUOTES, 'UTF-8' ) );
		$icon	= HtmlTag::create( 'a', $image, ['class' => 'image'] );
	}
	$icon		= '<div style="width: 16px; height: 16px; float: left; display: block">'.$icon.'</div>';

	$category	= $module->category;
	$abstract	= strlen( $abstract ) ? $abstract : '&nbsp;';
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
	$list[]		= '<tr class="'.$class.'">
		<td>'.$icon.'&nbsp;'.$link.'<br/><small class="shorten">'.$abstract.'</small></td>
		<td>'.$type.'</td>
		<td>'.$version.'</td>
		<td>'.$category.'</td>
	</tr>';
}
$heads		= [
	$words['index']['headTitle'],
	$words['index']['headType'],
	$words['index']['headVersion'],
	$words['index']['headCategory'],
];
$heads		= HtmlElements::TableHeads( $heads );
$colGroup	= HtmlElements::ColumnGroup( "58%,14%,8%,20%" );
$listAll	= '<table class="modules all table table-striped" style="table-layout: fixed">
	'.$colGroup.'
	<thead>'.$heads.'</thead>
	<tbody>'.join( $list ).'</tbody>
</table>';


function renderPagesIndicator( $count, $total, $offset, $template = '%1$s - %2$s / %3$s' ){
	$nrFrom		= 1 + (int) $offset;
	$nrTo		= $count + (int) $offset;
	$nrTotal	= $total;
	$spanFrom	= HtmlTag::create( 'span', $nrFrom, ['class' => 'pages-from'] );
	$spanTo		= HtmlTag::create( 'span', $nrTo, ['class' => 'pages-to'] );
	$spanTotal	= HtmlTag::create( 'span', $nrTotal, ['class' => 'pages-total'] );
	$line		= sprintf( $template, $spanFrom, $spanTo, $spanTotal );
	return HtmlTag::create( 'div', $line, ['class' => 'pages-indicator'] );
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
<div class="row-fluid">
	<div class="span3">
		'.$panelFilter.'
	</div>
	<div class="span9">
		<div class="content-panel">
			<div class="content-panel-inner">
				'.$listAll.'
				'.$pages.'
				'.$pagination.'
			</div>
		</div>
	</div>
</div>';
?>
