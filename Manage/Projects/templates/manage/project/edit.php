<?php

//print_m( $project );die;

/*  --  STATES  --  */
$optStatus	= array();
foreach( array_reverse( $words['states'], TRUE ) as $key => $value ){
	$attributes		= array(
		'value'		=> $key,
		'class'		=> 'project status'.$key,
		'selected'	=> ( $key == $project->status ? 'selected' : NULL )
	);
	$optStatus[]	= UI_HTML_Tag::create( 'option', $value, $attributes );
}
$optStatus		= join( '', $optStatus );

/*  --  PRIORITIES  --  */
$optPriority	= array();
foreach( $words['priorities'] as $key => $value ){
	$attributes		= array(
		'value'		=> $key,
		'class'		=> 'project priority'.$key,
		'selected'	=> ( $key == $project->priority ? 'selected' : NULL )
	);
	$optPriority[]	= UI_HTML_Tag::create( 'option', $value, $attributes );
}
$optPriority		= join( '', $optPriority );

$optCompany	= "";
if( isset( $projectCompanies ) ){
	$optCompany	= array();
	foreach( $projectCompanies as $company )
		$optCompany[$company->companyId]	= $company->title;
	$optCompany	= UI_HTML_Elements::Options( $optCompany, $projectId );
}

$w			= (object) $words['edit'];

$iconList		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'not-icon-arrow-left icon-list' ) );
$iconView		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-eye-open icon-white' ) );
$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-ok icon-white' ) );
$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-trash icon-white' ) );
$iconDefault	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-star' ) );
if( $env->getModules()->has( 'UI_Font_FontAwesome' ) ){
	$iconList		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-list' ) );
	$iconView		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-eye' ) );
	$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );
	$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-trash' ) );
	$iconDefault	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-star' ) );
}


$buttonCancel	= UI_HTML_Tag::create( 'a', $iconList.' '.$w->buttonCancel, array(
	'href'		=> './manage/project',
	'class'		=> 'btn btn-small'
) );
$buttonView		= UI_HTML_Tag::create( 'a', $iconView.' '.$w->buttonView, array(
	'href'		=> './manage/project/view/'.$project->projectId,
	'class'		=> 'btn btn-small btn-info'
) );
$buttonSave		= UI_HTML_Tag::create( 'button', $iconSave.' '.$w->buttonSave, array(
	'type'		=> 'submit',
	'name'		=> 'save',
	'class'		=> 'btn not-btn-success btn-primary',
	'disabled'	=> !$canEdit ? 'disabled' : NULL,
 ) );
$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove.'&nbsp;'.$w->buttonRemove, array(
	'href'		=> './manage/project/remove/'.$project->projectId,
	'class'		=> 'btn btn-small btn-danger',
	'disabled'	=> !$canRemove ? 'disabled' : NULL,
) );

$buttonDefault	= UI_HTML_Tag::create( 'a', $iconDefault.'&nbsp;'.$w->buttonDefault, array(
	'href'		=> './manage/project/setDefault/'.$project->projectId,
	'class'		=> 'btn btn-small',
	'disabled'	=> $isDefault ? 'disabled' : NULL,
) );

$panelEdit	= '
<div class="content-panel content-panel-form">
	<h3 class="autocut"><a href="./manage/project" class="muted">'.$w->heading.':</a> '.$project->title.'</h3>
	<div class="content-panel-inner">
		<form name="" action="./manage/project/edit/'.$project->projectId.'" method="post">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_title" class="mandatory">'.$w->labelTitle.'</label>
					<input type="text" name="title" id="input_title" class="span12 max mandatory" value="'.htmlentities( $project->title, ENT_COMPAT, 'UTF-8' ).'"/>
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
?>
