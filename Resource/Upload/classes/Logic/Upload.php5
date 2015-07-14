<?php
class Logic_Upload{

	/**	@var		$upload			Upload data object from request */
	protected $upload;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		object			$env				Environment object
	 *	@return		void
	 */
	public function __construct( $env ){
		$this->env	= $env;
	}

	/**
	 *	Destructor.
	 *	Removes originally uploaded file from temp folder.
	 *	@access		public
	 *	@return		void
	 */
	public function __destruct(){
		if( !empty( $this->upload ) )										//  upload is set
			if( !empty( $this->upload->tmp_name ) )							//  file has been uploaded
				if( file_exists( $this->upload->tmp_name ) )				//  uploaded file is still existing
					@unlink( $this->upload->tmp_name );						//  remove originally uploaded file
	}

	/**
	 *	Indicates whether file extension of current upload is allowed.
	 *	@access		public
	 *	@param		array			$allowedExtensions	List of allowed file extensions
	 *	@param		boolean			$noteError			Flag: note negative result as upload error
	 *	@return		boolean
	 *	@throws		RuntimeException					if no upload has been set before
	 *	@throws		InvalidArgumentException			if given list of extensions is not an array
	 */
	public function checkExtension( $allowedExtensions, $noteError = FALSE ){
		if( !$this->upload )
			throw new RuntimeException( 'No upload set' );
		if( !is_array( $allowedExtensions ) )
			throw new InvalidArgumentException( 'Allowed extensions must be given as list' );
//		if( $this->upload->error )
//			return FALSE;
		$extension		= $this->getExtension( TRUE );
		$allowed		= in_array( strtolower( $extension ), $allowedExtensions );
		if( !$allowed && $noteError )
			$this->upload->error	= 11;
		return $allowed;
	}

	/**
	 *	Indicates whether uploaded file is an (excepted) image.
	 *	@access		public
	 *	@param		boolean			$noteError			Flag: note negative result as upload error
	 *	@return		boolean
	 */
	public function checkIsImage( $noteError = FALSE ){
//		if( $this->upload->error )
//			return FALSE;
		$extension  = $this->getExtension( TRUE );
		$isImage    = preg_match( "/^(jpg|png)$/i", $extension );
		if( !$isImage && $noteError )
			$this->upload->error	= 12;
		return $isImage;
	}

	/**
	 *	Indicates whether uploaded file is of allowed MIME type.
	 *	@access		public
	 *	@param		array			$allowedMimeTypes	List of allowed file extensions
	 *	@param		boolean			$noteError			Flag: note negative result as upload error
	 *	@return		boolean
	 */
	public function checkMimeType( $allowedMimeTypes, $noteError = FALSE ){
		if( $this->upload->error )
			return FALSE;
		if( !is_array( $allowedMimeTypes ) )
			throw new InvalidArgumentException( 'Allowed MIME types must be given as list' );
		$allowed	= in_array( $this->upload->type, $allowedMimeTypes );
		if( !$allowed && $noteError )
			$this->upload->error	= 13;
		return $allowed;
	}

	/**
	 *	Indicates whether uploaded file is within allowed file size.
	 *	@access		public
	 *	@param		integer			$maxSize			Maximum allowed file size in bytes
	 *	@param		boolean			$noteError			Flag: note negative result as upload error
	 *	@return		boolean
	 *	@throws		InvalidArgumentException		if given size is not an integer larger than 0
	 */
	public function checkSize( $maxSize, $noteError = FALSE ){
		if( $this->upload->error )
			return FALSE;
		if( $maxSize <= 0 )
			throw new InvalidArgumentException( 'Invalid size' );
//		$size	= filesize( $this->upload->tmp_name );
		$size	= $this->upload->size;
		if( !( $size <= $maxSize ) && $noteError )
			$this->upload->error	= 10;
		return $size <= $maxSize;
	}

	/**
	 *	@todo	implement using clamav
	 */
	public function checkVirus(){
		$isClean	= 0;
		return $isClean;
	}

	public function getError(){
		return $this->upload->error;
	}

	/**
	 *	Returns extension of uploaded file.
	 *	@access		public
	 *	@param		boolean		$lowAndSimple		Flag: lower extension and clean up alternatives, disabled by default
	 *	@return		string		Extension of uploaded file
	 *	@throws		RuntimeException				if no file has been uploaded
	 */
	public function getExtension( $lowAndSimple = FALSE ){
		if( $this->upload->error === 4 )
			throw new RuntimeException( 'No image uploaded' );
		$extension	= pathinfo( $this->upload->name, PATHINFO_EXTENSION );
		if( $lowAndSimple ){
			$extension		= strtolower( $extension );
			$extension		= preg_replace( "/^(jpe|jpeg)$/i", 'jpg', $extension );
		}
		return $extension;
	}

	public function getFileName(){
		if( $this->upload->error === 4 )
			throw new RuntimeException( 'No image uploaded' );
		return $this->upload->name;
	}

	/**
	 *	Returns MIME type of uploaded file.
	 *	@access		public
	 *	@return		string
	 *	@throws		RuntimeException				if no file has been uploaded
	 */
	public function getMimeType(){
		if( $this->upload->error === 4 )
			throw new RuntimeException( 'No file uploaded' );
		return $this->upload->type;
	}

	/**
	 *	Copies uploaded file to target file.
	 *	@access		public
	 *	@param		string		$targetFile			Name of target file
	 *	@return		boolean
	 *	@throws		RuntimeException				if upload is invalid (after checks)
	 *	@throws		RuntimeException				if target file cannot be created
	 */
	public function saveTo( $targetFile ){
		if( $this->upload->error )
			throw new RuntimeException( 'Cannot save upload with errors' );
		$result	= @copy( $this->upload->tmp_name, $targetFile );
		if( !$result )
			throw new RuntimeException( 'File cannot be created: '.$targetFile );
		return TRUE;
	}

	public function scaleImage( $targetFile, $maxWidth, $maxHeight, $quality = NULL ){
		if( !$this->checkIsImage() )
			return FALSE;
		$this->saveTo( $targetFile );
		$image		= new UI_Image( $targetFile );
		$processor	= new UI_Image_Processing( $image );
		$processor->scaleDownToLimit( $maxWidth, $maxHeight, $quality );
		$image->save();
		return TRUE;
	}

	/**
	 *	Sets upload by array (or object) from request.
	 *	Checks file size if maxSize argument is given.
	 *	Checks file extension if allowedExtensions argument is given.
	 *	@access		public
	 *	@param		array|object	$uploadData			Array or object from request
	 *	@param		integer			$maxSize			Maximum allowed bytes of uploaded file
	 *	@param		array			$allowedExtensions	List of allowed file extensions
	 *	@return		void
	 *	@throws		InvalidArgumentException			if given upload data is neither array nor object
	 *	@throws		InvalidArgumentException			if given upload data is missing error property
	 */
	public function setUpload( $uploadData, $maxSize = 0, $allowedExtensions = array() ){
//		$this->env->getMessenger()->noteNotice( print_m( $uploadData, NULL, NULL, TRUE ) );
		if( is_array( $uploadData ) )
			$uploadData	= (object) $uploadData;
		if( !is_object( $uploadData ) )
			throw new InvalidArgumentException( 'No valid upload data given' );
		if( !isset( $uploadData->error ) )
			throw new InvalidArgumentException( 'No valid upload data given' );
		$this->upload	= $uploadData;
		$maxSize ? $this->checkSize( $maxSize, TRUE ) : NULL;
		$allowedExtensions ? $this->checkExtension( $allowedExtensions, TRUE ) : NULL;
	}
}
?>
