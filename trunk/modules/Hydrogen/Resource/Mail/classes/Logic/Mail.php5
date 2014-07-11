<?php
class Logic_Mail{

	protected $env;
	protected $model;

	public function __construct( $env ){
		$this->env		= $env;
		$this->model	= new Model_Mail( $this->env );
		$this->options	= $this->env->getConfig()->getAll( 'module.resource_mail.', TRUE );
		$this->options->set( 'queue', TRUE );
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
		return $this->model->count( $conditions );
	}

	/**
	 *	Send prepared mail later.
	 *	@access		public
	 *	@param		Mail_Abstract	$mail			Mail instance to be queued
	 *	@param		object			$receiver		Data object of receiver, must have member 'email', should have 'userId' and 'username'
	 *	@param		integer			$senderId		Optional: ID of sending user
	 *	@return		integer							ID of queued mail
	 */
	public function enqueueMail( Mail_Abstract $mail, $receiver, $senderId = NULL ){
		if( is_array( $receiver ) )
			$receiver	= (object) $receiver;
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
			'receiverId'		=> isset( $receiver->userId ) ? $receiver->userId : 0,
			'receiverAddress'	=> $receiver->email,
			'receiverName'		=> isset( $receiver->username ) ? $receiver->username : NULL,
			'subject'			=> $mail->getSubject(),
			'object'			=> $serial,
			'enqueuedAt'		=> time(),
		);
		return $this->model->add( $data, FALSE );
	}

	/**
	 *	Returns queued mail object of mail ID.
	 *	@access		public
	 *	@param		integer			$mailId			ID of queued mail
	 *	@return		object							Mail object from queue
	 *	@throws		OutOfRangeException				if mail ID is not existing
	 */
	public function getQueuedMail( $mailId ){
		$mail	= $this->model->get( $mailId );
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
		return $this->model->getAll( $conditions, $orders, $limits, $columns );
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
	 *	@param		object			$receiver		Data object of receiver, must have member 'email', should have 'userId' and 'username'
	 *	@return		void
	 */
	public function sendMail( Mail_Abstract $mail, $receiver ){
		if( is_array( $receiver ) )
			$receiver	= (object) $receiver;
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
		if( !empty( $mail->receiverId ) )
			return $object->sendToUser( $mail->receiverId );
		$receiver	= (object) array(
			'email'		=> $mail->receiverAddress,
			'username'	=> $mail->receiverName,
		);
		$this->model->edit( $mailId, array(
			'status'		=> 1,
			'attempts'		=> $mail->attempts + 1,
			'attemptedAt'	=> time()
		) );
		$object->sendTo( $receiver );
		$this->model->edit( $mailId, array(
			'status'		=> 2,
			'sentAt'		=> time()
		) );
	}
}
?>
