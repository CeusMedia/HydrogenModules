<?php
class View_Helper_Mail_View_Text{

	protected $env;
	protected $mail;
	protected $logicMail;

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

		$text	= '';
		$images	= array();
		if( $usedLibrary == Logic_Mail::LIBRARY_COMMON ){										//  mail uses library CeusMedia/Common
			if( $part->getMimeType() === "text/plain" ){
				$html	= $part->getContent();
				if( $part->getContentEncoding() === "base64" )
					$html	= base64_decode( $html );
				if( $part->getContentEncoding() === "quoted-printable" )
					$html	= quoted_printable_decode( $html );
			}
		}
		else if( $usedLibrary == Logic_Mail::LIBRARY_MAIL_V1 ){									//  mail uses library CeusMedia/Mail version 1
			foreach( $message->getParts( TRUE ) as $part )
				if( $part instanceof \CeusMedia\Mail\Part\Text )
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
}
?>
