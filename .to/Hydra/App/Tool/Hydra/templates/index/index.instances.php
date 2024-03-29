<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$list		= [];
$nrUpdates	= 0;
foreach( $instances as $id => $entry ){
	$entry->configPath	= !empty( $entry->configPath ) ? $entry->configPath : 'config/';
	$entry->configFile	= !empty( $entry->configFile ) ? $entry->configFile : 'config.ini';

	$configFile	= $entry->uri.$entry->configPath.$entry->configFile;
	$class		= $instanceId === $id ? ['active'] : [];
	$badge		= '<span class="badge badge-fail">!</span>';
	if( file_exists( $configFile )  ){
		if( $entry->modules['updatable'] ){
			$count		= count( $entry->modules['updatable'] );
			$nrUpdates	+= $count;
			$badge		= '<span class="badge badge-update" title="'.$count.' Update(s)">'.$count.'</span>';
		}
		else
			$badge		= '';
	}
	$url		= './admin/instance/select/'.$id;
	$attributes	= [
		'href'				=> $url,
		'class'				=> 'instance',
		'data-instance-id'	=> $id,
	];
	$link		= HtmlTag::create( 'a', $entry->title, $attributes ).$badge;
	$attributes	= array(
		'class'		=> join( ' ', $class ),
		'data-url'	=> $entry->protocol.$entry->host.$entry->path
	);
	$item		= HtmlTag::create( 'li', $link, $attributes );
	$list[$entry->title]	= $item;
}
ksort( $list );

$list	= HtmlTag::create( 'ul', $list, ['class' => 'instances'] );

$badgeNrUpdates	= $nrUpdates ? HtmlTag::create( 'span', $nrUpdates, ['class' => 'badge badge-update'] ) : '';
$panel	= '
<style>
.badge{
	display: inline-block;
	float: right;
	min-width: 6px;
	height: 18px;
	padding: 0 6px;
	margin: 0;
	margin-left: 0.5em;
	background-color: #777;
	border-radius: 9px;
	line-height: 1.6em;
	text-align: center;
	font-size: 0.9em;
	font-size: 11.5px;
	color: white;
	}

.badge.badge-fail{
	background-color: #922;
	}
.badge.badge-update{
	background-color: #05A;
	}

</style>
<fieldset>
	<legend>Instanzen&nbsp;'.$badgeNrUpdates.'</legend>
	<div style="position: absolute; right: 8px; top: 16px;">
		'.HtmlElements::LinkButton( './admin/instance/', '', 'button tiny edit' ).'
	</div>
	'.$list.'
</fieldset>
';
$env->getRuntime()->reach( 'Template: index/index - instances' );
return $panel;
?>
