<?php

$listPages		= $this->renderTree( $tree, $page );

$optModule		= array( '' => '-' );
foreach( $modules as $module )
	$optModule[$module]	= $module;

$optType		= UI_HTML_Elements::Options( $words['types'], $page->type );
$optScope		= UI_HTML_Elements::Options( $words['scopes'], $scope );
$optStatus		= UI_HTML_Elements::Options( $words['states'], $page->status );
$optFormat		= UI_HTML_Elements::Options( $words['formats'], $page->format );
$optParent		= UI_HTML_Elements::Options( $parentMap, $page->parentId );
$optModule		= UI_HTML_Elements::Options( $optModule, $page->module );

$iconAdd		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-plus icon-white' ) );

return '
<div class="row-fluid">
	<div id="manage-page-tree" class="span3">
		<div>
			<label for="input_scope">Navigationstyp</label>
<!--			<a href="./manage/page/add" class="btn btn-mini btn-primary pull-right">'.$iconAdd.'</a>-->
			<select class="span10" name="scope" id="input_scope" onclick="document.location.href=\'./manage/page/setScope/\'+this.value;">'.$optScope.'</select>
		</div>
		'.$listPages.'
	</div>
	<div class="span9">
		<form action="./manage/page/add" method="post" class="cmFormChange-auto">
			<h4>Neue Seite</h4>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_identifier">Adresse</label>
					<div class="input-prepend">
						<span class="add-on"><small>'.$path.'</small></span>
						<input type="text" name="identifier" class="span6 mandatory required" id="input_identifier" value="'.htmlentities( $page->identifier, ENT_QUOTES, 'UTF-8' ).'" required="required"/>
					</div>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span6">
					<label for="input_title">Titel</label>
					<input type="text" name="title" id="input_title" class="span12" value="'.htmlentities( $page->title, ENT_QUOTES, 'UTF-8' ).'" required="required"/>
				</div>
				<div class="span3">
					<label for="input_status" class="muted">Sichtbarkeit</label>
					<select name="status" class="span12 disabled muted" disabled="disabled" id="input_status">'.$optStatus.'</select>
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
			</div>
			<div class="buttonbar">
				<button type="submit" name="save" class="btn btn-small btn-success"><i class="icon-ok icon-white"></i> speichern</button>
			</div>
		</form>
	</div>
</div>
<script>
$(document).ready(function(){
	$("form .optional-trigger").trigger("change");
});
</script>

';
?>
