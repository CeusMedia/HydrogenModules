<?php
class Controller_Mangopay_Hook extends CMF_Hydrogen_Controller{

	public static $verbose	= TRUE;

	public function __onInit(){
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
		$this->mangopay		= Logic_Payment_Mangopay::getInstance( $this->env );
		$this->model		= new Model_Mangopay_Event( $this->env );
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.resource_payment_mangopay.', TRUE );
	}

	public function apply(){
		$hooks		= $this->mangopay->getHooks();
		$hookedEventTypes	= array();
		foreach( $hooks as $hook )
			$hookedEventTypes[$hook->EventType]	= $hook;

		if( $this->request->has( 'save' ) ){
			$path	= $this->request->get( 'path' );
			$types	= $this->request->get( 'types' );
//print_m( $types );die;
			if( !strlen( $path ) ){
				$this->messenger->noteError( 'Invalid hook path' );
				$this->restart( NULL, TRUE );
			}
			foreach( $types as $type ){
				$id	= 0;
				if( array_key_exists( $type, $hookedEventTypes ) ){
					if( $hookedEventTypes[$type]->Url == $this->env->url.$path )
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
		$this->addData( 'baseUrl', $this->env->url );
		$this->addData( 'eventTypes', $this->model->types );
		$this->addData( 'hooks', $hooks );
		$this->addData( 'hookedEventTypes', array_keys( $hookedEventTypes ) );
		$this->addData( 'currentUrl', $hooks ? $hooks[0]->Url : '' );
	}


	protected function handleMangopayResponseException( $e ){
		ob_start();
		print_r( $e->GetErrorDetails() );
		$details	= ob_get_clean();
		$message	= 'Response Exception "%s" (%s)<br/><small>%s</small>';
		$this->messenger->noteFailure( $message, $e->getMessage(), $e->getCode(), $details );
	}


	public function index( $refresh = NULL ){
		if( $hookId )
			$this->restart( 'view/'.$hookId, TRUE );
		$hooks		= $this->mangopay->getHooks( $refresh );
		$hookedEventTypes	= array();
		foreach( $hooks as $hook )
			$hookedEventTypes[$hook->EventType]	= $hook;

		$this->addData( 'hooks', $hooks );
		$this->addData( 'eventTypes', $this->model->types );
		$this->addData( 'hookedEventTypes', $hookedEventTypes );
	}

	public function view( $hookId ){
		$hook	= $this->mangopay->getHook( $hookId );
		if( !$hook ){
			$this->messenger->noteError( 'Invalid hook ID.' );
			$this->restart( NULL, TRUE );
		}
		$this->addData( 'hook', $hook );
	}
}
?>
