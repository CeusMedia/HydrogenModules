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
//		$this->options->set( 'queue', TRUE );

		if( $this->env->getModules()->get( 'Resource_Mail' )->versionInstalled == "0.4.7" )
			$this->_repairQueue();

		/*  --  INIT ATTACHMENTS  --  */
		$this->modelAttachment	= new Model_Mail_Attachment( $this->env );
		$this->pathAttachments	= $this->options->get( 'path.attachments' );
		if( !file_exists( $this->pathAttachments ) ){
			mkdir( $this->pathAttachments, 0755, TRUE );
			if( !file_exists( $this->pathAttachments.'.htaccess' ) )
				copy( 'classes/.htaccess', $this->pathAttachments.'.htaccess' );
		}
	}

	public function appendRegisteredAttachments( Mail_Abstract $mail ){
		$class			= get_class( $mail );
		$indices		= array( 'className' => $class, 'status' => 1 );
		$attachments	= $this->modelAttachment->getAllByIndices( $indices );
		foreach( $attachments as $attachment ){
			$fileName	= $this->pathAttachments.$attachment->filename;
			$mail->addAttachment( $fileName, $attachment->mimeType );
		}
	}

	public function collectConfiguredReceivers( $userIds, $groupIds = array(), $listConfigKeysToCheck = array() ){
		if( !$this->env->getModules()->has( 'Resource_Users' ) )
			return array();
		$receivers		= array();
		if( is_string( $userIds ) )
			$userIds	= explode( ",", trim( $userIds ) );
		if( is_string( $groupIds ) )
			$groupIds	= explode( ",", trim( $groupIds ) );
		if( !is_array( $userIds ) )
			throw new InvalidArgument( 'Invalid list of user IDs' );
		if( !is_array( $groupIds ) )
			throw new InvalidArgument( 'Invalid list of group IDs' );
		$modelUser		= new Model_User( $this->env );
		foreach( $groupIds as $groupId ){
			if( strlen( trim( $groupId ) ) && (int) $groupId > 0 ){
				$users	= $modelUser->getAllByIndex( 'roleId', $listIds );
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
	 *	@param		integer|object	$receiver		User ID or data object of receiver (must have member 'email', should have 'userId' and 'username')
	 *	@param		integer			$senderId		Optional: ID of sending user
	 *	@return		integer							ID of queued mail
	 */
	public function enqueueMail( Mail_Abstract $mail, $receiver, $senderId = NULL ){
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
		if( function_exists( 'bzcompress' ) && function_exists( 'bzdecompress' ) )
			$serial		= bzcompress( serialize( $mail ) );
		else if( function_exists( 'gzdeflate' ) && function_exists( 'gzinflate' ) )
			$serial		= gzdeflate( serialize( $mail ) );
		else
			$serial		= base64_encode( serialize( $mail ) );
		$data	= array(
			'senderId'			=> (int) $senderId,
			'senderAddress'		=> $mail->mail->getSender(),
			'receiverId'		=> isset( $receiver->userId ) ? $receiver->userId : 0,
			'receiverAddress'	=> $receiver->email,
			'receiverName'		=> isset( $receiver->username ) ? $receiver->username : NULL,
			'subject'			=> $mail->getSubject(),
			'object'			=> $serial,
			'enqueuedAt'		=> time(),
		);
		return $this->modelQueue->add( $data, FALSE );
	}

	public function getMailClassNames(){
		$list		= array();
		$matches	= array();
		$regexClass	= "/class\s+(Mail_\S+)\s+extends\s+Mail_/i";
		$index		= new File_RecursiveRegexFilter( 'classes/Mail/', "/\.php5$/", $regexClass );
		foreach( $index as $file ){
			$content	= File_Reader::load( $file->getPathname() );
			preg_match_all( $regexClass, $content, $matches );
			if( count( $matches[0] ) && count( $matches[1] ) )
				$list[]		= $matches[1][0];
		}
		return $list;
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
	 *	@param		boolean			$forceSendNow	Flag: override module settings and avoid queue
	 *	@return		void
	 */
	public function handleMail( Mail_Abstract $mail, $receiver, $forceSendNow = NULL ){
		if( $this->options->get( 'queue' ) && !$forceSendNow )
			return $this->enqueueMail( $mail, $receiver );
		return $this->sendMail( $mail, $receiver );
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
		$serial		= serialize( $mail );
		if( function_exists( 'bzcompress' ) )
			$serial		= bzcompress( $serial );
		else if( function_exists( 'gzcompress' ) )
			$serial		= gzcompress( $serial );

		if( !is_object( $receiver ) )
			throw new InvalidArgumentException( 'Receiver is neither an object nor an array' );
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
		if( (int) $mail->status > 1 && !$forceResent )
			throw new Exception( 'Mail already has been sent' );
		if( function_exists( 'bzcompress' ) && function_exists( 'bzdecompress' ) )
			$object		= bzdecompress( $mail->object );
		else if( function_exists( 'gzdeflate' ) && function_exists( 'gzinflate' ) )
			$object		= gzinflate( $mail->object );
		else
			$object		= base64_decode( $mail->object );
		$object = unserialize( $object );
		if( !is_object( $object ) )
			throw new RuntimeException( 'Deserialization of mail failed' );
		$object->setEnv( $this->env );
		$object->initTransport();
		$this->modelQueue->edit( $mailId, array(
			'status'		=> 1,
			'attempts'		=> $mail->attempts + 1,
			'attemptedAt'	=> time()
		) );
		if( !empty( $mail->receiverId ) ){
			$object->sendToUser( $mail->receiverId );
		}
		else{
			$receiver	= (object) array(
				'email'		=> $mail->receiverAddress,
				'username'	=> $mail->receiverName,
			);
			$object->sendTo( $receiver );
		}
		$this->modelQueue->edit( $mailId, array(
			'status'		=> 2,
			'sentAt'		=> time()
		) );
	}

	protected function _repairQueue(){
//		$mails	=  $this->modelQueue->getAll();
		foreach( $this->modelQueue->getAll() as $mail ){
			if( empty( $mail->senderAddress ) ){
				if( function_exists( 'bzcompress' ) && function_exists( 'bzdecompress' ) )
					$object		= bzdecompress( $mail->object );
				else if( function_exists( 'gzdeflate' ) && function_exists( 'gzinflate' ) )
					$object		= gzinflate( $mail->object );
				else
					$object		= base64_decode( $mail->object );
				$object = @unserialize( $object );
				if( is_object( $object ) ){
					if( method_exists( $object->mail, 'getSender' ) ){
						$this->modelQueue->edit( $mail->mailId, array( 'senderAddress' => $object->mail->getSender() ) );
					}
				}
			}
		}
	}
}
?>
