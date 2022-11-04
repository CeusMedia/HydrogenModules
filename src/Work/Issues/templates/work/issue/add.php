<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$types	= $words['types'];
$optType	= ['_selected' => $type];
foreach( $types as $key => $value )
	$optType[$key]	= $value;

$priorities	= $words['priorities'];
krsort( $priorities);
$optPriority	= ['_selected' => $priority];
foreach( $priorities as $key => $value )
	$optPriority[$key]	= $value;

$severities	= $words['severities'];
krsort( $severities );
$optSeverity	= [];
foreach( $severities as $key => $value )
	$optSeverity[$key]	= $value;
//$optSeverity['_selected']	= $severity;

/*
$optStatus	= ['' => '- alle -', '_selected' => $status];
foreach( $words['states'] as $key => $value )
	$optStatus[$key]	= $value;
 */

$optProject	= ['_selected' => $projectId];
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

	$("#input_title").on("keyup",function(){
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
<div class="content-panel">
	<h3>Beschreibung</h3>
	<div class="content-panel-inner">
		<form action="./work/issue/add" method="post">
			<div class="row-fluid">
				<div class="span3">
					<label for="type">'.$words['add']['labelType'].'</label>
					'.HtmlElements::Select( 'type', $optType, 'span12' ).'
				</div>
				<div class="span3">
					<label for="priority">'.$words['add']['labelPriority'].'</label>
					'.HtmlElements::Select( 'priority', $optPriority, 'span12' ).'
				</div>
	<!--			<div class="column-left-25">
					<label for="severity">'.$words['add']['labelSeverity'].'</label>
					'.HtmlElements::Select( 'severity', $optSeverity, 'span12' ).'
				</div>-->
				<div class="span3">
					<label for="input_projectId">'.$words['add']['labelProject'].'</label>
					'.HtmlElements::Select( 'projectId', $optProject, 'span12' ).'
				</div>
			</div>
			<div class="row-fluid">
				<label for="input_title" class="mandatory">'.$words['add']['labelTitle'].'</label>
				<input type="text" name="title" id="input_title" class="span12 mandatory" value="'.htmlentities( $title, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
			<div class="row-fluid">
				<label for="content">'.$words['add']['labelContent'].'</label>
				'.HtmlTag::create( 'textarea', htmlentities( $content, ENT_QUOTES, 'UTF-8' ), ['name' => 'content', 'rows' => 9, 'class' => 'span12 -max'] ).'
			</div>
			<div class="buttonbar">
				<a class="btn btn-small btn" href="./work/issue"><i class="icon-arrow-left"></i> '.$words['add']['buttonCancel'].'</a>
				<button type="submit" class="btn btn-small btn-success" name="save"><i class="icon-ok icon-white"></i> '.$words['add']['buttonSave'].'</button>
			</div>
		</form>
	</div>
</div>
';

return '
<div class="issue-add row-fluid">
	<div class="span8">
		'.$main.'
	</div>
	<div class="span4">
		<div class="content-panel">
			<h4>Ähnliche Einträge</h4>
			<div class="content-panel-inner">
				<table id="list-similar" class="issues table table-condensed table-striped table-fixed">
					<colgroup>
						<col width="30px">
						<col width="">
						<col width="50px">
					</colgroup>
					<tbody>
						<tr><td colspan="3">-</td></tr>
					</tbody>
				</table>
			</div>
		</div>
		<br/>
		<div class="content-panel">
			<h4>Letzte Einträge</h4>
			<div class="content-panel-inner">
				<table id="list-latest" class="issues table table-condensed table-striped table-fixed">
					<colgroup>
						<col width="30px">
						<col width="">
						<col width="50px">
					</colgroup>
					<tbody>
						<tr><td colspan="3">-</td></tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>';
?>
