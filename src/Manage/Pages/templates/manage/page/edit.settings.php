<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;

$w				= (object) $words['edit'];

$optController	= ['' => '-'];
foreach( $controllers as $item )
	$optController[$item]	= $item;

$optType		= HtmlElements::Options( $words['types'], $page->type );
$optScope		= HtmlElements::Options( $words['scopes'], $page->scope );
$optStatus		= HtmlElements::Options( $words['states'], $page->status );
$optParent		= HtmlElements::Options( $parentMap, $page->parentId );
$optFormat		= HtmlElements::Options( $words['formats'], $page->format );
$optController	= HtmlElements::Options( $optController, $page->controller );
$optTemplate	= HtmlElements::Options( $masterTemplates, $page->template );

$fieldAccess	= '';
if( $useAuth ){
	$optAccess		= HtmlElements::Options( $words['accesses'], $page->access );
	$fieldAccess	= '
		<div class="span4">
			<label for="input_page_access">'.$w->labelAccess.'</label>
			<select name="page_access" class="span12 optional-trigger has-optionals" id="input_page_access">'.$optAccess.'</select>
		</div>';
}

$icon	= '<i class="fa fa-fw fa-times fa-5x fa-pull-left fa-border" aria-hidden="true" style="margin: 18px 10px 16px 0; opacity: 0.75; color: rgba(127, 127, 127, 0.25)"></i>';
if( $page->icon ){
	$icon	= '<i class="fa fa-fw '.$page->icon.' fa-5x fa-pull-left fa-border" aria-hidden="true" style="margin: 18px 10px 16px 0"></i>';
}

$path	= preg_replace( '@^(https?://)(.+)$@', '<small class="muted">\\1</small><strong>\\2</strong>', $path );

$isWritable	= $source === 'Database';		//  not writable for 'Config' or 'Modules'
$hints		= [];
if( !$isWritable )
	$hints[]	= '<div class="alert alert-warning">Pages of source "'.$source.'" are not writable right now.</div>';
$hints	= join( $hints );

return $hints.'
<div class="content-panel content-panel-form">
<!--	<h3><span class="muted">Seite:</span> '.$page->title.'</h3>-->
	<div class="content-panel-inner">
		<form action="./manage/page/edit/'.$current.'/'.$version.'" method="post" class="cmFormChange-auto form-changes-auto">
			<h4>'.$w->sectionSettingsAppearance.'</h4>
			<div style="display: flex">
				<div style="width: 160px;">
					'.$icon.'
				</div>
				<div style="flex: 1;">
					<div class="row-fluid">
						<div class="span5">
							<label for="input_page_title">'.$w->labelTitle.'</label>
							<big><input type="text" name="page_title" id="input_page_title" class="span12" value="'.htmlentities( $page->title, ENT_QUOTES, 'UTF-8' ).'" required/></big>
						</div>
						<div class="span4">
							<label for="input_page_icon">'.$w->labelIcon.'</label>
							<input type="text" name="page_icon" id="input_page_icon" class="span12" value="'.htmlentities( $page->icon, ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_page_identifier" class="mandatory required">'.$w->labelIdentifier.'</label>
							<div class="input-prepend">
								<span class="add-on"><small>'.$path.'</small></span>
								<input type="text" name="page_identifier" class="span8 mandatory required" id="input_page_identifier" value="'.htmlentities( $page->identifier, ENT_QUOTES, 'UTF-8' ).'" required="required"/>
							</div>
						</div>
					</div>
				</div>
			</div>
			<h4>'.$w->sectionSettingsPosition.'</h4>
			<div class="row-fluid">
				<div class="span5">
					<label for="input_page_scope">'.$w->labelScope.'</label>
					<select name="page_scope" class="span12" id="input_page_scope">'.$optScope.'</select>
				</div>
				<div class="span5 optional page_type page_type-0 page_type-2" style="display: none">
					<label for="input_page_parentId">'.$w->labelParentId.'</label>
					<select name="page_parentId" class="span12" id="input_page_parentId">'.$optParent.'</select>
				</div>
				<div class="span2">
					<label for="input_page_rank">'.$w->labelRank.'</label>
					<input type="text" name="page_rank" id="input_page_rank" class="span12 numeric" value="'.htmlentities( $page->rank, ENT_QUOTES, 'UTF-8' ).'" required/>
				</div>
			</div>
			<h4>'.$w->sectionSettingsContent.'</h4>
			<div class="row-fluid">
				<div class="span4">
					<label for="input_page_type">'.$w->labelType.'</label>
					<select name="page_type" class="span12 optional-trigger has-optionals" id="input_page_type" data-onchange="showOptionals(this);">'.$optType.'</select>
				</div>
				<div class="span3 optional page_type page_type-0 page_type-2" style="display: none">
					<label for="input_page_template">'.$w->labelTemplate.'</label>
					<select name="page_template" class="span12" id="input_page_template">'.$optTemplate.'</select>
				</div>
				<div class="span2 optional page_type page_type-0" style="display: none">
					<label for="input_page_format">'.$w->labelFormat.'</label>
					<select name="page_format" id="input_page_format" class="span12">'.$optFormat.'</select>
				</div>
				<div class="span3 optional page_type page_type-2" style="display: none">
					<label for="input_page_controller">'.$w->labelController.'</label>
					<select name="page_controller" class="span12" id="input_page_controller">'.$optController.'</select>
				</div>
				<div class="span2 optional page_type page_type-2" style="display: none">
					<label for="input_page_action">'.$w->labelAction.'</label>
					<input type="text" name="page_action" class="span12" id="input_page_action" value="'.htmlentities( $page->action, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<h4>'.$w->sectionSettingsAccess.'</h4>
			<div class="row-fluid">
				<div class="span3">
					<label for="input_page_status">'.$w->labelStatus.'</label>
					<select name="page_status" class="span12" id="input_page_status">'.$optStatus.'</select>
				</div>
				'.$fieldAccess.'
			</div>
			<div class="buttonbar" style="'.( !$isWritable ? 'display: none' : '' ).'">
				<button type="submit" name="save" class="btn btn-primary"><i class="icon-ok icon-white"></i> '.$w->buttonSave.'</button>
				<button type="reset" class="btn btn-small">'.$w->buttonReset.'</button>
				<a href="./manage/page/copy/'.$current.'" class="btn btn-small">'.$w->buttonCopy.'</a>
				<button type="button" class="btn btn-small btn-danger" onclick="if(!confirm(\''.$w->buttonRemove_confirm.'\')) return false; document.location.href = \'./manage/page/remove/'.$page->pageId.'\';"><i class="icon-trash icon-white"></i> '.$w->buttonRemove.'</button>
			</div>
		</form>
	</div>
</div>
';
