<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var View_Manage_Role $view */
/** @var array<string,array<string|int,string|int>> $words */
/** @var object $role */

$optAccess	= [];
foreach( $words['type-access'] as $key => $label ){
	$selected	= $key == $role->get( 'access' );
	$class		= 'role-access access'.$role->get( 'access' );
	$optAccess[]	= HtmlElements::Option( (string) $key, $label, $selected, FALSE, $class );
}
$optAccess	= join( $optAccess );

$optRegister	= [];
foreach( $words['type-register'] as $key => $label ){
	$selected	= $key == $role->get( 'register' );
	$class		= 'role-register register'.$role->get( 'register' );
	$optRegister[]	= HtmlElements::Option( (string) $key, $label, $selected, FALSE, $class );
}
$optRegister	= join( $optRegister );

extract( $view->populateTexts( ['add.top', 'add.bottom', 'add.right'], 'html/manage/role/' ) );

$iconCancel		= HtmlTag::create( 'b', '', ['class' => 'fa fa-fw fa-arrow-left'] );
$iconSave		= HtmlTag::create( 'b', '', ['class' => 'fa fa-fw fa-check'] );

$panelAdd	= '
<div class="content-panel content-panel-form">
	<h3>'.$words['add']['heading'].'</h3>
	<div class="content-panel-inner">
		<form name="addRole" action="./manage/role/add" method="post">
			<div class="row-fluid">
				<div class="span6">
					<label for="title">'.$words['add']['labelTitle'].'</label>
					'.HtmlElements::Input( 'title', $role->get( 'title' ), 'span12' ).'
				</div>
				<div class="span3">
					<label for="access">'.$words['add']['labelAccess'].'</label>
					'.HtmlElements::Select( 'access', $optAccess, 'span12' ).'
				</div>
				<div class="span3">
					<label for="register">'.$words['add']['labelRegister'].'</label>
					'.HtmlElements::Select( 'register', $optRegister, 'span12' ).'
				</div>
			</div>
			<div class="row-fluid">
				<div class="span">
					<label for="description">'.$words['add']['labelDescription'].'</label>
					'.HtmlTag::create( 'textarea', $role->get( 'description' ), ['class' => 'span12', 'name' => 'description', 'rows' => 4] ).'
				</div>
			</div>
			<div class="buttonbar">
				<div class="btn-toolbar">
					'.HtmlElements::LinkButton( './manage/role', $iconCancel.' '.$words['add']['buttonCancel'], 'btn btn-small' ).'
					'.HtmlElements::Button( 'saveRole',$iconSave.' '. $words['add']['buttonSave'], 'btn btn-primary' ).'
				</div>
			</div>
		</form>
	</div>
</div>';

return $textAddTop.'
<div class="row-fluid">
	<div class="span9">
		'.$panelAdd.'
	</div>
	<div class="span3">
		'.$textAddRight.'
	</div>
</div>
'.$textAddBottom;
