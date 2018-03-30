<?php
class Job_Provision extends Job_Abstract{

	/** @todo rework */
	public function manageLicenses( $parameters = array() ){
		$logic	= Logic_User_Provision::getInstance( $this->env );
		$keys	= $logic->handleExpiredLicenses();
		if( $keys ){
			$followUps	= 0;
			foreach( $keys as $key )
				$followUps	+= (int) isset( $key->nextKey );
			$this->out( 'Provision.expire: Disabled '.count( $keys ).' license(s).', TRUE );
			if( $followUps )
				$this->out( 'Provision: Enabled '.$followUps.' license(s) afterwards.', TRUE );
		}
	}
}
