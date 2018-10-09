<?php

$w	= (object) $words['view'];

$iconList	= UI_HTML_Tag::create( 'i', '', array( 'class' => "icon-arrow-left" ) );
$iconRemove	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-trash icon-white' ) );
if( $env->getModules()->has( 'UI_Font_FontAwesome' ) ){
	$iconList	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-list' ) );
	$iconRemove	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );
}

if( isset( $exception->exception ) ){
	$exception->message	= $exception->exception->getMessage();
	$exception->code	= $exception->exception->getCode();
	$exception->file	= $exception->exception->getFile();
	$exception->line	= $exception->exception->getLine();
	$exception->trace	= $exception->exception->getTraceAsString();
}

$file		= preg_replace( "/^".preg_quote( realpath( $env->uri ), '/' )."/", '.', $exception->file );
$date		= date( 'Y.m.d', $exception->timestamp );
$time		= date( 'H:i:s', $exception->timestamp );

$facts	= array();
$facts['message']	= '<big><strong>'.$exception->message.'</strong></big>';
if( (int) $exception->code != 0 )
	$facts['code']	= $exception->code;
$facts['file']		= $file.' ('.$exception->line.')';
$facts['date']		= $date.' <small class="muted">('.$time.')</small>';
$facts['class']		= $exception->class;
$facts['parents']		= join( ', ', $exception->classParents );
$facts['interfaces']	= join( ', ', $exception->classInterfaces );

$classes	= array_values( array( $exception->class ) + $exception->classParents );
if( in_array( 'Exception_SQL', $classes ) ){
	if( isset( $exception->sqlState ) ){
		$meaning	= getMeaningOfSQLSTATE( $env, $exception->sqlState );
		$facts['sqlState']	= $exception->sqlState.': '.$meaning;
	}
}
if( in_array( 'Exception_IO', $classes ) ){
	$facts['resource']	= $exception->resource;
}
if( in_array( 'Exception_Logic', $classes ) ){
	$facts['subject']	= $exception->subject;
}


	/**
	 *	Resolves SQLSTATE Code and returns its Meaning.
	 *	@access		protected
	 *	@return		string
	 *	@see		http://developer.mimer.com/documentation/html_92/Mimer_SQL_Mobile_DocSet/App_Return_Codes2.html
	 *	@see		http://publib.boulder.ibm.com/infocenter/idshelp/v10/index.jsp?topic=/com.ibm.sqls.doc/sqls520.htm
	 */
	function getMeaningOfSQLSTATE( $env, $SQLSTATE ){
		$class1	= substr( $SQLSTATE, 0, 2 );
		$class2	= substr( $SQLSTATE, 2, 3 );

		$words		= $env->getLanguage()->getWords( 'server/log/exception/sqlstate' );
		if( isset( $words[$class1][$class2] ) )
			return $words[$class1][$class2];
		return 'unknown';
	}


/*$helperFacts	= new View_Helper_Mail_Exception_Facts( $env );
$helperFacts->setException( $data['exception'] );
if( !( isset( $data['showPrevious'] ) && !$data['showPrevious'] ) )
	$helperFacts->setShowPrevious( TRUE );
$list	= $helperFacts->render();*/

$list	= array();
foreach( $facts as $key => $value ){
	$list[]	= UI_HTML_Tag::create( 'dt', $words['view']['label'.ucfirst( $key)] );
	$list[]	= UI_HTML_Tag::create( 'dd', $value );
}
$list	= UI_HTML_Tag::create( 'dl', $list, array( 'class' => 'dl-horizontal' ) );

//  --  TRACE  --  //
if( isset( $exception->traceAsHtml ) )
	$trace	= $exception->traceAsHtml;
else if( isset( $exception->traceAsString ) )
	$trace	= '<xmp style="overflow: auto; border: 1px solid gray; background-color: #EFEFEF; padding: 1em 2em;">'.$exception->traceAsString.'</xmp>';
else
	$trace	= '<xmp style="overflow: auto; border: 1px solid gray; background-color: #EFEFEF; padding: 1em 2em;">'.$exception->trace.'</xmp>';
$topicTrace	= '<h4>'.$w->topicTrace.'</h4>
'.$trace.'';

//  --  REQUEST  --  //
$topicRequest	= '';
if( isset( $exception->request ) ){
	$dumpRequest	= UI_VariableDumper::dump( $exception->request );
	$topicRequest	= '<h4>'.$w->topicRequest.'</h4>
	'.$dumpRequest.'';
}

//  --  SESSION  --  //
$topicSession	= '';
if( isset( $exception->session ) ){
	if( isset( $exception->session['exception'] ) )
		unset( $exception->session['exception'] );
	$dumpSession	= UI_VariableDumper::dump( $exception->session );
	$topicSession	= '<h4>'.$w->topicSession.'</h4>
	'.$dumpSession.'';
}


return '
<div class="row-fluid">
	<div class="span12">
		<div class="content-panel">
			<h3>Exception</h3>
			<div class="content-panel-inner">
				'.$list.'
				<hr/>
				'.$topicTrace.'
				'.$topicRequest.'
				'.$topicSession.'
				<div class="buttonbar">
					'.UI_HTML_Tag::create( 'a', $iconList.'&nbsp;'.$w->buttonCancel, array(
						'href'	=> './server/log/exception'.( $page ? '/'.$page : '' ),
						'class'	=> 'btn',
					) ).'
					'.UI_HTML_Tag::create( 'a', $iconRemove.'&nbsp;'.$w->buttonRemove, array(
						'href'	=> './server/log/exception/remove/'.$exception->id,
						'class'	=> 'btn btn-danger',
					) ).'
				</div>
			</div>
		</div>
	</div>
</div>
';
