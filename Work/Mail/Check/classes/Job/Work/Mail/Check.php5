<?php
class Job_Work_Mail_Check extends Job_Abstract{

	protected $logic;

	public function __onInit(){
		$this->options	= $this->env->getConfig()->getAll( 'module.work_mail_check.', TRUE );
	}

	public function run(){
		$modelAddress	= new Model_Mail_Address( $this->env );
		$modelCheck		= new Model_Mail_Address_Check( $this->env );
		$sender			= new \CeusMedia\Mail\Participant( $this->options->get( 'sender' ) );
		$checker		= new \CeusMedia\Mail\Check\Recipient( $sender, TRUE );
		$checker->setVerbose( !TRUE );

		$conditions	= array( 'status' => 1 );
		$orders		= array( 'address' => 'ASC' );
		$limits		= array( 0, $this->options->get( 'job.limit' ) );
		$addresses	= $modelAddress->getAll( $conditions, $orders, $limits );
		foreach( $addresses as $address ){
			$this->out( "Checking: ".$address->address );
			try{
				$result		= $checker->test( new \CeusMedia\Mail\Participant( $address->address ) );
				$response	= $checker->getLastResponse();
				$modelCheck->add( array(
					'mailAddressId'	=> $address->mailAddressId,
					'status'		=> $result ? 1 : -1,
					'error'			=> $response->error,
					'code'			=> $response->code,
					'message'		=> $response->message,
					'createdAt'		=> time(),
				) );
				$status	= 2;
				if( !$result ){
					$status	= -2;
					if( substr( $response->code, 0, 1 ) == "4" )
						$status	= -1;
				}
				$modelAddress->edit( $address->mailAddressId, array(
					'status'	=> $status,
					'checkedAt'	=> time(),
				) );
			}
			catch( Exception $e ){
				$modelCheck->add( array(
					'mailAddressId'	=> $address->mailAddressId,
					'status'		=> -2,
					'error'			=> NULL,
					'code'			=> $e->getCode(),
					'message'		=> $e->getMessage(),
					'createdAt'		=> time(),
				) );
				$modelAddress->edit( $address->mailAddressId, array(
					'status'	=> -2,
					'checkedAt'	=> time(),
				) );
			}
		}
		$this->out( 'Done checking '.count( $addresses ).' mail address(es)' );
	}
}
