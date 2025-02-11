<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var array<object> $mailsCustomer */
/** @var array<object> $mailsManager */
/** @var int $page */
/** @var int $pages */

$modelForm	= new Model_Form( $env );
$modelBlock	= new Model_Form_Block( $env );

$iconList	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-list'] );
$iconSave	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );

$statuses	= [
	-1		=> 'deaktiviert',
	0		=> 'in Arbeit',
	1		=> 'aktiviert',
];
$optStatus	= HtmlElements::Options( $statuses );

$types		= [
	0		=> 'direkter Versand',
	1		=> 'mit Double-Opt-In',
];
$optType	= HtmlElements::Options( $types );

$optMailCustomer	= ['' => '- keine -'];
foreach( $mailsCustomer as $item )
	$optMailCustomer[$item->mailId]	= $item->title;
$optMailCustomer	= HtmlElements::Options( $optMailCustomer );

$optMailManager		= ['' => '- keine -'];
foreach( $mailsManager as $item )
	$optMailManager[$item->mailId]	= $item->title;
$optMailManager		= HtmlElements::Options( $optMailManager );

return '
<h2><span class="muted">Formular:</span> Neu</h2>
<div class="content-panel">
	<div class="content-panel-inner">
		<form action="./manage/form/add" method="post">
			<div class="row-fluid">
				<div class="span1">
					<label for="input_formId">ID</label>
					<input type="text" name="formId" id="input_formId" class="span12" disabled="disabled"/>
				</div>
				<div class="span7">
					<label for="input_title">Titel</label>
					<input type="text" name="title" id="input_title" class="span12"/>
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
				<div class="span12">
					<label for="input_receivers">Empfänger <small class="muted">(mit Komma getrennt)</small></label>
					<input type="text" name="receivers" id="input_receivers" class="span12"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span6">
					<label for="input_customerMailId">Ergebnis-Email an Kunden <small class="muted">(=Absender)</small></label>
					<select name="customerMailId" id="input_customerMailId" class="span12">'.$optMailCustomer.'</select>
				</div>
				<div class="span6">
					<label for="input_managerMailId">Ergebnis-Email an Manager <small class="muted">(=Empfänger)</small></label>
					<select name="managerMailId" id="input_managerMailId" class="span12">'.$optMailManager.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_forwardOnSuccess">URL-Weiterleitung bei Erfolg<!-- <small class="muted">(...)</small>--></label>
					<input type="text" name="forwardOnSuccess" id="input_forwardOnSuccess" class="span12"/>
				</div>
			</div>
			<div class="row-fluid" style="margin-bottom: 1em">
				<div class="span12">
					<label for="input_content">Inhalt</label>
					<textarea name="content" id="input_content" class="span12" rows="20"></textarea>
				</div>
			</div>
			<div class="buttonbar">
				<a href="./manage/form" class="btn">'.$iconList.'&nbsp;zur Liste</a>
				<button type="submit" name="save" class="btn btn-primary">'.$iconSave.'&nbsp;speichern</button>
			</div>
		</form>
	</div>
</div>
<script>
jQuery(document).ready(function(){
	FormEditor.applyAceEditor("#input_content");
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
