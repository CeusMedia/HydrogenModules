<?php

$optType	= $words['types'];
$optScope	= $words['scopes'];
$optStatus	= $words['states'];
$optFormat	= $words['formats'];

$optType		= UI_HTML_Elements::Options( $optType, $page->type );
$optScope		= UI_HTML_Elements::Options( $optScope, $page->scope );
$optStatus		= UI_HTML_Elements::Options( $optStatus, $page->status );
$optParent		= UI_HTML_Elements::Options( $parentMap, $page->parentId );
$optFormat		= UI_HTML_Elements::Options( $optFormat, $page->format );
$optModule		= UI_HTML_Elements::Options( array_combine( $modules, $modules ), $page->format );

return '
<div class="row-fluid">
	<div class="span12">
		<label for="input_identifier" class="mandatory required">Adresse</label>
		<div class="input-prepend">
			<span class="add-on"><small>'.$path.'</small></span>
			<input type="text" name="identifier" class="span6 mandatory required" id="input_identifier" value="'.htmlentities( $page->identifier, ENT_QUOTES, 'UTF-8' ).'" required="required"/>
		</div>
	</div>
</div>
<div class="row-fluid">
	<div class="span6">
		<label for="input_title">Titel</label>
		<input type="text" name="title" id="input_title" class="span12" value="'.htmlentities( $page->title, ENT_QUOTES, 'UTF-8' ).'" required/>
	</div>
	<div class="span3">
		<label for="input_status">Sichtbarkeit</label>
		<select name="status" class="span12" id="input_status">'.$optStatus.'</select>
	</div>
</div>
<div class="row-fluid">
	<div class="span5">
		<label for="input_scope">Navigationsbereich</label>
		<select name="scope" class="span12" id="input_scope">'.$optScope.'</select>
	</div>
	<div class="span1">
		<label for="input_title">Rang</label>
		<input type="text" name="rank" id="input_rank" class="span12 numeric" value="'.htmlentities( $page->rank, ENT_QUOTES, 'UTF-8' ).'" required/>
	</div>
	<div class="span6 optional type type-0 type-2">
		<label for="input_parentId">Unterseite von</label>
		<select name="parentId" class="span12" id="input_parentId">'.$optParent.'</select>
	</div>
</div>
<div class="row-fluid">
	<div class="span6">
		<label for="input_type">Seitentyp</label>
		<select name="type" class="span12 optional-trigger" id="input_type" onchange="showOptionals(this);">'.$optType.'</select>
	</div>
	<div class="span3 optional type type-0">
		<label for="input_format">Format</label>
		<select name="format" id="input_format" class="span12">'.$optFormat.'</select>
	</div>
	<div class="span6 optional type type-2">
		<label for="input_module">Modul</label>
		<select name="module" class="span12" id="input_module">'.$optModule.'</select>
	</div>
</div>';
?>
