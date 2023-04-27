<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

//print_m( $project );die;

/*  --  STATES  --  */
$optStatus	= [];
foreach( array_reverse( $words['states'], TRUE ) as $key => $value ){
	$attributes		= array(
		'value'		=> $key,
		'class'		=> 'project status'.$key,
		'selected'	=> ( $key == $project->status ? 'selected' : NULL )
	);
	$optStatus[]	= HtmlTag::create( 'option', $value, $attributes );
}
$optStatus		= join( '', $optStatus );

/*  --  PRIORITIES  --  */
$optPriority	= [];
foreach( $words['priorities'] as $key => $value ){
	$attributes		= array(
		'value'		=> $key,
		'class'		=> 'project priority'.$key,
		'selected'	=> ( $key == $project->priority ? 'selected' : NULL )
	);
	$optPriority[]	= HtmlTag::create( 'option', $value, $attributes );
}
$optPriority		= join( '', $optPriority );

$optCompany	= "";
if( isset( $projectCompanies ) ){
	$optCompany	= [];
	foreach( $projectCompanies as $company )
		$optCompany[$company->companyId]	= $company->title;
	$optCompany	= HtmlElements::Options( $optCompany, $projectId );
}

$optCreatorId	= [];
foreach( $projectUsers as $user )
	$optCreatorId[$user->userId]	= $user->username;
$optCreatorId	= HtmlElements::Options( $optCreatorId, $project->creatorId );

$w			= (object) $words['edit'];

$iconList		= HtmlTag::create( 'i', '', ['class' => 'not-icon-arrow-left icon-list'] );
$iconView		= HtmlTag::create( 'i', '', ['class' => 'icon-eye-open icon-white'] );
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'icon-ok icon-white'] );
$iconRemove		= HtmlTag::create( 'i', '', ['class' => 'icon-trash icon-white'] );
$iconDefault	= HtmlTag::create( 'i', '', ['class' => 'icon-star'] );
if( $env->getModules()->has( 'UI_Font_FontAwesome' ) ){
	$iconList		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-list'] );
	$iconView		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-eye'] );
	$iconSave		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );
	$iconRemove		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-trash'] );
	$iconDefault	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-star'] );
}


$buttonCancel	= HtmlTag::create( 'a', $iconList.' '.$w->buttonCancel, [
	'href'		=> './manage/project',
	'class'		=> 'btn btn-small'
] );
$buttonView		= HtmlTag::create( 'a', $iconView.' '.$w->buttonView, [
	'href'		=> './manage/project/view/'.$project->projectId,
	'class'		=> 'btn btn-small btn-info'
] );
$buttonSave		= HtmlTag::create( 'button', $iconSave.' '.$w->buttonSave, [
	'type'		=> 'submit',
	'name'		=> 'save',
	'class'		=> 'btn not-btn-success btn-primary',
	'disabled'	=> !$canEdit ? 'disabled' : NULL,
 ] );
$buttonRemove	= HtmlTag::create( 'a', $iconRemove.'&nbsp;'.$w->buttonRemove, [
	'href'		=> './manage/project/remove/'.$project->projectId,
	'class'		=> 'btn btn-small btn-danger',
	'disabled'	=> !$canRemove ? 'disabled' : NULL,
] );

$buttonDefault	= HtmlTag::create( 'a', $iconDefault.'&nbsp;'.$w->buttonDefault, [
	'href'		=> './manage/project/setDefault/'.$project->projectId,
	'class'		=> 'btn btn-small',
	'disabled'	=> $isDefault ? 'disabled' : NULL,
] );

$panelEdit	= '
<div class="content-panel content-panel-form">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<form name="" action="./manage/project/edit/'.$project->projectId.'" method="post">
			<div class="row-fluid">
				<div class="span12 autocut">
					<span class="muted">Projekt:</span> <big><strong>'.$project->title.'</strong></big>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span9">
					<label for="input_title" class="mandatory">'.$w->labelTitle.'</label>
					<input type="text" name="title" id="input_title" class="span12 max mandatory" value="'.htmlentities( $project->title, ENT_COMPAT, 'UTF-8' ).'"/>
				</div>
				<div class="span3">
					<label for="input_creatorId">'.$w->labelCreatorId.'</label>
					<select name="creatorId" id="input_creatorId" class="span12">'.$optCreatorId.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_description">'.$w->labelDescription.'</label>
					<textarea name="description" id="input_description" rows="6" class="span12 max CodeMirror-auto">'.htmlentities( $project->description, ENT_COMPAT, 'UTF-8' ).'</textarea>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span3">
					<label for="input_status" class="mandatory">'.$w->labelStatus.'</label>
					<select name="status" id="input_status" class="span12 max">'.$optStatus.'</select>
				</div>
				<div class="span3">
					<label for="input_priority" class="not-mandatory">'.$w->labelPriority.'</label>
					<select name="priority" id="input_priority" class="span12 max">'.$optPriority.'</select>
				</div>
				<div class="span6">
					<label for="input_url">'.$w->labelUrl.'</label>
					<input type="text" name="url" id="input_url" class="span12 max" value="'.htmlentities( $project->url, ENT_COMPAT, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="buttonbar clearfix">
				<div class="btn-toolbar">
					'.$buttonCancel.'
					'.$buttonView.'
					'.$buttonSave.'
					'.$buttonDefault.'
					'.$buttonRemove.'
				</div>
			</div>
<!--			<li class="">
				<label for="input_companyId">Unternehmen</label>
				<select name="companyId" id="input_companyId" class="max">'.$optCompany.'</select>
			</li>-->
		</form>
	</div>
</div>';

$panelInfo		= "";//$view->loadTemplateFile( 'manage/project/edit.info.php' );
$panelUsers		= $view->loadTemplateFile( 'manage/project/edit.users.php' );

return '
<div class="row-fluid">
	<div class="span8">
		'.$panelEdit.'
	</div>
	<div class="span4">
		'.$panelUsers.'
		'.$panelInfo.'
	</div>
</div>';
