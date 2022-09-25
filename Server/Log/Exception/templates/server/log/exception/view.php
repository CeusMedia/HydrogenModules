<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w	= (object) $words['view'];

$iconList	= HtmlTag::create( 'i', '', array( 'class' => "icon-arrow-left" ) );
$iconRemove	= HtmlTag::create( 'i', '', array( 'class' => 'icon-trash icon-white' ) );
if( $env->getModules()->has( 'UI_Font_FontAwesome' ) ){
	$iconList	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-list' ) );
	$iconRemove	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );
}

$file		= preg_replace( "/^".preg_quote( realpath( $env->uri ), '/' )."/", '.', $exception->file );
$date		= date( 'Y.m.d', $exception->createdAt );
$time		= date( 'H:i:s', $exception->createdAt );

$facts	= [];
$facts['message']	= '<big><strong>'.$exception->message.'</strong></big>';
if( (int) $exception->code != 0 )
	$facts['code']	= $exception->code;
$facts['file']		= $file.' ('.$exception->line.')';
$facts['date']		= $date.' <small class="muted">('.$time.')</small>';
$facts['class']		= $exception->type;

$classes	= array( $exception->type );
if( in_array( 'Exception_SQL', $classes ) ){
	if( isset( $exception->sqlCode ) ){
		$meaning	= getMeaningOfSQLSTATE( $env, $exception->sqlCode );
		$facts['sqlState']	= $exception->sqlCode.': '.$meaning;
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

$list	= [];
foreach( $facts as $key => $value ){
	$list[]	= HtmlTag::create( 'dt', $words['view']['label'.ucfirst( $key)] );
	$list[]	= HtmlTag::create( 'dd', $value );
}
$listFacts	= HtmlTag::create( 'dl', $list, array( 'class' => 'dl-horizontal' ) );

//  --  TRACE  --  //
if( !empty( $exception->traceAsHtml ) )
	$trace	= $exception->traceAsHtml;
else if( !empty( $exception->traceAsString ) )
	$trace	= '<xmp style="overflow: auto; border: 1px solid gray; background-color: #EFEFEF; padding: 1em 2em;">'.$exception->traceAsString.'</xmp>';
else
	$trace	= '<xmp style="overflow: auto; border: 1px solid gray; background-color: #EFEFEF; padding: 1em 2em;">'.$exception->trace.'</xmp>';
$topicTrace	= '<h4>'.$w->topicTrace.'</h4>
'.$trace.'';

//  --  REQUEST  --  //
$topicRequest	= '';
if( !empty( $exception->request ) ){
	$request	= unserialize( $exception->request );

	if( $request instanceof Net_HTTP_Request ){
		$rows	= [];
		foreach( $request->getHeaders()->getFields() as $field ){
			$value	= $field->getValue();
			if( $field->getName() === 'cookie' )
				$value	= str_replace( '; ', '<br/>', $value );
			$rows[]	= HtmlTag::create( 'tr', array(
				HtmlTag::create( 'th', $field->getName() ),
				HtmlTag::create( 'td', $value ),
			) );
		}
		$headers		= HtmlTag::create( 'table', array(
			HtmlElements::ColumnGroup( '20%', '' ),
			HtmlTag::create( 'tbody', $rows ),
		), array( 'class' => 'table table-condensed table-striped' ) );
		$dumpRequest	= UI_VariableDumper::dump( $request->getAll() );
	}
	else {
		$headers		= '';
		$dumpRequest	= UI_VariableDumper::dump( $exception->request );
	}
	$topicRequest	= '
	<div class="request-data"><h4>'.$w->topicRequest.'</h4>'.$dumpRequest.'</div>
	<div class="request-headers"><h4>Request Headers</h4>'.nl2br( $headers ).'</div>';
}

//  --  SESSION  --  //
$topicSession	= '';
if( !empty( $exception->session ) ){
	$session	= unserialize( $exception->session );
	if( isset( $session['exception'] ) )
		unset( $session['exception'] );
	if( isset( $session['exceptionReqeuest'] ) )
		unset( $session['exceptionReqeuest'] );
	$dumpSession	= UI_VariableDumper::dump( $session );
	$topicSession	= '<h4>'.$w->topicSession.'</h4>
	'.$dumpSession.'';
}

//  --  ENV  --  //
$topicEnv	= '';
if( !empty( $exception->env ) ){
	$env		= unserialize( $exception->env );
	$dumpEnv	= UI_VariableDumper::dump( $env );
	$topicEnv	= '<h4>'.$w->topicEnv.'</h4>
	'.$dumpEnv.'';
}

return '
<div class="row-fluid">
	<div class="span12">
		<div class="content-panel">
			<h3>Exception</h3>
			<div class="content-panel-inner">
				'.$listFacts.'
				<hr/>
				'.$topicTrace.'
				'.$topicEnv.'
				'.$topicRequest.'
				'.$topicSession.'
				<div class="buttonbar">
					'.HtmlTag::create( 'a', $iconList.'&nbsp;'.$w->buttonCancel, array(
						'href'	=> './server/log/exception'.( $page ? '/'.$page : '' ),
						'class'	=> 'btn',
					) ).'
					'.HtmlTag::create( 'a', $iconRemove.'&nbsp;'.$w->buttonRemove, array(
						'href'	=> './server/log/exception/remove/'.$exception->exceptionId.'/'.$page,
						'class'	=> 'btn btn-danger',
					) ).'
				</div>
			</div>
		</div>
	</div>
</div>
';
