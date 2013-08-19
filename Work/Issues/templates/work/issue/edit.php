<?php

$types	= $words['types'];
krsort( $types );
$optType	= array( '' => '- alle -' );
foreach( $types as $key => $value )
	$optType[$key]	= $value;
$optType['_selected']	= $issue->type;

$severities	= $words['severities'];
krsort( $severities );
$optSeverity	= array( '' => '- alle -' );
foreach( $severities as $key => $value )
	$optSeverity[$key]	= $value;
$optSeverity['_selected']	= $issue->severity;

$optStatus	= array( '' => '- alle -' );
foreach( $words['states'] as $key => $value )
	$optStatus[$key]	= $value;
$optStatus['_selected']	= $issue->status;


$optProject	= array( '_selected' => $issue->projectId );
if( !empty( $projects ) ){
	foreach( $projects as $project )
		$optProject[$project->projectId]	= $project->title;
}

$script	= '
$(document).ready(function(){
	$("#panel-mode-1").hide();
	$("#issue-edit-trigger-mode-0").bind("click",function(){
		$("#panel-mode-0").show();
		$("#panel-mode-1").hide();
	});
	$("#issue-edit-trigger-mode-1").bind("click",function(){
		$("#panel-mode-0").hide();
		$("#panel-mode-1").show();
	});
});
';
$env->page->js->addScript( $script );



$main	= '
<fieldset id="issue-details">
	<legend>Beschreibung</legend>
	<form action="./work/issue/edit/'.$issue->issueId.'" method="post">
		<div class="issue-id"><small class="muted">Eintrag #'.$issue->issueId.'</small></div>
		<div id="panel-mode-0">
			<div class="issue-title">'.$issue->title.'</div>
			<div class="issue-content">'.nl2br( $issue->content ).'</div>
			<div class="buttonbar">
				<a href="./work/issue" class="btn btn-small"><i class="icon-arrow-left"></i> '.$words['edit']['buttonCancel'].'</a>
				<button type="button" id="issue-edit-trigger-mode-1" class="btn btn-small btn-success"><i class="icon-ok icon-white"></i> verändern</button>
			</div>
		</div>
		<div id="panel-mode-1">
			<div class="row-fluid">
				<div class="span8">
					<label for="title" class="mandatory">'.$words['edit']['labelTitle'].'</label>
					'.UI_HTML_Elements::Input( 'title', $issue->title, 'span12 -max mandatory' ).'
				</div>
				<div class="span4">
					<label for="input_projectId">'.$words['add']['labelProject'].'</label>
					'.UI_HTML_Elements::Select( 'projectId', $optProject, 'span12 -max' ).'
				</div>
			</div>
			<div class="row-fluid">
				<label for="content">'.$words['edit']['labelContent'].'</label>
				'.UI_HTML_Tag::create( 'textarea', $issue->content, array( 'class' => 'span12', 'name' => 'content', 'rows' => 9 ) ).'
			</div>
			<div class="buttonbar">
				<a href="./work/issue" class="btn btn-small"><i class="icon-arrow-left"></i> '.$words['edit']['buttonCancel'].'</a>
				<button type="button" id="issue-edit-trigger-mode-0" class="btn btn-small"><i class="icon-eye-open"></i> anzeigen</button>
				<button type="submit" name="save" class="btn btn-small btn-success"><i class="icon-ok icon-white"></i> '.$words['edit']['buttonSave'].'</button>
			</div>
		</div>
	</form>
</fieldset>
';



$control	= $view->loadTemplateFile( 'work/issue/edit.info.php' );
$main		.= $view->loadTemplateFile( 'work/issue/edit.changes.php' );
$main		.= $view->loadTemplateFile( 'work/issue/edit.emerge.php' );

return '
<div class="issue-edit row-fluid">
	<div class="span9">
		'.$main.'
	</div>
	<div class="span3">
		'.$control.'
	</div>
</div>
';


?>
