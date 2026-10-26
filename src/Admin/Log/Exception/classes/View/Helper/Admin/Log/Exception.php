<?php
use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\CLI\ArgumentParser;
use CeusMedia\Common\Exception\IO as IoException;
use CeusMedia\Common\Exception\Logic as LogicException;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class View_Helper_Admin_Log_Exception extends CeusMedia\HydrogenFramework\View\Helper\Abstraction
{
	protected ?Entity_Log_Exception $exceptionEntity		= NULL;
	protected ?Entity_Log_Request $exceptionRequestEntity	= NULL;

	protected ?object $requestObject						= NULL;
	protected ?Dictionary $envDictionary					= NULL;
	protected ?Dictionary $sessionDictionary				= NULL;
	protected ?Dictionary $headerDictionary					= NULL;

	public function getExceptionRequestHeaders(): Dictionary
	{
		if( NULL !== $this->headerDictionary )
			return $this->headerDictionary;

		if( NULL !== $this->exceptionRequestEntity ){
			$headersJson	= $this->exceptionRequestEntity->headers ?? '';
			$headersJson	= trim( str_replace( 'null', '', $headersJson ) );
			if( '' !== $headersJson ){
				$headers	= [];
				foreach( json_decode( $headersJson, TRUE ) ?: [] as $line ){
					[$key, $value]	= explode( ':', $line, 2 );
					$headers[$key]	= trim( $value );
				}
				$this->headerDictionary	= new Dictionary( $headers );
				return $this->headerDictionary;
			}
		}

		if( NULL === $this->exceptionEntity )
			throw new RuntimeException( 'No exception entity set' );

		$exceptionRequestObject	= $this->getExceptionRequestObject();
		if( $exceptionRequestObject instanceof HttpRequest ){
			$lines	= [];
			foreach( $exceptionRequestObject->getHeaders()->getFields() as $field )
				$lines[$field->getName()]	= $field->getValue();
			$this->headerDictionary	= new Dictionary( $lines );
			return $this->headerDictionary;
		}
		return new Dictionary();
	}

	public function getExceptionEnv(): Dictionary
	{
		if( NULL !== $this->envDictionary )
			return $this->envDictionary;

		if( NULL === $this->exceptionEntity )
			throw new RuntimeException( 'No exception entity set' );

		$data	= [];
		if( '' !== ( $this->exceptionEntity->env ?? '' ) )
			$data	= unserialize( $this->exceptionEntity->env );

		$this->envDictionary	= Dictionary::create( $data );
		return $this->envDictionary;

	}

	protected function getExceptionRequestObject(): ArgumentParser|HttpRequest|Dictionary|NULL
	{
		if( $this->requestObject )
			return $this->requestObject;

		if( NULL === $this->exceptionEntity )
			throw new RuntimeException( 'No exception entity set' );

		if( '' === trim( $this->exceptionEntity->request ?? '' ) )
			return NULL;

		/** @var ArgumentParser|HttpRequest|Dictionary $exceptionRequestObject */
		$exceptionRequestObject	= unserialize( $this->exceptionEntity->request );
		$this->requestObject	= $exceptionRequestObject;
		return $exceptionRequestObject;
	}

	public function getExceptionRequestParameters(): Dictionary
	{
		if( NULL !== $this->exceptionRequestEntity ){
			$source	= $this->exceptionRequestEntity->request ?? '[]';
			return new Dictionary( json_decode( $source, TRUE ) ?: [] );
		}
		else if( NULL !== $this->exceptionEntity ){
			$object	= $this->getExceptionRequestObject();
			if( NULL !== $object )
				return $object->getAll( NULL, TRUE );
		}
		return new Dictionary();
	}

	public function getExceptionRequestMethod(): string
	{
		if( NULL !== $this->exceptionRequestEntity )
			return $this->exceptionRequestEntity->method;

		if( NULL === $this->exceptionEntity )
			throw new RuntimeException( 'No exception entity set' );

		$exceptionRequestObject	= $this->getExceptionRequestObject();
		if( $exceptionRequestObject instanceof HttpRequest )
			return $exceptionRequestObject->getMethod()->get();
		return '';
	}

	public function getExceptionSession(): Dictionary
	{
		if( NULL !== $this->sessionDictionary )
			return $this->sessionDictionary;

		if( NULL === $this->exceptionEntity )
			throw new RuntimeException( 'No exception entity set' );

		$data	= match( NULL !== $this->exceptionRequestEntity ){
			TRUE	=> json_decode( $this->exceptionRequestEntity->session ?? '[]', TRUE ) ?? [],
			FALSE	=> unserialize( $this->exceptionEntity->session ?? 'a:0:{}' ) ?: [],
		};
		if( is_array( $data ) )
			$data	= Dictionary::create( $data );
		$this->sessionDictionary	= $data;
		return $this->sessionDictionary;
	}

	public function getRelatedUserEntity(): ?Entity_User
	{
		if( NULL === $this->exceptionEntity )
			throw new RuntimeException( 'No exception entity set' );

		$session	= $this->getExceptionSession();
		$userId		= $session->get( Logic_Authentication::$sessionKeyAuthUserId, '0' );
		if( '0' === (string) $userId )
			return NULL;
		return Logic_User::getInstance( $this->env )->getUser( $userId );
	}

	public function render(): string
	{
		return join( '<hr/>', array_filter( [
			'facts'		=> $this->renderFactsSection(),
			'file'		=> $this->renderFileSection(),
			'trace'		=> $this->renderTraceSection(),
			'request'	=> $this->renderRequestSection(),
			'session'	=> $this->renderSessionSection(),
			'user'		=> $this->renderUserSection(),
			'headers'	=> $this->renderHeaderSection(),
		] ) );
	}

	public function setExceptionEntity( Entity_Log_Exception $exceptionEntity ): static
	{
		$this->exceptionEntity	= $exceptionEntity;
		$this->requestObject	= NULL;
		$this->envDictionary	= NULL;

		if( 0 !== (int) $exceptionEntity->requestId && class_exists( 'Logic_Server_Log_Request' ) ){
			$logic	= Logic_Server_Log_Request::getInstance( $this->env );
			$this->exceptionRequestEntity = $logic->get( $exceptionEntity->requestId );
		}

		return $this;
	}


	//  --  PROTECTED  --  //


	protected function renderFactsSection(): string
	{
		if( NULL === $this->exceptionEntity )
			throw new RuntimeException( 'No exception entity set' );

		$exceptionEnv	= $this->getExceptionEnv();
		$requestedPairs	= $this->getExceptionRequestParameters();
		$requestedPath	= '-';
		if( NULL !== $this->exceptionRequestEntity ){
			$requestedPath		= $requestedPairs['__path'] ?? '-';
			if( 'CLI' === $this->getExceptionRequestMethod() ){
				$script		= ( $requestedPairs['path'] ?? '' ).ltrim( $requestedPairs['script'] ?? '', '.' );
				$commands	= join( ' ', $requestedPairs['commands'] ?? [] );
				$requestedPath	= $script.' '.$commands;
			}
		}

		$file		= preg_replace( "/^".preg_quote( $exceptionEnv->get( 'uri' ), '/' )."/", '', $this->exceptionEntity->file );
		$file		= preg_replace( "/^".preg_quote( $this->env->uri, '/' )."/", '', $file );
		$date		= date( 'Y.m.d', $this->exceptionEntity->createdAt );
		$time		= date( 'H:i:s', $this->exceptionEntity->createdAt );

		$facts	= [];
		/** @noinspection HtmlDeprecatedTag */
		/** @noinspection XmlDeprecatedElement */
		$facts['Message']	= '<big><strong>'.$this->exceptionEntity->message.'</strong></big>';
		if( 0 != (int) $this->exceptionEntity->code )
			$facts['Code']	= $this->exceptionEntity->code;
		$facts['File (Line)']	= $file.' ('.$this->exceptionEntity->line.')';
		$facts['Date (Time)']	= $date.' <small class="muted">('.$time.')</small>';
		$facts['Request Method']	= $this->getExceptionRequestMethod();
		$facts['Request Path']	= $requestedPath;
		$facts['App Name']		= $exceptionEnv->get( 'appName' );
		$facts['Base URL']		= $exceptionEnv->get( 'url' );
		$facts['Environment']	= $exceptionEnv->get( 'class' );
		$facts['Error Type']	= $this->exceptionEntity->type;

		if( is_a( $this->exceptionEntity->type, IoException::class, TRUE ) && '' !== trim( $this->exceptionEntity->resource ?? '' ) )
			$facts['Resource']	= $this->exceptionEntity->resource;
		if( is_a( $this->exceptionEntity->type, LogicException::class, TRUE ) && '' !== trim( $this->exceptionEntity->subject ?? '' ) )
			$facts['Subject']	= $this->exceptionEntity->subject;

		$list	= [];
		foreach( $facts as $key => $value )
			$list[]	= HtmlTag::create( 'dt', $key ).HtmlTag::create( 'dd', $value ?: '&nbsp;' );
		return HtmlTag::create( 'dl', $list, ['class' => 'dl-horizontal'] );
	}

	protected function renderFileSection(): ?string
	{
		if( NULL === $this->exceptionEntity )
			throw new RuntimeException( 'No exception entity set' );

		$exception	= $this->exceptionEntity;
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
				HtmlTag::create( 'td', $line ),
			], ['class' => $nr === $nrLinesBefore ? 'warning' : ''] );
		}
		$tbody		= HtmlTag::create( 'tbody', $lines );
		$lines		= HtmlTag::create( 'table', $tbody, ['class' => 'table table-condensed'] );
		$heading	= HtmlTag::create( 'h4', 'File' );
		$container	= HtmlTag::create( 'div', $lines, ['class' => 'section-content-container lines'] );
		return $heading.$container;
	}

	protected function renderMapTable( array|Dictionary $map, bool $sort = TRUE ): string
	{
		$map	= is_object( $map ) ? $map->getAll() : $map;
		$rows	= [];
		if( $sort )
			ksort( $map );
		foreach( $map as $key => $value ){
			$key	= HtmlTag::create( 'div', $key, ['class' => 'section-table-key'] );
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
			'class'	=> 'table table-striped table-condensed section-content-container',
		] );
	}

	protected function renderHeaderSection(): ?string
	{
		$headers	= [];
		foreach( $this->getExceptionRequestHeaders()->getAll() as $key => $value )
			$headers[]	= $key.': '.$value;
		$headersLines	= join( PHP_EOL, $headers );

		if( [] === $headers )
			return '';

		$methodLine			= 'Method: '.$this->getExceptionRequestMethod().PHP_EOL;
		$heading			= HtmlTag::create( 'h4', 'Request Headers' );
		$code				= HtmlTag::create( 'xmp', $methodLine.$headersLines );
		$requestHeaders		= HtmlTag::create( 'div', $code, ['class' => 'section-content-container'] );
		return $heading.$requestHeaders;
	}

	protected function renderRequestSection(): ?string
	{
		$heading	= HtmlTag::create( 'h4', 'Request Data' );
		$table		= $this->renderMapTable( $this->getExceptionRequestParameters() );
		return $heading.$table;
	}

	protected function renderSessionSection(): ?string
	{
		if( NULL === $this->exceptionEntity )
			throw new RuntimeException( 'No exception entity set' );

		$exceptionSession	= $this->getExceptionSession();
		if( 0 === $exceptionSession->count() )
			return NULL;

		$heading		= HtmlTag::create( 'h4', 'Session Data' );
		$sessionData	= $this->renderMapTable( $exceptionSession->getAll() );
		return $heading.$sessionData;
	}

	protected function renderTraceSection(): string
	{
		$exceptionEnv	= $this->getExceptionEnv();

		$realPath	= preg_replace( '@admin/?$@', '', realpath( $exceptionEnv->get( 'uri' ) ) );
		$realPath	= rtrim( $realPath, '/' ).'/';

		$trace		= $this->exceptionEntity->trace;
		$heading	= HtmlTag::create( 'h4', 'Stack Trace' );
		$container	= $this->renderStackTrace( $trace, $realPath, 'section-content-container' );
		return $heading.$container;
	}

	protected function renderStackTrace( string $trace, string $pathPrefix, string $cssClass = '' ): string
	{
		$html	= '<div class="stacktrace '.$cssClass.'">';

		foreach( preg_split( '/\R/', trim( $trace ) ) as $line ){
			if( !preg_match( '/^#(\d+)\s+(.*)$/', trim( $line ), $matches ) )
				continue;

			$number	= $matches[1];
			$entry	= $matches[2];

			if( '{main}' === $entry )
				continue;

			$call	= $entry;

			// Datei und Zeilennummer am Anfang extrahieren.
			$file		= NULL;
			$lineNumber	= NULL;
			if( preg_match( '/^(.+?)\((\d+)\):\s*(.*)$/', $entry, $m ) ){
				$file		= $m[1];
				$lineNumber	= $m[2];
				$call		= $m[3];
			}

			$call	= str_replace( '[internal function]: ', '', $call );
			$call	= preg_replace( '/'.preg_quote( '()', '/' ).'$/', '', trim( $call ) );
			$html .= '<div class="stacktrace-entry">';

			$html .= '<div class="stacktrace-call">';
			$html .= '<span class="stacktrace-number">#'. htmlspecialchars( $number + 1 ) . '</span> ';
			$html .= htmlspecialchars( $call );
			$html .= '</div>';

			if( NULL !== $file ){
				$file	= preg_replace( "/".preg_quote( $pathPrefix, '/' )."/s", '', $file );
				$html .= '<div class="stacktrace-location">';
				$html .= '<span class="stacktrace-file">' . htmlspecialchars( $file ) . '</span>';
				if( NULL !== $lineNumber )
					$html .= '<span class="stacktrace-line">' . htmlspecialchars( '('.$lineNumber.')' ) . '</span>';
				$html .= '</div>';
			}
			$html .= '</div>';
		}
		return $html . '</div>';
	}

	protected function renderUserSection(): ?string
	{
		$user	= $this->getRelatedUserEntity();
		if( NULL === $user )
			return NULL;

		$data	= $this->renderMapTable( [
			'username'		=> $user->username,
			'fullname'		=> $user->firstname.' '.$user->surname,
			'email'			=> $user->email,
//		'status'		=> ...,
//		'role'			=> ...,
		], FALSE );
		return HtmlTag::create( 'h4', 'User' ).$data;
	}
}
