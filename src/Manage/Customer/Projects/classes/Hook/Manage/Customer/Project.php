<?php
declare(strict_types=1);

use CeusMedia\HydrogenFramework\Hook;

class Hook_Manage_Customer_Project extends Hook
{
	public function onRegisterTab(): void
	{
		View_Manage_Customer::registerTab( 'project/'.$this->payload['customerId'], '+Projekte', 5 );
	}
}