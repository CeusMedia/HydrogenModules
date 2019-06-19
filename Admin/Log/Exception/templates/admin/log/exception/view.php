<?php

$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );

//print_m( $exception->request->get( '__path' ) );die;
//print_m( $exception );die;

$file		= preg_replace( "/^".preg_quote( realpath( $env->uri ), '/' )."/", '.', $exception->file );
$date		= date( 'Y.m.d', $exception->timestamp );
$time		= date( 'H:i:s', $exception->timestamp );

$xmpStyle	= 'overflow: auto; border: 1px solid gray; background-color: #EFEFEF; padding: 1em 2em';

if( isset( $exception->traceAsHtml ) )
	$trace	= $exception->traceAsHtml;
else if( isset( $exception->traceAsString ) )
	$trace	= '<xmp style="'.$xmpStyle.'">'.$exception->traceAsString.'</xmp>';
else
	$trace	= '<xmp style="'.$xmpStyle.'">'.$exception->trace.'</xmp>';
$sectionTrace	= UI_HTML_Tag::create( 'h4', 'Stack Trace' ).$trace;

$facts	= array();
$facts['Message']	= '<big><strong>'.$exception->message.'</strong></big>';
if( (int) $exception->code != 0 )
	$facts['Code']	= $exception->code;
$facts['File (Line)']	= $file.' ('.$exception->line.')';
$facts['Date (Time)']	= $date.' <small class="muted">('.$time.')</small>';
$facts['Reqest Path']	= $exception->request->get( '__path' );
$facts['App Name']		= $exception->env['appName'];
$facts['Base URL']		= $exception->env['url'];
$facts['Environment']	= $exception->env['class'];

$list	= array();
foreach( $facts as $key => $value )
	$list[]	= UI_HTML_Tag::create( 'dt', $key ).UI_HTML_Tag::create( 'dd', $value );
$list	= UI_HTML_Tag::create( 'dl', $list, array( 'class' => 'dl-horizontal' ) );

$requestHeaders	= array();
if( isset( $exception->request ) ){
	$requestHeaders	= UI_HTML_Tag::create( 'xmp', $exception->request->getHeaders()->render(), array( 'style' => $xmpStyle ) );
	$sectionRequestHeaders	= UI_HTML_Tag::create( 'h4', 'Request Headers' ).$requestHeaders;
}

$buttonCancel	= UI_HTML_Tag::create( 'a', $iconCancel.'&nbsp;zur Liste', array(
	'href'		=> './admin/log/exception'.( $page ? '/'.$page : '' ),
	'class'		=> 'btn btn-small',
) );
$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove.'&nbsp;entfernen', array(
	'href'		=> './admin/log/exception/remove/'.$exception->id,
	'class'		=> 'btn btn-small btn-danger',
) );

return '
<div class="row-fluid">
	<div class="span12">
		<div class="content-panel">
			<h3>Exception</h3>
			<div class="content-panel-inner">
				'.$list.'
				<hr/>
				'.$sectionTrace.'
				<hr/>
				'.$sectionRequestHeaders.'
				<div class="buttonbar">
					'.$buttonCancel.'
					'.$buttonRemove.'
				</div>
			</div>
		</div>
	</div>
</div>';
