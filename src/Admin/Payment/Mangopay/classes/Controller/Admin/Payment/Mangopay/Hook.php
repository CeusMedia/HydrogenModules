<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

class Controller_Admin_Payment_Mangopay_Hook extends Controller
{
	public static bool $verbose	= TRUE;

	protected Dictionary $request;
	protected MessengerResource $messenger;
	protected Logic_Payment_Mangopay $mangopay;
	protected Model_Mangopay_Event $model;
	protected Dictionary $modulConfig;
	protected string $baseUrl;

	public function apply(): void
	{
		$hooks		= $this->mangopay->getHooks();
		$hookedEventTypes	= [];
		foreach( $hooks as $hook )
			$hookedEventTypes[$hook->EventType]	= $hook;

		if( $this->request->has( 'save' ) ){
			$path	= $this->request->get( 'path' );
			$types	= $this->request->get( 'types' );
			if( !strlen( $path ) ){
				$this->messenger->noteError( 'Invalid hook path' );
				$this->restart( NULL, TRUE );
			}
			foreach( $types as $type ){
				$id	= 0;
				if( array_key_exists( $type, $hookedEventTypes ) ){
					if( $hookedEventTypes[$type]->Url == $this->baseUrl.$path )
						continue;
					$id		= $hookedEventTypes[$type]->Id;
				}
				$tag		= 'Set on '.date( 'Y-m-d H:i:s' ).'.';
				try{
					$this->mangopay->setHook( $id, $type, $path, TRUE, $tag );
				}
				catch( Exception $e ){
					$this->handleMangopayResponseException( $e );
				}
			}
			$this->messenger->noteSuccess( 'Hooks applied ('.count( $hooks ).').' );
			$this->restart( 'apply', TRUE );
		}
		$this->addData( 'eventTypes', $this->model->types );
		$this->addData( 'hooks', $hooks );
		$this->addData( 'hookedEventTypes', array_keys( $hookedEventTypes ) );
		$this->addData( 'currentUrl', $hooks ? $hooks[0]->Url : '' );
	}

	public function index( $refresh = NULL ): void
	{
		$hooks		= $this->mangopay->getHooks( $refresh );
		$hookedEventTypes	= [];
		foreach( $hooks as $hook )
			$hookedEventTypes[$hook->EventType]	= $hook;

		$this->addData( 'hooks', $hooks );
		$this->addData( 'eventTypes', $this->model->types );
		$this->addData( 'hookedEventTypes', $hookedEventTypes );
	}

	public function view( $hookId ): void
	{
		$hook	= $this->mangopay->getHook( $hookId );
		if( !$hook ){
			$this->messenger->noteError( 'Invalid hook ID.' );
			$this->restart( NULL, TRUE );
		}
		$this->addData( 'hook', $hook );
	}

	protected function __onInit(): void
	{
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
		$this->mangopay		= Logic_Payment_Mangopay::getInstance( $this->env );
		$this->model		= new Model_Mangopay_Event( $this->env );
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.resource_payment_mangopay.', TRUE );
		$this->baseUrl		= $this->env->url;
		if( $this->env->getModules()->has( 'Resource_Frontend' ) )
			$this->baseUrl	= Logic_Frontend::getInstance( $this->env )->getUri();
		$this->addData( 'baseUrl', $this->baseUrl );
	}

	protected function handleMangopayResponseException( $e ): void
	{
		ob_start();
		print_r( $e->GetErrorDetails() );
		$details	= ob_get_clean();
		$message	= 'Response Exception "%s" (%s)<br/><small>%s</small>';
		$this->messenger->noteFailure( $message, $e->getMessage(), $e->getCode(), $details );
	}
}
