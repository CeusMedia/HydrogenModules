<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\Alg\Obj\Constant as ObjectConstants;
use CeusMedia\Common\Exception\Data\Ambiguous as AmbiguousDataException;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\Mail\Renderer as MailRendererV1;
use CeusMedia\Mail\Message\Renderer as MailRendererV2;

class View_Helper_Mail_View_Source
{
	protected Environment $env;
	protected ?Mail_Abstract $mailObject		= NULL;
	protected Logic_Mail $logicMail;
	protected int $mode							= 0;
	protected int $libraries;

	const MODE_NORMAL		= 0;
	const MODE_CONDENSED	= 1;

	public function __construct( Environment $env )
	{
		$this->env			= $env;
		$this->logicMail	= $env->getLogic()->get( 'Mail' );
		$this->libraries	= $this->logicMail->detectAvailableMailLibraries();
	}

	/**
	 * @return string
	 * @throws AmbiguousDataException
	 * @throws ReflectionException
	 */
	public function render(): string
	{
		if( !$this->mailObject )
			throw new RuntimeException( 'No mail object set' );

		$usedLibrary	= $this->logicMail->detectMailLibraryFromMailObjectInstance( $this->mailObject );
		if( !( $this->libraries & $usedLibrary ) ){
			$libraryKey	= ObjectConstants::staticGetKeyByValue( 'Logic_Mail', $usedLibrary );
			return '- used mail library ('.$libraryKey.') is not supported anymore or yet -';
		}
		$message	= $this->mailObject->mail;

		$code	= '';
		if( $usedLibrary == Logic_Mail::LIBRARY_MAIL_V1 )									//  mail uses library CeusMedia/Mail version 1
			$code	= MailRendererV1::render( $message );									//  @todo find better way: currently only parts content displayed but no headers
		else if( $usedLibrary == Logic_Mail::LIBRARY_MAIL_V2 )								//  mail uses library CeusMedia/Mail version 1
			$code	= MailRendererV2::render( $message );									//  @todo find better way: currently only parts content displayed but no headers
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

	public function setMail( $mailObjectOrId ): self
	{
		if( is_int( $mailObjectOrId ) )
			$mailObjectOrId	= $this->logicMail->getMail( $mailObjectOrId );
		if( !is_object( $mailObjectOrId ) )
			throw new InvalidArgumentException( 'Argument must be integer or object' );
		$this->setMailObjectInstance( $mailObjectOrId->object->instance );
		return $this;
	}

	public function setMailObjectInstance( Mail_Abstract $mail ): self
	{
		$this->mailObject	= $mail;
		return $this;
	}

	public function setMode( int $mode ): self
	{
		if( !in_array( $mode, [self::MODE_NORMAL, self::MODE_CONDENSED] ) )
			throw new RangeException( 'Invalid mode' );
		$this->mode	= $mode;
		return $this;
	}

	protected function shortenMailCode( $code )
	{
		$status	= 0;
		$list	= [];
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
