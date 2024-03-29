<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\Alg\Obj\Constant as ObjectConstants;
use CeusMedia\Common\Exception\Data\Ambiguous as AmbiguousDataException;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Mail_View_Text
{
	protected Environment $env;
	protected ?Mail_Abstract $mailObject		= NULL;
	protected Logic_Mail $logicMail;
	protected int $libraries;

	public function __construct( Environment $env )
	{
		$this->env			= $env;
		$this->logicMail	= $env->getLogic()->get( 'Mail' );
		$this->libraries	= $this->logicMail->detectAvailableMailLibraries();
	}

	/**
	 * @return string
	 * @throws ReflectionException
	 * @throws AmbiguousDataException
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

		$text	= '';
		$images	= [];
		if( $usedLibrary == Logic_Mail::LIBRARY_MAIL_V1 ){										//  mail uses library CeusMedia/Mail version 1
			foreach( $message->getParts( TRUE ) as $part )
				if( get_class( $part ) == 'CeusMedia\\Mail\\Part\\Text' )
					$text	= $part->getContent();
		}
		else if( $usedLibrary == Logic_Mail::LIBRARY_MAIL_V2 ){									//  mail uses library CeusMedia/Mail version 1
			if( $message->hasText() )
				$text	= $message->getText()->getContent();
		}
		else
			throw new RangeException( 'No source renderer for mail object available' );

		if( empty( $text ) )
			throw new Exception( 'No text part found' );

		return $text;
	}

	public function setMail( $mailObjectOrId )
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
}
