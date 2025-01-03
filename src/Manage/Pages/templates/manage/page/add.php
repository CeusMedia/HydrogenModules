<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var View_Manage_Page $view */
/** @var array<string,array<string,string>> $words */
/** @var string $path */
/** @var Entity_Page $page */
/** @var array<string> $controllers */
/** @var bool $useAuth */
/** @var array<int|string,string> $parentMap */
/** @var array<string,string> $masterTemplates */
/** @var ?int $scope */

$w				= (object) $words['add'];

$iconAdd		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-plus'] );
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );

$optController		= ['' => '-'];
foreach( $controllers as $item )
	$optController[$item]	= $item;

$optType		= HtmlElements::Options( $words['types'], $page->type );
$optScope		= HtmlElements::Options( $words['scopes'], $scope );
$optStatus		= HtmlElements::Options( $words['states'], $page->status );
$optFormat		= HtmlElements::Options( $words['formats'], $page->format );
$optParent		= HtmlElements::Options( $parentMap, $page->parentId );
$optController	= HtmlElements::Options( $optController, $page->controller );
$optTemplate	= HtmlElements::Options( $masterTemplates, $page->template );

$colAccess		= '';
$fieldAccess	= '';
if( $useAuth ){
	$optAccess		= HtmlElements::Options( $words['accesses'], $page->access );
	$colAccess	= '
		<div class="span4">
			<label for="input_page_access">'.$w->labelAccess.'</label>
			<select name="page_access" class="span12 optional-trigger has-optionals" id="input_page_access">'.$optAccess.'</select>
		</div>';
}


$panelTree	= $view->loadTemplateFile( 'manage/page/tree.php' );

return '
<div class="row-fluid">
	<div id="manage-page-tree" class="span3">
		'.$panelTree.'
	</div>
	<div class="span9">
		<div class="content-panel" id="panel-page-add">
			<h3>'.$w->heading.'</h3>
			<div class="content-panel-inner">
				<form action="./manage/page/add" method="post" class="cmFormChange-auto">
					<h4>Erscheinungsbild</h4>
					<div class="row-fluid">
						<div class="span6">
							<label for="input_page_title" class="mandatory required">'.$w->labelTitle.'</label>
							<input type="text" name="page_title" id="input_page_title" class="span12" required="required" value="'.htmlentities( $page->title, ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
						<div class="span3">
							<label for="input_page_icon">'.$w->labelIcon.'</label>
							<input type="text" name="page_icon" id="input_page_icon" class="span12" value="'.htmlentities( $page->icon, ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_page_identifier" class="mandatory required">'.$w->labelIdentifier.'</label>
							<div class="input-prepend">
								<span class="add-on"><small>'.$path.'</small></span>
								<input type="text" name="page_identifier" class="span6 mandatory required" id="input_page_identifier" required="required" value="'.htmlentities( $page->identifier ?? '', ENT_QUOTES, 'UTF-8' ).'"/>
							</div>
						</div>
					</div>
<!--					<hr/>-->
					<h4>Einordnung</h4>
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
							<label for="input_page_title">'.$w->labelRank.'</label>
							<input type="text" name="page_rank" id="input_page_rank" class="span12 numeric" value="'.htmlentities( $page->rank, ENT_QUOTES, 'UTF-8' ).'" required/>
						</div>
					</div>
<!--					<hr/>-->
					<h4>Inhalt</h4>
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
<!--					<hr/>-->
					<h4>Erreichbarkeit</h4>
					<div class="row-fluid">
						<div class="span3">
							<label for="input_page_status" class="muted">'.$w->labelStatus.'</label>
							<select name="page_status" class="span12 muted" id="input_page_status">'.$optStatus.'</select>
						</div>
						'.$colAccess.'
					</div>
					<div class="buttonbar">
						<button type="submit" name="save" class="btn btn-small btn-primary">'.$iconSave.' '.$w->buttonSave.'</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>';
