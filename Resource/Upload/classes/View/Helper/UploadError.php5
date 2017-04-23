<?php
class View_Helper_UploadError extends CMF_Hydrogen_View_Helper_Abstract{

	protected $number;

	public function __construct( $env ){
		$this->env	= $env;
		$words		= $this->env->getLanguage()->getWords( 'resource/upload' );
		$this->errors	= $words['errors'];
	}

	public function setUpload( $upload ){
		if( !is_object( $upload ) )
			throw new InvalidArgumentException( 'No upload object given' );
		if( $upload instanceof Logic_Upload )
			$upload	= $upload->getObject();
		if( !isset( $upload->error ) )
			throw new InvalidArgumentException( 'Not a valid upload object given' );
		$this->upload	= $upload;
		$this->number	= $number;
	}

	public function render(){
		if( !$this->upload )
			throw new RuntimeException( 'No upload object set, yet' );

		if( !$this->upload->error )
			return;

		if( !array_key_exists( $this->upload->error, $this->errors ) )
			throw new RangeException( 'Given number is not a valid upload error number' );

		$message	= $this->errors[$this->upload->error];
		switch( $this->number ){
			case 10:
				$size		= Alg_UnitFormater::formatBytes( $this->upload->allowedSize );
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
		}
		return $message;
	}
}
