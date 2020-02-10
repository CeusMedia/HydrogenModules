<?php
class Job_Mail_Queue extends Job_Abstract{

	protected $logic;
	protected $options;
//	protected $greylistingDelay	= 900;

	public function __onInit(){
		$this->logic		= Logic_Mail::getInstance( $this->env );
		$this->options		= $this->env->getConfig()->getAll( 'module.resource_mail.', TRUE );
	}

	public function countQueuedMails(){
		$conditions		= array( 'status' => array( Model_Mail::STATUS_NEW ) );
		$countNew		= $this->logic->countQueue( $conditions );
		$conditions		= array( 'status' => array( Model_Mail::STATUS_RETRY ) );
		$countRetry		= $this->logic->countQueue( $conditions );
		$this->out( sprintf( "%d mails to send, %d mail to retry.", $countNew, $countRetry ) );
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
			'attemptedAt'	=> '< '.( time() - $this->options->get( 'retry.delay' ) ),
		);
		$orders		= array( 'status' => 'ASC', 'mailId' => 'ASC' );
		$count		= $this->logic->countQueue( $conditions );
		if( $this->dryMode ){
			$this->out( 'DRY RUN - no changes will be made.' );
			$this->out( 'Would send '.$count.' mails.' );
		}
		if( $this->dryMode || !$count )
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
