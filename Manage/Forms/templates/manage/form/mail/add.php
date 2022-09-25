<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconList	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-list' ) );
$iconSave	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );

$optRoleType	= array(
	Model_Form_Mail::ROLE_TYPE_NONE				=> 'keinen',
	Model_Form_Mail::ROLE_TYPE_CUSTOMER_ALL		=> 'Kunde',
	Model_Form_Mail::ROLE_TYPE_CUSTOMER_RESULT	=> 'Kunde: Ergebnis',
	Model_Form_Mail::ROLE_TYPE_CUSTOMER_REACT	=> 'Kunde: Reaktion',
	Model_Form_Mail::ROLE_TYPE_LEADER_ALL		=> 'Leiter',
	Model_Form_Mail::ROLE_TYPE_LEADER_RESULT	=> 'Leiter: Ergebnis',
	Model_Form_Mail::ROLE_TYPE_LEADER_REACT		=> 'Leiter: Reaktion',
	Model_Form_Mail::ROLE_TYPE_MANAGER_ALL		=> 'Manager',
	Model_Form_Mail::ROLE_TYPE_MANAGER_RESULT	=> 'Manager: Ergebnis',
	Model_Form_Mail::ROLE_TYPE_MANAGER_REACT	=> 'Manager: Reaktion',
);
$optRoleType	= UI_HTML_Elements::Options( $optRoleType, Model_Form_Mail::ROLE_TYPE_NONE );

$optFormat	= array(
	0	=> 'nicht definiert',
	1	=> 'Text',
	2	=> 'HTML',
);
$optFormat	= UI_HTML_Elements::Options( $optFormat, Model_Form_Mail::FORMAT_TEXT );

return '
<h2><span class="muted">Mail:</span> Neu</h2>
<div class="content-panel">
	<div class="content-panel-inner">
		<form action="./manage/form/mail/add" method="post">
			<div class="row-fluid">
				<div class="span8">
					<label for="input_title">Titel</label>
					<input type="text" name="title" id="input_title" class="span12"/>
				</div>
				<div class="span4">
					<label for="input_identifier">Shortcode</label>
					<input type="text" name="identifier" id="input_identifier" class="span12"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span4">
					<label for="input_subject">Betreff</label>
					<input type="text" name="subject" id="input_subject" class="span12"/>
				</div>
				<div class="span4">
					<label for="input_roleType">Nutzbar für</label>
					<select name="roleType" id="input_roleType" class="span12">'.$optRoleType.'</select>
				</div>
				<div class="span4">
					<label for="input_format">Format</label>
					<select name="format" id="input_format" class="span12">'.$optFormat.'</select>
				</div>
			</div>
			<div class="row-fluid" style="margin-bottom: 1em">
				<div class="span12">
					<label for="input_content">Inhalt</label>
					<textarea name="content" id="input_content" class="span12" rows="20"></textarea>
				</div>
			</div>
			<div class="buttonbar">
				<a href="./manage/form/mail" class="btn">'.$iconList.'&nbsp;zur Liste</a>
				<button type="submit" name="save" class="btn btn-primary">'.$iconSave.'&nbsp;speichern</button>
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
