<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$buttonAdd 		= HtmlElements::LinkButton( './admin/project/add', $words['index']['buttonAdd'], 'button add' );

/*  --  PROJECT TABLE  --  */
$list	= [];
foreach( $projects as $projectId => $project ){
	$attributes	= array(
		'class'		=> 'project available',
		'title'		=> $project->description,
		'href'		=> './admin/project/edit/'.$project->projectId
	);
	$link		= HtmlTag::create( 'a', $project->title, $attributes );
	$type		= '<span class="project-status status-'.$project->status.'">'.$words['states'][(int) $project->status].'</span>';
	$class		= 'project available status-'.$project->status;
	$version	= '-';
	if( $project->version ){
		$label	= $project->version->version;
		if( $project->version->title )
			$label	= HtmlElements::Acronym( $label, $project->version->title );
		$version	= '<span class="project-version">'.$label.'</span>';
	}
	$list[]		= '<tr class="'.$class.'"><td>'.$link.'</td><td>'.$type.'</td><td>'.$version.'</td></tr>';
}
$heads		= array( $words['index']['headTitle'], $words['index']['headStatus'], $words['index']['headVersion'] );
$heads		= HtmlElements::TableHeads( $heads );
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
