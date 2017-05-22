<?php
$w				= (object) $words['edit'];

$optType	= $words['types'];
$optScope	= $words['scopes'];
$optStatus	= $words['states'];
$optFormat	= $words['formats'];

$optController	= array( '' => '-' );
foreach( $controllers as $item )
	$optController[$item]	= $item;

$optType		= UI_HTML_Elements::Options( $optType, $page->type );
$optScope		= UI_HTML_Elements::Options( $optScope, $page->scope );
$optStatus		= UI_HTML_Elements::Options( $optStatus, $page->status );
$optParent		= UI_HTML_Elements::Options( $parentMap, $page->parentId );
$optFormat		= UI_HTML_Elements::Options( $optFormat, $page->format );
$optController	= UI_HTML_Elements::Options( $optController, $page->controller );

return '
<div class="content-panel content-panel-form">
	<div class="content-panel-inner">
		<form action="./manage/page/edit/'.$current.'/'.$version.'" method="post" class="cmFormChange-auto form-changes-auto">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_page_identifier" class="mandatory required">'.$w->labelIdentifier.'</label>
					<div class="input-prepend">
						<span class="add-on"><small>'.$path.'</small></span>
						<input type="text" name="page_identifier" class="span6 mandatory required" id="input_page_identifier" value="'.htmlentities( $page->identifier, ENT_QUOTES, 'UTF-8' ).'" required="required"/>
					</div>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span6">
					<label for="input_page_title">'.$w->labelTitle.'</label>
					<input type="text" name="page_title" id="input_page_title" class="span12" value="'.htmlentities( $page->title, ENT_QUOTES, 'UTF-8' ).'" required/>
				</div>
				<div class="span3">
					<label for="input_page_status">'.$w->labelStatus.'</label>
					<select name="page_status" class="span12" id="input_page_status">'.$optStatus.'</select>
				</div>
				<div class="span3">
					<label for="input_page_icon">'.$w->labelIcon.'</label>
					<input type="text" name="page_icon" id="input_page_icon" class="span12" value="'.htmlentities( $page->icon, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span5">
					<label for="input_page_scope">'.$w->labelScope.'</label>
					<select name="page_scope" class="span12" id="input_page_scope">'.$optScope.'</select>
				</div>
				<div class="span1">
					<label for="input_page_rank">'.$w->labelRank.'</label>
					<input type="text" name="page_rank" id="input_page_rank" class="span12 numeric" value="'.htmlentities( $page->rank, ENT_QUOTES, 'UTF-8' ).'" required/>
				</div>
				<div class="span6 optional page_type page_type-0 page_type-2" style="display: none">
					<label for="input_page_parentId">'.$w->labelParentId.'</label>
					<select name="page_parentId" class="span12" id="input_page_parentId">'.$optParent.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span5">
					<label for="input_page_type">'.$w->labelType.'</label>
					<select name="page_type" class="span12 optional-trigger has-optionals" id="input_page_type" data-onchange="showOptionals(this);">'.$optType.'</select>
				</div>
				<div class="span3 optional page_type page_type-0" style="display: none">
					<label for="input_page_format">'.$w->labelFormat.'</label>
					<select name="page_format" id="input_page_format" class="span12">'.$optFormat.'</select>
				</div>
				<div class="span4 optional page_type page_type-2" style="display: none">
					<label for="input_page_controller">'.$w->labelController.'</label>
					<select name="page_controller" class="span12" id="input_page_controller">'.$optController.'</select>
				</div>
				<div class="span3 optional page_type page_type-2" style="display: none">
					<label for="input_page_action">'.$w->labelAction.'</label>
					<input type="text" name="page_action" class="span12" id="input_page_action" value="'.htmlentities( $page->action, ENT_QUOTES, 'UTF-8' ).'"/>
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
