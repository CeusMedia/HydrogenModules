<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\Alg\Obj\Constant as ObjectConstants;
use CeusMedia\Common\Exception\Data\Ambiguous as AmbiguousDataException;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\Mail\Part\HTML as MailPartHtmlV1;
use CeusMedia\Mail\Part\InlineImage as MailPartInlineImageV1;

class View_Helper_Mail_View_HTML
{
	protected Environment $env;
	protected ?Mail_Abstract $mailObject		= NULL;
	protected Logic_Mail $logicMail;
	protected int $libraries;

	public function __construct( $env )
	{
		$this->env			= $env;
		$this->logicMail	= $env->getLogic()->get( 'Mail' );
		$this->libraries	= $this->logicMail->detectAvailableMailLibraries();
	}

	/**
	 *	@return string
	 *	@throws ReflectionException
	 *	@throws AmbiguousDataException
	 */
	public function render(): string
	{
		if( !$this->mailObject )
			throw new RuntimeException( 'No mail object set' );

		$usedLibrary	= $this->logicMail->detectMailLibraryFromMailObjectInstance( $this->mailObject );
//		$this->env->getMessenger()->noteNotice( 'usedLibrary: '.ObjectConstants::staticGetKeyByValue( 'Logic_Mail', $usedLibrary, 'LIBRARY_' ) );
		if( !( $this->libraries & $usedLibrary ) ){
			$libraryKey	= ObjectConstants::staticGetKeyByValue( 'Logic_Mail', $usedLibrary );
			return '- used mail library ('.$libraryKey.') is not supported anymore or yet -';
		}
		$message	= $this->mailObject->mail;

		$html	= '';
		$images	= [];
		if( $usedLibrary == Logic_Mail::LIBRARY_MAIL_V1 ){										//  mail uses library CeusMedia/Mail version 1
			foreach( $message->getParts( TRUE ) as $part ){
				if( $part instanceof MailPartHtmlV1 )
					$html	= $part->getContent();
				else if( $part instanceof MailPartInlineImageV1 )
					$images[$part->getId()]	= $part;
			}
		}
		else if( $usedLibrary == Logic_Mail::LIBRARY_MAIL_V2 ){									//  mail uses library CeusMedia/Mail version 2
			if( $message->hasHTML() )
				$html	= $message->getHTML()->getContent();
			foreach( $message->getInlineImages() as $image )
				$images[$image->getId()]	= $image;
		}
		else
			throw new RangeException( 'No source renderer for mail object available' );

		if( empty( $html ) )
			throw new Exception( 'No HTML part found' );
		foreach( $images as $imageId => $part ){
			$find	= '"CID:'.trim( $imageId, '<>' ).'"';
			$subst	= '"data:'.$part->getMimeType().';base64,'.base64_encode( $part->getContent() ).'"';
			$html	= str_ireplace( $find, $subst, $html );
		}
		return $html;
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
}
