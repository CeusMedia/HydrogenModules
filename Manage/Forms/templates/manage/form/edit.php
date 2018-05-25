<?php

$modelForm	= new Model_Form( $env );
$modelBlock	= new Model_Form_Block( $env );
$modelMail	= new Model_Form_Mail( $env );

$iconList	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-list' ) );
$iconSave	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );
$iconRemove	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );
$iconBlock	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-square' ) );

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

$withinBlocks	= array();

$listBlocksWithin	= UI_HTML_Tag::create( 'p', '<em class="muted">Keine.</em>' );
$matches		= array();
preg_match_all( '/\[block_(\S+)\]/', $form->content, $matches );
if( isset( $matches[0] ) && count( $matches[0] ) ){
	$list	= array();
	foreach( array_keys( $matches[0] ) as $nr ){
		$item	= $modelBlock->getByIndex( 'identifier', $matches[1][$nr] );
		if( !$item )
			continue;
		$link	= UI_HTML_Tag::create( 'a', $iconBlock.'&nbsp;'.$item->title, array(
			'href'	=> './manage/form/block/edit/'.$item->blockId,
		) );
		$list[]	= UI_HTML_Tag::create( 'li', $link );
	}
	if( $list )
		$listBlocksWithin	= UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'unstyled' ) );
}

$optMail	= array( '' => '- keine -' );
$mails		= $modelMail->getAll( array( 'identifier' => 'customer_result_%' ), array( 'title' => 'ASC' ) );
foreach( $mails as $item )
	$optMail[$item->mailId]	= $item->title;
$optMail	= UI_HTML_Elements::Options( $optMail, $form->mailId );

return '
<h2><a href="./manage/form" class="muted">Formular:</a> '.$form->title.'</h2>
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
			<select name="mailId" id="input_mailId" class="span12">'.$optMail.'</select>
		</div>
	</div>
	<div class="row-fluid" style="margin-bottom: 1em">
		<div class="span12">
			<label for="input_content">Inhalt</label>
			<textarea name="content" id="input_content" class="span12" rows="20">'.htmlentities( $form->content, ENT_QUOTES, 'UTF-8' ).'</textarea>
			<div id="content_editor" class="ace-editor"></div>
		</div>
	</div>
	<div class="buttonbar">
		<a href="./manage/form" class="btn">'.$iconList.'&nbsp;zur Liste</a>
		<button type="submit" name="save" class="btn btn-primary">'.$iconSave.'&nbsp;speichern</button>
		'.UI_HTML_Tag::create( 'a', $iconRemove.'&nbsp;entfernen', array(
			'href'		=> './manage/form/remove/'.$form->formId,
			'class'		=> 'btn btn-danger',
			'disabled'	=> ( $withinBlocks || 1 ) ? 'disabled' : NULL,
			'onclick'	=> 'return confirm("Wirklich ?");',
		) ).'
	</div>
</form>
<div class="row-fluid">
	<div class="span6">
		<h4>Verwendete Blöcke</h4>
		'.$listBlocksWithin.'
	</div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.3.3/ace.js"></script>
<script>
jQuery(document).ready(function(){
	FormEditor.applyAceEditor("#input_content");
});
</script>';
