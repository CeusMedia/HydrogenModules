<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/*  --  VERSION  --  */
$version	= HtmlTag::create( 'small', 'unbekannt', ['class' => 'muted'] );
if( $moduleLocal->versionInstalled ){
	$version	= $moduleLocal->versionInstalled;
}

/*  --  SOURCE  --  */
$source	= HtmlTag::create( 'small', 'unbekannt', ['class' => 'muted'] );
if( $moduleLocal->source ){
	$source	= $moduleLocal->source;
}

/*  --  TYPE  --  */
$types	= $words['install-types'];
$type	= HtmlTag::create( 'small', $types[-1], ['class' => 'muted'] );
if( $moduleLocal->installType !== NULL ){
	$type	= $types[(int) $moduleLocal->installType];
}

/*  --  DATE  --  */
$date	= HtmlTag::create( 'small', 'unbekannt', ['class' => 'muted'] );
if( $moduleLocal->installDate ){
	$date	= date( 'd.m.Y H:i', $moduleLocal->installDate );
	if( $env->getModules()->has( 'UI_Helper_TimePhraser' ) ){
		$helper	= new View_Helper_TimePhraser( $env );
		$prefix	= 'vor';//$words['...']['datePhrasePrefix'];
		$suffix	= '';//$words['...']['datePhraseSuffix'];
		$date	= $helper->convert( $moduleLocal->installDate, TRUE );
		$date	= trim( $prefix.' '.$date.' '.$suffix );
	}
}

return '
<fieldset id="panel-module-update-local">
	<legend class="install-local">Lokale Installation</legend>
	<dl class="general">
		<dt>Version</dt>
		<dd>'.$version.'</dd>
		<dt>Quelle</dt>
		<dd>'.$source.'</dd>
		<dt>Typ</dt>
		<dd>'.$type.'</dd>
		<dt>Datum</dt>
		<dd>'.$date.'</dd>
	</dl>
	<div class="clearfix"></div>
</fieldset>
<style>
#panel-module-update-local .muted {
	font-style: italic;
	font-size: 0.9em;
	opacity: 0.5;
	}
</style>';
?>
