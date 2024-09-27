<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var View_Manage_Role $view */
/** @var array<string,array<string|int,string|int>> $words */
/** @var object $role */
/** @var int $userCount */

$optAccess	= [];
foreach( $words['type-access'] as $key => $label ){
	$selected		= $key == $role->access;
	$class			= 'role-access access'.$key;
	$optAccess[]	= HtmlElements::Option( (string) $key, $label, $selected, FALSE, $class );
}
$optAccess	= join( $optAccess );

$optRegister	= [];
foreach( $words['type-register'] as $key => $label ){
	$selected		= $key == $role->register;
	$class			= 'role-register register'.$key;
	$optRegister[]	= HtmlElements::Option( (string) $key, $label, $selected, FALSE, $class );
}
$optRegister	= join( $optRegister );

$panelEdit	= '
<div class="content-panel content-panel-form">
	<h3>'.$words['edit']['heading'].'</h3>
	<div class="content-panel-inner">
		<form name="editRole" action="./manage/role/edit/'.$role->roleId.'" method="post">
			<div class="row-fluid">
				<div class="span6">
					<label for="title">'.$words['edit']['labelTitle'].'</label>
					'.HtmlElements::Input( 'title', $role->title, 'span12' ).'
				</div>
				<div class="span3">
					<label for="access">'.$words['edit']['labelAccess'].'</label>
					'.HtmlElements::Select( 'access', $optAccess , 'span12' ).'
				</div>
				<div class="span3">
					<label for="register">'.$words['edit']['labelRegister'].'</label>
					'.HtmlElements::Select( 'register', $optRegister , 'span12' ).'
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="description">'.$words['edit']['labelDescription'].'</label>
			<!--		'.HtmlElements::Textarea( 'description', $role->description, 'xl-l' ).'-->
					'.HtmlTag::create( 'textarea', $role->description, ['class' => 'span12', 'name' => 'description', 'rows' => 4] ).'
				</div>
			</div>
			<div class="buttonbar">
				<div class="btn-toolbar">
					'.HtmlElements::LinkButton( './manage/role', '<i class="fa fa-fw fa-arrow-left"></i> '.$words['edit']['buttonCancel'], 'btn btn-small' ).'
					'.HtmlElements::Button( 'saveRole', '<i class="fa fa-fw fa-check"></i> '.$words['edit']['buttonSave'], 'btn btn-primary' ).'
					&nbsp;&nbsp;|&nbsp;&nbsp;
					'.HtmlElements::LinkButton( './manage/role/remove/'.$role->roleId, '<i class="fa fa-fw fa-remove"></i> '.$words['edit']['buttonRemove'], 'btn btn-small btn-danger', 'Wirklich?' ).'
					&nbsp;&nbsp;|&nbsp;&nbsp;
					'.HtmlElements::LinkButton( './manage/user/add?roleId='.$role->roleId, '<i class="fa fa-fw fa-plus"></i> '.$words['edit']['buttonAddUser'], 'btn btn-info btn-small' ).'
					'.HtmlElements::LinkButton( './manage/user/filter?roleId='.$role->roleId, '<i class="fa fa-fw fa-magnifying-class"></i> '.$words['edit']['buttonFilter'], 'btn btn-small' ).'
				</div>
			</div>
		</form>
	</div>
</div>
';

$panelRights	= $view->loadTemplateFile( 'manage/role/edit.rights.php' );
//$panelInfo		= $view->loadTemplateFile( 'manage/role/edit.info.php' );

$w				= (object) $words['info'];
$helperTime	= new View_Helper_TimePhraser( $env );
$createdAt		= $helperTime->convert( $role->createdAt, TRUE, $w->timePhrasePrefix, $w->timePhraseSuffix );
$modifiedAt		= $role->modifiedAt ? 'vor '.$helperTime->convert( $role->modifiedAt, TRUE ) : '-';
$panelInfo		= '
<div class="content-panel content-panel-info">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<dl class="not-dl-horizontal">
			<dt>'.$w->labelUserCount.'</dt>
			<dd>'.$userCount.'</dd>
			<dt>'.$w->labelCreatedAt.'</dt>
			<dd>'.$createdAt.'</dd>
			<dt>'.$w->labelModifiedAt.'</dt>
			<dd>'.$modifiedAt.'</dd>
		</dl>
	</div>
</div>';

extract( $view->populateTexts( ['index.top', 'index.bottom'], 'html/manage/role/' ) );

return $textIndexTop.'
<!--<h2><span class="muted">Rolle</span> '.$role->title.'</h2>-->
<div class="row-fluid">
	<div class="span9">
		'.$panelEdit.'
	</div>
	<div class="span3">
		'.$panelInfo.'
	</div>
</div>
<div class="row-fluid">
	<div class="span12">
		'.$panelRights.'
	</div>
</div>'.$textIndexBottom;
