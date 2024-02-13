<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\Exception\Data\Ambiguous as AmbiguousDataException;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Mail_View_HTML
{
	protected Environment $env;
	protected ?Mail_Abstract $mailObject		= NULL;
	protected Logic_Mail $logicMail;

	public function __construct( $env )
	{
		$this->env			= $env;
		$this->logicMail	= $env->getLogic()->get( 'Mail' );
	}

	/**
	 *	@return		string
	 *	@throws		ReflectionException
	 *	@throws		AmbiguousDataException
	 *	@throws		Exception
	 */
	public function render(): string
	{
		if( !$this->mailObject )
			throw new RuntimeException( 'No mail object set' );

		$message	= $this->mailObject->mail;

		if( !$message->hasHTML() )
			throw new Exception( 'No HTML part found' );

		$html	= $message->getHTML()->getContent();
		foreach( $message->getInlineImages() as $image ){
			$find	= '"CID:'.trim( $image->getId(), '<>' ).'"';
			$subst	= '"data:'.$image->getMimeType().';base64,'.base64_encode( $image->getContent() ).'"';
			$html	= str_ireplace( $find, $subst, $html );
		}
		return $html;
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
}
