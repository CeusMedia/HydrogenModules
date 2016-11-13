<?php
class Job_Work_Mail_Check extends Job_Abstract{

	protected $logic;

	public function __onInit(){
//		$this->options	= $this->env->getConfig()->getAll( 'module.resource_mail.', TRUE );
	}

	public function run(){
		$modelAddress	= new Model_Mail_Address( $this->env );
		$modelCheck		= new Model_Mail_Address_Check( $this->env );
		$sender			= new \CeusMedia\Mail\Participant( "dev@ceusmedia.de" );
		$checker		= new \CeusMedia\Mail\Check\Recipient( $sender, TRUE );
		$checker->setVerbose( !TRUE );

		$conditions	= array( 'status' => 1 );
		$orders		= array( 'address' => 'ASC' );
		$limits		= array( 0, 100 );
		$addresses	= $modelAddress->getAll( $conditions, $orders, $limits );
		foreach( $addresses as $address ){
			$this->out( "Checking: ".$address->address );

			$result		= $checker->test( new \CeusMedia\Mail\Participant( $address->address ) );
			$response	= $checker->getLastResponse();
/*			if( 0 ){
				print_m( $response );
				die;
			}*/

			$modelCheck->add( array(
				'mailAddressId'	=> $address->mailAddressId,
				'status'		=> $result ? 1 : -1,
				'error'			=> $response->error,
				'code'			=> $response->code,
				'message'		=> $response->message,
				'createdAt'		=> time(),
			) );
			$modelAddress->edit( $address->mailAddressId, array(
				'status'	=> $result ? 2 : -1,
				'checkedAt'	=> time(),
			) );
		}
		$this->out( 'Done checking '.count( $addresses ).' mail address(es)' );
	}
}
