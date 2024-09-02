<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\CLI\ArgumentParser;
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

	public function renderRequestSection( $exception, $exceptionRequest ): ?string
	{
		if( !$exceptionRequest )
			return NULL;

		$xmpStyle	= 'overflow: auto; border: 1px solid gray; background-color: #EFEFEF; padding: 1em 2em';

		$sectionRequestHeaders	= '';
		if( get_class( $exceptionRequest ) !== ArgumentParser::class ){
			$methodLine				= 'Method: '.$exceptionRequest->getMethod()->get().PHP_EOL;
			$lines					= $exceptionRequest->getHeaders()->render();
			$requestHeaders			= HtmlTag::create( 'xmp', $methodLine.$lines, ['style' => $xmpStyle] );
			$sectionRequestHeaders	= HtmlTag::create( 'h4', 'Request Headers' ).$requestHeaders;
		}
		$sectionRequestData			= HtmlTag::create( 'h4', 'Request Data' ).$this->renderMapTable( $exceptionRequest->getAll() );
		return $sectionRequestHeaders.'<hr/>'.$sectionRequestData;
	}

	function renderFactsSection( object $exception, array $exceptionEnv, $exceptionRequest ): string
	{
//	$file		= preg_replace( "/^".preg_quote( realpath( $this->env->uri ), '/' )."/", './', $exception->file );
		$file		= preg_replace( "/^".preg_quote( $this->env->uri, '/' )."/", './', $exception->file );
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
		$facts['Reqest Path']	= $exceptionRequest->get( '__path' ).'&nbsp;';
		$facts['App Name']		= $exceptionEnv['appName'];
		$facts['Base URL']		= $exceptionEnv['url'];
		$facts['Environment']	= $exceptionEnv['class'];

		$list	= [];
		foreach( $facts as $key => $value )
			$list[]	= HtmlTag::create( 'dt', $key ).HtmlTag::create( 'dd', $value );
		return HtmlTag::create( 'dl', $list, ['class' => 'dl-horizontal'] );
	}

	function renderFileSection( object $exception ): ?string
	{
		if( !file_exists( $exception->file ) )
			return NULL;

//	$fileLines	= FileReader::loadArray( $exception->file );
		$fileLines	= file( $exception->file );
		$firstLine	= max( 0, $exception->line - 5 );
		$fileLines	= array_slice( $fileLines, $firstLine, 11 );
		$lines		= [];
		foreach( $fileLines as $nr => $line ){
			/** @noinspection HtmlDeprecatedTag */
			/** @noinspection XmlDeprecatedElement */
			$lines[]	= HtmlTag::create( 'tr', [
				HtmlTag::create( 'th', $firstLine + $nr + 1 ),
				HtmlTag::create( 'td', '<tt>'.str_replace( "\t", "&nbsp;&nbsp;&nbsp;&nbsp;", $line ).'</tt>' ),
			], ['class' => $nr === 5 ? 'warning' : ''] );
		}
		$tbody		= HtmlTag::create( 'tbody', $lines );
		$lines		= HtmlTag::create( 'table', $tbody, [
			'class' => 'table table-striped table-condensed',
			'style'	=> 'border: 1px solid rgba(127, 127, 127, 0.5)',
		] );
		return HtmlTag::create( 'h4', 'File' ).$lines;
	}

	function renderMapTable( array $map, $sort = TRUE ): string
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


	public function renderSessionSection( object $exception, ?Dictionary $exceptionSession ): ?string
	{
		if( !$exceptionSession || !$exceptionSession->count() )
			return NULL;

		$sessionData	= $this->renderMapTable( $exceptionSession->getAll() );
		return HtmlTag::create( 'h4', 'Session Data' ).$sessionData;
	}

	public function renderTraceSection( object $exception ): string
	{
		$xmpStyle	= 'overflow: auto; border: 1px solid gray; background-color: #EFEFEF; padding: 1em 2em';

		if( isset( $exception->traceAsHtml ) )
			$trace	= $exception->traceAsHtml;
		else if( isset( $exception->traceAsString ) ){
			$trace	= $exception->traceAsString;
			$trace	= preg_replace( "/ ".preg_quote( realpath( $this->env->uri ), '/' )."/s", ' ./', $trace );
			$trace	= preg_replace( "/ ".preg_quote( $this->env->uri, '/' )."/s", ' ./', $trace );
			$trace	= '<xmp style="'.$xmpStyle.'">'.$trace.'</xmp>';
		}
		else{
			$trace	= $exception->trace;
			$trace	= preg_replace( "/ ".preg_quote( realpath( $this->env->uri ), '/' )."/s", ' ./', $trace );
			$trace	= preg_replace( "/ ".preg_quote( $this->env->uri, '/' )."/s", ' ./', $trace );
			$trace	= '<xmp style="'.$xmpStyle.'">'.$trace.'</xmp>';
		}
		return HtmlTag::create( 'h4', 'Stack Trace' ).$trace;
	}

	function renderUserSection( object $exception, ?object $user ): ?string
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
