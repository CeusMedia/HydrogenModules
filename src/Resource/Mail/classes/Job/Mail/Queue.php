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
	 *	@throws		ReflectionException
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
		$orders		= [
			'status'	=> 'ASC',		//  order: NEW, RETRY
			'priority'	=> 'DESC',		//  order: HIGHEST, HIGH, DEFAULT, LOW, LOWEST
			'mailId'	=> 'ASC'		//  FIFO
		];
		$count		= $this->logic->countQueue( $conditions );

		if( $this->dryMode ){
			$this->out( 'DRY RUN - no changes will be made.' );
			$this->out( 'Would send '.$count.' mails.' );
			$this->setResult( Entity_Job_Result::STATUS_SUCCESS, 0, [
				'mode'		=> 'dry',
				'would'		=> $count,
			] );
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
		$status	= Entity_Job_Result::STATUS_SUCCESS;
		if( 0 !== count( $listFailed ) ){
			$status	= Entity_Job_Result::STATUS_PARTIAL;
			if( 0 === count( $listSent ) )
				$status	= Entity_Job_Result::STATUS_FAILURE;
		}
		$resultData	= [
			'count'		=> $count,
			'limit'		=> $limit,
			'failed'	=> count( $listFailed ),
			'sent'		=> count( $listSent ),
			'ids'		=> $listSent,
		];
		$this->setResult( $status, count( $listSent ), $resultData );				//  save job results
		$this->log( json_encode( array_merge( [
			'timestamp'	=> time(),
			'datetime'	=> date( "Y-m-d H:i:s" ),
		], $resultData ) ) );
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
