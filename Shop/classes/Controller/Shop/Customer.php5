<?php
class Controller_Shop_Customer extends CMF_Hydrogen_Controller{

	/**	@var	Logic_ShopBridge		$brige */
	protected $bridge;

	protected $backends			= array();
	protected $cartTotal		= 0;

	protected $modelAddress;

	protected function __onInit(){
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
		$this->bridge		= new Logic_ShopBridge( $this->env );
		$this->modelAddress	= new Model_Address( $this->env );

		$captain	= $this->env->getCaptain();
		$captain->callHook( 'ShopPayment', 'registerPaymentBackend', $this, array() );
		$this->addData( 'paymentBackends', $this->backends );

		if( $this->session->get( 'shop_order_positions' ) ){
			foreach( $this->session->get( 'shop_order_positions' ) as $position ){
				$source		= $this->bridge->getBridgeObject( (int)$position->bridgeId );
				$article	= $source->get( $position->articleId, $position->quantity );
				$this->cartTotal	+= $article->price->all;
			}
		}
		$this->addData( 'cartTotal', $this->cartTotal );
	}

	public function address( $addressId, $type = NULL, $remove = NULL ){
		$customerMode	= $this->session->get( 'shop_customer_mode' );
		$countries		= $this->env->getLanguage()->getWords( 'countries' );
		switch( $customerMode ){
			case Model_Shop_Order::CUSTOMER_MODE_GUEST:
				$relationId		= $this->session->get( 'shop_customer_id' );
				$relationType	= 'customer';
				break;
			case Model_Shop_Order::CUSTOMER_MODE_ACCOUNT:
				$relationId		= $this->session->get( 'userId' );
				$relationType	= 'user';
				if( !$relationId )
					$this->restart( 'shop/customer' );
				break;
			default:
				throw new RuntimeException( 'Unknown customer mode: '.$customerMode );
		}

		if( $addressId && $remove ){
			$this->modelAddress->removeByIndices( array(
				'addressId'		=> $addressId,
 				'relationId'	=> $relationId,
				'relationType'	=> $relationType,
				'type'			=> $type,
			) );
			$this->restart( NULL, TRUE );
		}
		$data		= $this->request->getAll( NULL, TRUE );
		$countryKey	= 'DE';
		if( $data->get( 'country' ) )
			$countryKey	= array_search( $data->get( 'country' ), $countries );
		if( $this->request->has( 'save' ) ){
			if( $addressId > 0 ){
				$address	= $this->modelAddress->getByIndices( array(
					'addressId'		=> $addressId,
	 				'relationId'	=> $relationId,
					'relationType'	=> $relationType
				) );
				if( !$address ){
				//	@todo: handle this situation!
				}
				$mismatchingType	= $relationType === $address->relationType;
				$mismatchingId		= $address->relationId == $relationId;
				if( !$mismatchingType || !$mismatchingId ){
					$this->messenger->noteError( 'Access to address denied.' );
					$this->restart( NULL, TRUE );
				}
				$data->set( 'country', $countryKey );
				$data->set( 'modifiedAt', time() );
				$this->modelAddress->edit( $addressId, $data->getAll() );
				$this->restart( NULL, TRUE );
			}
			else{
				if( !$type || !in_array( (int) $type, array( Model_Address::TYPE_DELIVERY, Model_Address::TYPE_BILLING ) ) ){
				//	@todo: handle this situation!
				}
				$mandatory	= array(
					'firstname',
					'surname',
					'email',
					'country',
					'city',
					'postcode',
					'street',
				);
				$labels		= $this->getWords( 'customer' );
				foreach( $mandatory as $name ){
					if( !$data->get( $name ) ){
						$label	= Alg_Text_CamelCase::convert( $name, FALSE );
						$this->messenger->noteError(
							$this->words->errorFieldEmpty,
							'billing_'.$name,
							$labels['labelBilling'.$label]
						);
					}
				}
				if( !$this->messenger->gotError() ){
					$data->set( 'relationId', $relationId );
					$data->set( 'relationType', $relationType );
					$data->set( 'type', $type );
					$data->set( 'createdAt', time() );
					$data->set( 'country', $countryKey );
					$addressId	= $this->modelAddress->add( $data->getAll() );
				}
				$this->restart( NULL, TRUE );
			}
		}
		if( !$addressId ){
			$this->messenger->noteError( 'No address ID given.' );
			$this->restart( NULL, TRUE );
		}
		$address	= $this->modelAddress->get( $addressId );
		if( !$address ){
			$this->messenger->noteError( 'Invalid address ID given.' );
			$this->restart( NULL, TRUE );
		}
		$this->addData( 'address', $address );
	}

	public function index( $mode = NULL ){
		if( (int) $mode > 0 ){
			$this->session->set( 'shop_customer_mode', (int) $mode );
			$this->restart( NULL, TRUE );
		}
		if( !$this->session->get( 'shop_order_positions' ) ){
			$this->messenger->noteNotice( $this->words->errorCustomerEmptyCart );
			$this->restart( 'shop/cart' );
		}
		if( $this->env->getModules()->has( 'Resource_Authentication' ) ){
			$logicAuth	= Logic_Authentication::getInstance( $this->env );
			if( $logicAuth->isAuthenticated() )
				$this->session->set( 'shop_customer_mode', Model_Shop_Order::CUSTOMER_MODE_ACCOUNT );
			if( !$this->session->get( 'shop_customer_mode' ) )
				$this->session->set( 'shop_customer_mode', Model_Shop_Order::CUSTOMER_MODE_ACCOUNT );
		}
		switch( $this->session->get( 'shop_customer_mode' ) ){
			case Model_Shop_Order::CUSTOMER_MODE_ACCOUNT:
				$this->handleAccount();
				break;
			case Model_Shop_Order::CUSTOMER_MODE_GUEST:
			default:
				$this->handleGuest();
				break;
		}
	}

	protected function handleGuest(){
		$countries	= $this->env->getLanguage()->getWords( 'countries' );
		$this->addData( 'mode', Model_Shop_Order::CUSTOMER_MODE_GUEST );
		$this->addData( 'userId', 0 );

		$customerId		= $this->session->get( 'shop_customer_id' );
		if( !$customerId ){
			$model		= new Model_Shop_Customer( $this->env );
			$customerId	= $model->add( array() );
			$this->session->set( 'shop_customer_id', $customerId );
		}
		$addressDelivery	= $this->modelAddress->getByIndices( array(
			'relationType'	=> 'customer',
			'relationId'	=> $customerId,
			'type'			=> Model_Address::TYPE_DELIVERY,
		) );
		$addressBilling		= $this->modelAddress->getByIndices( array(
			'relationType'	=> 'customer',
			'relationId'	=> $customerId,
			'type'			=> Model_Address::TYPE_BILLING,
		) );
		$this->addData( 'addressBilling', $addressBilling );
		$this->addData( 'addressDelivery', $addressDelivery );

		if( $this->request->has( 'save' ) && $addressBilling && $addressDelivery ){
			$this->session->set( 'shop_order_customer', $customerId );
			$this->restart( 'shop/conditions' );
		}

		$user	= (object) array(
			'firstname'	=> NULL,
			'surname'	=> NULL,
			'email'		=> NULL,
			'street'	=> NULL,
			'city'		=> NULL,
			'postcode'	=> NULL,
			'country'	=> NULL,
			'region'	=> NULL,
			'phone'		=> NULL,
		);
		$this->addData( 'user', $user );
	}

	protected function handleAccount(){
		$logicAuth	= Logic_Authentication::getInstance( $this->env );
		$customer	= $this->session->get( 'shop_order_customer' );
		$countries	= $this->env->getLanguage()->getWords( 'countries' );
		$userId		= 0;
		if( $logicAuth->isAuthenticated() ){
			$userId	= $logicAuth->getCurrentUserId();
			if( $userId ){
				$modelUser	= new Model_User( $this->env );
				$user		= $modelUser->get( $userId );
				$addressDelivery	= $this->modelAddress->getByIndices( array(
					'relationType'	=> 'user',
					'relationId'	=> $userId,
					'type'			=> Model_Address::TYPE_DELIVERY,
				) );
				$addressBilling		= $this->modelAddress->getByIndices( array(
					'relationType'	=> 'user',
					'relationId'	=> $userId,
					'type'			=> Model_Address::TYPE_BILLING,
				) );
				if( $this->request->has( 'save' ) && $addressDelivery && $addressBilling ){
					$this->session->set( 'shop_order_customer', $userId );
					$this->restart( 'shop/conditions' );
				}
				if( !array_key_exists( $user->country, $countries ) )
					$user->country	= 'DE';
/*				if( $user ){
					$customer	= (object) array(
						'institution'	=> NULL,
						'firstname'		=> $user->firstname,
						'surname'		=> $user->surname,
						'email'			=> $user->email,
						'phone'			=> $user->phone,
						'address'		=> $user->street.' '.$user->number,
						'postcode'		=> $user->postcode,
						'city'			=> $user->city,
						'state'			=> NULL,
						'region'		=> NULL,
						'country'		=> (object) array(
							'code'		=> $user->country,
							'label'		=> $countries[$user->country],
						),
					);
				}*
				$this->addData( 'customer', $customer );*/
				$this->addData( 'countries', $countries );
				$this->addData( 'user', $user );
				$this->addData( 'addressBilling', $addressBilling );
				$this->addData( 'addressDelivery', $addressDelivery );
			}
		}
		$this->addData( 'mode', Model_Shop_Order::CUSTOMER_MODE_ACCOUNT );
		$this->addData( 'userId', $userId );
		$this->addData( 'username', $this->request->get( 'username' ) );
		$this->addData( 'useOauth2', $this->env->getModules()->has( 'Resource_Authentication_Backend_OAuth2' ) );
	}

	public function registerPaymentBackend( $backend, $key, $title, $path, $priority = 5, $icon = NULL ){
		$this->backends[]	= (object) array(
			'backend'	=> $backend,
			'key'		=> $key,
			'title'		=> $title,
			'path'		=> $path,
			'priority'	=> $priority,
			'icon'		=> $icon,
		);
	}
}
