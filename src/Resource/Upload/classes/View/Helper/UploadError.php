<?php

use CeusMedia\Common\Alg\UnitFormater;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View\Helper\Abstraction;

class View_Helper_UploadError extends Abstraction
{
	protected array $words;
	protected ?object $upload		= NULL;

	public function __construct( Environment $env )
	{
		$this->env			= $env;
		$this->words		= $this->env->getLanguage()->getWords( 'resource/upload' );
	}

	public function render(): string
	{
		if( !$this->upload )
			throw new RuntimeException( 'No upload object set, yet' );

		if( !$this->upload->error )
			return '';

		$messages	= $this->words['errors'];
		if( !array_key_exists( $this->upload->error, $messages ) )
			throw new RangeException( 'Given number is not a valid upload error number' );

		$message	= $messages[$this->upload->error];
		switch( $this->upload->error ){
			case 10:
				$size		= UnitFormater::formatBytes( $this->upload->allowedSize );
				$message	= sprintf( $message, $size );
				break;
			case 11:
				$extensions	= join( ', ', $this->upload->allowedExtensions );
				$message	= sprintf( $message, $extensions );
				break;
			case 12:
				$mimeTypes	= join( ', ', $this->upload->allowedMimeTypes );
				$message	= sprintf( $message, $mimeTypes );
				break;
			case 14:
				$name  		= htmlentities( $this->upload->name, ENT_QUOTES, 'UTF-8' );
				$message	= sprintf( $message, $name, $this->upload->clamscan->message );
				break;
		}
		return $message;
	}

	public static function renderStatic( Environment $env, Logic_Upload $upload ): string
	{
		$helper	= new self( $env );
		$helper->setUpload( $upload );
		return $helper->render();
	}

	/**
	 *	@param		Logic_Upload|object		$upload
	 *	@return		self
	 */
	public function setUpload( $upload ): self
	{
		if( !is_object( $upload ) )
			throw new InvalidArgumentException( 'No upload object given' );
		if( $upload instanceof Logic_Upload )
			$upload	= $upload->getObject();
		if( !isset( $upload->error ) )
			throw new InvalidArgumentException( 'Not a valid upload object given' );
		$this->upload	= $upload;
		return $this;
	}
}
