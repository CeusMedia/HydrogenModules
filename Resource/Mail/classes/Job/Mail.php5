<?php
class Job_Mail extends Job_Abstract{

	protected $logic;
	protected $greylistingDelay	= 900;

	public function __onInit(){
		$this->options	= $this->env->getConfig()->getAll( 'module.resource_mail.', TRUE );
		$this->logic	= new Logic_Mail( $this->env );
	}

	public function countQueuedMails(){
		$conditions	= array( 'status' => array( 0, 1 ) );
		$count		= $this->logic->countQueue( $conditions );
		$this->out( sprintf( "%s mails on queue.\n", $count ) );
	}

	public function sendQueuedMails(){
		$sleep		= (float) $this->options->get( 'queue.job.sleep' );
		$limit		= (integer) $this->options->get( 'queue.job.limit' );
		set_time_limit( ( $timeLimit = ( 5 + $sleep ) * $limit + 10 ) );

//		$this->log( 'run with config: {sleep: '.$sleep.', limit: '.$limit.'}' );
		$this->logic->abortMailsWithTooManyAttempts();

		$counter	= 0;
		$listSent	= array();
		$listFailed	= array();
		$conditions	= array(
			'status'		=> array( Model_Mail::STATUS_NEW, Model_Mail::STATUS_RETRY ),
			'attemptedAt'	=> '<'.( time() - $this->options->get( 'retry.delay' ) ),
		);
		$orders		= array( 'status' => 'ASC', 'mailId' => 'ASC' );
		$count		= $this->logic->countQueue( $conditions );
		if( !$count )
			return;
		while( $count && $counter < $count && ( !$limit || $counter < $limit ) ){
			if( $counter > 0 && $sleep > 0 )
				$sleep >= 1 ? sleep( $sleep ) : usleep( $sleep * 1000 * 1000 );
			$mails	= $this->logic->getQueuedMails( $conditions, $orders, array( 0, 1 ) );
			if( $mails && $mail = array_pop( $mails ) ){
				$counter++;
				if( $this->logic->sendQueuedMail( $mail->mailId ) )
					$listSent[]	= (int) $mail->mailId;
				else
					$listFailed[]	= (int) $mail->mailId;
			}
		}
		$this->log( json_encode( array(
			'timestamp'	=> time(),
			'datetime'	=> date( "Y-m-d H:i:s" ),
			'count'		=> $count,
			'failed'	=> count( $listFailed ),
			'sent'		=> count( $listSent ),
			'ids'		=> $listSent,
		) ) );
	}

	protected function loadMailClasses(){
		$mailClassPaths	= array( './' );
		if( $this->env->getModules()->has( 'Resource_Frontend' ) ){
			$logicFrontend		= Logic_Frontend::getInstance( $this->env );
			if( !in_array( $logicFrontend->getPath(), $mailClassPaths ) )
				$mailClassPaths[]	= $logicFrontend->getPath();
		}
		foreach( $mailClassPaths as $mailClassPaths ){
			$index	= new FS_File_RecursiveRegexFilter( $mailClassPaths.'/classes/Mail/', '/\.php5?/' );
			foreach( $index as $entry ){
				@include_once( $entry->getPathname() );
			}
		}
	}

	public function migrate(){
		if( $this->env->getModules()->get( 'Resource_Mail' )->versionInstalled < "0.4.8" )			// todo: to be removed
			$this->__migrateRepositoryFromCommonToMail();
		if( $this->env->getModules()->get( 'Resource_Mail' )->versionInstalled >= "0.6.8" ){			// todo: to be removed
			$this->__detectCompression( array(), array(), array( 0, 100 ) );
			$this->__detectMailClass( array(), array(), array( 0, 100 ) );
		}
	}

	public function clean(){
		$this->__removeNewsletters( array( 'createdAt' => '<'.( time()- 7 * 24 * 3600 ) ), array( 'mailId' => 'ASC' ), array( 0, 100 ) );
		$this->__removeAttachments( array(), array( 'mailId' => 'ASC' ), array( 0, 1000 ) );
	}

	protected function __detectMailClass( $conditions = array(), $orders = array(), $limits = array() ){
		if( !is_array( $conditions ) )
			throw new InvalidArgumentException( 'Conditions must be an array' );
		if( !is_array( $orders ) )
			throw new InvalidArgumentException( 'Orders must be an array' );
		if( !is_array( $limits ) )
			throw new InvalidArgumentException( 'Limits must be an array' );

		$model		= new Model_Mail( $this->env );
		$this->loadMailClasses();
		$conditions['mailClass']	= '';
		$orders		= $orders ? $orders : array( 'mailId' => 'DESC' );
		$limits		= $limits ? $limits : array( 0, 10 );

		$count		= 0;
		$mails		= $model->getAll( $conditions, $orders, $limits );
		foreach( $mails as $mail ){
			try{
				$this->logic->decodeMailObject( $mail, TRUE );
				$model->edit( $mail->mailId, array( 'mailClass' => get_class( $mail->object ) ) );
				$count++;
			}
			catch( Exception $e ){
				remark( $e->getMessage() );
//				$this->env->getMessenger()->noteFailure( 'Decoding of mail ('.$mail->mailId.') failed: '.$e->getMessage() );
			}
		}
		$this->out( 'Resource.Mail.Queue.repair/detectMailClass: Detected mail class for '.$count.' mails.' );
	}

	protected function __removeNewsletters( $conditions = array(), $orders = array(), $limits = array() ){
		if( !is_array( $conditions ) )
			throw new InvalidArgumentException( 'Conditions must be an array' );
		if( !is_array( $orders ) )
			throw new InvalidArgumentException( 'Orders must be an array' );
		if( !is_array( $limits ) )
			throw new InvalidArgumentException( 'Limits must be an array' );
		$model		= new \Model_Mail( $this->env );

		$this->loadMailClasses();
		$conditions['status']		= array( -2, 2 );
		$conditions['mailClass']	= 'Mail_Newsletter';
		$orders		= $orders ? $orders : array( 'mailId' => 'DESC' );
		$limits		= $limits ? $limits : array( 0, 10 );

		$list		= array();
		foreach( $model->getAll( $conditions, $orders, $limits ) as $mail ){
			try{
				$this->logic->decodeMailObject( $mail, TRUE );
				if( get_class( $mail->object ) === "Mail_Newsletter" ){
					$list[]	= $mail->mailId;
					$model->remove( $mail->mailId );
				}
			}
			catch( Exception $e ){
				remark( $e->getMessage() );
//				$this->env->getMessenger()->noteFailure( 'Decoding of mail ('.$mail->mailId.') failed: '.$e->getMessage() );
			}
		}
		$this->out( 'Resource.Mail.Queue.repair/removeNewsletters: Removed '.count( $list ).' newsletter mails.' );
	}

	protected function __removeAttachments( $conditions = array(), $orders = array(), $limits = array() ){
		if( !is_array( $conditions ) )
			throw new InvalidArgumentException( 'Conditions must be an array' );
		if( !is_array( $orders ) )
			throw new InvalidArgumentException( 'Orders must be an array' );
		if( !is_array( $limits ) )
			throw new InvalidArgumentException( 'Limits must be an array' );
		$model		= new \Model_Mail( $this->env );

		$this->loadMailClasses();

		$conditions['status']	= array( -2, 2 );
		$orders		= $orders ? $orders : array( 'mailId' => 'DESC' );
		$limits		= $limits ? $limits : array( 0, 10 );

		$list		= array();
		foreach( $model->getAll( $conditions, $orders, $limits ) as $mail ){
			try{
				$this->logic->decodeMailObject( $mail, TRUE );
				if( method_exists( $mail->object->mail, 'getParts' ) ){
					$mailParts		= $mail->object->mail->getParts( TRUE );
					$nrAttachments	= array();
					foreach( $mailParts as $nr => $part ){
						if( $part instanceof \CeusMedia\Mail\Part\Attachment ){
							if( !isset( $list[$mail->mailId] ) )
								$list[$mail->mailId]	= (object) array(
									'size'		=> strlen( $mail->serial ),
									'indices'	=> array(),
								);
							$list[$mail->mailId]->indices[]	= $nr;
						}
					}
				}
			}
			catch( Exception $e ){
				remark( $e->getMessage() );
//				$this->env->getMessenger()->noteFailure( 'Decoding of mail failed: '.$e->getMessage() );
			}
		}

		$sizeOld		= 0;
		$sizeNew		= 0;
		$nrAttachments	= 0;
		foreach( $list as $mailId => $message ){
			$mail		= $this->logic->get( $mailId );
			$sizeOld	+= $message->size;
			foreach( $message->indices as $attachmentIndex ){
				$nrAttachments++;
				$mail->object->mail->removePart( $attachmentIndex );
			}
			$encoding	= $this->logic->encodeMailObject( $mail->object );
			$sizeNew	+= strlen( $encoding->code );
			$model->edit( $mailId, array(
				'compression'	=> $encoding->compression,
				'object'		=> $encoding->code,
			), FALSE );
		}
		$message	= 'Resource.Mail.Queue.repair/removeAttachments: Reduced %s mails by %s attachments from %s to %s.';
		$this->out( vsprintf( $message, array(
			count( $list ),
			$nrAttachments,
			Alg_UnitFormater::formatBytes( $sizeOld ),
			Alg_UnitFormater::formatBytes( $sizeNew )
		) ) );
	}

	protected function __detectCompression( $conditions = array(), $orders = array(), $limits = array() ){
		if( !is_array( $conditions ) )
			throw new InvalidArgumentException( 'Conditions must be an array' );
		if( !is_array( $orders ) )
			throw new InvalidArgumentException( 'Orders must be an array' );
		if( !is_array( $limits ) )
			throw new InvalidArgumentException( 'Limits must be an array' );

		$model		= new Model_Mail( $this->env );
		$conditions['compression']	= array( 0 );
		$orders		= $orders ? $orders : array( 'mailId' => 'DESC' );
		$limits		= $limits ? $limits : array( 0, 10 );

		$count		= 0;
		$found		= $model->count( $conditions );
		if( $found ){
			$mails		= $model->getAll( $conditions, $orders, $limits );
			foreach( $mails as $mail ){
				$compression	= 0;
				$finfo			= new finfo( FILEINFO_MIME );
				$mimeType		= $finfo->buffer( $mail->object );
				if( preg_match( '@application/x-bzip2@', $mimeType ) )
					$compression	= 3;
				else if( preg_match( '@application/x-gzip@', $mimeType ) )
					$compression	= 2;
				else if( preg_match( '@^[A-Za-z0-9+/=]+$@', $mimeType ) )
					$compression	= 1;
				if( $compression ){
					$count++;
					$model->edit( $mail->mailId, array( 'compression' => $compression ) );
				}
			}
		}
		$this->out( 'Resource.Mail.Queue.repair/detectCompression: Detected compression of '.$count.' mails.' );
	}

	protected function __migrateRepositoryFromCommonToMail( $conditions = array(), $orders = array(), $limits = array() ){
		if( !is_array( $conditions ) )
			throw new InvalidArgumentException( 'Conditions must be an array' );
		if( !is_array( $orders ) )
			throw new InvalidArgumentException( 'Orders must be an array' );
		if( !is_array( $limits ) )
			throw new InvalidArgumentException( 'Limits must be an array' );

		$model		= new Model_Mail( $this->env );
		$orders		= $orders ? $orders : array( 'mailId' => 'DESC' );
		$limits		= $limits ? $limits : array( 0, 10 );

		$count		= 0;
		$found		= $model->count( $conditions );
		foreach( $model->getAll( $conditions, $orders, $limits ) as $mail ){
			if( empty( $mail->senderAddress ) ){
				try{
					$this->decodeMailObject( $mail, TRUE );												//  decompress & unserialize mail object serial and apply to mail data object
					if( is_object( $mail->object ) ){
						die( get_class( $mail->object ) );
						if( method_exists( $mail->object->mail, 'getSender' ) ){
							$count++;
							$model->edit( $mail->mailId, array( 'senderAddress' => $mail->object->mail->getSender() ) );
						}
					}
				}
				catch( Exception $e ){
					remark( $e->getMessage() );
				}
			}
		}
		$this->out( 'Resource.Mail.Queue.repair/migrateRepositoryFromCommonToMail: Migrated '.$count.' mails.' );
	}
}
?>
