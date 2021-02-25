<?php
if( !$moduleSource->versionLog )
	return '';

$w			= (object) $words['update'];
$changes	= array();
foreach( $moduleSource->versionLog as $entry ){
	$isNewer	= version_compare( $entry->version, $moduleLocal->version ) > 0;
	$class		= $isNewer ? 'new' : 'old';
	$label		= UI_HTML_Tag::create( 'dt', '<small>Version</small> '.$entry->version, array( 'class' => $class ) );
	$value		= UI_HTML_Tag::create( 'dd', $entry->note, array( 'class' => $class ) );
	$changes[]	= $label.$value;
}
$changes	= array_reverse( $changes );
$changes	= UI_HTML_Tag::create( 'dl', $changes, array( 'class' => 'general list-changes' ) );

return '
<fieldset class="panel-changes">
	<legend class="module-changes">Änderungen</legend>
	<div>
		'.$changes.'
	</div>
</fieldset>
<style>
fieldset.panel-changes dl.list-changes .new {
	font-size: 1.1em;
	}
fieldset.panel-changes dl.list-changes dt.new,
fieldset.panel-changes dl.list-changes dd.new {
	line-height: 1.6em;
	}
fieldset.panel-changes dl.list-changes dt.old,
fieldset.panel-changes dl.list-changes dd.old {
	opacity: 0.66 !important;
	}
fieldset.panel-changes div {
	max-height: 80px;
	overflow: scroll;
	overflow-x: hidden;
	overflow-y: auto;
	}
</style>';
?>
