<?php

$optType	= $words['types'];
$optScope	= $words['scopes'];
$optStatus	= $words['states'];
$optEditor	= $words['editors'];

$listPages	= $this->renderTree( $tree, $page );

//  --  PAGE EDITOR  --  //
$content	= "";
if( $current ){
	$optType		= UI_HTML_Elements::Options( $optType, $page->type );
	$optScope		= UI_HTML_Elements::Options( $optScope, $page->scope );
	$optStatus		= UI_HTML_Elements::Options( $optStatus, $page->status );
	$optEditor		= UI_HTML_Elements::Options( $optEditor, $editor );

	$inputParent	= "";
	if( $page->type == 0 || $page->type == 2 ){
		$optParent		= UI_HTML_Elements::Options( $parentMap, $page->parentId );
		$inputParent	= '
		<label for="input_parentId">Unterseite von</label>
		<select name="parentId" class="span12" id="input_parentId">'.$optParent.'</select>';
	}
	$inputEditor	= '<div><small class="muted"><em>'.$words['edit']['no_editor'].'</em></small></div><br/>';
	if( $page->type == 0 ){
		$inputEditor	= '
		<div class="row-fluid">
			<div class="span3">
				<div class="input-prepend">
					<span class="add-on">'.$words['edit']['labelEditor'].'</span>
					<select class="span12" name="editor" id="input_editor" onchange="PageEditor.setEditor(this)">'.$optEditor.'</select>
				</div>
			</div>
		</div>
		<div class="row-fluid">
			<textarea name="content" id="input_content" class="span12" rows="20">'.htmlentities( $page->content, ENT_QUOTES, 'UTF-8' ).'</textarea>
		</div>';
	}

	
$tabs	= array(
	'Einstellungen',
	'HTML-Inhalt',
	'Suchmaschinenfutter'
);

$listTabs		= array();
foreach( $tabs as $nr => $label ){
	$attributes	= array( 'href' => '#tab'.++$nr, 'data-toggle' => 'tab' );
	$link		= UI_HTML_Tag::create( 'a', $label, $attributes );
	$attributes	= array( 'id' => 'page-editor-tab-'.$nr, 'class' => $nr == $tab ? "active" : NULL );
	$listTabs[]	= UI_HTML_Tag::create( 'li', $link, $attributes );
}
$listTabs	= UI_HTML_Tag::create( 'ul', $listTabs, array( 'class' => "nav nav-tabs" ) );

	$content	= '
<form action="./manage/page/edit/'.$current.'" method="post">
	<div class="tabbable" id="tabs-page-editor"> <!-- Only required for left/right tabs -->
		'.$listTabs.'
		<div class="tab-content">
			<div class="tab-pane" id="tab1">
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
				</div>
			</div>
			<div class="tab-pane" id="tab2">
				'.$inputEditor.'
			</div>
			<div class="tab-pane" id="tab3">
				<div><small class="muted"><em>Kommt noch...</em></small></div><br/>
			</div>
		</div>
		<div class="buttonbar">
			<button type="submit" name="save" class="btn btn-small btn-success"><i class="icon-ok icon-white"></i> speichern</button>
		</div>
	</div>
</form>
<script>
var pageType = '.(int) $page->type.';
$(document).ready(function(){
	PageEditor.editor = "'.$editor.'";
	PageEditor.init();
})
</script>
';
}

//  --  LAYOUT  --  //
return '
<div>
	<div id="manage-page-tree">
		<h4>Seiten</h4>
		'.$listPages.'
<!--		<button type="button" onclick="document.location.href=\'./manage/page/add\';" class="btn btn-small btn-info"><i class="icon-plus icon-white"></i> neue Seite</button>-->
	</div>
	<div style="margin-left: 220px">
		<div style="float: left; width: 100%">
			'.$content.'
		</div>
	</div>
</div>
';
?>
