<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Manage_My_User_Avatar extends Hook
{
	public function onRegisterTab()
	{
		$words	= (object) $this->env->getLanguage()->getWords( 'manage/my/user/avatar' );	//  load words
		$this->context->registerTab( 'avatar', $words->module['tab'], 6 );							//  register main tab
	}

	public function onUserRemove(): void
	{
		if( !empty( $this->payload['userId'] ) ){
			$model	= new Model_User_Avatar( $this->env );
			$count	= $model->removeByIndex( 'userId', $this->payload['userId'] );
			if( isset( $this->payload['counts'] ) )
				$this->payload['counts']['Manage_My_User_Avatar']	= (object) ['entities' => $count];
		}
	}
}
