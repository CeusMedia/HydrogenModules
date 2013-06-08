<?php

$optType	= $words['types'];
$optScope	= $words['scopes'];
$optStatus	= $words['states'];

$optType		= UI_HTML_Elements::Options( $optType, $page->type );
$optScope		= UI_HTML_Elements::Options( $optScope, $page->scope );
$optStatus		= UI_HTML_Elements::Options( $optStatus, $page->status );

$inputParent	= "";
if( $page->type == 0 || $page->type == 2 ){
	$optParent		= UI_HTML_Elements::Options( $parentMap, $page->parentId );
	$inputParent	= '
	<label for="input_parentId">Unterseite von</label>
	<select name="parentId" class="span12" id="input_parentId">'.$optParent.'</select>';
}

return '
<div class="row-fluid">
	<div class="span4">
		<label for="input_title">Titel</label>
		<input type="text" name="title" id="input_title" class="span12" value="'.htmlentities( $page->title, ENT_QUOTES, 'UTF-8' ).'" required/>
	</div>
	<div class="span2">
		<label for="input_status">Sichtbarkeit</label>
		<select name="status" class="span12" id="input_status">'.$optStatus.'</select>
	</div>
	<div class="span4">
		<label for="input_identifier">Adresse</label>
		<div class="input-prepend">
			<span class="add-on">'.$path.'</span>
			<input type="text" name="identifier" class="span6" id="input_identifier" value="'.htmlentities( $page->identifier, ENT_QUOTES, 'UTF-8' ).'" required/>
		</div>
	</div>
</div>
<div class="row-fluid">
	<div class="span3">
		<label for="input_scope">Navigationsbereich</label>
		<select name="scope" class="span12" id="input_scope">'.$optScope.'</select>
	</div>
	<div class="span1">
		<label for="input_title">Rang</label>
		<input type="text" name="rank" id="input_rank" class="span12 numeric" value="'.htmlentities( $page->rank, ENT_QUOTES, 'UTF-8' ).'" required/>
	</div>
	<div class="span3">
		'.$inputParent.'
	</div>
	<div class="span3">
		<label for="input_type">Seitentyp</label>
		<select name="type" class="span12 optional-trigger" id="input_type" onchange="showOptionals(this);">'.$optType.'</select>
	</div>
	<div class="span2 optional type type-2">
		<label for="input_module">Modul</label>
		<input type="text" name="module" class="span12" id="input_module" value="'.htmlentities( $page->module, ENT_QUOTES, 'UTF-8' ).'"/>
	</div>
</div>';
?>
