<?php
/**
 *	@author		Christian Würker <christian.wuerker@ceusmedia.de>
 */
/**
 *	@author		Christian Würker <christian.wuerker@ceusmedia.de>
 *	@todo		code doc
 */
class Logic_Mail{

	protected $env;
	protected $modelQueue;
	protected $modelAttachment;
	protected $options;
	protected $pathAttachments;

	public function __construct( $env ){
		$this->env				= $env;
		$this->options			= $this->env->getConfig()->getAll( 'module.resource_mail.', TRUE );

		/*  --  INIT QUEUE  --  */
		$this->modelQueue		= new Model_Mail( $this->env );

		$this->phpHasGzip	= function_exists( 'gzdeflate' ) && function_exists( 'gzinflate' );
		$this->phpHasBzip	= function_exists( 'bzcompress' ) && function_exists( 'bzdecompress' );

		/*  --  INIT ATTACHMENTS  --  */
		$this->modelAttachment	= new Model_Mail_Attachment( $this->env );
		$this->pathAttachments	= $this->options->get( 'path.attachments' );
		if( $this->env->getModules()->has( 'Resource_Frontend' ) )
			$this->pathAttachments	= Logic_Frontend::getInstance( $this->env )->getPath().$this->pathAttachments;
		if( !file_exists( $this->pathAttachments ) ){
			mkdir( $this->pathAttachments, 0755, TRUE );
			if( !file_exists( $this->pathAttachments.'.htaccess' ) )
				copy( 'classes/.htaccess', $this->pathAttachments.'.htaccess' );
		}
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
			throw new InvalidArgument( 'Invalid list of user IDs' );
		if( !is_array( $roleIds ) )
			throw new InvalidArgument( 'Invalid list of role IDs' );
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

		if( $mail->mail->getSender() instanceof \CeusMedia\Mail\Participant )
			$senderAddress	= $mail->mail->getSender()->getAddress();
		else
			$senderAddress	= $mail->mail->getSender()->address;

		$encoding	= $this->encodeMailObject( $mail );
		$data		= array(
			'language'			=> strtolower( trim( $language ) ),
			'senderId'			=> (int) $senderId,
			'senderAddress'		=> $senderAddress,
			'receiverId'		=> isset( $receiver->userId ) ? $receiver->userId : 0,
			'receiverAddress'	=> $receiver->email,
			'receiverName'		=> isset( $receiver->username ) ? $receiver->username : NULL,
			'subject'			=> $mail->getSubject(),
			'compression'		=> $encoding->compression,
			'object'			=> $encoding->code,
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

	public function decodeMailObject( $mail, $apply = TRUE ){
		switch( $mail->compression ){
			case Model_Mail::COMPRESSION_NONE:
				$serial	= $mail->object;
				break;
			case Model_Mail::COMPRESSION_BASE64:
				$serial	= base64_decode( $mail->object );
				break;
			case Model_Mail::COMPRESSION_GZIP:
				if( !$this->phpHasGzip )
					throw new RuntimeException( 'Extension for gzip is not installed' );
				$serial	= gzinflate( $mail->object );
				break;
			case Model_Mail::COMPRESSION_BZIP2:
				if( !$this->phpHasBzip )
					throw new RuntimeException( 'Extension for bzip2 is not installed' );
				$serial	= bzdecompress( $mail->object );
				break;
			default:
				throw new RangeException( 'Compression mode is not supported' );
		}
		$object	= @unserialize( $serial );
		if( gettype( $object ) !== "object" )
			throw new RuntimeException( 'Decoding mail object failed ('.$mail->mailId.')' );

		if( $apply ){
			$mail->serial	= $serial;
			$mail->object	= $object;
		}
		return $object;
	}

	public function encodeMailObject( $mailObject ){
		$order	= array(
			Model_Mail::COMPRESSION_GZIP,
			Model_Mail::COMPRESSION_BZIP2,
			Model_Mail::COMPRESSION_BASE64,
			Model_Mail::COMPRESSION_NONE,
		);
		$code		= NULL;
		$serial		= @serialize( $mailObject );
		if( !$serial )
			throw new RuntimeException( 'Serializing mail object failed' );
//		xmp( $serial );
		foreach( $order as $compression ){
			switch( $compression ){
				case Model_Mail::COMPRESSION_NONE:
					$code	= $serial;
					break;
				case Model_Mail::COMPRESSION_BASE64:
					$code	= base64_encode( $serial );
					break;
				case Model_Mail::COMPRESSION_GZIP:
					if( $this->phpHasGzip )
						$code	= gzdeflate( $serial );
					break;
				case Model_Mail::COMPRESSION_BZIP2:
					if( $this->phpHasBzip )
						$code	= bzcompress( $serial );
					break;
			}
			if( $code )
				break;
		}
//		if( !$code )
//			throw new RuntimeException( 'Encoding mail object failed' );
		return (object) array(
			'compression'	=> $compression,
			'code'			=> $code,
		);
	}

	public function get( $mailIdOrObject ){
		if( !is_object( $mailIdOrObject ) )
			$mailIdOrObject		= $this->modelQueue->get( $mailIdOrObject );
		if( !is_object( $mailIdOrObject ) )
			throw new OutOfRangeException( 'Invalid mail ID: '.$mailIdOrObject );
		$mail		= $mailIdOrObject;
		$firstTwo	= substr( $mail->object, 0, 2 );
		$this->decodeMailObject( $mail, TRUE );														//  decompress & unserialize mail object serial and apply to mail data object
		return $mail;
	}

	/**
	 *	Returns queued mail object of mail ID.
	 *	@access		public
	 *	@param		integer			$mailId			ID of queued mail
	 *	@return		object							Mail object from queue
	 *	@throws		OutOfRangeException				if mail ID is not existing
	 */
	public function getQueuedMail( $mailId ){
		$mail	= $this->modelQueue->get( $mailId );
		if( !$mail )
			throw new OutOfRangeException( 'Invalid mail ID: '.$mailId );
		return $mail;
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
	 *	@return		void
	 */
	public function handleMail( Mail_Abstract $mail, $receiver, $language, $forceSendNow = NULL ){
		if( $this->options->get( 'queue.enabled' ) && !$forceSendNow )
			$this->enqueueMail( $mail, $language, $receiver );
		else
			$this->sendMail( $mail, $receiver );
	}

	/**
	 *	Send prepared mail right now.
	 *	@access		public
	 *	@param		Mail_Abstract	$mail			Mail to be sent
	 *	@param		integer|object	$receiver		User ID or data object of receiver (must have member 'email', should have 'userId' and 'username')
	 *	@return		void
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
		$mail->sendTo( $receiver );
	}

	/**
	 *	Send prepared mail right now.
	 *	@access		public
	 *	@return		integer							ID of queued mail to be sent
	 *	@param		boolean			$forceResent	Flag: send mail again although last attempt was successful
	 *	@return		void
	 */
	public function sendQueuedMail( $mailId, $forceResent = FALSE ){
		$mail		= $this->getQueuedMail( $mailId );
		if( (int) $mail->status > Model_Mail::STATUS_SENDING && !$forceResent )
			throw new Exception( 'Mail already has been sent' );

		$this->decodeMailObject( $mail, TRUE );														//  decompress & unserialize mail object serial and apply to mail data object
		$mail->object->setEnv( $this->env );
		$mail->object->initTransport();
		$this->modelQueue->edit( $mailId, array(
			'status'		=> Model_Mail::STATUS_SENDING,
			'attempts'		=> $mail->attempts + 1,
			'attemptedAt'	=> time()
		) );
		try{
			if( !empty( $mail->receiverId ) ){
				$object->sendToUser( $mail->receiverId );
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
		remark( $e->getMessage() );
			$this->modelQueue->edit( $mailId, array(
				'status'		=> Model_Mail::STATUS_RETRY,
			) );
		}
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
			return $this->options->get( 'queue' );
		$this->options->set( 'queue', (bool) $toggle );
	}
}
if( !class_exists( 'PHP_Incomplete_Class' ) ){
	class PHP_Incomplete_Class{}
}

?>
