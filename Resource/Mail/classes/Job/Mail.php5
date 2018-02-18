<?php
class Job_Mail extends Job_Abstract{

	protected $logic;
	protected $model;
	protected $greylistingDelay	= 900;

	public function __onInit(){
		$this->options	= $this->env->getConfig()->getAll( 'module.resource_mail.', TRUE );
		$this->logic	= new Logic_Mail( $this->env );
		$this->model	= new Model_Mail( $this->env );
		$this->phpHasGzip	= function_exists( 'gzdeflate' ) && function_exists( 'gzinflate' );
		$this->phpHasBzip	= function_exists( 'bzcompress' ) && function_exists( 'bzdecompress' );
		$this->_loadMailClasses();
	}

	public function clean(){
		$this->__removeNewsletters();
	}

	public function countQueuedMails( $commands, $parameters ){
		$conditions	= array( 'status' => array( Model_Mail::STATUS_NEW ) );
		$countNew		= $this->logic->countQueue( $conditions );
		$conditions	= array( 'status' => array( Model_Mail::STATUS_RETRY ) );
		$countRetry		= $this->logic->countQueue( $conditions );
		$this->out( sprintf( "%d mails to send, %d mail to retry.\n", $countNew, $countRetry ) );
	}

	public function migrate(){
		$module		= $this->env->getModules()->get( 'Resource_Mail' );
//		if( $module->versionInstalled >= "0.4.8" )									// todo: to be removed
//			$this->__migrateRepositoryFromCommonToMail();
		$this->out( 'Detecting mail classes of old mail entries:' );
		$this->__detectMailClass();
		$this->out( 'Detecting compression of old mail entries:' );
		$this->__detectCompression();
		$this->out( 'Migrate outdated mail mail classes:' );
		$this->__migrateMailClasses();
	}

	public function removeAttachments( $parameters = array() ){
		$conditions	= array( 'status' => array( -2, 2 ) );
		$orders		= array( 'mailId' => 'DESC' );
		$fails		= array();
		$results	= array(
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
							$results['attachments']++;
							$results['sizeBefore']	+= $sizeBefore;
							$results['sizeAfter']	+= strlen( $object );
						}
					}
				}
				$this->showProgress( ++$results['mails'], count( $mails ) );
			}
			catch( Exception $e ){
				$this->showProgress( ++$results['mails'], count( $mails ), 'E' );
				$fails[$mail->mailId]	= $e->getMessage();
			}
		}
		if( $mails )
			$this->out();
		if( $results['attachments'] ){
			$message	= 'Detached %s attachments, deflated mails from %s to %s.';
			$this->out( vsprintf( $message, array(
				$results['attachments'],
				Alg_UnitFormater::formatBytes( $results['sizeBefore'] ),
				Alg_UnitFormater::formatBytes( $results['sizeAfter'] )
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
				try{
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


	//  --  PRIVATE  --  //


	protected function _loadMailClasses(){
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

	protected function _packMailObject( $mail, $objectOrSerial ){
		$serial	= $objectOrSerial;
		if( is_object( $objectOrSerial ) )
			$serial	= serialize( $objectOrSerial );

		$object	= $serial;
		if( $mail->compression == Model_Mail::COMPRESSION_BZIP ){
			if( !$this->phpHasBzip )
				throw new RuntimeException( 'Missing extension for BZIP compression' );
			$object	= bzcompress( $serial );
		}
		else if( $mail->compression == Model_Mail::COMPRESSION_GZIP ){
			if( !$this->phpHasGzip )
				throw new RuntimeException( 'Missing extension for BZIP compression' );
			$object	= gzdeflate( $serial );
		}
		else if( $mail->compression == Model_Mail::COMPRESSION_BASE64 )
			$object	= base64_encode( $serial );
		return $object;
	}

	protected function _unpackMailObject( $mail, $unserialize = TRUE ){
		$serial	= $mail->object;
		if( $mail->compression == Model_Mail::COMPRESSION_BZIP ){
			if( !$this->phpHasBzip )
				throw new RuntimeException( 'Missing extension for BZIP compression' );
			$serial	= bzdecompress( $mail->object );
			if( is_int( $serial ) )
				throw new RuntimeException( 'Decompression failed' );
		}
		else if( $mail->compression == Model_Mail::COMPRESSION_GZIP ){
			if( !$this->phpHasGzip )
				throw new RuntimeException( 'Missing extension for BZIP compression' );
			$serial	= gzinflate( $mail->object );
		}
		else if( $mail->compression == Model_Mail::COMPRESSION_BASE64 )
			$serial	= base64_decode( $mail->object );

		return $unserialize ? unserialize( $serial ) : $serial;
	}





	protected function __detectMailClass(){
		$conditions	= array( 'mailClass' => '' );
		$orders		= array( 'mailId' => 'DESC' );
		$count		= 0;
		$mails		= $this->model->getAllByIndices( $conditions, $orders, array(), array( 'mailId' ) );
		foreach( $mails as $mailId ){
			try{
				$mail			= $this->model->get( $mailId );
				$serial			= $this->_unpackMailObject( $mail, FALSE );
				$serialStart	= substr( $serial, 0, 80 );
				$mailClass		= preg_replace( '/^O:[0-9]+:"([^"]+)":.+$/U', '\\1', $serialStart );
				$this->model->edit( $mail->mailId, array( 'mailClass' => $mailClass ) );
				$this->showProgress( ++$count, count( $mails ) );
			}
			catch( Exception $e ){
				$fails[$mailId]	= $e->getMessage();
				$this->showProgress( ++$count, count( $mails ), 'E' );
			}
		}
		if( $mails )
			$this->out();
		$this->out( 'Detected mail class for '.$count.' mails.' );
		$this->showErrors( 'detectMailClass', $fails );
	}

	protected function __removeNewsletters(){
		$conditions	= array(
			'status'		=> array( -2, 2 ),
			'mailClass'		=> 'Mail_Newsletter',
			'createdAt' 	=> '<'.( time() - 7 * 24 * 3600 ),
		);
		$orders		= array( 'mailId' => 'ASC' );
		$count		= 0;
		$fails		= array();
		$mails		= $this->model->getAll( $conditions, $orders, $limits );

		foreach( $mails as $mail ){
			try{
				$mail	= $this->logic->getMail( $mail->mailId );
				if( get_class( $mail->object ) === "Mail_Newsletter" ){
					$this->model->remove( $mail->mailId );
					$this->showProgress( ++$count, count( $mails ) );
				}
			}
			catch( Exception $e ){
				$this->showProgress( ++$count, count( $mails ), 'E' );
				$fails[$mail->mailId]	= $e->getMessage();
//					$this->env->getMessenger()->noteFailure( 'Decoding of mail ('.$mail->mailId.') failed: '.$e->getMessage() );
			}
		}
		if( $mails )
			$this->out();
		$this->out( 'Removed '.$count.' newsletter mails.' );
		$this->showErrors( 'removeNewsletters', $fails );
	}

	public function __migrateMailClasses( $conditions = array(), $orders = array(), $limits = array() ){
		$classMigrations	= array(
			'Mail_Auth_Password'		=> 'Mail_Auth_Local_Password',
			'Mail_Auth_Register'		=> 'Mail_Auth_Local_Register',
//			'Mail_Shop_Order_Customer'	=> 'Mail_Shop_Customer_Ordered',
//			'Mail_Shop_Order_Manager'	=> 'Mail_Shop_Manager_Ordered',
		);

		$count		= 0;
		$fails		= array();
		$conditions	= array(
			'status'	=> array( -2, 2 ),
			'mailClass' => array_keys( $classMigrations ),
		);
		$orders		= $orders ? $orders : array( 'mailId' => 'ASC' );
		$limits		= $limits ? $limits : array();
		$mails		= $this->model->getAll( $conditions, $orders, $limits );
		foreach( $mails as $mail ){
			try{
				$serial		= $this->_unpackMailObject( $mail, FALSE );
				$newerClass	= $classMigrations[$mail->mailClass];
				$find		= 'O:'.strlen( $mail->mailClass ).':"'.$mail->mailClass.'":';
				$replace	= 'O:'.strlen( $newerClass ).':"'.$newerClass.'":';
				$serial		= str_replace( $find, $replace, $serial );
				$object		= $this->_packMailObject( $mail, $serial );
				$this->model->edit( $mail->mailId, array(
					'object'	=> $object,
					'mailClass'	=> $newerClass,
				), FALSE );
				$this->showProgress( ++$count, count( $mails ) );
			}
			catch( Exception $e ){
				$fails[$mail->mailId]	= $e->getMessage();
				$this->showProgress( ++$count, count( $mails ), 'E' );
			}
		}
		if( $mails )
			$this->out();
		$this->out( "Migrated ".$count." mails, ".count( $fails )." failed" );
		$this->showErrors( 'migrateMailClasses', $fails );
	}

	protected function __detectCompression(){
		$conditions	= array( 'compression' => array( Model_Mail::COMPRESSION_UNKNOWN ) );
		$orders		= array( 'mailId' => 'DESC' );

		$count	= 0;
		$fails	= array();
		$mails	= $this->model->getAllByIndices( $conditions, $orders, array(), array( 'mailId' ) );
		foreach( $mails as $mailId ){
			try{
				$mail			= $this->model->get( $mailId );
				$compression	= Model_Mail::COMPRESSION_UNKNOWN;
				$finfo			= new finfo( FILEINFO_MIME );
				$mimeType		= $finfo->buffer( $mail->object );
				if( preg_match( '@application/x-bzip2@', $mimeType ) )
					$compression	= Model_Mail::COMPRESSION_BZIP;
				else if( preg_match( '@application/x-gzip@', $mimeType ) )
					$compression	= Model_Mail::COMPRESSION_GZIP;
				else if( preg_match( '@^[A-Za-z0-9+/=]+$@', $mimeType ) )
					$compression	= Model_Mail::COMPRESSION_BASE64;
				if( $compression ){
					$this->model->edit( $mailId, array( 'compression' => $compression ) );
					$this->showProgress( ++$count, count( $mails ) );
				}
			}
			catch( Exception $e ){
				$this->showProgress( ++$count, count( $mails ), 'E' );
				$fails[$mailId]	= $e->getMessage();
			}
		}
		if( $mails )
			$this->out();
		$this->out( 'Detected compression of '.$count.' mails.' );
		$this->showErrors( 'detectCompression', $fails );
	}

/*	protected function __migrateRepositoryFromCommonToMail(){
		$count	= 0;
		$mails	= $this->model->getAll( $conditions, array( 'mailId' => 'DESC' ) );
		foreach( $mails as $mail ){
			if( empty( $mail->senderAddress ) ){
				try{
					$this->decodeMailObject( $mail, TRUE );												//  decompress & unserialize mail object serial and apply to mail data object
					if( is_object( $mail->object ) ){
//							die( get_class( $mail->object ) );
						if( method_exists( $mail->object->mail, 'getSender' ) ){
							$count++;
							$this->model->edit( $mail->mailId, array( 'senderAddress' => $mail->object->mail->getSender() ) );
						}
					}
				}
				catch( Exception $e ){
					remark( $e->getMessage() );
				}
			}
		}
		$this->out( '' );
		$this->out( '- migrateRepositoryFromCommonToMail: Migrated '.$count.' mails.' );
	}*/
}
?>
