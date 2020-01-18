<?php

$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );

//print_m($exception);die;

$file		= preg_replace( "/^".preg_quote( realpath( $env->uri ), '/' )."/", '.', $exception->file );
$date		= date( 'Y.m.d', $exception->createdAt );
$time		= date( 'H:i:s', $exception->createdAt );

$xmpStyle	= 'overflow: auto; border: 1px solid gray; background-color: #EFEFEF; padding: 1em 2em';

if( isset( $exception->traceAsHtml ) )
	$trace	= $exception->traceAsHtml;
else if( isset( $exception->traceAsString ) )
	$trace	= '<xmp style="'.$xmpStyle.'">'.$exception->traceAsString.'</xmp>';
else
	$trace	= '<xmp style="'.$xmpStyle.'">'.$exception->trace.'</xmp>';
$sectionTrace	= UI_HTML_Tag::create( 'h4', 'Stack Trace' ).$trace;

$exceptionEnv		= unserialize( $exception->env );
$exceptionRequest	= unserialize( $exception->request );
$exceptionSession	= unserialize( $exception->session );

$facts	= array();
$facts['Message']	= '<big><strong>'.$exception->message.'</strong></big>';
if( (int) $exception->code != 0 )
	$facts['Code']	= $exception->code;
$facts['File (Line)']	= $file.' ('.$exception->line.')';
$facts['Date (Time)']	= $date.' <small class="muted">('.$time.')</small>';
$facts['Reqest Path']	= $exceptionRequest->get( '__path' );
$facts['App Name']		= $exceptionEnv['appName'];
$facts['Base URL']		= $exceptionEnv['url'];
$facts['Environment']	= $exceptionEnv['class'];



$list	= array();
foreach( $facts as $key => $value )
	$list[]	= UI_HTML_Tag::create( 'dt', $key ).UI_HTML_Tag::create( 'dd', $value );
$list	= UI_HTML_Tag::create( 'dl', $list, array( 'class' => 'dl-horizontal' ) );

if( $exceptionRequest ){
	$requestHeaders	= UI_HTML_Tag::create( 'xmp', $exceptionRequest->getHeaders()->render(), array( 'style' => $xmpStyle ) );
	$sectionRequestHeaders	= UI_HTML_Tag::create( 'h4', 'Request Headers' ).$requestHeaders;
}

$sectionSession	= '';
if( $exceptionSession ){
	$rows	= array();
	ksort( $exceptionSession );
	foreach( $exceptionSession as $key => $value ){
		$key	= UI_HTML_Tag::create( 'div', $key, array( 'style' => 'font-family: monospace; font-size: 0.85em; letter-spacing: -0.5px' ) );
		$type	= ucfirst( gettype( $value ) );
		$type	= UI_HTML_Tag::create( 'small', $type, array( 'class' => 'muted' ) );
		$rows[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', count( $rows ) + 1, array( 'style' => 'text-align: right' ) ),
			UI_HTML_Tag::create( 'td', $key ),
			UI_HTML_Tag::create( 'td', $type, array( 'style' => 'text-align: right' ) ),
//			UI_HTML_Tag::create( 'td', json_encode( $value ) ),
			UI_HTML_Tag::create( 'td', stripslashes( trim( json_encode( $value ), '"' ) ) ),
		) );
	}
	$colgroup		= UI_HTML_Elements::ColumnGroup( '40px', '35%', '7%', '' );
	$thead			= UI_HTML_Tag::create( 'thead', UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'th', '#', array( 'style' => 'text-align: right' ) ),
		UI_HTML_Tag::create( 'th', 'Key' ),
		UI_HTML_Tag::create( 'th', 'Type', array( 'style' => 'text-align: right' ) ),
		UI_HTML_Tag::create( 'th', 'Value' )
	) ) );
	$tbody			= UI_HTML_Tag::create( 'tbody', $rows );
	$sessionData	= UI_HTML_Tag::create( 'table', array( $colgroup, $thead, $tbody ), array(
		'class'	=> 'table table-striped table-condensed',
		'style'	=> 'border: 1px solid rgba(127, 127, 127, 0.5)',
	) );
	$sectionSession	= UI_HTML_Tag::create( 'h4', 'Session Data' ).$sessionData;
}

$sectionFile	= '';
if( file_exists( $exception->file ) ){
	$fileLines	= FS_File_Reader::loadArray( $exception->file );
	$firstLine	= max( 0, $exception->line - 5 );
	$fileLines	= array_slice( $fileLines, $firstLine, 11 );
	$lines		= array();
	foreach( $fileLines as $nr => $line ){
		$lines[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'th', $firstLine + $nr + 1 ),
			UI_HTML_Tag::create( 'td', '<tt>'.$line.'</tt>' ),
		) );
	}
	$tbody		= UI_HTML_Tag::create( 'tbody', $lines );
	$lines		= UI_HTML_Tag::create( 'table', $tbody, array(
		'class' => 'table table-striped table-condensed',
		'style'	=> 'border: 1px solid rgba(127, 127, 127, 0.5)',
	) );
	$sectionFile	= UI_HTML_Tag::create( 'h4', 'File' ).$lines;
}


$buttonCancel	= UI_HTML_Tag::create( 'a', $iconCancel.'&nbsp;zur Liste', array(
	'href'		=> './admin/log/exception'.( $page ? '/'.$page : '' ),
	'class'		=> 'btn btn-small',
) );
$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove.'&nbsp;entfernen', array(
	'href'		=> './admin/log/exception/remove/'.$exception->exceptionId,
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
				'.$sectionFile.'
				<hr/>
				'.$sectionTrace.'
				<hr/>
				'.$sectionRequestHeaders.'
				<hr/>
				'.$sectionSession.'
				<div class="buttonbar">
					'.$buttonCancel.'
					'.$buttonRemove.'
				</div>
			</div>
		</div>
	</div>
</div>';
