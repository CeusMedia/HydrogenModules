<?php

use CeusMedia\Common\CLI\ArgumentParser;

$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );

// already done in controller
//$exceptionEnv		= unserialize( $exception->env );
//$exceptionRequest	= unserialize( $exception->request );
//$exceptionSession	= unserialize( $exception->session );

//print_m($exception);die;
//print_m($exceptionRequest->getAll());die;

$sections	= [
	'facts'		=> renderFactsSection( $env, $exception, $exceptionEnv, $exceptionRequest ),
	'file'		=> renderFileSection( $env, $exception ),
	'trace'		=> renderTraceSection( $env, $exception ),
	'request'	=> renderRequestSection( $env, $exception, $exceptionRequest ),
	'session'	=> renderSessionSection( $env, $exception, $exceptionSession ),
	'user'		=> renderUserSection( $env, $exception, $user ),
];

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
				'.join( '<hr/>', array_filter( $sections ) ).'
				<div class="buttonbar">
					'.$buttonCancel.'
					'.$buttonRemove.'
				</div>
			</div>
		</div>
	</div>
</div>';


function renderFactsSection( $env, $exception, $exceptionEnv, $exceptionRequest )
{
	$file		= preg_replace( "/^".preg_quote( realpath( $env->uri ), '/' )."/", './', $exception->file );
	$file		= preg_replace( "/^".preg_quote( $env->uri, '/' )."/", './', $exception->file );
	$date		= date( 'Y.m.d', $exception->createdAt );
	$time		= date( 'H:i:s', $exception->createdAt );

	$facts	= [];
	$facts['Message']	= '<big><strong>'.$exception->message.'</strong></big>';
	if( (int) $exception->code != 0 )
		$facts['Code']	= $exception->code;
	$facts['File (Line)']	= $file.' ('.$exception->line.')';
	$facts['Date (Time)']	= $date.' <small class="muted">('.$time.')</small>';
	$facts['Reqest Path']	= $exceptionRequest->get( '__path' ).'&nbsp;';
	$facts['App Name']		= $exceptionEnv['appName'];
	$facts['Base URL']		= $exceptionEnv['url'];
	$facts['Environment']	= $exceptionEnv['class'];

	$list	= [];
	foreach( $facts as $key => $value )
		$list[]	= UI_HTML_Tag::create( 'dt', $key ).UI_HTML_Tag::create( 'dd', $value );
	return UI_HTML_Tag::create( 'dl', $list, array( 'class' => 'dl-horizontal' ) );
}

function renderFileSection( $env, $exception ): ?string
{
	if( !file_exists( $exception->file ) )
		return NULL;

	$fileLines	= FS_File_Reader::loadArray( $exception->file );
	$fileLines	= file( $exception->file );
	$firstLine	= max( 0, $exception->line - 5 );
	$fileLines	= array_slice( $fileLines, $firstLine, 11 );
	$lines		= [];
	foreach( $fileLines as $nr => $line ){
		$lines[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'th', $firstLine + $nr + 1 ),
			UI_HTML_Tag::create( 'td', '<tt>'.str_replace( "\t", "&nbsp;&nbsp;&nbsp;&nbsp;", $line ).'</tt>' ),
		), ['class' => $nr === 5 ? 'warning' : ''] );
	}
	$tbody		= UI_HTML_Tag::create( 'tbody', $lines );
	$lines		= UI_HTML_Tag::create( 'table', $tbody, array(
		'class' => 'table table-striped table-condensed',
		'style'	=> 'border: 1px solid rgba(127, 127, 127, 0.5)',
	) );
	return UI_HTML_Tag::create( 'h4', 'File' ).$lines;
}

function renderMapTable( $map, $sort = TRUE ){
	$rows	= [];
	if( $sort )
		ksort( $map );
	foreach( $map as $key => $value ){
		$key	= UI_HTML_Tag::create( 'div', $key, array( 'style' => 'font-family: monospace; font-size: 0.85em; letter-spacing: -0.5px' ) );
		$type	= ucfirst( gettype( $value ) );
		$type	= UI_HTML_Tag::create( 'small', $type, array( 'class' => 'muted' ) );
		$rows[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', count( $rows ) + 1, array( 'style' => 'text-align: right' ) ),
			UI_HTML_Tag::create( 'td', $key ),
			UI_HTML_Tag::create( 'td', $type, array( 'style' => 'text-align: right' ) ),
//			UI_HTML_Tag::create( 'td', json_encode( $value ) ),
			UI_HTML_Tag::create( 'td', htmlentities( stripslashes( trim( json_encode( $value ), '"' ) ), ENT_QUOTES, 'utf-8' ) ),
		) );
	}
	$colgroup		= UI_HTML_Elements::ColumnGroup( '40px', '35%', '7%', '' );
	$thead			= UI_HTML_Tag::create( 'thead', UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'th', '#', array( 'style' => 'text-align: right' ) ),
		UI_HTML_Tag::create( 'th', 'Key' ),
		UI_HTML_Tag::create( 'th', 'Type', array( 'style' => 'text-align: right' ) ),
		UI_HTML_Tag::create( 'th', 'Value' )
	) ) );
	$tbody	= UI_HTML_Tag::create( 'tbody', $rows );
	$table	= UI_HTML_Tag::create( 'table', array( $colgroup, $thead, $tbody ), array(
		'class'	=> 'table table-striped table-condensed',
		'style'	=> 'border: 1px solid rgba(127, 127, 127, 0.5)',
	) );
	return $table;
}

function renderRequestSection( $env, $exception, $exceptionRequest ): ?string
{
	if( !$exceptionRequest )
		return NULL;

	$xmpStyle	= 'overflow: auto; border: 1px solid gray; background-color: #EFEFEF; padding: 1em 2em';

	$sectionRequestHeaders	= '';
	if( get_class( $exceptionRequest ) !== 'CLI_ArgumentParser' ){
		$methodLine				= 'Method: '.$exceptionRequest->getMethod()->get().PHP_EOL;
		$lines					= $exceptionRequest->getHeaders()->render();
		$requestHeaders			= UI_HTML_Tag::create( 'xmp', $methodLine.$lines, array( 'style' => $xmpStyle ) );
		$sectionRequestHeaders	= UI_HTML_Tag::create( 'h4', 'Request Headers' ).$requestHeaders;
	}
	$sectionRequestData			= UI_HTML_Tag::create( 'h4', 'Request Data' ).renderMapTable( $exceptionRequest->getAll() );
	return $sectionRequestHeaders.'<hr/>'.$sectionRequestData;
}

function renderSessionSection( $env, $exception, $exceptionSession ): ?string
{
	if( !$exceptionSession || !$exceptionSession->count() )
		return NULL;

	$sessionData	= renderMapTable( $exceptionSession->getAll() );
	return UI_HTML_Tag::create( 'h4', 'Session Data' ).$sessionData;
}

function renderTraceSection( $env, $exception ): string
{
	$xmpStyle	= 'overflow: auto; border: 1px solid gray; background-color: #EFEFEF; padding: 1em 2em';

	if( isset( $exception->traceAsHtml ) )
		$trace	= $exception->traceAsHtml;
	else if( isset( $exception->traceAsString ) ){
		$trace	= $exception->traceAsString;
		$trace	= preg_replace( "/ ".preg_quote( realpath( $env->uri ), '/' )."/s", ' ./', $trace );
		$trace	= preg_replace( "/ ".preg_quote( $env->uri, '/' )."/s", ' ./', $trace );
		$trace	= '<xmp style="'.$xmpStyle.'">'.$trace.'</xmp>';
	}
	else{
		$trace	= $exception->trace;
		$trace	= preg_replace( "/ ".preg_quote( realpath( $env->uri ), '/' )."/s", ' ./', $trace );
		$trace	= preg_replace( "/ ".preg_quote( $env->uri, '/' )."/s", ' ./', $trace );
		$trace	= '<xmp style="'.$xmpStyle.'">'.$trace.'</xmp>';
	}
	return UI_HTML_Tag::create( 'h4', 'Stack Trace' ).$trace;
}

function renderUserSection( $env, $exception, $user ): ?string
{
	if( !$user )
		return NULL;
	$data	= renderMapTable( [
		'username'		=> $user->username,
		'fullname'		=> $user->firstname.' '.$user->surname,
		'email'			=> $user->email,
//		'status'		=> ...,
//		'role'			=> ...,
	] );
	return UI_HTML_Tag::create( 'h4', 'User' ).$data;
}
