<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Mail\Address as MailAddress;
use CeusMedia\Mail\Address\Check\Availability as MailAvailabilityCheck;

class Job_Work_Mail_Check extends Job_Abstract
{
	protected Dictionary $options;

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function run()
	{
		$modelAddress	= new Model_Mail_Address( $this->env );
		$modelCheck		= new Model_Mail_Address_Check( $this->env );
		$sender			= new MailAddress( $this->options->get( 'sender' ) );
		$checker		= new MailAvailabilityCheck( $sender );
#		$checker->setVerbose( !TRUE );

		$conditions	= ['status' => 1];
		$orders		= ['address' => 'ASC'];
		$limits		= [0, $this->options->get( 'job.limit' )];
		$addresses	= $modelAddress->getAll( $conditions, $orders, $limits );
		foreach( $addresses as $address ){
			$this->out( "Checking: ".$address->address );
			try{
				$result		= $checker->test( new MailAddress( $address->address ) );
				$response	= $checker->getLastResponse();
				$modelCheck->add( [
					'mailAddressId'	=> $address->mailAddressId,
					'status'		=> $result ? 1 : -1,
					'error'			=> $response->error,
					'code'			=> $response->code,
					'message'		=> $response->message,
					'createdAt'		=> time(),
				] );
				$status	= 2;
				if( !$result ){
					$status	= -2;
					if( substr( $response->code, 0, 1 ) == "4" )
						$status	= -1;
				}
				$modelAddress->edit( $address->mailAddressId, [
					'status'	=> $status,
					'checkedAt'	=> time(),
				] );
			}
			catch( Exception $e ){
				$modelCheck->add( [
					'mailAddressId'	=> $address->mailAddressId,
					'status'		=> -2,
					'error'			=> NULL,
					'code'			=> $e->getCode(),
					'message'		=> $e->getMessage(),
					'createdAt'		=> time(),
				] );
				$modelAddress->edit( $address->mailAddressId, [
					'status'	=> -2,
					'checkedAt'	=> time(),
				] );
			}
		}
		$this->out( 'Done checking '.count( $addresses ).' mail address(es)' );
	}

	protected function __onInit(): void
	{
		$this->options	= $this->env->getConfig()->getAll( 'module.work_mail_check.', TRUE );
	}
}
