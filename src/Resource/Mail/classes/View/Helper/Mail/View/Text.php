<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Mail_View_Text
{
	protected Environment $env;
	protected ?Mail_Abstract $mailObject		= NULL;
	protected Logic_Mail $logicMail;

	/**
	 *	@param		Environment		$env
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
	 *	@throws		Exception
	 */
	public function render(): string
	{
		if( !$this->mailObject )
			throw new RuntimeException( 'No mail object set' );

		$message	= $this->mailObject->mail;

		if( !$message->hasText() )
			throw new Exception( 'No text part found' );

		return $message->getText()->getContent();
	}

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
}
