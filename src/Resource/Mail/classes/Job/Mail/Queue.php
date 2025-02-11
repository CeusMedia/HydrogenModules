<?php

use CeusMedia\Common\ADT\Collection\Dictionary;

class Job_Mail_Queue extends Job_Abstract
{
	protected Logic_Mail $logic;
	protected Dictionary $options;
//	protected $greylistingDelay	= 900;

	public function countQueuedMails(): void
	{
		$conditions		= ['status' => [Model_Mail::STATUS_NEW]];
		$countNew		= $this->logic->countQueue( $conditions );
		$conditions		= ['status' => [Model_Mail::STATUS_RETRY]];
		$countRetry		= $this->logic->countQueue( $conditions );
		$this->out( sprintf( "%d mails to send, %d mail to retry.", $countNew, $countRetry ) );
	}

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function sendQueuedMails(): void
	{
		$sleep		= (float) $this->options->get( 'queue.job.sleep' );
		$limit		= (integer) $this->options->get( 'queue.job.limit' );
		set_time_limit( ( 5 + $sleep ) * $limit + 10 );

		if( !$this->dryMode )
			$this->logic->abortMailsWithTooManyAttempts();

		$counter	= 0;
		$listSent	= [];
		$listFailed	= [];
		$conditions	= [
			'status'		=> [
				Model_Mail::STATUS_NEW,
				Model_Mail::STATUS_RETRY
			],
			'attemptedAt'	=> '< '.( time() - $this->options->get( 'retry.delay' ) ),
		];
		$orders		= ['status' => 'ASC', 'mailId' => 'ASC'];		//  FIFO
		$count		= $this->logic->countQueue( $conditions );

		if( $this->dryMode ){
			$this->out( 'DRY RUN - no changes will be made.' );
			$this->out( 'Would send '.$count.' mails.' );
			$this->results	= [
				'mode'		=> 'dry',
				'count'		=> $count,
			];
			return;
		}
		while( $count && $counter < $count && ( !$limit || $counter < $limit ) ){
			if( $counter > 0 && $sleep > 0 )
				$sleep >= 1 ? sleep( $sleep ) : usleep( $sleep * 1000 * 1000 );
			$mails	= $this->logic->getQueuedMails( $conditions, $orders, [0, 1] );
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
		$this->results	= [				//  save job results
			'count'		=> $count,
			'failed'	=> count( $listFailed ),
			'sent'		=> count( $listSent ),
			'ids'		=> $listSent,
		];
		$this->log( json_encode( array_merge( [
			'timestamp'	=> time(),
			'datetime'	=> date( "Y-m-d H:i:s" ),
		], $this->results ) ) );
	}

	/**
	 * @return void
	 * @throws ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->logic		= Logic_Mail::getInstance( $this->env );
		$this->options		= $this->env->getConfig()->getAll( 'module.resource_mail.', TRUE );
	}
}
