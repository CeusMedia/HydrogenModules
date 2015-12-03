<?php

$file		= preg_replace( "/^".preg_quote( realpath( $env->uri ), '/' )."/", '.', $exception->file );
$date		= date( 'Y.m.d', $exception->timestamp );
$time		= date( 'H:i:s', $exception->timestamp );

if( isset( $exception->traceAsHtml ) )
	$trace	= $exception->traceAsHtml;
else if( isset( $exception->traceAsString ) )
	$trace	= '<xmp style="overflow: auto; border: 1px solid gray; background-color: #EFEFEF; padding: 1em 2em;">'.$exception->traceAsString.'</xmp>';
else
	$trace	= '<xmp style="overflow: auto; border: 1px solid gray; background-color: #EFEFEF; padding: 1em 2em;">'.$exception->trace.'</xmp>';

$facts	= array();
$facts['Message']	= '<big><strong>'.$exception->message.'</strong></big>';
if( (int) $exception->code != 0 )
	$facts['Code']	= $exception->code;
$facts['File (Line)']	= $file.' ('.$exception->line.')';
$facts['Date (Time)']	= $date.' <small class="muted">('.$time.')</small>';

$list	= array();
foreach( $facts as $key => $value )
	$list[]	= UI_HTML_Tag::create( 'dt', $key ).UI_HTML_Tag::create( 'dd', $value );
$list	= UI_HTML_Tag::create( 'dl', $list, array( 'class' => 'dl-horizontal' ) );

return '
<div class="row-fluid">
	<div class="span12">
		<div class="content-panel">
			<h3>Exception</h3>
			<div class="content-panel-inner">
				'.$list.'
				<hr/>
				<h4>Trace</h4>
				'.$trace.'
				<div class="buttonbar">
					<a href="./system/log'.( $page ? '/'.$page : '' ).'" class="btn btn-small"><i class="icon-arrow-left"></i>&nbsp;back</a>
					<a href="./system/log/remove/'.$exception->id.'" class="btn btn-small btn-inverse"><i class="icon-trash icon-white"></i>&nbsp;remove</a>
				</div>
			</div>
		</div>
	</div>
</div>
';
