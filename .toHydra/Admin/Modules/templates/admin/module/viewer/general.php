<?php

$w			= (object) $words['view'];
$sources	= array();
$model		= new Model_ModuleSource( $this->env );
$sources	= $model->getAll();

$icon	= $module->icon ? UI_HTML_Tag::create( 'img', NULL, array(
	'src'	=> $module->icon,
	'style'	=> array(
		'min-width'		=> '64px',
		'min-height'	=> '64px',
		'max-width'		=> '128px',
		'max-height'	=> '128px'
) ) ) : '';

$desc	= trim( $module->description );
$desc	= strlen( $desc ) ? View_Helper_ContentConverter::render( $env, $desc ).'<br/>' : '';

$facts	= array(
	array(),
	array(),
	array(),
);

if( $module->authors ){
	$authors	= array();
	foreach( $module->authors as $author){
		$label	= $author->name;
		if( $author->email )
			$label	= UI_HTML_Tag::create( 'a', $label, array( 'href' => 'mailto:'.$author->email ) );
		else if( $author->site )
			$label	= UI_HTML_Tag::create( 'a', $label, array( 'href' => $author->site ) );
		$authors[]	= UI_HTML_Tag::create( 'dd', $label );
	}
	$label	= count( $authors ) > 1 ? $w->labelAuthors : $w->labelAuthor;
	array_unshift( $facts[0], UI_HTML_Tag::create( 'dt', $label ).join( $authors ) );
}
if( $module->companies ){
	$companies	= array();
	foreach( $module->companies as $company){
		$label	= $company->name;
		if( $company->site )
			$label	= UI_HTML_Tag::create( 'a', $label, array( 'href' => $company->site ) );
		$companies[]	= $label;
	}
	$label	= count( $companies ) > 1 ? $w->labelCompanies : $w->labelCompany;
	$item	= UI_HTML_Tag::create( 'dt', $label ).UI_HTML_Tag::create( 'dd', join( ' / ', $companies ) );
	array_unshift( $facts[0], $item );
}

if( $module->licenses ){
	$licenses	= array();
	foreach( $module->licenses as $license ){
		$label	= $license->label;
		if( $license->source )
			$label	= UI_HTML_Tag::create( 'a', $label, array( 'href' => $license->source ) );
		$licenses[]	= $label;
	}
	$label	= count( $licenses ) > 1 ? $w->labelLicenses : $w->labelLicense;
	$facts[0][]	= UI_HTML_Tag::create( 'dt', $label ).UI_HTML_Tag::create( 'dd', join( ' / ', $licenses ) );
}
$facts[1][]	= UI_HTML_Tag::create( 'dt', $w->labelStatus );
$facts[1][]	= UI_HTML_Tag::create( 'dd', UI_HTML_Tag::create( 'span', $words['types'][$module->type], array( 'class' => 'module-type type-'.$module->type ) ) );

/* --  MODULE SOURCE  --  */
$source	= 'local';
if( isset( $sources[$module->source] ) ){
	$source	= $sources[$module->source];
	$source	= UI_HTML_Tag::create( 'acronym', $module->source, array( 'title' => htmlentities( $source->title ) ) );
}
$facts[0][]	= UI_HTML_Tag::create( 'dt', $w->labelSource ).UI_HTML_Tag::create( 'dd', $source );
//$facts[2][]	= UI_HTML_Tag::create( 'dt', $w->labelSource ).UI_HTML_Tag::create( 'dd', $source );

//$isUpdatable	= FALSE;
if( $module->versionAvailable || $module->versionInstalled ){
	$facts[1][]	= UI_HTML_Tag::create( 'dt', $w->labelVersion );
	if( $module->versionAvailable )
		$facts[1][]	= UI_HTML_Tag::create( 'dd', $module->versionAvailable.' - verfügbar' );
	if( $module->versionInstalled )
		$facts[1][]	= UI_HTML_Tag::create( 'dd', $module->versionInstalled.' - installiert' );
	if( $module->install->date ){
		$facts[1][]	= UI_HTML_Tag::create( 'dt', 'installiert am' );
		$facts[1][]	= UI_HTML_Tag::create( 'dd', date( 'd.m.Y', $module->install->date ).' <small><em>um '.date( 'H:i', $module->install->date ).' Uhr</em></small>' );
	}
	if( $module->install->source ){
		$facts[1][]	= UI_HTML_Tag::create( 'dt', 'aus Quelle' );
		$facts[1][]	= UI_HTML_Tag::create( 'dd', $module->install->source );
	}
//	$isUpdatable	= $module->versionAvailable !== $module->versionInstalled;
}

$facts0	= UI_HTML_Tag::create( 'dl', join( $facts[0] ), array( 'class' => 'general' ) );
$facts1	= UI_HTML_Tag::create( 'dl', join( $facts[1] ), array( 'class' => 'general' ) );
$facts2	= UI_HTML_Tag::create( 'dl', join( $facts[2] ), array( 'class' => 'general' ) );

$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
$iconInstall	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-play' ) );
$iconUpdate		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-forward' ) );
$iconReload		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-refresh' ) );
$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-trash' ) );
$iconEdit		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-pencil' ) );


$labelInstall	= "Das Modul ist <b>nicht installiert</b>.";
$attributes		= array( 'type' => 'button', 'class' => 'btn auto-back former-button former-cancel', 'readonly' => 'readonly', 'disabled' => 'disabled' );
$buttonBack		= UI_HTML_Tag::create( 'button', $iconCancel.'&nbsp;'.$w->buttonBack, $attributes );
$buttonList		= UI_HTML_Elements::LinkButton( './admin/module', $w->buttonList, 'btn btn-small button cancel' );
$buttonCancel	= UI_HTML_Elements::LinkButton( './admin/module', $iconCancel.'&nbsp;'.$w->buttonCancel, 'btn' );
$buttonReload	= UI_HTML_Elements::LinkButton( './admin/module/viewer/reload/'.$module->id, $iconReload.'&nbsp;'.$w->buttonReload, 'btn btn-small'/*, NULL, $disabled*/ );
$buttonEdit			= '';
$buttonInstall		= '';
$buttonUpdate		= '';
$buttonUninstall	= '';
if( $isInstalled ){
	$labelInstall	= "Das Modul ist installiert.";
	if( $env->getModules()->has( 'Admin_Module_Installer' ) ){
		$buttonInstall		= UI_HTML_Elements::LinkButton( './admin/module/installer/index/'.$module->id, $iconInstall.'&nbsp;'.$w->buttonInstall, 'btn btn-success disabled former-button former-add', NULL, 'disabled' );
		$buttonUninstall	= UI_HTML_Elements::LinkButton( './admin/module/installer/uninstall/'.$module->id, $iconRemove.'&nbsp;'.$w->buttonRemove, 'btn btn-small btn-inverse formder-button former-remove' );
		if( $hasUpdate )
			$buttonUpdate		= UI_HTML_Elements::LinkButton( './admin/module/installer/update/'.$module->id, $iconUpdate.'&nbsp;'.$w->buttonUpdate, 'btn btn-primary former-button former-update' );
	}
	if( $env->getModules()->has( 'Admin_Module_Editor' ) )
		$buttonEdit			= UI_HTML_Elements::LinkButton( './admin/module/editor/'.$module->id, $iconEdit.'&nbsp;'.$w->buttonEdit, 'btn former-button former-edit' );
}
else{
	if( $env->getModules()->has( 'Admin_Module_Installer' ) ){
		$buttonInstall		= UI_HTML_Elements::LinkButton( './admin/module/installer/index/'.$module->id, $iconInstall.'&nbsp;'.$w->buttonInstall, 'btn btn-success former-button former-add' );
		$buttonUpdate		= UI_HTML_Elements::LinkButton( './admin/module/installer/update/'.$module->id, $iconUpdate.'&nbsp;'.$w->buttonUpdate, 'btn btn-primary disabled former-button former-update', NULL, 'disabled' );
		$buttonUninstall	= UI_HTML_Elements::LinkButton( './admin/module/installer/uninstall/'.$module->id, $iconRemove.'&nbsp;'.$w->buttonRemove, 'btn btn-small btn-inverse disabled former-button former-remove', NULL, 'disabled' );
	}
	if( $env->getModules()->has( 'Admin_Module_Editor' ) )
		$buttonEdit			= UI_HTML_Elements::LinkButton( './admin/module/editor/'.$module->id, $iconEdit.'&nbsp;'.$w->buttonEdit, 'btn disabled former-button former-edit', NULL, 'disabled' );
}

$labelDetails	= UI_HTML_Tag::create( 'span', 'Details' );
$buttonDetails	= UI_HTML_Tag::create( 'button', $labelDetails, array( 'class' => 'btn button info more', 'onclick' => "$('#panel-details').toggle()" ) );

return '
<div class="row-fluid">
	<div class="span8">
		<div class="content-panel former-fieldset-module-info">
			<div class="content-panel-inner">
				<div style="float: right; margin: -0.6em 1em 0.8em 1em">
					'.$icon.'
				</div>
				<h3>'.View_Admin_Module::formatLabel( $module ).'</h3>
				<div class="description" style="min-height: 45px; max-height: 230px; overflow: auto">
					'.$desc.'
					<br/>
				</div>
				<div class="column-clear"></div>
				<div class="buttonbar">
					'.$buttonBack.'
<!--					&nbsp;|&nbsp;-->
					'.$buttonInstall.'
					'.$buttonUpdate.'
					'.$buttonUninstall.'
<!--					&nbsp;|&nbsp;-->
<!--					'.$buttonDetails.'-->
					'.$buttonEdit.'
				</div>
			</div>
		</div>
	</div>
	<div class="span4">
		<div class="content-panel former-fieldset-module-facts">
			<div class="content-panel-inner">
				<h4 class="icon info">Informationen</h4>
<!--				'.$facts0.'-->
<!--				<hr/>-->
				'.$facts1.'
				'.$facts2.'
				<br/>
				<div class="buttonbar">
					'.$buttonReload.'
				</div>
			</div>
		</div>
	</div>
</div>
<style>
.former-fieldset-module-info {
/*	min-height: 200px;*/
	}
.former-fieldset-module-facts {
	}
.former-fieldset-module-facts div.module-icon {
	}
.former-fieldset-module-facts div.module-icon img {
	margin: 1em;
	}
.former-fieldset-module-facts dl {
	margin-top: 5px;
	margin-bottom: 2px;
	}
.former-fieldset-module-facts dl dt {
	width: 100px;
/*	font-weight: lighter;*/
	}
.former-fieldset-module-facts dl dd {
	margin-left: 100px;
	}
span.button-group {
	border: 1px solid red;
	}
span.button-group button {
	float: left;
	margin: 0px;
	border-radius: 0px;
	}
span.button-group button:first-child {
	border: 1px solid green;
	}
span.button-group button:last-child {
	border: 1px solid blue;
	}
hr {
	height: 1px;
	border: none;
	background-color: #CCC;
	padding: 0;
	margin: 1em 0em 1em 0em;
	}
</style>';
