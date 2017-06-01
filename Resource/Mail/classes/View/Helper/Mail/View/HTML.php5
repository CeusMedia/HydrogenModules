<?php
class View_Helper_Mail_View_HTML{

	protected $env;
	protected $mail;
	protected $logicMail;

	public function __construct( $env ){
		$this->env			= $env;
		$this->logicMail	= new Logic_Mail( $this->env );
	}

	public function render(){
		if( !$this->mail )
			throw new RuntimeException( 'No mail object or ID set' );
		$images		= array();
		$parts		= $this->logicMail->getMailParts( $this->mail );
		foreach( $parts as $key => $part ){
			if( $part instanceof \CeusMedia\Mail\Part\InlineImage )
				$images[$part->getId()]	= $part;
			else if( $part instanceof \CeusMedia\Mail\Part\HTML )
				$html	= $part->getContent();
			else if( $part instanceof Net_Mail_Body )
				if( $part->getMimeType() === "text/html" )
					$html	= $part->getContent();
		}
		if( !$html )
			throw new Exception( 'No HTML part found' );
		foreach( $images as $imageId => $part ){
			$find	= '"CID:'.$imageId.'"';
			$subst	= '"data:'.$part->getMimeType().';base64,'.base64_encode( $part->getContent() ).'"';
			$html	= str_replace( $find, $subst, $html );
		}
		return $html;
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
