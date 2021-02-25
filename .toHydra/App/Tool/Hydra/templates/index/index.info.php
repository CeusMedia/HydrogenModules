<?php
if( empty( $remoteConfig ) )
	return "";
$name		= '<cite>'.$remoteConfig->get( 'app.name' ).'</cite>';
if( strlen( $remoteConfig->get( 'app.version' ) ) )
	$name	.= ' <span class="small">v'.$remoteConfig->get( 'app.version' ).'</span>';
if( strlen( $remoteConfig->get( 'app.revision' ) ) )
	$name	.= ' <span class="small">rev'.$remoteConfig->get( 'app.version' ).'</span>';

if( $instance->host === "localhost" ){
	$instance->host     = $env->host;
	if( $env->port && $env->port != 80 )
		$instance->host	.= ":".$env->port;
}
//$instance->host		= $instance->host === "localhost" ? $env->host : $instance->host;

$instanceUrl		= $instance->protocol.$instance->host.$instance->path;
$instanceBase		= $remoteConfig->get( 'app.base.url' ) ? $remoteConfig->get( 'app.base.url' ) : "";

$linkInstanceUrl	= UI_HTML_Tag::create( 'a', $instanceUrl, array( 'href' => $instanceUrl ) );
$linkInstanceBase	= $instanceBase	? UI_HTML_Tag::create( 'a', $instanceBase, array( 'href' => $instanceBase ) ) : "<em>autodetect</em>";

$panel	= '
<fieldset style="position: relative">
	<legend class="info">Application Instance Information</legend>
	<div style="position: absolute; right: 8px; top: 16px;">
		'.UI_HTML_Elements::LinkButton( './admin/instance/edit/'.$instanceId, '', 'button tiny edit' ).'
	</div>
	<dl>
		<dt>Application Name</dt>
		<dd>'.$name.'</cite></dd>
		<dt>Application Instance URL <small class="muted"><em>(defined by Hydra Instance)</em></small></dt>
		<dd>'.$linkInstanceUrl.'</dd>
		<dt>Application Base URL <small class="muted"><em>(defined by application configuration)</em></small></dt>
		<dd>'.$linkInstanceBase.'</dd>
		<dt>Path to Application Instance</dt>
		<dd><a href="file://'.$remote->path.'" target="_blank">'.$remote->path.'</a></dd>
	</dl>
	<br/>
	<div class="page-preview not-page-preview-blocked" data-url="'.$instanceUrl.'">
		<div class="page-preview-container">
			<div class="page-preview-iframe-container">
				<iframe class="preview" src="'.$instanceUrl.'"></iframe>
			</div>
			<div class="page-preview-mask" style=""></div>
		</div>
	</div>
<style>
div.page-preview {
	width: 99%;
	height: 400px;
	margin-bottom: 0.25em;
	overflow: hidden;
	border: 1px solid gray;
	border-radius: 0.3em;
	box-shadow: 2px 2px 6px rgba(0, 0, 0, 0.33);
	}
div.page-preview div.page-preview-container {
	width: 133.33%;
	height: 133.33%;
	overflow: hidden;
	transform-origin: 0 0;
	-webkit-transform-origin: 0 0;
	-ms-transform-origin: 0 0;
	transform: scale(.75);
	-ms-transform: scale(.75);
	-webkit-transform: scale(.75);
	}
div.page-preview div.page-preview-container div.page-preview-iframe-container {
	width: 100%;
	height: 100%;
	}
div.page-preview div.page-preview-container div.page-preview-iframe-container iframe.preview {
	width: 100%;
	height: 100%;
	border: 0px;
	}
div.page-preview div.page-preview-container div.page-preview-mask {
	width: 100%;
	height: 100%;
	position: absolute;
	left: 0;
	top: 0;
	box-shadow: inset 2px 2px 12px gray;
	background-color: rgba(255, 255, 255, 0.21);
	display: none;
	}
div.page-preview-blocked div.page-preview-container div.page-preview-mask {
	display: block;
	}
div.page-preview-blocked div.page-preview-container div.page-preview-mask {
	display: block;
	}
</style>
</fieldset>';
$env->clock->profiler->tick( 'Template: index/index - info' );
return $panel;
?>
