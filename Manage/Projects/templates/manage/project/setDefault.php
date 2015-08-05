<?php


$optProjectId	= array();
foreach( $projects as $project )
	$optProjectId[$project->projectId]	= $project->title;
$optProjectId	= UI_HTML_Elements::Options( $optProjectId );

$panelDefault = '
<div class="content-panel">
	<h3>'.$words['setDefault']['heading'].'</h3>
	<div class="content-panel-inner">
		<form action="./manage/project/setDefault" method="post">
			<input type="hidden" name="from" value="'.$from.'"/>
			<label for="input_projectId">'.$words['setDefault']['labelProjectId'].'</label>
			<select name="projectId" id="input_projectId" class="span12">'.$optProjectId.'</select>
			<div class="buttonbar">
				<button type="submit" name="save" class="btn btn-primary"><i class="icon-ok icon-white"></i>&nbsp;'.$words['setDefault']['buttonSave'].'</button>
			</div>
		</form>
	</div>
</div>';

extract( $view->populateTexts( array( 'setDefault.info' ), 'html/manage/project/' ) );

return '<div class="row-fluid">
	<div class="span6">
		'.$panelDefault.'
	</div>
	<div class="span6">
		'.$textSetDefaultInfo.'
	</div>
</div>';
