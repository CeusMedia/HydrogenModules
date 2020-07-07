<?php

if( $runs ){
	$rows	= array();
	foreach( $runs as $item ){
		$message	= json_decode( $item->message );
		if( $message->type === 'throwable' ){
			$file	= removeEnvPath( $env, $message->file );
			$output	= '<div>
				<div>Error: '.$message->message.'</div>
				<div>File: '.$file.' - Line: '.$message->line.'</div>
				<xmp>'.removeEnvPath( $env, $message->trace ).'</xmp>
			</div>';
		}
		else if( $message->type === 'result' ){
			$output	= '<div>
				<div>Type: Result</div>
				<pre>'.print_m( $message->results, NULL, NULL, TRUE ).'</pre>
			</div>';
		}

		switch( (int) $item->status ){
			case Model_Job_Run::STATUS_TERMINATED:
				$status	= UI_HTML_Tag::create( 'span', 'verhindert', array( 'class' => 'badge badge-important' ) );
				break;
			case Model_Job_Run::STATUS_FAILED:
				$status	= UI_HTML_Tag::create( 'span', 'gescheitert', array( 'class' => 'badge badge-important' ) );
				break;
			case Model_Job_Run::STATUS_ABORTED:
				$status	= UI_HTML_Tag::create( 'span', 'abgebrochen', array( 'class' => 'badge badge-important' ) );
				break;
			case Model_Job_Run::STATUS_PREPARED:
				$status	= UI_HTML_Tag::create( 'span', 'abgebrochen', array( 'class' => 'badge' ) );
				break;
			case Model_Job_Run::STATUS_RUNNING:
				$status	= UI_HTML_Tag::create( 'span', 'abgebrochen', array( 'class' => 'badge badge-warning' ) );
				break;
			case Model_Job_Run::STATUS_DONE:
				$status	= UI_HTML_Tag::create( 'span', 'erledigt', array( 'class' => 'badge badge-success' ) );
				break;
		}
		$rows[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', '<small class="muted">'.$item->processId.'</small>' ),
			UI_HTML_Tag::create( 'td', '<a href="./manage/job/definition/view/'.$definition->jobDefinitionId.'">'.$definition->identifier.'</a>' ),
			UI_HTML_Tag::create( 'td', $status ),
			UI_HTML_Tag::create( 'td', $output ),
			UI_HTML_Tag::create( 'td', date( 'd.m.Y', $item->createdAt ) ),
			UI_HTML_Tag::create( 'td', $item->ranAt ? date( 'd.m.Y', $item->ranAt ) : '-' ),
			UI_HTML_Tag::create( 'td', $item->finishedAt ? date( 'd.m.Y', $item->finishedAt ) : '-' ),
		) );
	}
	$tbody		= UI_HTML_Tag::create( 'tbody', $rows );
	$runList	= UI_HTML_Tag::create( 'table', array( $tbody ), array( 'class' => 'table table-striped table-condensed' ) );
}
return UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'h4', 'Code' ),
		UI_HTML_Tag::create( 'xmp', join( PHP_EOL, $definitionCode ) ),
		UI_HTML_Tag::create( 'h4', 'Run List' ),
		$runList,
	), array( 'class' => 'content-panel-inner' ) )
), array( 'class' => 'content-panel' ) );

function removeEnvPath( $env, $string ): string
{
	return preg_replace( '@'.preg_quote( $env->uri, '@' ).'@', '', $string );
}
