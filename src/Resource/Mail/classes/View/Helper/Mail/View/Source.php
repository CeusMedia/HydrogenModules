<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\Mail\Message\Renderer as MailRenderer;

class View_Helper_Mail_View_Source
{
	protected Environment $env;
	protected ?Mail_Abstract $mailObject		= NULL;
	protected Logic_Mail $logicMail;
	protected int $mode							= 0;

	const MODE_NORMAL		= 0;
	const MODE_CONDENSED	= 1;

	/**
	 *	@param		Environment $env
	 *	@throws		ReflectionException
	 */
	public function __construct( Environment $env )
	{
		$this->env			= $env;
		/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
		$this->logicMail	= $env->getLogic()->get( 'Mail' );
	}

	/**
	 *	@return		string
	 *	@throws		RangeException
	 */
	public function render(): string
	{
		if( !$this->mailObject )
			throw new RuntimeException( 'No mail object set' );

		$message	= $this->mailObject->mail;

		$code		= MailRenderer::render( $message );			//  @todo find better way: currently only parts content displayed but no headers

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

	/**
	 *	@param		object|string		$mailObjectOrId
	 *	@return		self
	 */
	public function setMail( object|string $mailObjectOrId ): self
	{
		if( is_string( $mailObjectOrId ) )
			$mailObjectOrId	= $this->logicMail->getMail( $mailObjectOrId );
		if( !is_object( $mailObjectOrId ) )
			throw new InvalidArgumentException( 'Argument must be string or object' );
		$this->setMailObjectInstance( $mailObjectOrId->object->instance );
		return $this;
	}

	/**
	 *	@param		Mail_Abstract		$mail
	 *	@return		self
	 */
	public function setMailObjectInstance( Mail_Abstract $mail ): self
	{
		$this->mailObject	= $mail;
		return $this;
	}

	/**
	 *	@param		int		$mode
	 *	@return		self
	 */
	public function setMode( int $mode ): self
	{
		if( !in_array( $mode, [self::MODE_NORMAL, self::MODE_CONDENSED] ) )
			throw new RangeException( 'Invalid mode' );
		$this->mode	= $mode;
		return $this;
	}

	/**
	 *	@param		string		$code
	 *	@return		string
	 */
	protected function shortenMailCode( string $code ): string
	{
		$status	= 0;
		$list	= [];
		foreach( explode( PHP_EOL, $code ) as $line ){
			$isEmpty	= !strlen( trim( $line ) );
			$isBased	= preg_match( '/^\S{74,80}$/', trim( $line ) );
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
