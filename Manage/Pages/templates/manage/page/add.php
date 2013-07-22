<?php

$listPages	= $this->renderTree( $tree, $page );

$optType		= UI_HTML_Elements::Options( $words['types'], $page->type );
$optScope		= UI_HTML_Elements::Options( $words['scopes'], $scope );
$optStatus		= UI_HTML_Elements::Options( $words['states'], $page->status );
$optParent		= UI_HTML_Elements::Options( $parentMap, $page->parentId );

/*
$inputParent	= '
	<div class="row-fluid">
		<label for="input_parentId">Unterseite von</label>
		<select name="parentId" class="span12" id="input_parentId">'.$optParent.'</select>
	</div>';
*/

return '
<div class="row-fluid">
	<div id="manage-page-tree" class="span3">
		<div>
			<label for="input_scope">Navigationstyp</label>
			<select class="span10" name="scope" id="input_scope" onclick="document.location.href=\'./manage/page/setScope/\'+this.value;">'.$optScope.'</select>
		</div>
		'.$listPages.'
	</div>
	<div class="span9">
		<form action="./manage/page/add" method="post">
			<h4>Neue Seite</h4>
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
	</div>
</div>
';
?>
