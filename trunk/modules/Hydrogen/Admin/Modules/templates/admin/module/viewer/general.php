<?php

$w			= (object) $words['view'];
$sources	= array();
$model		= new Model_ModuleSource( $this->env );
foreach( $model->getAll() as $source )
	$sources[$source->id]	= $source;

$icon	= "";
if( $module->icon ){
	$icon	= '<img src="'.$module->icon.'" style="min-width: 64px; min-height: 64px; max-width: 128px; max-height: 128px"/>';
}

$desc	= $module->description;
$desc	= preg_replace( "/\[(http\S+) ([^]]+)\]/", '<a href="\\1">\\2</a>', $desc );
$desc	= preg_replace( "/\[(http\S+)\]/", '<a href="\\1">\\1</a>', $desc );

$desc	= View_Helper_ContentConverter::formatText( $env, $desc );
$desc	= View_Helper_ContentConverter::formatLinks( $env, $desc );
$desc	= View_Helper_ContentConverter::formatCurrencies( $env, $desc );
$desc	= View_Helper_ContentConverter::formatWikiLinks( $env, $desc );
$desc	= View_Helper_ContentConverter::formatCodeBlocks( $env, $desc );
$desc	= View_Helper_ContentConverter::formatBreaks( $env, $desc );

$list	= array();

$source	= 'lokal';
if( isset( $sources[$module->source] ) ){
	$source	= $sources[$module->source];
	$source	= UI_HTML_Tag::create( 'acronym', $module->source, array( 'title' => htmlentities( $source->title ) ) );
}
$list[]	= UI_HTML_Tag::create( 'dt', $w->labelSource ).UI_HTML_Tag::create( 'dd', $source );

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
	$list[]	= UI_HTML_Tag::create( 'dt', $label ).join( $authors );
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
	$list[]	= UI_HTML_Tag::create( 'dt', $label ).UI_HTML_Tag::create( 'dd', join( ' / ', $companies ) );
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
	$list[]	= UI_HTML_Tag::create( 'dt', $label ).UI_HTML_Tag::create( 'dd', join( ' / ', $licenses ) );
}
if( $module->price )
	$list[]	= UI_HTML_Tag::create( 'dt', $w->labelPrice ).UI_HTML_Tag::create( 'dd', $module->price );
$list[]	= UI_HTML_Tag::create( 'dt', $w->labelStatus );
$list[]	= UI_HTML_Tag::create( 'dd', UI_HTML_Tag::create( 'span', $words['types'][$module->type], array( 'class' => 'module-type type-'.$module->type ) ) );

if( $module->versionAvailable || $module->versionInstalled ){
	$list[]	= UI_HTML_Tag::create( 'dt', $w->labelVersion );
	if( $module->versionAvailable )
		$list[]	= UI_HTML_Tag::create( 'dd', $module->versionAvailable.' - verfügbar' );
	if( $module->versionInstalled )
		$list[]	= UI_HTML_Tag::create( 'dd', $module->versionInstalled.' - installiert' );
}

$list	= UI_HTML_Tag::create( 'dl', join( $list ), array( 'class' => 'general' ) );


$attributes		= array( 'type' => 'button', 'class' => 'button cancel auto-back', 'readonly' => 'readonly', 'disabled' => 'disabled' );
$buttonBack		= UI_HTML_Tag::create( 'button', '<span>'.$w->buttonBack.'</span>', $attributes );

$disabled			= $module->type == 4 ? '' : 'disabled';
$buttonList			= UI_HTML_Elements::LinkButton( './admin/module', $w->buttonList, 'button cancel' );
$buttonCancel		= UI_HTML_Elements::LinkButton( './admin/module', $w->buttonCancel, 'button cancel' );
$buttonInstall		= UI_HTML_Elements::LinkButton( './admin/module/installer/index/'.$module->id, $w->buttonInstall, 'button add', NULL, $disabled );
$disabled			= $module->type == 4 ? 'disabled' : '';
$buttonEdit			= UI_HTML_Elements::LinkButton( './admin/module/editor/'.$module->id, $w->buttonEdit, 'button edit', NULL, $disabled );
$buttonUninstall	= UI_HTML_Elements::LinkButton( './admin/module/viewer/uninstall/'.$module->id, $w->buttonRemove, 'button remove', 'Die Modulkopie oder -referenz wird gelöscht. Wirklich?', $disabled );

return '
<fieldset>
	<legend class="icon module">Module-Informationen</legend>
	<div class="column-right-20" style="margin: 2em; text-align: right;">
		'.$icon.'
	</div>
	<div class="column-left-70">
		<h3>'.$module->title.'</h3>
		<br/>
		<div class="description">
			'.nl2br( $desc ).'
		</div>
		'.$list.'
	</div>
	<div class="column-clear"></div>	
	<div class="buttonbar">
		'.$buttonBack.'
		'.$buttonInstall.'
		'.$buttonEdit.'
		'.$buttonUninstall.'
	</div>
</fieldset>
';

?>