<?php
class View_Helper_Mail_View_Source{

	protected $env;
	protected $mail;
	protected $logicMail;
	protected $mode			= 0;

	const MODE_NORMAL		= 0;
	const MODE_CONDENSED	= 1;

	public function __construct( $env ){
		$this->env			= $env;
		$this->logicMail	= $env->getLogic()->get( 'Mail' );
		$this->libraries	= $this->logicMail->detectAvailableMailLibraries();
	}

	public function render(){
		if( !$this->mailObject )
			throw new RuntimeException( 'No mail object set' );

		$usedLibrary	= $this->logicMail->detectMailLibraryFromMailObjectInstance( $this->mailObject );
		if( !( $this->libraries & $usedLibrary ) ){
			$libraryKey	= Alg_Object_Constant::staticGetKeyByValue( 'Logic_Mail', $usedLibrary );
			return '- used mail library ('.$libraryKey.') is not supported anymore or yet -';
		}
		$message	= $this->mailObject->mail;

		$code	= '';
		if( $usedLibrary == Logic_Mail::LIBRARY_COMMON )										//  mail uses library CeusMedia/Common
			$code	= $message->getBody();														//  @todo find better way: currently only parts content displayed but no headers
		else if( $usedLibrary == Logic_Mail::LIBRARY_MAIL_V1 )									//  mail uses library CeusMedia/Mail version 1
			$code	= CeusMedia\Mail\Renderer::render( $message );								//  @todo find better way: currently only parts content displayed but no headers
		else if( $usedLibrary == Logic_Mail::LIBRARY_MAIL_V2 )									//  mail uses library CeusMedia/Mail version 1
			$code	= CeusMedia\Mail\Message\Renderer::render( $message );						//  @todo find better way: currently only parts content displayed but no headers
		else
			throw new RangeException( 'No source renderer for mail object available' );

		switch( $this->mode ){
			case self::MODE_CONDENSED:
				$code	= $this->shortenMailCode( $code );
				break;
			case self::MODE_NORMAL:
				break;
			default:
				throw new RangeException( 'Invalid render mode' );
		}
		return $code;
	}

	public function setMail( $mailObjectOrId ){
		if( is_int( $mailObjectOrId ) )
			$mailObjectOrId	= $this->logicMail->getMail( $mailObjectOrId );
		if( !is_object( $mailObjectOrId ) )
			throw new InvalidArgumentException( 'Argument must be integer or object' );
		$this->setMailObjectInstance( $this->mail->object->instance );
		return $this;
	}

	public function setMailObjectInstance( Mail_Abstract $mail ){
		$this->mailObject	= $mail;
		return $this;
	}

	public function setMode( $mode ){
		if( !in_array( $mode, array( self::MODE_NORMAL, self::MODE_CONDENSED ) ) )
			throw new RangeException( 'Invalid mode' );
		$this->mode	= $mode;
	}

	protected function shortenMailCode( $code ){
		$status	= 0;
		$list	= array();
		foreach( explode( PHP_EOL, $code ) as $nr => $line ){
			$isEmpty	= !strlen( trim( $line ) );
			$isBased	= preg_match( '/^[\S]{74,80}$/', trim( $line ) );
			if( !$isEmpty && !$isBased ){
				if( $status === 3 ){
					$status	= 0;
					continue;
				}
				$status	= 0;
			}
			else if( $isEmpty )
				$status	= 1;
			else if( $status === 1 && $isBased )
				$status++;
			else if( $status === 2 && $isBased ){
				$list[count( $list ) - 1]	= '[data encoded with base64]';
				$status++;
			}
			if( $status === 3 )
				continue;
			$list[]	= $line;
		}
		return implode( PHP_EOL, $list );
	}
}
?>
