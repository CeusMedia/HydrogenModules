<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\CLI\ArgumentParser;
use CeusMedia\Common\Exception\IO as IoException;
use CeusMedia\Common\Exception\Logic as LogicException;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\View;

class View_Admin_Log_Exception extends View
{
	/**
	 *	@return		void
	 */
	public function index(): void
	{
		$script	= 'ModuleAdminLogException.Index.init();';
		$this->env->getPage()->js->addScriptOnReady( $script );
	}

	/**
	 *	@return		void
	 */
	public function view(): void
	{
	}

	/**
	 *	@param		Entity_Log_Exception		$exception
	 *	@return		string|NULL
	 */
	public function renderRequestSection( Entity_Log_Exception $exception ): ?string
	{
		$exceptionRequest	= $exception->request;
		if( NULL === $exceptionRequest )
			return NULL;

		$xmpStyle	= 'overflow: auto; border: 1px solid gray; background-color: #EFEFEF; padding: 1em 2em';

		/** @var ArgumentParser|HttpRequest|Dictionary $exceptionRequest */
		$exceptionRequest		= unserialize( $exception->request );
		$sectionRequestHeaders	= '';
		if( $exceptionRequest instanceof HttpRequest ){
			$methodLine				= 'Method: '.$exceptionRequest->getMethod()->get().PHP_EOL;
			$lines					= $exceptionRequest->getHeaders()->render();
			$requestHeaders			= HtmlTag::create( 'xmp', $methodLine.$lines, ['style' => $xmpStyle] );
			$sectionRequestHeaders	= HtmlTag::create( 'h4', 'Request Headers' ).$requestHeaders.'<hr/>';
		}
		$heading				= HtmlTag::create( 'h4', 'Request Data' );
		$table					= $this->renderMapTable( $exceptionRequest->getAll() );
		$sectionRequestData		= $heading.$table;

		return $sectionRequestHeaders.$sectionRequestData;
	}

	/**
	 *	@param		Entity_Log_Request		$request
	 *	@return		string|NULL
	 */
	public function renderRequestSection2( Entity_Log_Request $request ): ?string
	{
		$xmpStyle	= 'overflow: auto; border: 1px solid gray; background-color: #EFEFEF; padding: 1em 2em';

		$sectionRequestHeaders	= '';
		$methodLine				= 'Method: '.$request->method.PHP_EOL;
		$lines					= join( PHP_EOL, json_decode( $request->headers, TRUE ) );
		$requestHeaders			= HtmlTag::create( 'xmp', $methodLine.$lines, ['style' => $xmpStyle] );
		$sectionRequestHeaders	= HtmlTag::create( 'h4', 'Request Headers' ).$requestHeaders;
		$pairs					= json_decode( $request->request, TRUE );
		$sectionRequestData		= HtmlTag::create( 'h4', 'Request Data' ).$this->renderMapTable( $pairs );
		return $sectionRequestHeaders.'<hr/>'.$sectionRequestData;
	}

	/**
	 *	@param		Entity_Log_Exception	$exception
	 *	@param		array					$exceptionEnv
	 *	@param		HttpRequest|Dictionary|Entity_Log_Request	$exceptionRequest
	 *	@return		string
	 */
	public function renderFactsSection( Entity_Log_Exception $exception, array $exceptionEnv, HttpRequest|Dictionary|Entity_Log_Request $exceptionRequest ): string
	{
		if( $exceptionRequest instanceof Entity_Log_Request ){
			$requestedPairs		= json_decode( $exceptionRequest->request, TRUE );
			$requestedPath		= $requestedPairs['__path'];
			$exceptionRequest	= Dictionary::create( $requestedPairs );
		}
		else{
			$requestedPath		= $exceptionRequest->get( '__path' );

		}
		$file		= preg_replace( "/^".preg_quote( $exceptionEnv['uri'], '/' )."/", './', $exception->file );
		$file		= preg_replace( "/^".preg_quote( $this->env->uri, '/' )."/", './', $file );
		$date		= date( 'Y.m.d', $exception->createdAt );
		$time		= date( 'H:i:s', $exception->createdAt );

		$facts	= [];
		/** @noinspection HtmlDeprecatedTag */
		/** @noinspection XmlDeprecatedElement */
		$facts['Message']	= '<big><strong>'.$exception->message.'</strong></big>';
		if( (int) $exception->code != 0 )
			$facts['Code']	= $exception->code;
		$facts['File (Line)']	= $file.' ('.$exception->line.')';
		$facts['Date (Time)']	= $date.' <small class="muted">('.$time.')</small>';
		$facts['Request Path']	= $requestedPath;
		$facts['App Name']		= $exceptionEnv['appName'];
		$facts['Base URL']		= $exceptionEnv['url'];
		$facts['Environment']	= $exceptionEnv['class'];
		$facts['Error Type']	= $exception->type;

		if( is_a( $exception->type, IoException::class, TRUE ) && '' !== trim( $exception->resource ?? '' ) )
			$facts['Resource']	= $exception->resource;
		if( is_a( $exception->type, LogicException::class, TRUE ) && '' !== trim( $exception->subject ?? '' ) )
			$facts['Subject']	= $exception->subject;


		$list	= [];
		foreach( $facts as $key => $value )
			$list[]	= HtmlTag::create( 'dt', $key ).HtmlTag::create( 'dd', $value ?: '&nbsp;' );
		return HtmlTag::create( 'dl', $list, ['class' => 'dl-horizontal'] );
	}

	/**
	 *	@param		Entity_Log_Exception	$exception
	 *	@return		string|NULL
	 */
	public function renderFileSection( Entity_Log_Exception $exception ): ?string
	{
		if( !file_exists( $exception->file ) )
			return NULL;

//		$fileLines	= FileReader::loadArray( $exception->file );
		$fileLines	= file( $exception->file );

		$nrLinesBefore	= 9;
		$nrLinesAfter	= 3;
		$firstLine	= max( 0, $exception->line - $nrLinesBefore - 1 );
		$fileLines	= array_slice( $fileLines, $firstLine, $nrLinesBefore + 1 + $nrLinesAfter );
		$lines		= [];
		foreach( $fileLines as $nr => $line ){
			/** @noinspection HtmlDeprecatedTag */
			/** @noinspection XmlDeprecatedElement */
			$lines[]	= HtmlTag::create( 'tr', [
				HtmlTag::create( 'th', $firstLine + $nr + 1 ),
				HtmlTag::create( 'td', '<tt>'.str_replace( "\t", "&nbsp;&nbsp;&nbsp;&nbsp;", $line ).'</tt>' ),
			], ['class' => $nr === $nrLinesBefore ? 'warning' : ''] );
		}
		$tbody		= HtmlTag::create( 'tbody', $lines );
		$lines		= HtmlTag::create( 'table', $tbody, [
			'class' => 'table table-striped table-condensed',
			'style'	=> 'border: 1px solid rgba(127, 127, 127, 0.5)',
		] );
		return HtmlTag::create( 'h4', 'File' ).'<div style="font-size: 0.85em">'.$lines.'</div>';
	}

	/**
	 *	@param		array		$map
	 *	@param		bool		$sort
	 *	@return		string
	 */
	public function renderMapTable( array $map, bool $sort = TRUE ): string
	{
		$rows	= [];
		if( $sort )
			ksort( $map );
		foreach( $map as $key => $value ){
			$key	= HtmlTag::create( 'div', $key, ['style' => 'font-family: monospace; font-size: 0.85em; letter-spacing: -0.5px'] );
			$type	= ucfirst( gettype( $value ) );
			$type	= HtmlTag::create( 'small', $type, ['class' => 'muted'] );
			$rows[]	= HtmlTag::create( 'tr', [
				HtmlTag::create( 'td', count( $rows ) + 1, ['style' => 'text-align: right'] ),
				HtmlTag::create( 'td', $key ),
				HtmlTag::create( 'td', $type, ['style' => 'text-align: right'] ),
//			HtmlTag::create( 'td', json_encode( $value ) ),
				HtmlTag::create( 'td', htmlentities( stripslashes( trim( json_encode( $value ), '"' ) ), ENT_QUOTES, 'utf-8' ) ),
			] );
		}
		$colgroup		= HtmlElements::ColumnGroup( '40px', '35%', '7%', '' );
		$thead			= HtmlTag::create( 'thead', HtmlTag::create( 'tr', [
			HtmlTag::create( 'th', '#', ['style' => 'text-align: right'] ),
			HtmlTag::create( 'th', 'Key' ),
			HtmlTag::create( 'th', 'Type', ['style' => 'text-align: right'] ),
			HtmlTag::create( 'th', 'Value' )
		] ) );
		$tbody	= HtmlTag::create( 'tbody', $rows );
		return HtmlTag::create( 'table', [$colgroup, $thead, $tbody], [
			'class'	=> 'table table-striped table-condensed',
			'style'	=> 'border: 1px solid rgba(127, 127, 127, 0.5)',
		] );
	}

	/**
	 *	@param		Entity_Log_Exception	$exception
	 *	@param		Dictionary|NULL			$exceptionSession
	 *	@return		string|NULL
	 */
	public function renderSessionSection( Entity_Log_Exception $exception, ?Dictionary $exceptionSession ): ?string
	{
		if( !$exceptionSession || !$exceptionSession->count() )
			return NULL;

		$sessionData	= $this->renderMapTable( $exceptionSession->getAll() );
		return HtmlTag::create( 'h4', 'Session Data' ).$sessionData;
	}

	/**
	 *	@param		Entity_Log_Request	$request
	 *	@return		string|NULL
	 */
	public function renderSessionSection2( Entity_Log_Request $request ): ?string
	{
		$data = json_decode( $request->session, TRUE );
		if( !$data || [] === $data )
			return NULL;

		$sessionData	= $this->renderMapTable( $data );
		return HtmlTag::create( 'h4', 'Session Data' ).$sessionData;
	}

	/**
	 *	@param		Entity_Log_Exception	$exception
	 *	@param		array					$exceptionEnv
	 *	@return		string
	 */
	public function renderTraceSection( Entity_Log_Exception $exception, array $exceptionEnv ): string
	{
		$xmpStyle	= 'overflow: auto; border: 1px solid gray; background-color: #EFEFEF; padding: 1em 2em';
		$realPath	= preg_replace( '@admin/?$@', '', realpath( $exceptionEnv['uri'] ) );
		$realPath	= rtrim( $realPath, '/' ).'/';

		$trace	= $exception->trace;
		$trace	= preg_replace( "/ ".preg_quote( $realPath, '/' )."/s", ' ', $trace );
		$trace	= '<xmp style="'.$xmpStyle.'">'.$trace.'</xmp>';

		return HtmlTag::create( 'h4', 'Stack Trace' ).$trace;
	}

	/**
	 *	@param		Entity_Log_Exception			$exception
	 *	@param		object|NULL		$user
	 *	@return		string|NULL
	 */
	public function renderUserSection( Entity_Log_Exception $exception, ?object $user ): ?string
	{
		if( !$user )
			return NULL;
		$data	= $this->renderMapTable( [
			'username'		=> $user->username,
			'fullname'		=> $user->firstname.' '.$user->surname,
			'email'			=> $user->email,
//		'status'		=> ...,
//		'role'			=> ...,
		] );
		return HtmlTag::create( 'h4', 'User' ).$data;
	}

	/**
	 *	@return		void
	 */
	protected function __onInit(): void
	{
		$this->env->getPage()->addCommonStyle( 'module.admin.log.exception.css' );
		$this->env->getPage()->js->addModuleFile( 'module.admin.log.exception.js' );
	}
}
