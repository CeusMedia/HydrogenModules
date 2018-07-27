<?php
class Job_Mail extends Job_Abstract{

	protected $logic;
	protected $model;
//	protected $greylistingDelay	= 900;
	protected $libraries;
	protected $statusesHandledMails	= array(
		Model_Mail::STATUS_ABORTED,																//  status: -3
		Model_Mail::STATUS_FAILED,																//  status: -2
		Model_Mail::STATUS_SENT,																//  status: 2
		Model_Mail::STATUS_RECEIVED,															//  status: 3
		Model_Mail::STATUS_OPENED,																//  status: 4
		Model_Mail::STATUS_REPLIED,																//  status: 5
	);

	public function __onInit(){
		$this->options		= $this->env->getConfig()->getAll( 'module.resource_mail.', TRUE );
		$this->logic		= Logic_Mail::getInstance( $this->env );
		$this->model		= new Model_Mail( $this->env );
		$this->libraries	= Logic_Mail::detectAvailableMailLibraries();
		$this->_loadMailClasses();
	}

	public function clean(){
		$age	= $this->parameters->get( '--age', '1Y' ) ;
		$age	= $age ? $age : '1Y';
		$threshold	= date_create()->sub( new DateInterval( 'P'.$age ) );
		$conditions	= array(
			'status'		=> $this->statusesHandledMails,
			'mailClass'		=> 'Mail_Newsletter',
			'enqueuedAt' 	=> '<'.$threshold->format( 'U' ),
		);
		$orders		= array( 'mailId' => 'ASC' );
		$mails		= $this->model->getAll( $conditions, $orders );
		if( $this->dryMode ){
			$this->out( 'DRY RUN - no changes will be made.' );
			$this->out( 'Would remove '.count( $mails ).' old mails.' );
		}
		else{
			$count	= 0;
	//		$fails	= array();
			foreach( $mails as $mail ){
				$this->model->remove( $mail->mailId );
				$this->showProgress( ++$count, count( $mails ) );
			}
			if( $mails )
				$this->out();
			$this->out( 'Removed '.$count.' old mails.' );
//			$this->showErrors( 'removeOldMails', $fails );
		}
	}

	public function countQueuedMails(){
		$conditions		= array( 'status' => array( Model_Mail::STATUS_NEW ) );
		$countNew		= $this->logic->countQueue( $conditions );
		$conditions		= array( 'status' => array( Model_Mail::STATUS_RETRY ) );
		$countRetry		= $this->logic->countQueue( $conditions );
		$this->out( sprintf( "%d mails to send, %d mail to retry.", $countNew, $countRetry ) );
	}

	public function migrate(){
		$conditions	= array( 'status' > $this->statusesHandledMails );
		$orders		= array( 'mailId' => 'ASC' );
		$mails		= $this->model->getAll( $conditions, $orders, array(), array( 'mailId' ) );
		$count		= 0;
		$fails		= array();
		foreach( $mails as $mailId ){
			$mail		= $this->model->get( $mailId );
			$mailClone	= clone( $mail );
			try{
				$this->_detectMailCompression( $mailClone );
				$this->_detectMailClass( $mailClone );
				$this->_migrateMailClass( $mailClone );
				$this->_migrateMailObject( $mailClone );

				$changes	= array();
				foreach( $mailClone as $key => $value )
					if( $mail->$key != $value )
						$changes[$key]	= $value;

				if( $changes ){
					if( !$this->dryMode )
						$this->model->edit( $mailId, $changes, FALSE );
					$this->showProgress( ++$count, count( $mails ), '+' );
				}
				else{
					$this->showProgress( ++$count, count( $mails ) );
				}
			}
			catch( Exception $e ){
				$fails[$mailId]	= $e->getMessage();
				$this->showProgress( ++$count, count( $mails ), 'E' );
			}
		}
		$this->showErrors( 'migrate', $fails );
		$this->_migrateMailTemplates();
	}

	public function removeAttachments(){
		$conditions	= array( 'status' => $this->statusesHandledMails );
		$orders		= array( 'mailId' => 'DESC' );
		$fails		= array();
		$results	= (object) array(
			'mails'			=> 0,
			'attachments'	=> 0,
			'sizeBefore'	=> 0,
			'sizeAfter'		=> 0,
		);

		$mails	= $this->model->getAllByIndices( $conditions, $orders, array(), array( 'mailId' ) );
		foreach( $mails as $mailId ){
			try{
				$mail			= $this->model->get( $mailId );
				$sizeBefore		= strlen( $mail->object );
				$mail->object	= $this->_unpackMailObject( $mail );
				if( method_exists( $mail->object->mail, 'getParts' ) ){
					$attachments		= $mail->object->mail->getAttachments( TRUE );
					foreach( $attachments as $nr => $part ){
//						$this->out( "Part: ".get_class( $part ) );
						if( $part instanceof \CeusMedia\Mail\Part\Attachment ){
							$mail->object->mail->removePart( $nr );
							$object	= $this->_packMailObject( $mail, $mail->object );
							$this->model->edit( $mail->mailId, array( 'object' => $object), FALSE );
							$results->attachments++;
							$results->sizeBefore	+= $sizeBefore;
							$results->sizeAfter		+= strlen( $object );
						}
					}
				}
				$this->showProgress( ++$results->mails, count( $mails ) );
			}
			catch( Exception $e ){
				$this->showProgress( ++$results->mails, count( $mails ), 'E' );
				$fails[$mail->mailId]	= $e->getMessage();
			}
		}
		if( $mails )
			$this->out();
		if( $results->attachments ){
			$message	= 'Detached %s attachments, deflated mails from %s to %s.';
			$this->out( vsprintf( $message, array(
				$results->attachments,
				Alg_UnitFormater::formatBytes( $results->sizeBefore ),
				Alg_UnitFormater::formatBytes( $results->sizeAfter )
			) ) );
		}
		else
			$this->out( 'No detachable attachments found.' );
		$this->showErrors( 'removeAttachments', $fails );
	}

	public function sendQueuedMails(){
		$sleep		= (float) $this->options->get( 'queue.job.sleep' );
		$limit		= (integer) $this->options->get( 'queue.job.limit' );
		set_time_limit( ( $timeLimit = ( 5 + $sleep ) * $limit + 10 ) );

		if( !$this->dryMode )
			$this->logic->abortMailsWithTooManyAttempts();

		$counter	= 0;
		$listSent	= array();
		$listFailed	= array();
		$conditions	= array(
			'status'		=> array(
				Model_Mail::STATUS_NEW,
				Model_Mail::STATUS_RETRY
			),
			'attemptedAt'	=> '<'.( time() - $this->options->get( 'retry.delay' ) ),
		);
		$orders		= array( 'status' => 'ASC', 'mailId' => 'ASC' );
		$count		= $this->logic->countQueue( $conditions );
		if( $this->dryMode ){
			$this->out( 'DRY RUN - no changes will be made.' );
			$this->out( 'Would send '.$count.' mails.' );
		}
		else{
			if( !$count )
				return;
			while( $count && $counter < $count && ( !$limit || $counter < $limit ) ){
				if( $counter > 0 && $sleep > 0 )
					$sleep >= 1 ? sleep( $sleep ) : usleep( $sleep * 1000 * 1000 );
				$mails	= $this->logic->getQueuedMails( $conditions, $orders, array( 0, 1 ) );
				if( $mails && $mail = array_pop( $mails ) ){
					$counter++;
					try{
						if( !$this->dryMode )
							$this->logic->sendQueuedMail( $mail->mailId );
						$listSent[]	= (int) $mail->mailId;
					}
					catch( Exception $e ){
						$this->logError( $e->getMessage() );
						$listFailed[]	= (int) $mail->mailId;
					}
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
	}

	//  --  PRIVATE  --  //
	private function _detectMailClass( $mail ){
		$serial			= $this->_unpackMailObject( $mail, FALSE );
		$serialStart	= substr( $serial, 0, 80 );
		$mailClass		= preg_replace( '/^O:[0-9]+:"([^"]+)":.+$/U', '\\1', $serialStart );
		if( $mail->mailClass == $mailClass )
			return FALSE;
		$mail->mailClass = $mailClass;
		return TRUE;
	}

	private function _detectMailCompression( $mail ){
		$compression	= Model_Mail::COMPRESSION_UNKNOWN;
		$finfo			= new finfo( FILEINFO_MIME );
		$mimeType		= $finfo->buffer( $mail->object );
		if( preg_match( '@application/x-bzip2@', $mimeType ) )
			$compression	= Model_Mail::COMPRESSION_BZIP;
		else if( preg_match( '@application/x-gzip@', $mimeType ) )
			$compression	= Model_Mail::COMPRESSION_GZIP;
		else if( preg_match( '@^[A-Za-z0-9+/=]+$@', $mimeType ) )
			$compression	= Model_Mail::COMPRESSION_BASE64;
		if( $mail->compression == $compression )
			return FALSE;
		$mail->compression	= $compression;
		return TRUE;
	}

	private function _loadMailClasses(){
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

	private function _migrateMailClass( $mail ){
		$classMigrations	= array(
			'Mail_Auth_Password'		=> 'Mail_Auth_Local_Password',
			'Mail_Auth_Register'		=> 'Mail_Auth_Local_Register',
//			'Mail_Shop_Order_Customer'	=> 'Mail_Shop_Customer_Ordered',
//			'Mail_Shop_Order_Manager'	=> 'Mail_Shop_Manager_Ordered',
		);

		if( !array_key_exists( $mail->mailClass, $classMigrations ) )
			return FALSE;

		$serial		= $this->_unpackMailObject( $mail, FALSE );
		$newerClass	= $classMigrations[$mail->mailClass];
		$find		= 'O:'.strlen( $mail->mailClass ).':"'.$mail->mailClass.'":';
		$replace	= 'O:'.strlen( $newerClass ).':"'.$newerClass.'":';
		$serial		= str_replace( $find, $replace, $serial );
		$object		=
		$mail->object		= $this->_packMailObject( $mail, $serial );
		$mail->mailClass	= $newerClass;
		return TRUE;
	}

	private function _migrateMailObject( $mail ){
		$object		= $this->_unpackMailObject( $mail );
		$usedLibrary	 = Logic_Mail::detectMailLibraryFromMailObject( $object );

		if( $usedLibrary === Logic_Mail::LIBRARY_COMMON ){
			if( $this->libraries & ( Logic_Mail::LIBRARY_MAIL1 | Logic_Mail::LIBRARY_MAIL1 ) ){	// @todo finish support for v2, see todo below
				$newInstance	= new \CeusMedia\Mail\Message();

				$newInstance->setSubject( $object->mail->getSubject() );
				$sender	= $mail->senderAddress;
				if( $object->mail->getSender() )
					$sender	= $object->mail->getSender();
				$newInstance->setSender( $sender );

				if( $this->libraries & Logic_Mail::LIBRARY_MAIL1 ){
					$receiver	= new \CeusMedia\Mail\Participant();
 					$receiver->setAddress( $mail->receiverAddress );
					if( $mail->receiverName )
						$receiver->setName( $mail->receiverName );
					$newInstance->addRecipient( $receiver );
					$parts	= \CeusMedia\Mail\Parser::parseBody( $object->mail->getBody() );
					foreach( $parts as $part )
						$newInstance->addPart( $part );
				}
				if( $this->libraries & Logic_Mail::LIBRARY_MAIL2 ){
					$receiver	= new \CeusMedia\Mail\Address();
 					$receiver->set( $mail->receiverAddress );
					if( $mail->receiverName )
						$receiver->setName( $mail->receiverName );
					$newInstance->addRecipient( $receiver );
// @todo find a way to get Net_Mail::parts and import in new CeusMedia\Mail\Message instance
//					$newInstance->...
				}
				$object->mail	= $newInstance;
				$mail->object	= $this->_packMailObject( $mail, $object );
				return TRUE;
			}
		}
		else if( get_class( $object->mail ) === 'CeusMedia\Mail\Message' ){

		}
		return FALSE;
	}

	private function _migrateMailTemplates(){
		$model		= new Model_Mail_Template( $this->env );
		foreach( $model->getAll() as $template ){
			if( strlen( trim( $template->styles ) ) ){
				if( substr( $template->styles, 0, 2 ) !== '["' ){
					$list	= array( $template->styles );
					if( strpos( $template->styles, ',' ) )
						$list	= explode( ',', $template->styles );
					$model->edit( $template->mailTemplateId, array( 'styles' => json_encode( $list ) ) );
				}
			}
			if( strlen( trim( $template->images ) ) ){
				if( substr( $template->images, 0, 2 ) !== '["' ){
					$list	= array( $template->images );
					if( strpos( $template->images, ',' ) )
						$list	= explode( ',', $template->images );
					$model->edit( $template->mailTemplateId, array( 'images' => json_encode( $list ) ) );
				}
			}
		}
	}

	private function _packMailObject( $mail, $objectOrSerial ){
		$serial	= $objectOrSerial;
		if( is_object( $objectOrSerial ) )
			$serial	= serialize( $objectOrSerial );

		$object	= $serial;
		if( $mail->compression == Model_Mail::COMPRESSION_BZIP ){
			if( !Logic_Mail::canBzip() )
				throw new RuntimeException( 'Missing extension for BZIP compression' );
			$object	= bzcompress( $serial );
		}
		else if( $mail->compression == Model_Mail::COMPRESSION_GZIP ){
			if( !Logic_Mail::canGzip() )
				throw new RuntimeException( 'Missing extension for BZIP compression' );
			$object	= gzdeflate( $serial );
		}
		else if( $mail->compression == Model_Mail::COMPRESSION_BASE64 )
			$object	= base64_encode( $serial );
		return $object;
	}

	private function _unpackMailObject( $mail, $unserialize = TRUE ){
		$serial	= $mail->object;
		if( $mail->compression == Model_Mail::COMPRESSION_BZIP ){
			if( !Logic_Mail::canBzip() )
				throw new RuntimeException( 'Missing extension for BZIP compression' );
			$serial	= bzdecompress( $mail->object );
			if( is_int( $serial ) )
				throw new RuntimeException( 'Decompression failed' );
		}
		else if( $mail->compression == Model_Mail::COMPRESSION_GZIP ){
			if( !Logic_Mail::canGzip() )
				throw new RuntimeException( 'Missing extension for BZIP compression' );
			$serial	= gzinflate( $mail->object );
		}
		else if( $mail->compression == Model_Mail::COMPRESSION_BASE64 )
			$serial	= base64_decode( $mail->object );

		return $unserialize ? unserialize( $serial ) : $serial;
	}
}
?>
