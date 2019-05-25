<?php
$modelForm	= new Model_Form( $env );
$modelMail	= new Model_Form_Mail( $env );

$iconList	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-list' ) );
$iconView	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-eye' ) );
$iconSave	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );
$iconRemove	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );
$iconMail	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-square' ) );
$iconForm	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-th' ) );

$withinForms		= $modelForm->getAllByIndex( 'mailId', $mail->mailId );
$listWithinForms	= UI_HTML_Tag::create( 'p', '<em class="muted">Keine.</em>' );
if( $withinForms ){
	$list	= array();
	foreach( $withinForms as $item ){
		$link	= UI_HTML_Tag::create( 'a', $iconForm.'&nbsp;'.$item->title, array(
			'href'	=> './manage/form/edit/'.$item->formId,
		) );
		$list[]	= UI_HTML_Tag::create( 'li', $link );
	}
	$listWithinForms	= UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'unstyled' ) );
}

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
$optRoleType	= UI_HTML_Elements::Options( $optRoleType, (int) $mail->roleType );

$optFormat	= array(
	0	=> 'nicht definiert',
	1	=> 'Text',
	2	=> 'HTML',
);
$optFormat	= UI_HTML_Elements::Options( $optFormat, $mail->format );

return '
<h2><span class="muted">Mails-Vorlage:</span> '.$mail->title.'</h2>
<div class="content-panel">
	<!--<h3><span class="muted">Mails-Vorlage:</span> '.$mail->title.'</h3>-->
	<div class="content-panel-inner">
		<form action="./manage/form/mail/edit/'.$mail->mailId.'" method="post">
			<div class="row-fluid">
				<div class="span8">
					<label for="input_title">Titel</label>
					<input type="text" name="title" id="input_title" class="span12" value="'.htmlentities( $mail->title, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span4">
					<label for="input_identifier">Shortcode</label>
					<input type="text" name="identifier" id="input_identifier" class="span12" value="'.htmlentities( $mail->identifier, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span4">
					<label for="input_subject">Betreff</label>
					<input type="text" name="subject" id="input_subject" class="span12" value="'.htmlentities( $mail->subject, ENT_QUOTES, 'UTF-8' ).'"/>
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
					<textarea name="content" id="input_content" class="span12" rows="20">'.htmlentities( $mail->content, ENT_QUOTES, 'UTF-8' ).'</textarea>
					<div id="content_editor" class="ace-editor"></div>
				</div>
			</div>
			<div class="buttonbar">
				<a href="./manage/form/mail" class="btn">'.$iconList.' zur Liste</a>
				<a href="./manage/form/mail/view/'.$mail->mailId.'" class="btn btn-info">'.$iconView.' anzeigen</a>
				<button type="submit" name="save" class="btn btn-primary">'.$iconSave.' speichern</button>
				'.UI_HTML_Tag::create( 'a', $iconRemove.'&nbsp;entfernen', array(
					'href'		=> './manage/form/mail/remove/'.$mail->mailId,
					'class'		=> 'btn btn-danger',
					'onclick'	=> 'return confirm("Wirklich ?");',
				) ).'
			</div>
		</form>
	</div>
</div>
<div class="row-fluid">
	<div class="span6">
		<h4>Verwendung in Formularen</h4>
		'.$listWithinForms.'
	</div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.3.3/ace.js"></script>
<script>
jQuery(document).ready(function(){
	FormEditor.applyAceEditor("#input_content");
});
</script>';
