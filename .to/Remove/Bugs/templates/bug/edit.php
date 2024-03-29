<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$types	= $words['types'];
krsort( $types );
$optType	= ['' => '- alle -'];
foreach( $types as $key => $value )
	$optType[$key]	= $value;
$optType['_selected']	= $bug->type;

$severities	= $words['severities'];
krsort( $severities );
$optSeverity	= ['' => '- alle -'];
foreach( $severities as $key => $value )
	$optSeverity[$key]	= $value;
$optSeverity['_selected']	= $bug->severity;

$optStatus	= ['' => '- alle -'];
foreach( $words['states'] as $key => $value )
	$optStatus[$key]	= $value;
$optStatus['_selected']	= $bug->status;



$script	= '
$(document).ready(function(){
	$("#panel-mode-1").hide();
	$("#bug-edit-trigger-mode-0").on("click",function(){
		$("#panel-mode-0").show();
		$("#panel-mode-1").hide();
	});
	$("#bug-edit-trigger-mode-1").on("click",function(){
		$("#panel-mode-0").hide();
		$("#panel-mode-1").show();
	});
});
';
$this->env->page->js->addScript( $script );

$main	= '
<fieldset id="bug-details">
	<legend>Beschreibung</legend>
	<form action="./bug/edit/'.$bug->bugId.'" method="post">
		<div class="bug-id">Eintrag #'.$bug->bugId.'</div>
		<div id="panel-mode-0">
			<div class="bug-title">'.$bug->title.'</div>
			<div class="bug-content">'.nl2br( $bug->content ).'</div>
			<div class="buttonbar">
				'.HtmlElements::LinkButton( './bug', $words['edit']['buttonCancel'], 'button cancel' ).' |
				<button type="button" id="bug-edit-trigger-mode-1" class="button edit"><span>verändern</span></button>
			</div>
		</div>
		<div id="panel-mode-1">
			<ul class="input">
				<li>
					<label for="title">'.$words['edit']['labelTitle'].'</label><br/>
					'.HtmlElements::Input( 'title', $bug->title, '' ).'
				</li>
				<li>
					<label for="content">'.$words['edit']['labelContent'].'</label><br/>
					'.HtmlTag::create( 'textarea', $bug->content, ['name' => 'content', 'rows' => 9] ).'
				</li>
			</ul>
			<div class="buttonbar">
				'.HtmlElements::LinkButton( './bug', $words['edit']['buttonCancel'], 'button cancel' ).' |
				<button type="button" id="bug-edit-trigger-mode-0" class="button view"><span>anzeigen</span></button>
				'.HtmlElements::Button( 'save', $words['edit']['buttonSave'], 'button save' ).'
			</div>
		</div>
	</form>
</fieldset>
';



$control	= require_once 'templates/bug/edit.info.php';
$main		.= require_once 'templates/bug/edit.changes.php';
$main		.= require_once 'templates/bug/edit.emerge.php';

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
