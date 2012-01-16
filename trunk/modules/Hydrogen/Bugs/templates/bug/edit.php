<?php

$types	= $words['types'];
krsort( $types );
$optType	= array( '' => '- alle -' );
foreach( $types as $key => $value )
	$optType[$key]	= $value;
$optType['_selected']	= $bug->type;

$severities	= $words['severities'];
krsort( $severities );
$optSeverity	= array( '' => '- alle -' );
foreach( $severities as $key => $value )
	$optSeverity[$key]	= $value;
$optSeverity['_selected']	= $bug->severity;

$optStatus	= array( '' => '- alle -' );
foreach( $words['states'] as $key => $value )
	$optStatus[$key]	= $value;
$optStatus['_selected']	= $bug->status;



$script	= '
$(document).ready(function(){
	$("#panel-mode-1").hide();
	$("#bug-edit-trigger-mode-0").bind("click",function(){
		$("#panel-mode-0").show();
		$("#panel-mode-1").hide();
	});
	$("#bug-edit-trigger-mode-1").bind("click",function(){
		$("#panel-mode-0").hide();
		$("#panel-mode-1").show();
	});
});
';
$this->env->page->js->addScript( $script );

$main	= '
<fieldset id="bug-details">
	<legend>Beschreibung</legend>
	<form action="./labs/bug/edit/'.$bug->bugId.'" method="post">
		<div class="bug-id">Eintrag #'.$bug->bugId.'</div>
		<div id="panel-mode-0">
			<div class="bug-title">'.$bug->title.'</div>
			<div class="bug-content">'.nl2br( $bug->content ).'</div>
			<div class="buttonbar">
				'.UI_HTML_Elements::LinkButton( './labs/bug', $words['edit']['buttonCancel'], 'button cancel' ).' | 
				<button type="button" id="bug-edit-trigger-mode-1" class="button edit"><span>verändern</span></button>
			</div>
		</div>
		<div id="panel-mode-1">
			<ul class="input">
				<li>
					<label for="title">'.$words['edit']['labelTitle'].'</label><br/>
					'.UI_HTML_Elements::Input( 'title', $bug->title, '' ).'
				</li>
				<li>
					<label for="content">'.$words['edit']['labelContent'].'</label><br/>
					'.UI_HTML_Tag::create( 'textarea', $bug->content, array( 'name' => 'content', 'rows' => 9 ) ).'
				</li>
			</ul>
			<div class="buttonbar">
				'.UI_HTML_Elements::LinkButton( './labs/bug', $words['edit']['buttonCancel'], 'button cancel' ).' |
				<button type="button" id="bug-edit-trigger-mode-0" class="button view"><span>anzeigen</span></button>
				'.UI_HTML_Elements::Button( 'save', $words['edit']['buttonSave'], 'button save' ).'
			</div>
		</div>
	</form>
</fieldset>
';



$control	= require_once 'templates/labs/bug/edit.info.php';
$main		.= require_once 'templates/labs/bug/edit.changes.php';
$main		.= require_once 'templates/labs/bug/edit.emerge.php';

return '
<div class="bug-edit">
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