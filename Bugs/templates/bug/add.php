<?php

$types	= $words['types'];
$optType	= array( '' => '- alle -' );
foreach( $types as $key => $value )
	$optType[$key]	= $value;
$optType['_selected']	= $this->env->getRequest()->get( 'type' );

$severities	= $words['severities'];
krsort( $severities );
$optSeverity	= array( '' => '- alle -' );
foreach( $severities as $key => $value )
	$optSeverity[$key]	= $value;
$optSeverity['_selected']	= $this->env->getRequest()->get( 'severity' );

$optStatus	= array( '' => '- alle -' );
foreach( $words['states'] as $key => $value )
	$optStatus[$key]	= $value;
$optStatus['_selected']	= $this->env->getRequest()->get( 'status' );




$script	= '$(document).ready(function(){});';
$this->env->page->js->addScript( $script );

$main	= '
<fieldset>
	<legend>Beschreibung</legend>
	<form action="./labs/bug/add" method="post">
		<div style="float: left; width: 30%; margin-right: 3%">
				<label for="status">'.$words['add']['labelType'].'</label><br/>
				'.UI_HTML_Elements::Select( 'type', $optType, 'm' ).'
		</div>
		<div style="float: left; width: 30%; margin-right: 3%">
				<label for="status">'.$words['add']['labelSeverity'].'</label><br/>
				'.UI_HTML_Elements::Select( 'severity', $optSeverity, 'm' ).'
		</div>
		<div style="clear: left"></div>
		<br/>
		<ul class="input">
			<li>
				<label for="title">'.$words['add']['labelTitle'].'</label><br/>
				'.UI_HTML_Elements::Input( 'title', $this->env->getRequest()->get( 'title' ), '' ).'
			</li>
			<li>
				<label for="content">'.$words['add']['labelContent'].'</label><br/>
				'.UI_HTML_Tag::create( 'textarea', $this->env->getRequest()->get( 'content' ), array( 'name' => 'content', 'rows' => 9 ) ).'
			</li>
		</ul>
		<div class="buttonbar">
			'.UI_HTML_Elements::LinkButton( './labs/bug', $words['add']['buttonCancel'], 'button cancel' ).' |
			'.UI_HTML_Elements::Button( 'save', $words['add']['buttonSave'], 'button save' ).'
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