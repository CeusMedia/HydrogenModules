<?php

use CeusMedia\HydrogenFramework\Hook;

class Hook_Resource_User extends Hook
{
	/**
	 *	...
	 *	@access		public
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function onUserRemove(): void
	{
		if( !empty( $this->payload['userId'] ) ){
			$modelUser		= new Model_User( $this->env );
			$modelPassword	= new Model_User_Password( $this->env );
			$modelPassword->removeByIndex( 'userId', $this->payload['userId'] );
			$modelUser->remove( $this->payload['userId'] );
			if( isset( $this->payload['counts'] ) )
				$this->payload['counts']['Resource_Users']	= (object) ['entities' => 1];
		}
	}
}
