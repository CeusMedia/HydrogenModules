<?php

use CeusMedia\HydrogenFramework\Hook;

class Hook_Work_Meeting extends Hook
{
	public function onUserRemove(): void
	{
		if( empty( $this->payload['userId'] ) )
			return;
		$model	= Model_Work_Meeting_Participant::getInstance( $this->env );
		$count	= $model->removeByIndex( 'userId', $this->payload['userId'] );
		if( isset( $this->payload['counts'] ) )
			$this->payload['counts']['Work_Meeting']	= (object) ['entities' => $count];
	}
}