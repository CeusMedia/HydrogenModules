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
		if( !class_exists( 'CMM_Mail_Parser' ) )
			throw new RuntimeException( 'No mail parser available.' );
		if( !$mail->object ){
			print 'No mail object available.';
			exit;
		}
		if( substr( $mail->object, 0, 2 ) == "BZ" )
			$mail->object	= bzdecompress( $mail->object );
		else if( substr( $mail->object, 0, 2 ) == "GZ" )
			$mail->object	= gzinflate( $mail->object );
		$mail->object	= @unserialize( $mail->object );
		if( !is_object( $mail->object ) ){
			print 'Mail object could not by parsed.';
			exit;
		}
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
