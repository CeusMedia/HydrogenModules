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

$list	= array();
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
if( $module->price )
	$list[0][]	= UI_HTML_Tag::create( 'dt', $w->labelPrice ).UI_HTML_Tag::create( 'dd', $module->price );
$facts[1][]	= UI_HTML_Tag::create( 'dt', $w->labelStatus );
$facts[1][]	= UI_HTML_Tag::create( 'dd', UI_HTML_Tag::create( 'span', $words['types'][$module->type], array( 'class' => 'module-type type-'.$module->type ) ) );

/* --  MODULE SOURCE  --  */
$source	= 'local';
if( isset( $sources[$module->source] ) ){
	$source	= $sources[$module->source];
	$source	= UI_HTML_Tag::create( 'acronym', $module->source, array( 'title' => htmlentities( $source->title ) ) );
}
$facts[0][]	= UI_HTML_Tag::create( 'dt', $w->labelSource ).UI_HTML_Tag::create( 'dd', $source );
$facts[2][]	= UI_HTML_Tag::create( 'dt', $w->labelSource ).UI_HTML_Tag::create( 'dd', $source );




//$isUpdatable	= FALSE;
if( $module->versionAvailable || $module->versionInstalled ){
	$facts[2][]	= UI_HTML_Tag::create( 'dt', $w->labelVersion );
	if( $module->versionAvailable )
		$facts[2][]	= UI_HTML_Tag::create( 'dd', $module->versionAvailable.' - verfügbar' );
	if( $module->versionInstalled )
		$facts[2][]	= UI_HTML_Tag::create( 'dd', $module->versionInstalled.' - installiert' );
//	$isUpdatable	= $module->versionAvailable !== $module->versionInstalled;
}

$list	= UI_HTML_Tag::create( 'dl', join( $list ), array( 'class' => 'general' ) );
$facts0	= UI_HTML_Tag::create( 'dl', join( $facts[0] ), array( 'class' => 'general' ) );
$facts1	= UI_HTML_Tag::create( 'dl', join( $facts[1] ), array( 'class' => 'general' ) );
$facts2	= UI_HTML_Tag::create( 'dl', join( $facts[2] ), array( 'class' => 'general' ) );



$labelInstall	= "Das Modul ist <b>nicht installiert</b>.";
$attributes		= array( 'type' => 'button', 'class' => 'button cancel auto-back', 'readonly' => 'readonly', 'disabled' => 'disabled' );
$buttonBack		= UI_HTML_Tag::create( 'button', '<span>'.$w->buttonBack.'</span>', $attributes );
$buttonList		= UI_HTML_Elements::LinkButton( './admin/module', $w->buttonList, 'button cancel' );
$buttonCancel	= UI_HTML_Elements::LinkButton( './admin/module', $w->buttonCancel, 'button cancel' );
$buttonReload	= UI_HTML_Elements::LinkButton( './admin/module/viewer/reload/'.$module->id, $w->buttonReload, 'button icon refresh'/*, NULL, $disabled*/ );
$buttonUpdate	= UI_HTML_Elements::LinkButton( './admin/module/installer/update/'.$module->id, $w->buttonUpdate, 'button update', NULL, 'disabled' );
if( $isInstalled ){
	$labelInstall	= "Das Modul ist installiert.";
	$buttonInstall		= UI_HTML_Elements::LinkButton( './admin/module/installer/index/'.$module->id, $w->buttonInstall, 'button add', NULL, 'disabled' );
	$buttonUninstall	= UI_HTML_Elements::LinkButton( './admin/module/installer/uninstall/'.$module->id, $w->buttonRemove, 'button remove', $w->buttonRemoveConfirm );
	$buttonEdit			= UI_HTML_Elements::LinkButton( './admin/module/editor/'.$module->id, $w->buttonEdit, 'button edit' );

	if( $hasUpdate )
		$buttonUpdate		= UI_HTML_Elements::LinkButton( './admin/module/installer/update/'.$module->id, $w->buttonUpdate, 'button update' );

}
else{
	$buttonInstall		= UI_HTML_Elements::LinkButton( './admin/module/installer/index/'.$module->id, $w->buttonInstall, 'button add' );
	$buttonUninstall	= UI_HTML_Elements::LinkButton( './admin/module/installer/uninstall/'.$module->id, $w->buttonRemove, 'button remove', NULL, 'disabled' );
	$buttonEdit			= UI_HTML_Elements::LinkButton( './admin/module/editor/'.$module->id, $w->buttonEdit, 'button edit', NULL, 'disabled' );
}


return '
<style>
fieldset.module-info {
	min-height: 200px;
	}
fieldset.module-facts {
	}
fieldset.module-facts div.module-icon {
	}
fieldset.module-facts div.module-icon img {
	margin: 1em;
	}
fieldset.module-facts dl {
	margin-top: 5px;
	margin-bottom: 2px;
	}
fieldset.module-facts dl dt {
	width: 100px;
/*	font-weight: lighter;*/
	}
fieldset.module-facts dl dd {
	margin-left: 80px;
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

</style>
<div class="column-left-30">
	<fieldset class="module-info module-facts">
		<div class="module-icon" style="text-align: center">
			'.$icon.'
		</div>
		'.$list.'
		'.$facts0.'
	</fieldset>
</div>
<div class="column-right-25" style="">
	<fieldset class="module-info module-facts">
		<legend>Aktionen</legend>
		<div class="buttonlist">
			'.$facts1.'
<!--			'.$labelInstall.'<br/>-->
			<span class="button-group">
				'.$buttonInstall.'
				'.$buttonUninstall.'
			</span><br/><br/>
			'.$facts2.'
			'.$buttonUpdate.'
			'.$buttonReload.'<br/><br/>
			'.$buttonBack.'
			'.$buttonEdit.'
		</div>
	</fieldset>
</div>
<div class="column-left-45">
	<fieldset class="module-info">
		<legend class="icon module">Module-Informationen</legend>
<!--		<div class="column-right-20" style="margin: 2em; text-align: right;">
			'.$icon.'
		</div>
		<div class="column-left-70">-->
			<h3>'.View_Admin_Module::formatLabel( $module ).'</h3>
			<div class="description">
<!--				<div class="module-icon" style="float: right; margin: 0 1em 1.5em 2em;">
					'.$icon.'
				</div>-->
				'.$desc.'
				<br/>
			</div>
<!--		'.$list.'-->
<!--	</div>-->
	<div class="column-clear"></div>
<!--	<div class="buttonbar">
		'.$buttonBack.'
		&nbsp;|&nbsp;
		'.$buttonInstall.'
		'.$buttonUpdate.'
		'.$buttonUninstall.'
		&nbsp;|&nbsp;
		'.$buttonEdit.'
		'.$buttonReload.'
	</div>-->
</fieldset>
</div>
<div class="column-clear"></div>
';
?>
