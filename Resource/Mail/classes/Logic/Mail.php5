<?php
/**
 *	@author		Christian Würker <christian.wuerker@ceusmedia.de>
 */
/**
 *	@author		Christian Würker <christian.wuerker@ceusmedia.de>
 *	@todo		code doc
 */
class Logic_Mail extends CMF_Hydrogen_Logic{

	const LIBRARY_UNKNOWN		= 0;
	const LIBRARY_COMMON		= 1;
	const LIBRARY_MAIL_V1		= 2;
	const LIBRARY_MAIL_V2		= 4;

	protected $libraries		= 0;
	protected $options;
	protected $modelQueue;
	protected $modelAttachment;
	protected $pathAttachments;

	static public function canBzip(){
		return function_exists( 'bzcompress' ) && function_exists( 'bzdecompress' );
	}

	static public function canGzip(){
		return function_exists( 'gzdeflate' ) && function_exists( 'gzinflate' );
	}

	public function __onInit(){
		$this->options			= $this->env->getConfig()->getAll( 'module.resource_mail.', TRUE );
		$this->libraries		= static::detectAvailableMailLibraries();

		/*  --  INIT QUEUE  --  */
		$this->modelQueue		= new Model_Mail( $this->env );

		$this->_repair();
		$this->checkActiveTemplate();

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

	protected function checkActiveTemplate(){
		$modelTemplate	= new Model_Mail_Template( $this->env );
		$template		= $modelTemplate->getByIndex( 'status', Model_Mail_Template::STATUS_ACTIVE );
		if( $template )
			return $template;
		$moduleTemplateId	= $this->env->getConfig()->get( 'module.resource_mail.template' );
		if( $this->env->getModules()->has( 'Resource_Frontend' ) ){
			$frontend			= Logic_Frontend::getInstance( $this->env );
			$moduleTemplateId	= $frontend->getModuleConfigValue( 'Resource_Mail', 'template' );
		}
		if( $moduleTemplateId ){
			$template	= $modelTemplate->get( $moduleTemplateId );
			if( $template && $template->status == Model_Mail_Template::STATUS_USABLE ){
				$modelTemplate->edit( $moduleTemplateId, array(
					'status'	=> Model_Mail_Template::STATUS_ACTIVE
				) );
				return $modelTemplate->get( $moduleTemplateId );
			}
		}
		return NULL;
	}

	public function abortMailsWithTooManyAttempts(){
		$model		= new Model_Mail( $this->env );
		$mails		= $model->getAll( array(
			'status'	=> Model_Mail::STATUS_RETRY,
			'attempts'	=> '>='.$this->options->get( 'retry.attempts' ),
		) );
		foreach( $mails as $mail )
			$model->edit( $mail->mailId, array( 'status' => Model_Mail::STATUS_FAILED ) );
		return count( $mails );
	}


	public function appendRegisteredAttachments( Mail_Abstract $mail, $language ){
		$class			= get_class( $mail );
		$indices		= array( 'className' => $class, 'status' => Model_Mail::STATUS_SENDING, 'language' => $language );
		$attachments	= $this->modelAttachment->getAllByIndices( $indices );
		foreach( $attachments as $attachment ){
			$fileName	= $this->pathAttachments.$attachment->filename;
			$mail->addAttachment( $fileName, $attachment->mimeType );
		}
	}

	public function collectConfiguredReceivers( $userIds, $roleIds = array(), $listConfigKeysToCheck = array() ){
		if( !$this->env->getModules()->has( 'Resource_Users' ) )
			return array();
		$receivers		= array();
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
	 *	Returns number of mails in queue by given conditions.
	 *	@access		public
	 *	@param		array		$conditions		Map of column conditions to look for
	 *	@return		integer						Number of mails in queue matching conditions
	 */
	public function countQueue( $conditions = array() ){
		return $this->modelQueue->count( $conditions );
	}

	/**
	 *	Creates instance of mail class with given mail data.
	 *	Return mail object contains availble mail parts.
	 *	An active mail template will be applied.
	 *	@access		public
	 *	@param		string		$mailClassName		Name of mail class without Mail_ prefix
	 *	@param		array		$mailData			Data map for mail content generation, nested arrays and objects are possible
	 *	@return		object							Instance of mail class containing rendered mail parts
	 *	@throws		RuntimeException				If mail class is not existing
	 */
	public function createMail( $mailClassName, $data ){
		$className	= 'Mail_'.$mailClassName;
		if( !class_exists( $className ) )
			throw new RuntimeException( 'Mail class "'.$className.'" is not existing' );
		$env	= $this->env;
		if( $this->env->getModules()->has( 'Resource_Frontend' ) ){
			$this->frontendPath	= $this->env->getConfig()->get( 'module.resource_frontend.path' );
			if( $this->frontendPath != './' )
				$env	= Logic_Frontend::getRemoteEnv( $this->env );
		}
		return Alg_Object_Factory::createObject( $className, array( $env, $data ) );
	}

	/**
	 *	...
	 *	@static
	 *	@access		public
	 *	@return		integer			Flags of available mail library contants
	 */
	static public function detectAvailableMailLibraries(){
		$libraries	= static::LIBRARY_UNKNOWN;
		if( class_exists( 'Net_Mail_Body' ) )
			$libraries	|= static::LIBRARY_COMMON;
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
	 *	@param		integer		$mailId		ID of mail to get used library for
	 *	@return		integer		ID of used library using Logic_Mail::LIBRARY_*
	 */
	public function detectMailLibraryFromMailId( $mailId ){
		$mail	= $this->getMail( $mailId );
		return $this->detectMailLibraryFromMail( $mail );
	}

	/**
	 *	Tries to detect mail library used for mail data object.
	 *	Returns detected library ID using constants of Logic_Mail::LIBRARY_*.
	 *	@access		public
	 *	@param		object		$mail		Mail data object from database to get used library for
	 *	@return		integer		ID of used library using Logic_Mail::LIBRARY_*
	 */
	public function detectMailLibraryFromMail( $mail ){
		if( !is_object( $mail ) )
			throw new InvalidArgumentException( 'No mail object given' );
		if( !isset( $mail->object ) || empty( $mail->object ) || !is_object( $mail->object ) )
			throw new InvalidArgumentException( 'Mail object has not been unpacked yet' );
		return static::detectMailLibraryFromMailObject( $mail->object );
	}

	/**
	 *	Tries to detect mail library used for unpacked mail object.
	 *	Returns detected library ID using constants of Logic_Mail::LIBRARY_*.
	 *	@static
	 *	@access		public
	 *	@param		object		$mail		Mail data object from database to get used library for
	 *	@return		integer		ID of used library using Logic_Mail::LIBRARY_*
	 */
	static public function detectMailLibraryFromMailObject( $mailObject ){
		if( is_a( $mailObject, 'Mail_Abstract' ) ){
			if( is_a( $mailObject->mail, 'Net_Mail' ) )
				return Logic_Mail::LIBRARY_COMMON;
			if( is_a( $mailObject->mail, 'CeusMedia\Mail\Message' ) ){
				$agent		= $mailObject->mail->getUserAgent();
				if( preg_match( '/^'.preg_quote( 'CeusMedia::Mail/1.', '/' ).'/', $agent ) )
					return Logic_Mail::LIBRARY_MAIL_V1;
				if( preg_match( '/^'.preg_quote( 'CeusMedia::Mail/2.', '/' ).'/', $agent ) )
					return Logic_Mail::LIBRARY_MAIL_V2;
			}
		}
		return Logic_Mail::LIBRARY_UNKNOWN;
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
	public function enqueueMail( Mail_Abstract $mail, $language, $receiver, $senderId = NULL ){
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

		$compression	= $this->getRecommendedCompression();
		$serial			= $this->compressMailClassObject( $mail, $compression );

		if( $mail->mail->getSender() instanceof \CeusMedia\Mail\Address )
			$senderAddress	= $mail->mail->getSender()->getAddress();
		else if( $mail->mail->getSender() instanceof \CeusMedia\Mail\Participant )
			$senderAddress	= $mail->mail->getSender()->getAddress();
		else
			$senderAddress	= $mail->mail->getSender()->address;

		$data		= array(
			'templateId'		=> $mail->templateId,
			'language'			=> strtolower( trim( $language ) ),
			'senderId'			=> (int) $senderId,
			'senderAddress'		=> $senderAddress,
			'receiverId'		=> isset( $receiver->userId ) ? $receiver->userId : 0,
			'receiverAddress'	=> $receiver->email,
			'receiverName'		=> isset( $receiver->username ) ? $receiver->username : NULL,
			'subject'			=> $mail->getSubject(),
			'mailClass'			=> get_class( $mail ),
			'compression'		=> $compression,
			'object'			=> $serial,
			'enqueuedAt'		=> time(),
			'attemptedAt'		=> 0,
			'sentAt'			=> 0,
		);
		return $this->modelQueue->add( $data, FALSE );
	}

	/**
	 *	@todo		kriss: (performance) remove double preg check for class (remove 3rd argument on index and double check if clause in loop)
	 */
	public function getMailClassNames( $strict = TRUE ){
		$list			= array();																	//  prepare empty result list
		$matches		= array();																	//  prepare empty matches list
		$pathClasses	= $this->options->get( 'path.classes' );									//  get path to mail classes from module config
		if( $this->env->getModules()->has( 'Resource_Frontend' ) )
			$pathClasses	= Logic_Frontend::getInstance( $this->env )->getPath().$pathClasses;
		if( !file_exists( $pathClasses ) ){
			if( $strict )
				throw new RuntimeException( 'Path to mail classes invalid or not existing' );
			return $list;
		}
		$regexExt		= "/\.php5$/";																//  define regular expression of acceptable mail class file extensions
		$regexClass		= "/class\s+(Mail_\S+)\s+extends\s+Mail_/i";								//  define regular expression of acceptable mail class implementations
		$index			= new FS_File_RecursiveRegexFilter( $pathClasses, "/\.php5$/", $regexClass );	//  get recursive list of acceptable files
		foreach( $index as $file ){																	//  iterate recursive list
			$content	= FS_File_Reader::load( $file->getPathname() );								//  get content of class file
			preg_match_all( $regexClass, $content, $matches );										//  apply regular expression of mail class to content
			if( count( $matches[0] ) && count( $matches[1] ) ){										//  if valid mail class name found
				$path			= substr( $file->getPathname(), strlen( $pathClasses ) );			//  get filename of class file as list key
				$list[$path]	= $matches[1][0];													//  enqueue mail class name by list key
			}
		}
		return $list;																				//  return map of found mail classes by their files
	}

	/**
	 *	Returns saved mail as uncompressed object by mail ID.
	 *	@access		public
	 *	@param		integer			$mailId			ID of queued mail
	 *	@return		object							Mail object from queue
	 *	@throws		OutOfRangeException				if mail ID is not existing
	 *	@throws		RuntimeException				if mail is compressed by BZIP which is not supported in this environment
	 *	@throws		RuntimeException				if mail is compressed by GZIP which is not supported in this environment
	 *	@throws		RuntimeException				if unserialize mail serial fails
	 */
	public function getMail( $mailId ){
		$mail	= $this->modelQueue->get( $mailId );
		if( !$mail )
			throw new OutOfRangeException( 'Invalid mail ID: '.$mailId );
		if( !$this->decompressMailObject( $mail ) )
			throw new RuntimeException( 'Deserialization of mail failed' );
		return $mail;
	}

	/**
	 *	Returns headers of mail as array map.
	 *	@access		public
	 *	@param		Mail_Abstract|integer	$mail		Mail object or ID
	 *	@return		array					Map of headers
	 *	@deprecated	this method has no real value and will be removed
	 *	@todo		remove this method
	 */
	public function getMailHeaders( $mail ){
		$complete	= TRUE;
		$mail		= $this->getMailFromObjectOrId( $mail );
		if( !is_object( $mail->object ) )
			throw new Exception( 'No mail object available' );
		if( !is_a( $mail->object, 'Mail_Abstract' ) )											//  stored mail object os not a known mail class
			throw new Exception( 'Mail object is not extending Mail_Abstract' );
		$list		= array();
		foreach( $mail->object->mail->getHeaders()->getFields() as $headerField )
			$list[$headerField->getName()]	= $headerField->getValue();
		return $list;
	}

	/**
	 *	Returns mail parts.
	 *	@access		public
	 *	@param		int|object		$mail		Mail object or ID
	 *	@return		array			List of mail part objects
	 *	@throws		InvalidArgumentException	if given argument is neither integer nor object
	 *	@throws		Exception					if given mail object is not uncompressed and unserialized (use getMail)
	 *	@throws		RuntimeException			if given mail object is of outdated Net_Mail and parser CMM_Mail_Parser is not available
	 *	@throws		RuntimeException			if given no parser is available for mail object
	 */
	public function getMailParts( $mail ){
		$mails	= $this->getMailFromObjectOrId( $mail );
		if( !is_object( $mail->object ) )
			throw new Exception( 'No mail object available' );
		if( !is_a( $mail->object, 'Mail_Abstract' ) )											//  stored mail object os not a known mail class
			throw new Exception( 'Mail object is not extending Mail_Abstract' );
		if( $mail->object->mail instanceof \CeusMedia\Mail\Message )							//  modern mail message with parsed body parts
			return $mail->object->mail->getParts( TRUE );
		else if( $mail->object->mail instanceof Net_Mail ){										//  outdated mail message using cmClasses implementation
			if( method_exists( $mail->object->mail, 'getParts' ) )
				return $mail->object->mail->getParts();
			if( !class_exists( 'CMM_Mail_Parser' ) )											//  Net_Mail needs Parser from cmModules
				throw new RuntimeException( 'Mail parser "CMM_Mail_Parser" is not available.' );
			return CMM_Mail_Parser::parseBody( $mail->object->mail->getBody() );
		}
		throw new RuntimeException( 'No mail parser available.' );							//  ... which is not available
	}

	/**
	 *	Returns queued mail object of mail ID.
	 *	@access		public
	 *	@param		integer			$mailId			ID of queued mail
	 *	@return		object							Mail object from queue
	 *	@throws		OutOfRangeException				if mail ID is not existing
	 */
	public function getQueuedMail( $mailId ){
		return $this->getMail( $mailId );
	}

	/**
	 *	Returns list of mails in queue.
	 *	@access		public
	 *	@param		array			$conditions		Map of Conditions to include in SQL Query
	 *	@param		array			$orders			Map of Orders to include in SQL Query
	 *	@param		array			$limits			Map of Limits to include in SQL Query
	 *	@param		array			$groupings		List of columns to group by
	 *	@param		array			$havings		List of conditions to apply after grouping
	 *	@return		array
	 */
	public function getQueuedMails( $conditions = array(), $orders = array(), $limits = array(), $columns = array() ){
		return $this->modelQueue->getAll( $conditions, $orders, $limits, $columns );
	}

	/**
	 *	Handles mail by sending immediately or appending to queue if allowed.
	 *	Sending mail immediately can be configured or forced by third argment.
	 *	@access		public
	 *	@param		Mail_Abstract	$mail			Mail to be sent
	 *	@param		object			$receiver		Data object of receiver, must have member 'email', should have 'userId' and 'username'
	 *	@param		string			$language		Language key
	 *	@param		boolean			$forceSendNow	Flag: override module settings and avoid queue
	 *	@return		boolean			TRUE if success
	 */
	public function handleMail( Mail_Abstract $mail, $receiver, $language, $forceSendNow = NULL ){
		if( $this->options->get( 'queue.enabled' ) && !$forceSendNow )
			return (bool) $this->enqueueMail( $mail, $language, $receiver );
		return $this->sendMail( $mail, $receiver );
	}

	/**
	 *	Remove mail by its ID.
	 *	@access		public
	 *	@param		integer			$mailId			ID of mail to remove
	 *	@return		boolean
	 */
	public function removeMail( $mailId ){
		return (bool) $this->modelQueue->remove( $mailId );
	}

	/**
	 *	Send prepared mail right now.
	 *	@access		public
	 *	@param		Mail_Abstract	$mail			Mail to be sent
	 *	@param		integer|object	$receiver		User ID or data object of receiver (must have member 'email', should have 'userId' and 'username')
	 *	@return		boolean			TRUE if success
	 */
	public function sendMail( Mail_Abstract $mail, $receiver ){
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
	public function sendQueuedMail( $mailId, $forceResent = FALSE ){
		$mail		= $this->getMail( $mailId );
		if( (int) $mail->status > Model_Mail::STATUS_SENDING && !$forceResent )
			throw new Exception( 'Mail already has been sent' );
		$mail->object->setEnv( $this->env );
		$mail->object->initTransport();
		$this->modelQueue->edit( $mailId, array(
			'status'		=> Model_Mail::STATUS_SENDING,
			'attempts'		=> $mail->attempts + 1,
			'attemptedAt'	=> time()
		) );
		try{
			if( !empty( $mail->receiverId ) ){
				$mail->object->sendToUser( $mail->receiverId );
			}
			else{
				$receiver	= (object) array(
					'email'		=> $mail->receiverAddress,
					'username'	=> $mail->receiverName,
				);
				$mail->object->sendTo( $receiver );
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
	public function setMailStatus( $mail, $status ){
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
	 *	@param		boolean|NULL	$toggle			New state or NULL to return current state
	 *	@return		boolean|NULL	Current state if no new state is given
	 */
	public function useQueue( $toggle = NULL ){
		if( $toggle === NULL )
			return $this->options->get( 'queue.enabled' );
		$this->options->set( 'queue.enabled', (bool) $toggle );
	}

	//  --  PROTECTED  --  //

	/**
	 *	Returns the compressed serial of a mail class instance.
	 *	@access		protected
	 *	@param		Mail_Abstract	$mail			Mail class instance to serialize and compress
	 *	@param		integer			$compression	Compression (or encoding) to apply, one of Model_Mail::COMPRESSION_*
	 *	@return		string
	 */
	protected function compressMailClassObject( Mail_Abstract $mailClassObject, $compression ){
		$serial		= serialize( $mailClassObject );
		if( $compression === Model_Mail::COMPRESSION_BZIP )
			return bzcompress( $serial );
		if( $compression === Model_Mail::COMPRESSION_GZIP )
			return gzdeflate( $serial );
		if( $compression === Model_Mail::COMPRESSION_NONE )
			return base64_encode( $serial );
		return $serial;
	}

	/**
	 *	Tries to decompress raw mail object serial and recreated the serialized mail class object.
	 *	The recreated mail class object will replace the compressed serial with the given mail object.
	 *  Since decompression is applied, the identified compression is set to mail object, as well.
	 *	Returns FALSE of decompression or deserialization failed.
	 *	@access		protected
	 *	@param		object			$mail		Mail object to decompress serial within
	 *	@return		boolean
	 */
	protected function decompressMailObject( $mail ){
		if( !is_object( $mail->object ) ){
			$this->detectUsedMailCompression( $mail );
			if( $mail->compression == Model_Mail::COMPRESSION_BZIP ){
				if( !static::canBzip() )
					throw new RuntimeException( 'Missing extension for BZIP compression' );
				$mail->serial	= bzdecompress( $mail->object );
			}
			else if( $mail->compression == Model_Mail::COMPRESSION_GZIP ){
				if( !static::canGzip() )
					throw new RuntimeException( 'Missing extension for BZIP compression' );
				$mail->serial	= gzinflate( $mail->object );
			}
			else if( $mail->compression == Model_Mail::COMPRESSION_BASE64 ){
				$mail->serial	= base64_decode( $mail->object );
			}
			$creation	= unserialize( $mail->serial );
			if( $creation === FALSE )
				return FALSE;
			$mail->object	= $creation;
		}
		return TRUE;
	}

	protected function detectUsedMailCompression( $mail ){
		if( !$mail->compression && !is_string( $mail->object ) ){
			$mail->compression	= Model_Mail::COMPRESSION_BASE64;
			if( substr( $mail->object, 0, 2 ) == "BZ" )												//  BZIP compression detected
				$mail->compression	= Model_Mail::COMPRESSION_BZIP;
			else if( substr( $mail->object, 0, 2 ) == "GZ" )										//  GZIP compression detected
				$mail->compression	=  Model_Mail::COMPRESSION_GZIP;
		}
		return $mail->compression;
	}

	protected function getMailFromObjectOrId( $mailObjectOrId ){
		if( is_object( $mailObjectOrId ) )
			return $mailObjectOrId;
		if( !preg_match( '/^[0-9]+$/', $mailObjectOrId ) )
			throw new InvalidArgumentException( 'Arguments must be mail ID or mail object' );
		return $this->getMail( (int) $mailObjectOrId );
	}

	protected function getRecommendedCompression(){
		if( static::canBzip() )
			return Model_Mail::COMPRESSION_BZIP;
		if( static::canGzip() )
			return Model_Mail::COMPRESSION_GZIP;
		return Model_Mail::COMPRESSION_BASE64;
	}

	/**
	 *	Utility for migration.
	 *	@deprecated
	 *	@todo		remove after migration
	 *	@todo		to be removed in version 0.9
	 */
	protected function _repair(){
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
	protected function _repair_extendMailsByCompression( $limit = 10 ){
		$conditions	= array( 'compression' => '0' );
		$orders		= array( 'mailId' => 'DESC' );
		$limits		= array( 0, max( 10, min( 100, $limit ) ) );
		$mails		= $this->modelQueue->getAll( $conditions, $orders, $limits );
		foreach( $mails as $mail ){
			$prefix = substr( $mail->object, 0, 2 );
			if( $prefix == "BZ" )
				$this->modelQueue->edit( $mail->mailId, array( 'compression' => Model_Mail::COMPRESSION_BZIP ) );
			else if( !preg_match( '/^[a-z0-9]{20}/i', $mail->object ) )
				$this->modelQueue->edit( $mail->mailId, array( 'compression' => Model_Mail::COMPRESSION_GZIP ) );
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
	protected function _repair_extendMailsBySenderAddress( $limit = 10 ){
		$conditions	= array( 'senderAddress' => '' );
		$orders		= array( 'mailId' => 'DESC' );
		$limits		= array( 0, max( 10, min( 100, $limit ) ) );
		$mails		= $this->modelQueue->getAll( $conditions, $orders, $limits );
		foreach( $mails as $mail ){
			$mail	= $this->getMail( $mail->mailId );
			remark( "repair: ".$mail->mailId );
			if( empty( $mail->senderAddress ) ){
				if( method_exists( $mail->object->mail, 'getSender' ) ){
					$address 	= $mail->object->mail->getSender();
					if( $mail->object->mail->getSender() instanceof \CeusMedia\Mail\Address )				//  use library CeusMedia/Mail version 2
						$address	= $address->getAddress();
					else if( $mail->object->mail->getSender() instanceof \CeusMedia\Mail\Participant )		//  use library CeusMedia/Mail version 1
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
?>
