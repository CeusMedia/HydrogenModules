<?php
declare(strict_types=1);

use CeusMedia\HydrogenFramework\Hook;

class Hook_Manage_Customer_Project extends Hook
{
	public function onRegisterTab(): void
	{
		$words	= $this->env->getLanguage()->getWords( 'manage/customer/project' );
		View_Manage_Customer::registerTab( 'project/'.$this->payload['customerId'], $words['tabs']['project'], 5 );
	}
}