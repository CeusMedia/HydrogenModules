<?php

$buttonAdd 		= UI_HTML_Elements::LinkButton( './admin/project/add', $words['index']['buttonAdd'], 'button add' );

/*  --  PROJECT TABLE  --  */
$list	= array();
foreach( $projects as $projectId => $project ){
	$attributes	= array(
		'class'		=> 'project available',
		'title'		=> $project->description,
		'href'		=> './admin/project/edit/'.$project->projectId
	);
	$link		= UI_HTML_Tag::create( 'a', $project->title, $attributes );
	$type		= '<span class="project-status status-'.$project->status.'">'.$words['status'][(int) $project->status].'</span>';
	$class		= 'project available status-'.$project->status;
//	$version	= $project->version;
//	$version	= '<span class="project-version">'.$version.'</span>';
	$list[]		= '<tr class="'.$class.'"><td>'.$link.'</td><td>'.$type.'</td><td>'/*.$version*/.'</td></tr>';
}
$heads		= array( $words['index']['headTitle'], $words['index']['headStatus'], $words['index']['headVersion'] );
$heads		= UI_HTML_Elements::TableHeads( $heads );
$rows		= join( $list );

return '
<div>
	<h2>'.$words['index']['heading'].'</h2>
	<fieldset>
		<legend>'.$words['index']['legend'].'</legend>
		<table class="projects available">
			'.$heads.'
			'.$rows.'
		</table>
		<div class="buttonbar">
			'.$buttonAdd.'
		</div>
	</fieldset>
</div>';
?>


<fieldset>
	<legend>'.$words['index']['legend'].'</legend>
	<table width="100%">
		<colgroup>
			<col width="3%"/>
			<col width="72%"/>
			<col width="15%"/>
			<col width="10%"/>
		</colgroup>
		'.$heads.'
		'.$rows.'
	</table>
</fieldset>
