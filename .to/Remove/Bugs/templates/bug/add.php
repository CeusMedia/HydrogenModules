<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$types	= $words['types'];
$optType	= ['' => '- alle -'];
foreach( $types as $key => $value )
	$optType[$key]	= $value;
$optType['_selected']	= $this->env->getRequest()->get( 'type' );

$severities	= $words['severities'];
krsort( $severities );
$optSeverity	= ['' => '- alle -'];
foreach( $severities as $key => $value )
	$optSeverity[$key]	= $value;
$optSeverity['_selected']	= $this->env->getRequest()->get( 'severity' );

$optStatus	= ['' => '- alle -'];
foreach( $words['states'] as $key => $value )
	$optStatus[$key]	= $value;
$optStatus['_selected']	= $this->env->getRequest()->get( 'status' );

$optProject	= ['' => '- alle -'];
foreach( $projects as $project )
	$optProject[$project->projectId]	= $project->title;
$optProject['_selected']	= $this->env->getRequest()->get( 'projectId' );



$script	= '$(document).ready(function(){});';
$this->env->page->js->addScript( $script );

$main	= '
<fieldset>
	<legend>Beschreibung</legend>
	<form action="./bug/add" method="post">
		<div style="float: left; width: 30%; margin-right: 3%">
				<label for="status">'.$words['add']['labelType'].'</label><br/>
				'.HtmlElements::Select( 'type', $optType, 'm' ).'
		</div>
		<div style="float: left; width: 30%; margin-right: 3%">
				<label for="status">'.$words['add']['labelSeverity'].'</label><br/>
				'.HtmlElements::Select( 'severity', $optSeverity, 'm' ).'
		</div>
		<div style="float: left; width: 30%; margin-right: 3%">
				<label for="projectId">'.$words['add']['labelProject'].'</label><br/>
				'.HtmlElements::Select( 'projectId', $optProject, 'm' ).'
		</div>
		<div style="clear: left"></div>
		<br/>
		<ul class="input">
			<li>
				<label for="title">'.$words['add']['labelTitle'].'</label><br/>
				'.HtmlElements::Input( 'title', $this->env->getRequest()->get( 'title' ), '' ).'
			</li>
			<li>
				<label for="content">'.$words['add']['labelContent'].'</label><br/>
				'.HtmlTag::create( 'textarea', $this->env->getRequest()->get( 'content' ), ['name' => 'content', 'rows' => 9] ).'
			</li>
		</ul>
		<div class="buttonbar">
			'.HtmlElements::LinkButton( './bug', $words['add']['buttonCancel'], 'button cancel' ).' |
			'.HtmlElements::Button( 'save', $words['add']['buttonSave'], 'button save' ).'
		</div>
	</form>
</fieldset>
';

return '
<div class="bug-add">
	<div class="column-main">
		'.$main.'
	</div>
	<div style="clear: both"></div>
</div>
';


?>