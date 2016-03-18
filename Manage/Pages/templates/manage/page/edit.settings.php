<?php
$w				= (object) $words['edit'];

$optType	= $words['types'];
$optScope	= $words['scopes'];
$optStatus	= $words['states'];
$optFormat	= $words['formats'];

$optModule	= array( '' => '-' );
foreach( $controllers as $module )
	$optModule[$module]	= $module;

$optType		= UI_HTML_Elements::Options( $optType, $page->type );
$optScope		= UI_HTML_Elements::Options( $optScope, $page->scope );
$optStatus		= UI_HTML_Elements::Options( $optStatus, $page->status );
$optParent		= UI_HTML_Elements::Options( $parentMap, $page->parentId );
$optFormat		= UI_HTML_Elements::Options( $optFormat, $page->format );
$optModule		= UI_HTML_Elements::Options( $optModule, $page->module );

return '
<div class="content-panel content-panel-form">
	<div class="content-panel-inner">
		<form action="./manage/page/edit/'.$current.'/'.$version.'" method="post" class="cmFormChange-auto form-changes-auto">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_identifier" class="mandatory required">'.$w->labelIdentifier.'</label>
					<div class="input-prepend">
						<span class="add-on"><small>'.$path.'</small></span>
						<input type="text" name="identifier" class="span6 mandatory required" id="input_identifier" value="'.htmlentities( $page->identifier, ENT_QUOTES, 'UTF-8' ).'" required="required"/>
					</div>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span6">
					<label for="input_title">'.$w->labelTitle.'</label>
					<input type="text" name="title" id="input_title" class="span12" value="'.htmlentities( $page->title, ENT_QUOTES, 'UTF-8' ).'" required/>
				</div>
				<div class="span3">
					<label for="input_status">'.$w->labelStatus.'</label>
					<select name="status" class="span12" id="input_status">'.$optStatus.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span5">
					<label for="input_scope">'.$w->labelScope.'</label>
					<select name="scope" class="span12" id="input_scope">'.$optScope.'</select>
				</div>
				<div class="span1">
					<label for="input_title">'.$w->labelRank.'</label>
					<input type="text" name="rank" id="input_rank" class="span12 numeric" value="'.htmlentities( $page->rank, ENT_QUOTES, 'UTF-8' ).'" required/>
				</div>
				<div class="span6 optional type type-0 type-2" style="display: none">
					<label for="input_parentId">'.$w->labelParentId.'</label>
					<select name="parentId" class="span12" id="input_parentId">'.$optParent.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span6">
					<label for="input_type">'.$w->labelType.'</label>
					<select name="type" class="span12 optional-trigger has-optionals" id="input_type" data-onchange="showOptionals(this);">'.$optType.'</select>
				</div>
				<div class="span3 optional type type-0" style="display: none">
					<label for="input_format">'.$w->labelFormat.'</label>
					<select name="format" id="input_format" class="span12">'.$optFormat.'</select>
				</div>
				<div class="span6 optional type type-2" style="display: none">
					<label for="input_module">'.$w->labelModule.'</label>
			<!--		<input type="text" name="module" class="span12" id="input_module" value="'.$page->module.'"/>-->
					<select name="module" class="span12" id="input_module">'.$optModule.'</select>
				</div>
			</div>
			<div class="buttonbar">
				<button type="submit" name="save" class="btn btn-primary"><i class="icon-ok icon-white"></i> '.$w->buttonSave.'</button>
				<button type="reset" class="btn btn-small">'.$w->buttonReset.'</button>
				<a href="./manage/page/copy/'.$current.'" class="btn btn-small">'.$w->buttonCopy.'</a>
				<button type="button" class="btn btn-small btn-danger" onclick="if(!confirm(\''.$w->buttonRemove_confirm.'\')) return false; document.location.href = \'./manage/page/remove/'.$page->pageId.'\';"><i class="icon-trash icon-white"></i> '.$w->buttonRemove.'</button>
			</div>
		</form>
	</div>
</div>
';
?>
