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
$this->env->page->js->addScript( $script );

$main	= '
<fieldset id="issue-details">
	<legend>Beschreibung</legend>
	<form action="./work/issue/edit/'.$issue->issueId.'" method="post">
		<div class="issue-id">Eintrag #'.$issue->issueId.'</div>
		<div id="panel-mode-0">
			<div class="issue-title">'.$issue->title.'</div>
			<div class="issue-content">'.nl2br( $issue->content ).'</div>
			<div class="buttonbar">
				'.UI_HTML_Elements::LinkButton( './work/issue', $words['edit']['buttonCancel'], 'button cancel' ).' | 
				<button type="button" id="issue-edit-trigger-mode-1" class="button edit"><span>verändern</span></button>
			</div>
		</div>
		<div id="panel-mode-1">
			<ul class="input">
				<li>
					<label for="title">'.$words['edit']['labelTitle'].'</label><br/>
					'.UI_HTML_Elements::Input( 'title', $issue->title, '' ).'
				</li>
				<li>
					<label for="content">'.$words['edit']['labelContent'].'</label><br/>
					'.UI_HTML_Tag::create( 'textarea', $issue->content, array( 'name' => 'content', 'rows' => 9 ) ).'
				</li>
			</ul>
			<div class="buttonbar">
				'.UI_HTML_Elements::LinkButton( './work/issue', $words['edit']['buttonCancel'], 'button cancel' ).' |
				<button type="button" id="issue-edit-trigger-mode-0" class="button view"><span>anzeigen</span></button>
				'.UI_HTML_Elements::Button( 'save', $words['edit']['buttonSave'], 'button save' ).'
			</div>
		</div>
	</form>
</fieldset>
';



$control	= require_once 'templates/work/issue/edit.info.php';
$main		.= require_once 'templates/work/issue/edit.changes.php';
$main		.= require_once 'templates/work/issue/edit.emerge.php';

return '
<div class="issue-edit">
	<div class="column-control">
		'.$control.'
	</div>
	<div class="column-main">
		'.$main.'
	</div>
	<div style="clear: both"></div>
</div>
';


?>
