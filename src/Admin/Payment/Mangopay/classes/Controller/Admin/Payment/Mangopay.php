<?php

use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

class Controller_Admin_Payment_Mangopay extends Controller
{
	protected MessengerResource $messenger;

	public function index()
	{
		$this->restart( 'client', TRUE );
	}

	protected function __onInit(): void
	{
		$this->messenger	= $this->env->getMessenger();
	}

	protected function handleMangopayResponseException( $e )
	{
		ob_start();
		print_r( $e->GetErrorDetails()->Errors );
		$details	= ob_get_clean();
		$message	= 'Response Exception "%s" (%s)<br/><small>%s</small>';
		$this->messenger->noteFailure( $message, $e->getMessage(), $e->getCode(), $details );
	}
}
