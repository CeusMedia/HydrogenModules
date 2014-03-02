<?php

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

$optCompany	= "";
if( isset( $projectCompanies ) ){
	$optCompany	= array();
	foreach( $projectCompanies as $company )
		$optCompany[$company->companyId]	= $company->title;
	$optCompany	= UI_HTML_Elements::Options( $optCompany, $projectId );
}

$w			= (object) $words['edit'];
$buttonSave	= UI_HTML_Elements::Button( 'save', $words['edit']['buttonSave'], 'button add' );
if( !$canEdit )
	$buttonSave	= UI_HTML_Elements::Button( 'save', $words['edit']['buttonSave'], 'button add', NULL, TRUE );
$panelEdit	= '
<div class="content-panel content-panel-form">
	<h3>'.$w->heading.'</h3>
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
				<div class="span4">
					<label for="input_status" class="mandatory">'.$w->labelStatus.'</label>
					<select name="status" id="input_status" class="span12 max">'.$optStatus.'</select>
				</div>
				<div class="span8">
					<label for="input_url">'.$w->labelUrl.'</label>
					<input type="text" name="url" id="input_url" class="span12 max" value="'.htmlentities( $project->url, ENT_COMPAT, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="buttonbar">
				<a href="./manage/project" class="btn not-btn-small"><i class="icon-arrow-left"></i> '.$w->buttonCancel.'</a>
				<button type="submit" name="save" class="btn not-btn-small btn-success"><i class="icon-ok icon-white"></i> '.$w->buttonSave.'</button>	
			</div>
<!--			<li class="">
				<label for="input_companyId">Unternehmen</label>
				<select name="companyId" id="input_companyId" class="max">'.$optCompany.'</select>
			</li>-->
		</form>
	</div>
</div>';

$panelFilter	= $view->loadTemplateFile( 'manage/project/index.filter.php' );
$panelInfo		= $view->loadTemplateFile( 'manage/project/edit.info.php' );
$panelUsers		= $view->loadTemplateFile( 'manage/project/edit.users.php' );

return '
<div class="row-fluid">
	<div class="span3">
		'.$panelFilter.'
	</div>
	<div class="span6">
		'.$panelEdit.'
	</div>
	<div class="span3">
		'.$panelUsers.'
		'.$panelInfo.'
	</div>
</div>';
?>
