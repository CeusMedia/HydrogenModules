<?php
class View_Admin_Mail_Queue extends CMF_Hydrogen_View{

	public function __onInit(){
	}

	public function enqueue(){
	}

	public function index(){
	}

	public function view(){
	}

	protected function getMailParts( $mail ){
		if( !$mail->object ){
			print 'No mail object available.';
			exit;
		}
		if( substr( $mail->object, 0, 2 ) == "BZ" )													//  BZIP compression detected
			$mail->object	= bzdecompress( $mail->object );										//  inflate compressed mail object
		else if( substr( $mail->object, 0, 2 ) == "GZ" )											//  GZIP compression detected
			$mail->object	= gzinflate( $mail->object );											//  inflate compressed mail object
		$mail->object	= @unserialize( $mail->object );											//  get mail object from serial
		if( !is_object( $mail->object ) )															//  wake up failed
			throw new RuntimeException( 'Mail object could not by parsed.' );						//  exit with exception

		if( $mail->object->mail instanceof \CeusMedia\Mail\Message )								//  modern mail message with parsed body parts
			return $mail->object->mail->getParts();

		//  support for older implementation using cmClasses
		if( !class_exists( 'CMM_Mail_Parser' ) )													//  @todo change to \CeusMedia\Mail\Parser
			throw new RuntimeException( 'No mail parser available.' );
		return CMM_Mail_Parser::parseBody( $mail->object->mail->getBody() );
	}

	public function html(){
		try{
			$parts	= $this->getMailParts( $this->getData( 'mail' ) );
			foreach( $parts as $key => $part ){
				if( strlen( trim( $part->getContent() ) ) ){
					if( $part->getMimeType() === "text/html" ){
						print $part->getContent();
						exit;
					}
				}
			}
			print 'No HTML part found.';
		}
		catch( Exception $e ){
			print $e->getMessage();
		}
		exit;
	}
}
?>
