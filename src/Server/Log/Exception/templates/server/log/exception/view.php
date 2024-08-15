<?php

use CeusMedia\Common\Exception\IO as CommonIoException;
use CeusMedia\Common\Exception\Logic as CommonLogicException;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\Common\UI\VariableDumper;
use CeusMedia\HydrogenFramework\Environment;

/** @var Environment $env */
/** @var array $words */
/** @var object $entity */
/** @var int $page */

/*
 * Exception entity properties:
 * - exceptionId: string
 * - message: string
 * - code: int
 * - sqlCode: int
 * - file: string
 * - line: int
 * - type: string
 * - createdAt: int
 * - trace: string
 * - traceAsHtml: string
 * - traceAsString: string
 * - resource: ?string
 * - subject: ?string
 * - env: string (serialized object)
 * - request: string (serialized object)
 * - session: string (serialized object)
 */

$w	= (object) $words['view'];

$iconList	= HtmlTag::create( 'i', '', ['class' => "icon-arrow-left"] );
$iconRemove	= HtmlTag::create( 'i', '', ['class' => 'icon-trash icon-white'] );
if( $env->getModules()->has( 'UI_Font_FontAwesome' ) ){
	$iconList	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-list'] );
	$iconRemove	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] );
}

$file		= preg_replace( "/^".preg_quote( realpath( $env->uri ), '/' )."/", '.', $entity->file );
$date		= date( 'Y.m.d', $entity->createdAt );
$time		= date( 'H:i:s', $entity->createdAt );

$facts	= [];
/** @noinspection XmlDeprecatedElement */
/** @noinspection HtmlDeprecatedTag */
$facts['message']	= '<big><strong>'.$entity->message.'</strong></big>';
if( (int) $entity->code != 0 )
	$facts['code']	= $entity->code;
$facts['file']		= $file.' ('.$entity->line.')';
$facts['date']		= $date.' <small class="muted">('.$time.')</small>';
$facts['class']		= $entity->type;

$classes	= [$entity->type];
if( in_array( 'Exception_SQL', $classes ) ){
	if( isset( $entity->sqlCode ) ){
		$meaning	= getMeaningOfSQLSTATE( $env, $entity->sqlCode );
		$facts['sqlState']	= $entity->sqlCode.': '.$meaning;
	}
}
if( in_array( CommonIoException::class, $classes ) ){
	$facts['resource']	= $entity->resource;
}
if( in_array( CommonLogicException::class, $classes ) ){
	$facts['subject']	= $entity->subject;
}


	/**
	 *	Resolves SQLSTATE Code and returns its Meaning.
	 *	@access		public
	 *	@param		Environment	$env
	 *	@param		string		$SQLSTATE
	 *	@return		string
	 *	@see		http://developer.mimer.com/documentation/html_92/Mimer_SQL_Mobile_DocSet/App_Return_Codes2.html
	 *	@see		http://publib.boulder.ibm.com/infocenter/idshelp/v10/index.jsp?topic=/com.ibm.sqls.doc/sqls520.htm
	 */
	function getMeaningOfSQLSTATE( Environment $env, string $SQLSTATE ): string
	{
		$class1	= substr( $SQLSTATE, 0, 2 );
		$class2	= substr( $SQLSTATE, 2, 3 );

		$words	= $env->getLanguage()->getWords( 'server/log/exception/sqlstate' );
		return $words[$class1][$class2] ?? 'unknown';
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
$listFacts	= HtmlTag::create( 'dl', $list, ['class' => 'dl-horizontal'] );

//  --  TRACE  --  //
if( !empty( $entity->traceAsHtml ) )
	$trace	= $entity->traceAsHtml;
else if( !empty( $entity->traceAsString ) )
	$trace	= '<xmp style="overflow: auto; border: 1px solid gray; background-color: #EFEFEF; padding: 1em 2em;">'.$entity->traceAsString.'</xmp>';
else
	$trace	= '<xmp style="overflow: auto; border: 1px solid gray; background-color: #EFEFEF; padding: 1em 2em;">'.$entity->trace.'</xmp>';
$topicTrace	= '<h4>'.$w->topicTrace.'</h4>'.$trace;

//  --  REQUEST  --  //
$topicRequest	= '';
if( !empty( $entity->request ) ){
	$request	= unserialize( $entity->request );

	if( $request instanceof HttpRequest ){
		$rows	= [];
		foreach( $request->getHeaders()->getFields() as $field ){
			$value	= $field->getValue();
			if( $field->getName() === 'cookie' )
				$value	= str_replace( '; ', '<br/>', $value );
			$rows[]	= HtmlTag::create( 'tr', [
				HtmlTag::create( 'th', $field->getName() ),
				HtmlTag::create( 'td', $value ),
			] );
		}
		$headers		= HtmlTag::create( 'table', [
			HtmlElements::ColumnGroup( '20%', '' ),
			HtmlTag::create( 'tbody', $rows ),
		], ['class' => 'table table-condensed table-striped'] );
		$dumpRequest	= VariableDumper::dump( $request->getAll() );
	}
	else {
		$headers		= '';
		$dumpRequest	= VariableDumper::dump( $entity->request );
	}
	$topicRequest	= '
	<div class="request-data"><h4>'.$w->topicRequest.'</h4>'.$dumpRequest.'</div>
	<div class="request-headers"><h4>Request Headers</h4>'.nl2br( $headers ).'</div>';
}

//  --  SESSION  --  //
$topicSession	= '';
if( !empty( $entity->session ) ){
	$session	= unserialize( $entity->session );
	if( isset( $session['exception'] ) )
		unset( $session['exception'] );
	if( isset( $session['exceptionRequest'] ) )
		unset( $session['exceptionRequest'] );
	$dumpSession	= VariableDumper::dump( $session );
	$topicSession	= '<h4>'.$w->topicSession.'</h4>
	'.$dumpSession;
}

//  --  ENV  --  //
$topicEnv	= '';
if( !empty( $entity->env ) ){
	$env		= unserialize( $entity->env );
	$dumpEnv	= VariableDumper::dump( $env );
	$topicEnv	= '<h4>'.$w->topicEnv.'</h4>
	'.$dumpEnv;
}

return HtmlTag::create( 'div', [
	HtmlTag::create( 'div', [
		HtmlTag::create( 'div', [
			HtmlTag::create( 'h3', 'Exception' ),
			HtmlTag::create( 'div', [
				$listFacts,
				'<hr/>',
				$topicTrace,
				$topicEnv,
				$topicRequest,
				$topicSession,
				HtmlTag::create( 'div', [
					HtmlTag::create( 'a', $iconList.'&nbsp;'.$w->buttonCancel, [
						'href'	=> './server/log/exception'.( $page ? '/'.$page : '' ),
						'class'	=> 'btn',
					] ),
					HtmlTag::create( 'a', $iconRemove.'&nbsp;'.$w->buttonRemove, [
						'href'	=> './server/log/exception/remove/'.$entity->exceptionId.'/'.$page,
						'class'	=> 'btn btn-danger',
					] ),
				], ['class' => 'buttonbar'] ),
			], ['class' => 'content-panel-inner'] )
		], ['class' => 'content-panel'] )
	], ['class' => 'span12'] )
], ['class' => 'row-fluid'] );

