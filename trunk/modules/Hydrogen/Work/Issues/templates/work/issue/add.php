<?php

$types	= $words['types'];
$optType	= array( '_selected' => $type );
foreach( $types as $key => $value )
	$optType[$key]	= $value;

$priorities	= $words['priorities'];
krsort( $priorities);
$optPriority	= array( '_selected' => $priority );
foreach( $priorities as $key => $value )
	$optPriority[$key]	= $value;

$severities	= $words['severities'];
krsort( $severities );
$optSeverity	= array();
foreach( $severities as $key => $value )
	$optSeverity[$key]	= $value;
//$optSeverity['_selected']	= $severity;

/*
$optStatus	= array( '' => '- alle -', '_selected' => $status );
foreach( $words['states'] as $key => $value )
	$optStatus[$key]	= $value;
 */

$optProject	= array( '_selected' => $projectId );
if( !empty( $projects ) ){
	foreach( $projects as $project )
		$optProject[$project->projectId]	= $project->title;
}

#$script	= '$(document).ready(function(){});';
#$env->page->js->addScript( $script );

$main	= '
<script>
var intervalIssueSearch;
$(document).ready(function(){
	Issues.loadLatest("#list-latest");

	$("#input_title").bind("keyup",function(){
		if(intervalIssueSearch)
			window.clearTimeout(intervalIssueSearch);
		intervalIssueSearch = window.setTimeout(function(){
			$("#list-similar tbody").html("<tr><td colspan=2>-</td></tr>");
			if($("#input_title").val().length)
				$.ajax({
					url: "./work/issue/search",
					data: {term: $("#input_title").val()},
					type: "post",
					dataType: "json",
					success: function(response){
						Issues.renderIssues($("#list-similar tbody"), response);
					}
				});
	//		
		}, 250);
	});
});
</script>
<fieldset>
	<legend class="icon add">Beschreibung</legend>
	<form action="./work/issue/add" method="post">
		<div class="column-left-25">
			<label for="type">'.$words['add']['labelType'].'</label><br/>
			'.UI_HTML_Elements::Select( 'type', $optType, 'max' ).'
		</div>
		<div class="column-left-25">
			<label for="priority">'.$words['add']['labelPriority'].'</label><br/>
			'.UI_HTML_Elements::Select( 'priority', $optPriority, 'max' ).'
		</div>
<!--		<div class="column-left-25">
			<label for="severity">'.$words['add']['labelSeverity'].'</label><br/>
			'.UI_HTML_Elements::Select( 'severity', $optSeverity, 'max' ).'
		</div>-->
		<div class="column-left-25">
			<label for="input_projectId">'.$words['add']['labelProject'].'</label><br/>
			'.UI_HTML_Elements::Select( 'projectId', $optProject, 'max' ).'
		</div>
		<div class="column-clear"></div>
		<br/>
		<ul class="input">
			<li>
				<label for="input_title" class="mandatory">'.$words['add']['labelTitle'].'</label><br/>
				<input type="text" name="title" id="input_title" class="max mandatory" value="'.htmlentities( $title, ENT_QUOTES, 'UTF-8' ).'"/>
			</li>
			<li>
				<label for="content">'.$words['add']['labelContent'].'</label><br/>
				'.UI_HTML_Tag::create( 'textarea', htmlentities( $content, ENT_QUOTES, 'UTF-8' ), array( 'name' => 'content', 'rows' => 9, 'class' => 'max' ) ).'
			</li>
		</ul>
		<div class="buttonbar">
			'.UI_HTML_Elements::LinkButton( './work/issue', $words['add']['buttonCancel'], 'button cancel' ).' |
			'.UI_HTML_Elements::Button( 'save', $words['add']['buttonSave'], 'button save' ).'
		</div>
	</form>
</fieldset>
';

return '
<div class="issue-add">
	<div class="column-left-66">
		'.$main.'
	</div>
	<div class="column-left-33">
		<fieldset>
			<legend class="icon info">Ähnliche Einträge</legend>
			<table id="list-similar" class="issues">
				<tbody>
					<tr><td colspan="3">-</td></tr>
				</tbody>
			</table>
		</fieldset>
		<fieldset>
			<legend class="icon info">Letzte Einträge</legend>
			<table id="list-latest" class="issues">
				<tbody>
					<tr><td colspan="3">-</td></tr>
				</tbody>
			</table>
		</fieldset>
	</div>
	<div style="clear: both"></div>
</div>
';


?>
