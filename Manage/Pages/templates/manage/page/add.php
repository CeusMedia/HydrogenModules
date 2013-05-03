<?php

$optType	= $words['types'];
$optScope	= $words['scopes'];
$optStatus	= $words['states'];

$listPages	= $this->renderTree( $tree, $page );

//  --  PAGE EDITOR  --  //
$optType		= UI_HTML_Elements::Options( $optType, $page->type );
$optScope		= UI_HTML_Elements::Options( $optScope, $page->scope );
$optStatus		= UI_HTML_Elements::Options( $optStatus, $page->status );
$optParent		= UI_HTML_Elements::Options( $parentMap, $page->parentId );

$inputParent	= '
	<div class="row-fluid">
		<label for="input_parentId">Unterseite von</label>
		<select name="parentId" class="span12" id="input_parentId">'.$optParent.'</select>
	</div>';
$inputContent	= '
			<label for="input_content">Inhalt</label>
	<textarea name="content" id="input_content" class="span12" rows="20">'.$page->content.'</textarea>
	';

$content	= '
<form action="./manage/page/add" method="post">
	<h4>Neue Seite</h4>
	<br/>
	<div class="row-fluid">
		<div class="span6">
			<label for="input_title">Titel</label>
			<input type="text" name="title" id="input_title" class="span12" value="'.htmlentities( $page->title, ENT_QUOTES, 'UTF-8' ).'" required/>
		</div>
		<div class="span5">
			<label for="input_identifier">Adresse</label>
			<div class="input-prepend">
				<span class="add-on">'.$path.'</span>
				<input type="text" name="identifier" class="span6" id="input_identifier" value="'.htmlentities( $page->identifier, ENT_QUOTES, 'UTF-8' ).'" required/>
			</div>
		</div>
	</div>
	<div class="row-fluid">
		<label for="input_type">Seitentyp</label>
		<select name="type" class="span6" id="input_type">'.$optType.'</select>
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
	</div>
	<div class="row-fluid">
		<div class="span4">
			<label for="input_status" class="muted">Sichtbarkeit</label>
			<select name="status" class="span12 disabled muted" disabled="disabled" id="input_status">'.$optStatus.'</select>
		</div>
	</div>
	<div class="buttonbar">
		<button type="submit" name="save" class="btn btn-small btn-success"><i class="icon-ok icon-white"></i> speichern</button>
	</div>
</form>
';

//  --  LAYOUT  --  //
return '
<div>
	<div id="manage-page-tree">
		<h4>Seiten</h4>
		'.$listPages.'
	</div>
	<div style="margin-left: 220px">
		<div style="float: left; width: 100%">
			'.$content.'
		</div>
	</div>
</div>
';

?>
