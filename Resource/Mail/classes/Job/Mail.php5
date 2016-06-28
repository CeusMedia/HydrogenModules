<?php
class Job_Mail extends Job_Abstract{

	protected $logic;

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

		$this->log( 'run with config: {sleep: '.$sleep.', limit: '.$limit.'}' );

		$counter	= 0;
		$listSent	= array();
		$conditions	= array( 'status' => array( 0, 1 ) );
		$orders		= array( 'status' => 'ASC', 'mailId' => 'ASC' );
		$count		= $this->logic->countQueue( $conditions, array() );
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
					$this->logException( $e );
				}
			}
		}
		$this->log( '{count: '.$count.', sent: '.count( $listSent ).', ids: ['.join( ',', $listSent ).']}' );
	}
}
?>
