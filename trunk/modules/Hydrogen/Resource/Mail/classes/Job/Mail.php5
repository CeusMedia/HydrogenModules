<?php
class Job_Mail extends Job_Abstract{

	public function __onInit(){
		$this->options	= $this->env->getConfig()->getAll( 'module.resource_mail.', TRUE );
	}

	public function sendQueuedMails(){
		$sleep		= (float) $this->options->get( 'job.sleep' );
		$limit		= (integer) $this->options->get( 'job.limit' );
		set_time_limit( ( $timeLimit = ( 5 + $sleep ) * $limit + 10 ) );
#remark( function_exists( 'bzcompress') );
#remark( "time limit: ".$timeLimit );
#print_m( $this->options->getAll() );
#die;
		$this->log( 'run with config: {sleep: '.$sleep.', limit: '.$limit.'}' );
		$logic	= new Logic_Mail( $this->env );
		$conditions	= array( 'status' => '<2' );
		$limits		= $limit > 0 ? array( 0, $limit ) : array();
		$listSent	= array();
		if( ( $count = $logic->countQueue( $conditions, array(), $limits ) ) ){
			foreach( $logic->getQueuedMails( array( 'status' => '<2' ) ) as $mail ){
				try{
					$logic->sendQueuedMail( $mail->mailId );
					$listSent[]	= (int) $mail->mailId;
					if( $sleep > 0 )
						$sleep >= 1 ? sleep( $sleep ) : usleep( $sleep * 1000 * 1000 );
				}
				catch( Exception $e ){
					$this->logException( $e );
				}
			}
			$this->log( '{count: '.$count.', sent: '.count( $listSent ).', ids: ['.join( ',', $listSent ).']}' );
		}
	}
}
?>
