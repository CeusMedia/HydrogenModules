<?php

use CeusMedia\Bootstrap\Icon;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var array<string,array<string,string>> $words */

$w	= (object) $words['add'];

$iconList	= new Icon( 'list' );
$iconSave	= new Icon( 'check' );

$optRoleType	= $words['roleTypes'];
$optRoleType	= HtmlElements::Options( $optRoleType, Model_Form_Mail::ROLE_TYPE_NONE );

$optFormat	= $words['formats'];
$optFormat	= HtmlElements::Options( $optFormat, Model_Form_Mail::FORMAT_TEXT );

return '
<h2><span class="muted">'.$w->headingPrefix.'</span> '.$w->heading.'</h2>
<div class="content-panel">
	<div class="content-panel-inner">
		<form action="./manage/form/mail/add" method="post">
			<div class="row-fluid">
				<div class="span8">
					<label for="input_title">'.$w->labelTitle.'</label>
					<input type="text" name="title" id="input_title" class="span12"/>
				</div>
				<div class="span4">
					<label for="input_identifier">'.$w->labelIdentifier.'</label>
					<input type="text" name="identifier" id="input_identifier" class="span12"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span4">
					<label for="input_subject">'.$w->labelSubject.'</label>
					<input type="text" name="subject" id="input_subject" class="span12"/>
				</div>
				<div class="span4">
					<label for="input_roleType">'.$w->labelRoleType.'</label>
					<select name="roleType" id="input_roleType" class="span12">'.$optRoleType.'</select>
				</div>
				<div class="span4">
					<label for="input_format">'.$w->labelFormat.'</label>
					<select name="format" id="input_format" class="span12">'.$optFormat.'</select>
				</div>
			</div>
			<div class="row-fluid" style="margin-bottom: 1em">
				<div class="span12">
					<label for="input_content">'.$w->labelContent.'</label>
					<textarea name="content" id="input_content" class="span12" rows="20"></textarea>
				</div>
			</div>
			<div class="buttonbar">
				<a href="./manage/form/mail" class="btn">'.$iconList.'&nbsp;'.$w->buttonList.'</a>
				<button type="submit" name="save" class="btn btn-primary">'.$iconSave.'&nbsp;'.$w->buttonSave.'</button>
			</div>
		</form>
	</div>
</div>
<script>
jQuery(document).ready(function(){
	FormEditor.applyAceEditor("#input_content", {options:{lineHeight: 1.2}, flags:{fontSize: 14}});
});
</script>
<style>
.ace_editor {
	border: 1px solid rgba(127, 127, 127, 0.5);
	border-radius: 4px;
	padding: 6px;
	}
</style>
';
