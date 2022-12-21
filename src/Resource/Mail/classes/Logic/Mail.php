<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 *	@author		Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Alg\Obj\Factory as ObjectFactory;
use CeusMedia\Common\FS\File\Reader as FileReader;
use CeusMedia\Common\FS\File\RecursiveRegexFilter as RecursiveRegexFileIndex;
use CeusMedia\HydrogenFramework\Logic;
use CeusMedia\Mail\Message\Renderer as MailMessageRendererV2;

/**
 *	@author		Christian Würker <christian.wuerker@ceusmedia.de>
 *	@todo		code doc
 */
class Logic_Mail extends Logic
{
	const LIBRARY_UNKNOWN		= 0;
	const LIBRARY_COMMON		= 1;
	const LIBRARY_MAIL_V1		= 2;
	const LIBRARY_MAIL_V2		= 4;

	protected array $detectedTemplates	= [];
	protected int $libraries			= 0;
	protected Dictionary $options;
	protected Model_Mail $modelQueue;
	protected Model_Mail_Template $modelTemplate;
	protected Model_Mail_Attachment $modelAttachment;
	protected string $pathAttachments;
	protected string $frontendPath;

	public function abortMailsWithTooManyAttempts(): int
	{
		$model		= new Model_Mail( $this->env );
		$mails		= $model->getAll( array(
			'status'	=> Model_Mail::STATUS_RETRY,
			'attempts'	=> '>= '.$this->options->get( 'retry.attempts' ),
		) );
		foreach( $mails as $mail )
			$model->edit( $mail->mailId, ['status' => Model_Mail::STATUS_FAILED] );
		return count( $mails );
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		Mail_Abstract	$mail			...
	 *	@param		string			$language		...
	 *	@return		void
	 */
	public function appendRegisteredAttachments( Mail_Abstract $mail, string $language )
	{
		$class			= get_class( $mail );
		$indices		= ['className' => $class, 'status' => Model_Mail::STATUS_SENDING, 'language' => $language];
		$attachments	= $this->modelAttachment->getAllByIndices( $indices );
		foreach( $attachments as $attachment ){
			$fileName	= $this->pathAttachments.$attachment->filename;
			$mail->addAttachment( $fileName, $attachment->mimeType );
		}
	}

	public function canBzip(): bool
	{
		return function_exists( 'bzcompress' ) && function_exists( 'bzdecompress' );
	}

	public function canGzip(): bool
	{
		return function_exists( 'gzdeflate' ) && function_exists( 'gzinflate' );
	}

	public function collectConfiguredReceivers( $userIds, array $roleIds = [], array $listConfigKeysToCheck = [] ): array
	{
		if( !$this->env->getModules()->has( 'Resource_Users' ) )
			return [];
		$receivers		= [];
		if( is_string( $userIds ) )
			$userIds	= explode( ",", trim( $userIds ) );
		if( is_string( $roleIds ) )
			$roleIds	= explode( ",", trim( $roleIds ) );
		if( !is_array( $userIds ) )
			throw new InvalidArgumentException( 'Invalid list of user IDs' );
		if( !is_array( $roleIds ) )
			throw new InvalidArgumentException( 'Invalid list of role IDs' );
		$modelUser		= new Model_User( $this->env );
		foreach( $roleIds as $roleId ){
			if( strlen( trim( $roleId ) ) && (int) $roleId > 0 ){
				$users	= $modelUser->getAllByIndex( 'roleId', $roleId );
				foreach( $users as $user )
					$receivers[(int) $user->userId]	= $user;
			}
		}
		foreach( $userIds as $userId ){
			if( strlen( trim( $userId ) ) && (int) $userId > 0 ){
				if( !isset( $receivers[(int) $userId] ) ){
					$user	= $modelUser->get( (int) $userId );
					$receivers[(int) $userId]	= $user;
				}
			}
		}
		if( $listConfigKeysToCheck ){
			foreach( $listConfigKeysToCheck as $key ){

			}
		}
		return $receivers;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		string		$string			String to compress
	 *	@param		integer		$compression	Compression to apply
	 *	@return		string
	 */
	public function compressString( string $string, int $compression ): string
	{
		switch( $compression ){
			case Model_Mail::COMPRESSION_BZIP:
				if( !$this->canBzip() )
					throw new RuntimeException( 'Missing extension for BZIP compression' );
				return bzcompress( $string );
			case Model_Mail::COMPRESSION_GZIP:
				if( !$this->canGzip() )
					throw new RuntimeException( 'Missing extension for GZIP compression' );
				return gzdeflate( $string );
			case Model_Mail::COMPRESSION_BASE64:
				return base64_encode( $string );
			case Model_Mail::COMPRESSION_NONE:
			default:
				return $string;
		}
	}

	/**
	 *	Returns number of mails in queue by given conditions.
	 *	@access		public
	 *	@param		array		$conditions		Map of column conditions to look for
	 *	@return		integer						Number of mails in queue matching conditions
	 */
	public function countQueue( array $conditions = [] ): int
	{
		return $this->modelQueue->count( $conditions );
	}

	/**
	 *	Creates instance of mail class with given mail data.
	 *	Return mail object contains available mail parts.
	 *	An active mail template will be applied.
	 *	@access		public
	 *	@param		string		$mailClassName		Name of mail class without Mail_ prefix
	 *	@param		array		$mailData			Data map for mail content generation, nested arrays and objects are possible
	 *	@return		object							Instance of mail class containing rendered mail parts
	 *	@throws		RuntimeException				If mail class is not existing
	 *	@throws		ReflectionException
	 */
	public function createMail( string $mailClassName, array $mailData ): object
	{
		$className	= 'Mail_'.$mailClassName;
		if( !class_exists( $className ) )
			throw new RuntimeException( 'Mail class "'.$className.'" is not existing' );
		$env	= $this->env;
		if( $this->env->getModules()->has( 'Resource_Frontend' ) ){
			$this->frontendPath	= $this->env->getConfig()->get( 'module.resource_frontend.path' );
			if( $this->frontendPath != './' ){
				$logicFrontend	= $this->env->getLogic()->get( 'Frontend' );
				$env	= $logicFrontend->getRemoteEnv( $this->env );
			}
		}
		return ObjectFactory::createObject( $className, [$env, $mailData] );
	}

	/**
	 *	Tries to compress mail object from serial.
	 *	This serial will be created from the mail object instance, by default.
	 *	If disabled, a set serial by before taken decompression is used.
	 *
	 *	The created raw output will replace the raw content within given mail object.
	 *	@access		public
	 *	@param		object		$mail			Mail object to compress object serial within
	 *	@param		boolean		$serialize		Flag: try to realize mail object from decompressed serial (default: yes)
	 *	@param		boolean		$force			Flag: not used yet, exists for symmetry
	 *	@throws		RuntimeException			if no object instance is available
	 *	@throws		RuntimeException			if no object serial is available
	 */
	public function compressMailObject( object $mail, bool $serialize = TRUE, bool $force = FALSE )
	{
		if( $serialize ){
			if( empty( $mail->object->instance ) )
				throw new RuntimeException( 'Mail object has not been decompressed before, no mail object instance available' );
			$mail->object->serial	= serialize( $mail->object->instance );
		}
		if( empty( $mail->object->serial ) )
			throw new RuntimeException( 'Mail object has not been serialized or decompressed before, no mail object serial available' );
		$mail->object->raw	= $this->compressString( $mail->object->serial, $mail->compression );
	}

	/**
	 *	Tries to decompress raw mail object serial and recreated the serialized mail class object.
	 *
	 *	The recreated mail class object will replace the compressed serial within the given mail object.
	 *	Since decompression is applied, the identified compression is set to mail object, as well.
	 *	@access		public
	 *	@param		object		$mail			Mail object to decompress serial within
	 *	@param		boolean		$unserialize	Flag: try to realize mail object from decompressed serial (default: yes)
	 *	@param		boolean		$force			Flag: force detection and decompression (default: no)
	 *	@throws		RuntimeException			if no compressed raw column content is available
	 *	@throws		RuntimeException			if unserialization fails
	 */
	public function decompressMailObject( object $mail, bool $unserialize = TRUE, bool $force = FALSE )
	{
		if( is_string( $mail->object ) ){
			$mail->object	= (object) array(
				'raw'		=> $mail->object,
				'serial'	=> NULL,
				'instance'	=> NULL,
			);
		}
		if( empty( $mail->object->raw ) )
			throw new RuntimeException( 'No raw (compressed) mail object serial available' );
		if( empty( $mail->object->serial ) || $force ){
			$this->detectUsedMailCompression( $mail, $force );
			$mail->object->serial	= $this->decompressString( $mail->object->raw, (int) $mail->compression );
		}
		$noInstanceYet	= empty( $mail->object->instance );
		if( ( $noInstanceYet && $unserialize ) || ( $force && $unserialize ) ){
			$creation		= unserialize( $mail->object->serial );
			if( !$creation )
				throw new RuntimeException( 'Unserialization failed' );
			$mail->object->instance	= $creation;
		}
	}


	/**
	 *	Tries to decompress the raw column of mail database item.
	 *	In force mode, a beforehand made copy of raw database column will be used.
	 *	@access		public
	 *	@param		object		$mail		...
	 *	@param		boolean		$force		Flag: force detection and decompression (default: no)
	 *	@return		boolean
	 *	@throws		RuntimeException		if no compressed raw column content is available
	 */
	public function decompressMailRaw( object $mail, bool $force = FALSE )
	{
		if( is_object( $mail->raw ) && !$force )
			return TRUE;
		if( is_string( $mail->raw ) || $force ){
			if( $force ){
				if( !isset( $mail->raw->raw ) )
					throw new RuntimeException( 'Re-decompression failed' );
				$mail->raw	= $mail->raw->raw;
			}
		}
		$this->detectUsedMailCompression( $mail, $force );
		$object	= (object) array(
			'raw'		=> $mail->raw,
			'serial'	=> $this->decompressString( $mail->raw, (int) $mail->compression ),
			'instance'	=> NULL,
		);
		$mail->raw		= $object;
		return TRUE;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		string		$string			String to compress
	 *	@param		integer		$compression	Compression to apply
	 *	@return		string
	 */
	public function decompressString( string $string, int $compression ): string
	{
		switch( (int) $compression ){
			case Model_Mail::COMPRESSION_BZIP:
				if( !$this->canBzip() )
					throw new RuntimeException( 'Missing extension for BZIP compression' );
				$result	= bzdecompress( $string );
				if( is_int( $result ) )
					throw new RuntimeException( 'Decompression failed' );
				return $result;
			case Model_Mail::COMPRESSION_GZIP:
				if( !$this->canGzip() )
					throw new RuntimeException( 'Missing extension for GZIP compression' );
				$result	= gzinflate( $string );
				if( is_int( $result ) )
					throw new RuntimeException( 'Decompression failed' );
				return $result;
			case Model_Mail::COMPRESSION_BASE64:
				return base64_decode( $string );
			case Model_Mail::COMPRESSION_NONE:
			default:
				return $string;
		}
	}

	/**
	 *	...
	 *	@access		public
	 *	@return		integer			Flags of available mail library constants
	 */
	public function detectAvailableMailLibraries(): int
	{
		$libraries	= static::LIBRARY_UNKNOWN;
		if( class_exists( 'CeusMedia\Mail\Part\HTML' ) )
			$libraries	|= static::LIBRARY_MAIL_V1;
		if( class_exists( 'CeusMedia\Mail\Message\Part\HTML' ) )
			$libraries	|= static::LIBRARY_MAIL_V2;
		return $libraries;
	}

	/**
	 *	Tries to detect mail library used for mail by its ID.
	 *	Returns detected library ID using constants of Logic_Mail::LIBRARY_*.
	 *	@access		public
	 *	@param		string		$mailId		ID of mail to get used library for
	 *	@return		integer		ID of used library using Logic_Mail::LIBRARY_*
	 */
	public function detectMailLibraryFromMailId( string $mailId ): int
	{
		return $this->detectMailLibraryFromMail( $this->getMail( $mailId ) );
	}

	/**
	 *	Tries to detect mail library used for mail data object.
	 *	Returns detected library ID using constants of Logic_Mail::LIBRARY_*.
	 *	@access		public
	 *	@param		object		$mail		Mail data object from database to get used library for
	 *	@return		integer		ID of used library using Logic_Mail::LIBRARY_*
	 */
	public function detectMailLibraryFromMail( object $mail ): int
	{
		if( !is_object( $mail ) )
			throw new InvalidArgumentException( 'No mail object given' );
		if( empty( $mail->usedLibrary ) ){
			if( is_string( $mail->object ) )
				$this->decompressMailObject( $mail );
			$mail->usedLibrary	= $this->detectMailLibraryFromMailObjectInstance( $mail->object->instance );
		}
		return $mail->usedLibrary;
	}

	/**
	 *	Tries to detect mail library used for unpacked mail object.
	 *	Returns detected library ID using constants of Logic_Mail::LIBRARY_*.
	 *	@access		public
	 *	@param		object		$mailObject		Mail data object from database to get used library for
	 *	@return		integer		ID of used library using Logic_Mail::LIBRARY_*
	 */
	public function detectMailLibraryFromMailObjectInstance( object $mailObject ): int
	{
		if( is_a( $mailObject, 'Mail_Abstract' ) ){
			if( is_a( $mailObject->mail, 'Net_Mail' ) )
				return Logic_Mail::LIBRARY_COMMON;
			if( is_a( $mailObject->mail, 'CeusMedia\Mail\Message' ) ){
				$agent		= $mailObject->mail->getUserAgent();
//				$this->env->getMessenger()->noteNotice( 'Agent: '.$agent );die;
				if( preg_match( '/^'.preg_quote( 'CeusMedia::Mail/2.', '/' ).'/', $agent ) )
					return Logic_Mail::LIBRARY_MAIL_V2;
//				if( preg_match( '/^'.preg_quote( 'CeusMedia::Mail/1.', '/' ).'/', $agent ) )
				return Logic_Mail::LIBRARY_MAIL_V1;
			}
		}
		return Logic_Mail::LIBRARY_UNKNOWN;
	}

	/**
	 *	Detects the best template to use by looking for:
	 *	- given template ID, realizing mail settings of mail class of a module
	 *	- active mail template ID within database
	 *	- default mail template ID of mail resource module of frontend application (if considered)
	 *	- default mail template ID of mail resource module
	 *	The first of these templates being usable will be stored and returned.
	 *	@access		public
	 *	@param		integer			$preferredTemplateId	Template ID to override database and module defaults, if usable
	 *	@param		boolean			$considerFrontend		Flag: consider mail resource module of frontend, if available
	 *	@param		boolean			$strict					Flag: throw exception if something goes wrong
	 *	@return		object|NULL		Model entity object of detected mail template
	 *	@todo		see code doc
	 */
	public function detectTemplateToUse( int $preferredTemplateId = 0, bool $considerFrontend = FALSE, bool $strict = TRUE ): ?object
	{
		$preferredTemplateId	= (int) $preferredTemplateId;										//  @todo remove after update to PHP 7.x using type hints
		if( array_key_exists( $preferredTemplateId, $this->detectedTemplates ) )
			return $this->detectedTemplates[$preferredTemplateId];

		$defaultFromMailModule	= $this->options->get( 'template' );
		$defaultFromDatabase	= $this->modelTemplate->getByIndex( 'status', Model_Mail_Template::STATUS_ACTIVE, [], ['mailTemplateId'] );
		$defaultFromFrontend	= 0;
		if( $considerFrontend && $this->env->getModules()->has( 'Resource_Frontend' ) ){
			try{
				$frontend				= $this->env->getLogic()->get( 'Frontend' );
				$defaultFromFrontend	= $frontend->getModuleConfigValue( 'Resource_Mail', 'template' );
			}
			catch( Exception $e ){}
		}

		//  collect template defaults and overrides
		$templateIds	= array_unique( [
			$defaultFromMailModule ?: 0,
			$defaultFromDatabase ?: 0,
			$defaultFromFrontend ?: 0,
			$preferredTemplateId ?: 0,
		] );

		//  get usable templates from database
		$availableTemplateIds	= $this->modelTemplate->getAll( [
			'mailTemplateId'	=> $templateIds,
			'status'			=> '>= '.Model_Mail_Template::STATUS_USABLE,
		], [], [], ['mailTemplateId'] );
		if( !$availableTemplateIds ){
			if( $strict )
				throw new RuntimeException( 'No usable mail template available' );
			return NULL;
		}

		//  match collected and usable templates
		$templateIds	= array_intersect( $templateIds, $availableTemplateIds );
		if( !$templateIds ){
			if( $strict )
				throw new RuntimeException( 'No usable mail template found' );
			return NULL;
		}

		//  get the best template and store for later
		$detectedTemplateId	= array_pop( $templateIds );
		$template			= $this->modelTemplate->get( $detectedTemplateId );
		$this->detectedTemplates[$detectedTemplateId]	= $template;
		return $template;
	}

	/**
	 *	Detected compression used when mail object was stored.
	 *	@access		public
	 *	@param		object		$mail		Mail item from database
	 *	@param		boolean		$force		Flag: re-detect (default: no)
	 *	@return		integer		Detected compression as of Model_Mail::COMPRESSION_*
	 */
	public function detectUsedMailCompression( object $mail, bool $force = FALSE ): int
	{
		if( !$mail->compression || $force ){
			if( is_string( $mail->object ) )
				$source	= $mail->object;
			else if( is_object( $mail->object ) && !empty( $mail->object->raw ) )
				$source	= $mail->object->raw;
			else
				throw new RuntimeException( 'Detection failed since no raw source is available' );
			$mail->compression	= Model_Mail::COMPRESSION_BASE64;
			if( substr( $source, 0, 2 ) === "BZ" )										//  BZIP compression detected
				$mail->compression	= Model_Mail::COMPRESSION_BZIP;
			else if( substr( $source, 0, 2 ) === "GZ" )								//  GZIP compression detected
				$mail->compression	=  Model_Mail::COMPRESSION_GZIP;
		}
		return $mail->compression;
	}

	/**
	 *	Send prepared mail later.
	 *	@access		public
	 *	@param		Mail_Abstract	$mail			Mail instance to be queued
	 *	@param		string			$language		Language key
	 *	@param		integer|object	$receiver		User ID or data object of receiver (must have member 'email', should have 'userId' and 'username')
	 *	@param		integer			$senderId		Optional: ID of sending user
	 *	@return		integer							ID of queued mail
	 */
	public function enqueueMail( Mail_Abstract $mail, string $language, $receiver, $senderId = NULL )
	{
		if( is_array( $receiver ) )
			$receiver	= (object) $receiver;
		if( is_integer( $receiver ) ){
			$model		= new Model_User( $this->env );
			$receiver	= $model->get( $receiver );
		}
		if( !is_object( $receiver ) )
			throw new InvalidArgumentException( 'Receiver is neither an object nor an array' );
		if( empty( $receiver->email ) )
			throw new InvalidArgumentException( 'Receiver object is missing "email"' );

		if( $mail->mail->getSender() instanceof \CeusMedia\Mail\Address )
			$senderAddress	= $mail->mail->getSender()->getAddress();
		else if( $mail->mail->getSender() instanceof \CeusMedia\Mail\Participant )
			$senderAddress	= $mail->mail->getSender()->getAddress();
		else
			$senderAddress	= $mail->mail->getSender()->address;

		$incompleteMailDataObject	= (object) array(
			'compression'	=> $this->getRecommendedCompression(),
			'object'		=> (object) ['instance' => $mail],
			'raw'			=> NULL,
		);

		$this->compressMailObject( $incompleteMailDataObject, TRUE, TRUE );

		$raw			= '';
		$libraryObject	= $incompleteMailDataObject->object->instance->mail;
		if( $this->libraries & Logic_Mail::LIBRARY_MAIL_V2 ){
			$raw	= MailMessageRendererV2::render( $libraryObject );
		}
		else if( $this->libraries & Logic_Mail::LIBRARY_MAIL_V1 ){
			$raw	= \CeusMedia\Mail\Renderer::render( $libraryObject );
		}
		else if( $this->libraries & Logic_Mail::LIBRARY_COMMON ){
			$rawLines	= [];
			foreach( $libraryObject->getHeaders()->getFields() as $header )
				$rawLines[]	= $header->toString();
			$rawLines[]	= '';
			$rawLines[]	= $libraryObject->getBody();
			$raw		= implode( Net_Mail::$delimiter, $rawLines );
		}
		$incompleteMailDataObject->raw	= $this->compressString( $raw, $incompleteMailDataObject->compression );

		$data		= array(
			'templateId'		=> $mail->getTemplateId(),
			'language'			=> strtolower( trim( $language ) ),
			'senderId'			=> (int) $senderId,
			'senderAddress'		=> $senderAddress,
			'receiverId'		=> $receiver->userId ?? 0,
			'receiverAddress'	=> $receiver->email,
			'receiverName'		=> $receiver->username ?? NULL,
			'subject'			=> $mail->getSubject(),
			'mailClass'			=> get_class( $mail ),
			'compression'		=> $incompleteMailDataObject->compression,
			'object'			=> $incompleteMailDataObject->object->raw,
			'raw'				=> $incompleteMailDataObject->raw,
			'enqueuedAt'		=> time(),
			'attemptedAt'		=> 0,
			'sentAt'			=> 0,
		);
		return $this->modelQueue->add( $data, FALSE );
	}

	/**
	 *	Returns saved mail as uncompressed object by mail ID.
	 *	@access		public
	 *	@param		string			$mailId			ID of queued mail
	 *	@return		object							Mail object from queue
	 *	@throws		OutOfRangeException				if mail ID is not existing
	 *	@throws		RuntimeException				if mail is compressed by BZIP which is not supported in this environment
	 *	@throws		RuntimeException				if mail is compressed by GZIP which is not supported in this environment
	 *	@throws		RuntimeException				if unserialize mail serial fails
	 */
	public function getMail( string $mailId ): object
	{
		$mail	= $this->modelQueue->get( $mailId );
		if( !$mail )
			throw new OutOfRangeException( 'Invalid mail ID: '.$mailId );
		$this->decompressMailObject( $mail );
		$this->decompressMailRaw( $mail );
		if( !$mail->object->instance )
			throw new RuntimeException( 'Deserialization of mail object failed' );
		if( !$mail->raw->serial )
			throw new RuntimeException( 'Decompression of raw mail failed' );
		return $mail;
	}

	/**
	 *	@todo		kriss: (performance) remove double preg check for class (remove 3rd argument on index and double check if clause in loop)
	 *	@todo		kriss: (migration) adjust regex for upcoming Hydrogen with namespaces, maybe use reflection
	 */
	public function getMailClassNames( bool $strict = TRUE, string $sort = 'ASC' ): array
	{
		$list			= [];																	//  prepare empty result list
		$matches		= [];																	//  prepare empty matches list
		$pathClasses	= $this->options->get( 'path.classes' );									//  get path to mail classes from module config
		if( $this->env->getModules()->has( 'Resource_Frontend' ) )
			$pathClasses	= Logic_Frontend::getInstance( $this->env )->getPath().$pathClasses;
		if( !file_exists( $pathClasses ) ){
			if( $strict )
				throw new RuntimeException( 'Path to mail classes invalid or not existing' );
			return $list;
		}
		$regexExt		= "/\.php5?$/";																//  define regular expression of acceptable mail class file extensions
		$regexClass		= "/class\s+(Mail_\S+)\s+extends\s+Mail_/i";								//  define regular expression of acceptable mail class implementations
		$index			= new RecursiveRegexFileIndex( $pathClasses, "/\.php5$/", $regexClass );	//  get recursive list of acceptable files
		foreach( $index as $file ){																	//  iterate recursive list
			$content	= FileReader::load( $file->getPathname() );								//  get content of class file
			preg_match_all( $regexClass, $content, $matches );										//  apply regular expression of mail class to content
			if( count( $matches[0] ) && count( $matches[1] ) ){										//  if valid mail class name found
				$path			= substr( $file->getPathname(), strlen( $pathClasses ) );			//  get filename of class file as list key
				$list[$path]	= $matches[1][0];													//  enqueue mail class name by list key
			}
		}
		in_array( $sort, ['DESC', -1], TRUE ) ? krsort( $list ) : ksort( $list );			//  sort list
		return $list;																				//  return map of found mail classes by their files
	}

	/**
	 *	Returns headers of mail as array map.
	 *	@access		public
	 *	@param		Mail_Abstract|string	$mail		Mail object or ID
	 *	@return		array					Map of headers
	 *	@deprecated	this method has no real value and will be removed
	 *	@todo		remove this method
	 */
	public function getMailHeaders( $mail ): array
	{
		$complete	= TRUE;
		$mail		= $this->getMailFromObjectOrId( $mail );
		if( !is_object( $mail->object ) )
			$this->decompressMailObject( $mail );
			throw new Exception( 'No mail object available' );
		if( !is_a( $mail->object->instance, 'Mail_Abstract' ) )											//  stored mail object os not a known mail class
			throw new Exception( 'Mail object is not extending Mail_Abstract' );
		$list		= [];
		foreach( $mail->object->instance->mail->getHeaders()->getFields() as $headerField )
			$list[$headerField->getName()]	= $headerField->getValue();
		return $list;
	}

	/**
	 *	Returns mail parts.
	 *	@access		public
	 *	@param		object|string		$mail		Mail object or ID
	 *	@return		array							List of mail part objects
	 *	@throws		InvalidArgumentException		if given argument is neither integer nor object
	 *	@throws		Exception						if given mail object is not uncompressed and unserialized (use getMail)
	 *	@throws		RuntimeException				if given mail object is of outdated Net_Mail and parser CMM_Mail_Parser is not available
	 *	@throws		RuntimeException				if given no parser is available for mail object
	 */
	public function getMailParts( $mail ): array
	{
		$this->decompressMailObject( $mail );
		if( !is_a( $mail->object->instance, 'Mail_Abstract' ) )											//  stored mail object os not a known mail class
			throw new Exception( 'Mail object is not extending Mail_Abstract, but '.get_class( $mail->object->instance ) );
		if( $mail->object->instance->mail instanceof \CeusMedia\Mail\Message )							//  modern mail message with parsed body parts
			return $mail->object->instance->mail->getParts( TRUE );
		throw new RuntimeException( 'No mail parser available.' );							//  ... which is not available
	}

	/**
	 *	Returns queued mail object of mail ID.
	 *	@access		public
	 *	@param		string			$mailId			ID of queued mail
	 *	@return		object							Mail object from queue
	 *	@throws		OutOfRangeException				if mail ID is not existing
	 */
	public function getQueuedMail( string $mailId ): object
	{
		return $this->getMail( $mailId );
	}

	/**
	 *	Returns list of mails in queue.
	 *	@access		public
	 *	@param		array			$conditions		Map of Conditions to include in SQL Query
	 *	@param		array			$orders			Map of Orders to include in SQL Query
	 *	@param		array			$limits			Map of Limits to include in SQL Query
	 *	@param		array			$columns		List of columns to get
	 *	@return		array
	 */
	public function getQueuedMails( array $conditions = [], array $orders = [], array $limits = [], array $columns = [] ): array
	{
		return $this->modelQueue->getAll( $conditions, $orders, $limits, $columns );
	}

	/**
	 *	Return map of mail classes used in all queued mails and the number of related mails.
	 *	@access		public
	 *	@param		array			$conditions		Map of Conditions to include in SQL Query
	 *	@return 	array
	 *	@todo		remove check for model method "getDistinct" after next minor framework release (0.8.8)
	 */
	public function getUsedMailClassNames( array $conditions = [] ): array
	{
		$list			= [];
		$orders			= ['mailClass' => 'ASC'];
		if( method_exists( $this->modelQueue, 'getDistinct' ) )
			$mailClassNames	= $this->modelQueue->getDistinct( 'mailClass', $conditions, $orders );
		else
			$mailClassNames	= array_values( $this->getMailClassNames() );
		foreach( $mailClassNames as $mailClassName )
			$list[$mailClassName]	= $this->modelQueue->count( ['mailClass' => $mailClassName] );
		return $list;
	}

	/**
	 *	Handles mail by sending immediately or appending to queue if allowed.
	 *	Sending mail immediately can be configured or forced by third argument.
	 *	@access		public
	 *	@param		Mail_Abstract	$mail			Mail to be sent
	 *	@param		object			$receiver		Data object of receiver, must have member 'email', should have 'userId' and 'username'
	 *	@param		string			$language		Language key
	 *	@param		boolean			$forceSendNow	Flag: override module settings and avoid queue
	 *	@return		boolean			TRUE if success
	 */
	public function handleMail( Mail_Abstract $mail, object $receiver, string $language, bool $forceSendNow = NULL ): bool
	{
		if( $this->options->get( 'queue.enabled' ) && !$forceSendNow )
			return (bool) $this->enqueueMail( $mail, $language, $receiver );
		return $this->sendMail( $mail, $receiver );
	}

	/**
	 *	Remove mail by its ID.
	 *	@access		public
	 *	@param		string			$mailId			ID of mail to remove
	 *	@return		boolean
	 */
	public function removeMail( string $mailId ): bool
	{
		return (bool) $this->modelQueue->remove( $mailId );
	}

	/**
	 *	Send prepared mail right now.
	 *	@access		public
	 *	@param		Mail_Abstract	$mail			Mail to be sent
	 *	@param		integer|object	$receiver		User ID or data object of receiver (must have member 'email', should have 'userId' and 'username')
	 *	@return		boolean			TRUE if success
	 */
	public function sendMail( Mail_Abstract $mail, $receiver )
	{
		if( is_array( $receiver ) )
			$receiver	= (object) $receiver;
		if( is_integer( $receiver ) ){
			$model		= new Model_User( $this->env );
			$receiver	= $model->get( $receiver );
		}
		if( !is_object( $receiver ) )
			throw new InvalidArgumentException( 'Receiver is neither an object nor an array' );
		$mail->setEnv( $this->env );																//  override serialized environment
		$mail->initTransport();																		//  override serialized mail transfer
		return $mail->sendTo( $receiver );
	}

	/**
	 *	Send prepared mail right now.
	 *	@access		public
	 *	@return		integer							ID of queued mail to be sent
	 *	@param		boolean			$forceResent	Flag: send mail again although last attempt was successful
	 *	@return		void
	 *	@todo		use logging on exception (=sending mail failed)
	 */
	public function sendQueuedMail( $mailId, bool $forceResent = FALSE )
	{
		$mail		= $this->getMail( $mailId );
		$this->decompressMailObject( $mail );
		if( (int) $mail->status > Model_Mail::STATUS_SENDING && !$forceResent )
			throw new Exception( 'Mail already has been sent' );
		$mail->object->instance->setEnv( $this->env );
		$mail->object->instance->initTransport();
		$this->modelQueue->edit( $mailId, array(
			'status'		=> Model_Mail::STATUS_SENDING,
			'attempts'		=> $mail->attempts + 1,
			'attemptedAt'	=> time()
		) );
		try{
			if( !empty( $mail->receiverId ) ){
				$mail->object->instance->sendToUser( $mail->receiverId );
			}
			else{
				$receiver	= (object) array(
					'email'		=> $mail->receiverAddress,
					'username'	=> $mail->receiverName,
				);
				$mail->object->instance->sendTo( $receiver );
			}
			$this->modelQueue->edit( $mailId, array(
				'status'		=> Model_Mail::STATUS_SENT,
				'sentAt'		=> time()
			) );
		}
		catch( Exception $e ){
//			remark( $e->getMessage() );
			$this->modelQueue->edit( $mailId, array(
				'status'		=> Model_Mail::STATUS_RETRY,
//				'error'			=> $e->getMessage(),
			) );
			throw new RuntimeException( 'Mail could not been sent: '.$e->getMessage(), 0, $e );
		}
	}

	/**
	 *	Set mail status.
	 *	New status will be validated against allowed status transitions.
	 *	@access		public
	 *	@param		Mail_Abstract|integer	$mail		Mail object or ID
	 *	@param		integer					$status		Status to set, will be validated against allowed status transitions
	 *	@return 	boolean
	 *	@throws		DomainException			if given status is invalid
	 *	@throws		DomainException			if transition to new status is not allowed
	 */
	public function setMailStatus( $mail, $status ): bool
	{
		$status			= (int) $status;
		$mail			= $this->getMailFromObjectOrId( $mail );
		$mail->status	= (int) $mail->status;
		$modelStatuses	= Alg_Object_Constant::staticGetAll( 'Model_Mail', 'STATUS_' );
		$statusMap		= array_flip( $modelStatuses );
		if( !in_array( $status, array_values( $modelStatuses ) ) )
			throw new DomainException( 'Invalid status: '.$status );
		if( $status === $mail->status )
			return FALSE;
		if( !in_array( $status, Model_Mail::$transitions[$mail->status] ) )
			throw new DomainException( 'Transition from status '.$statusMap[$mail->status].' to '.$statusMap[$status]. ' is not allowed' );
		return (bool) $this->modelQueue->edit( $mail->mailId, array(
			'status'		=> $status,
			'modifiedAt'	=> time(),
		) );
	}

	/**
	 *	Enable or disable use of queue or return current state.
	 *	Returns current state of no new state is given.
	 *	@access		public
	 *	@return		void
	 *	@param		boolean|NULL	$toggle			New state or NULL to return current state
	 *	@return		boolean|NULL	Current state if no new state is given
	 */
	public function useQueue( ?bool $toggle = NULL )
	{
		if( $toggle === NULL )
			return $this->options->get( 'queue.enabled' );
		$this->options->set( 'queue.enabled', (bool) $toggle );
	}

	//  --  STATIC PUBLIC  --  //

	//  --  PROTECTED  --  //
	/**
	 *	@todo		check if needed or remove
	 */
	public function decompressObjectInMail( object $mail, bool $unserialize = TRUE, bool $force = FALSE )
	{
		return $this->decompressMailObject( $mail, $unserialize, $force );
	}

	protected function __onInit(): void
	{
		$this->options			= $this->env->getConfig()->getAll( 'module.resource_mail.', TRUE );
		$this->libraries		= $this->detectAvailableMailLibraries();

		/*  --  INIT QUEUE  --  */
		$this->modelQueue		= new Model_Mail( $this->env );
		$this->modelTemplate	= new Model_Mail_Template( $this->env );

		$this->_repair();
//		$this->detectTemplateToUse();

		/*  --  INIT ATTACHMENTS  --  */
		$this->modelAttachment	= new Model_Mail_Attachment( $this->env );
		$this->pathAttachments	= $this->options->get( 'path.attachments' );
		$this->frontendPath		= './';
		if( $this->env->getModules()->has( 'Resource_Frontend' ) ){
			$frontend				= Logic_Frontend::getInstance( $this->env );
			$this->frontendPath		= $frontend->getPath();
			$this->pathAttachments	= $this->frontendPath.$this->pathAttachments;
		}
		if( !file_exists( $this->pathAttachments ) ){
			mkdir( $this->pathAttachments, 0755, TRUE );
			if( !file_exists( $this->pathAttachments.'.htaccess' ) )
				copy( 'classes/.htaccess', $this->pathAttachments.'.htaccess' );
		}
	}

	protected function getMailFromObjectOrId( $mailObjectOrId ): object
	{
		if( is_object( $mailObjectOrId ) )
			return $mailObjectOrId;
		if( !preg_match( '/^[0-9]+$/', $mailObjectOrId ) )
			throw new InvalidArgumentException( 'Arguments must be mail ID or mail object' );
		return $this->getMail( (int) $mailObjectOrId );
	}

	/**
	 *	...
	 *	@access		protected
	 *	@return		integer
	 */
	protected function getRecommendedCompression(): int
	{
		if( $this->canBzip() )
			return Model_Mail::COMPRESSION_BZIP;
		if( $this->canGzip() )
			return Model_Mail::COMPRESSION_GZIP;
		return Model_Mail::COMPRESSION_BASE64;
	}

	/**
	 *	Utility for migration.
	 *	@access		protected
	 *	@return		void
	 *	@deprecated
	 *	@todo		remove after migration
	 *	@todo		to be removed in version 0.9
	 */
	protected function _repair()
	{
		$this->_repair_extendMailsBySenderAddress();
		$this->_repair_extendMailsByCompression();
	}

	/**
	 *	Detects and notes used compression method of formerly enqueued mails.
	 *	@deprecated
	 *	@access		protected
	 *	@param		integer		$limit		Number of mails to repair
	 *	@return		void
	 *	@todo		remove after migration
	 *	@todo		to be removed in version 0.9
	 */
	protected function _repair_extendMailsByCompression( int $limit = 10 )
	{
		$conditions	= ['compression' => '0'];
		$orders		= ['mailId' => 'DESC'];
		$limits		= [0, max( 10, min( 100, $limit ) )];
		$mails		= $this->modelQueue->getAll( $conditions, $orders, $limits );
		foreach( $mails as $mail ){
			$prefix = substr( $mail->object, 0, 2 );
			if( $prefix == "BZ" )
				$this->modelQueue->edit( $mail->mailId, ['compression' => Model_Mail::COMPRESSION_BZIP] );
			else if( !preg_match( '/^[a-z0-9]{20}/i', $mail->object ) )
				$this->modelQueue->edit( $mail->mailId, ['compression' => Model_Mail::COMPRESSION_GZIP] );
		}
	}

	/**
	 *	Detects mail sender from mail object to note to database.
	 *	@deprecated
	 *	@access		protected
	 *	@param		integer		$limit		Number of mails to repair
	 *	@return		void
	 *	@todo		remove after migration
	 *	@todo		remove in version 0.9
	 */
	protected function _repair_extendMailsBySenderAddress( int $limit = 10 )
	{
		$conditions	= ['senderAddress' => ''];
		$orders		= ['mailId' => 'DESC'];
		$limits		= [0, max( 10, min( 100, $limit ) )];
		$mails		= $this->modelQueue->getAll( $conditions, $orders, $limits );
		foreach( $mails as $mail ){
			$mail	= $this->getMail( $mail->mailId );
			$this->decompressMailObject( $mail );
			if( empty( $mail->senderAddress ) ){
				if( method_exists( $mail->object->instance->mail, 'getSender' ) ){
					$address 	= $mail->object->instance->mail->getSender();
					if( $mail->object->instance->mail->getSender() instanceof \CeusMedia\Mail\Address )				//  use library CeusMedia/Mail version 2
						$address	= $address->getAddress();
					else if( $mail->object->instance->mail->getSender() instanceof \CeusMedia\Mail\Participant )		//  use library CeusMedia/Mail version 1
						$address	= $address->getAddress();
					$this->modelQueue->edit( $mail->mailId, array(
						'senderAddress'	=> $address,
					) );
				}
			}
		}
	}
}
if( !class_exists( 'PHP_Incomplete_Class' ) ){
	class PHP_Incomplete_Class{}
}
