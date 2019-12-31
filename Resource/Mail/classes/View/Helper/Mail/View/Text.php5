<?php
class View_Helper_Mail_View_Text{

	protected $env;
	protected $mail;
	protected $logicMail;

	public function __construct( $env ){
		$this->env			= $env;
		$this->logicMail	= $env->getLogic()->get( 'Mail' );
	}

	public function render(){
		if( !$this->mail )
			throw new RuntimeException( 'No mail object or ID set' );

		$libraries		= $this->logicMail->detectAvailableMailLibraries();
		$usedLibrary	= $this->logicMail->detectMailLibraryFromMailObjectInstance( $this->mail->object->instance );

		if( !( $libraries & $usedLibrary ) ){
			$libraryKey	= Alg_Object_Constant::staticGetKeyByValue( 'Logic_Mail', $usedLibrary );
			return '- used mail library ('.$libraryKey.') is not supported anymore or yet -';
		}
		$mailObject	= $this->mail->object->instance;

		$code	= '';
		if( $usedLibrary == Logic_Mail::LIBRARY_COMMON ){										//  mail uses library CeusMedia/Common
			$code	= $mailObject->mail->getBody();												//  @todo find better way: currently only parts content displayed but no headers
		}
		else if( $usedLibrary == Logic_Mail::LIBRARY_MAIL_V1 ){									//  mail uses library CeusMedia/Mail version 1
			$code	= CeusMedia\Mail\Renderer::render( $mailObject->mail );						//  @todo find better way: currently only parts content displayed but no headers
		}
		else if( $usedLibrary == Logic_Mail::LIBRARY_MAIL_V2 ){									//  mail uses library CeusMedia/Mail version 1
			$code	= CeusMedia\Mail\Message\Renderer::render( $mailObject->mail );				//  @todo find better way: currently only parts content displayed but no headers
		}
		else{
			throw new RangeException( 'No source renderer for mail object available' );
		}

		$parts		= $this->logicMail->getMailParts( $this->mail );
		foreach( $parts as $key => $part ){
			if( is_a( $part, 'CeusMedia\Mail\Part\HTML' ) )
				continue;
			if( is_a( $part, 'CeusMedia\Mail\Part\Text' ) )
				$text	= $part->getContent();
			else if( is_a( $part, 'CeusMedia\Mail\Message\Part\Text' ) )
				$text	= $part->getContent();
			else if( is_a( $part, 'Net_Mail_Body' ) ){
				if( $part->getMimeType() === "text/plain" ){
					$text	= $part->getContent();
					if( $part->getContentEncoding() === "base64" )
						$text	= base64_decode( $text );
					if( $part->getContentEncoding() === "quoted-printable" )
						$text	= quoted_printable_decode( $text );
				}
			}
		}
		if( empty( $text ) )
			throw new Exception( 'No plain text part found' );
		return $text;
	}

	public function setMail( $mailObjectOrId ){
		if( is_int( $mailObjectOrId ) )
			$mailObjectOrId	= $this->logicMail->getMail( $mailObjectOrId );
		if( !is_object( $mailObjectOrId ) )
			throw new InvalidArgumentException( 'Argument must be integer or object' );
		$this->mail	= $mailObjectOrId;
	}
}
?>
