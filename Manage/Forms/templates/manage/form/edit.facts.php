<?php

$iconList	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-list' ) );
$iconAdd	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) );
$iconSave	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );
$iconRemove	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );
$iconPrev	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
$iconNext	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-right' ) );

$statuses	= array(
	-1		=> 'deaktiviert',
	0		=> 'in Arbeit',
	1		=> 'aktiviert',
);
$optStatus	= UI_HTML_Elements::Options( $statuses, $form->status );

$types		= array(
	0		=> 'direkter Versand',
	1		=> 'mit Double-Opt-In',
);
$optType	= UI_HTML_Elements::Options( $types, $form->type );

$optMailCustomer	= array( '' => '- keine -' );
foreach( $mailsCustomer as $item )
	$optMailCustomer[$item->mailId]	= $item->title;
$optMailCustomer	= UI_HTML_Elements::Options( $optMailCustomer, $form->mailId );

return '
<div class="content-panel">
	<div class="content-panel-inner">
		<form action="./manage/form/edit/'.$form->formId.'" method="post">
			<div class="row-fluid">
				<div class="span1">
					<label for="input_formId">ID</label>
					<input type="text" name="formId" id="input_formId" class="span12" disabled="disabled" value="'.htmlentities( $form->formId, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span7">
					<label for="input_title">Titel</label>
					<input type="text" name="title" id="input_title" class="span12" value="'.htmlentities( $form->title, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span2">
					<label for="input_type">Typ</label>
					<select name="type" id="input_type" class="span12">'.$optType.'</select>
				</div>
				<div class="span2">
					<label for="input_status">Zustand</label>
					<select name="status" id="input_status" class="span12">'.$optStatus.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span8">
					<label for="input_receivers">Empfänger <small class="muted">(mit Komma getrennt)</small></label>
					<input type="text" name="receivers" id="input_receivers" class="span12" value="'.htmlentities( $form->receivers, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span4">
					<label for="input_mailId">Ergebnis-Email an Kunden</label>
					<select name="mailId" id="input_mailId" class="span12">'.$optMailCustomer.'</select>
				</div>
			</div>
			<div class="buttonbar">
				'.$navButtons['list'].'
				'.UI_HTML_Tag::create( 'button', $iconSave.'&nbsp;speichern', array(
					'type'		=> 'submit',
					'name'		=> 'save',
					'class'		=> 'btn btn-primary',
				) ).'
				'.$navButtons['nextView'].'
				'.UI_HTML_Tag::create( 'a', $iconRemove.'&nbsp;entfernen', array(
					'href'		=> './manage/form/remove/'.$form->formId,
					'class'		=> 'btn btn-danger',
					'disabled'	=> $hasFills ? 'disabled' : NULL,
					'onclick'	=> "return confirm('Wirklich ?');",
				) ).'
			</div>
		</form>
	</div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.3.3/ace.js"></script>
<script>
jQuery(document).ready(function(){
	FormEditor.applyAceEditor("#input_content");
});
</script>';
?>
