<?php
class Controller_Shop_Customer extends CMF_Hydrogen_Controller{

	/**	@var	Logic_ShopBridge		$brige */
	protected $bridge;

	protected $backends			= array();
	protected $cartTotal		= 0;

	/**	@var	Model_Address				$modelAddress */
	protected $modelAddress;

	/**	@var	Model_Shop_Cart				$modelCart */
	protected $modelCart;

	protected function __onInit(){
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
		$this->bridge		= new Logic_ShopBridge( $this->env );
		$this->modelAddress	= new Model_Address( $this->env );
		$this->modelCart	= new Model_Shop_Cart( $this->env );

		$captain	= $this->env->getCaptain();
		$captain->callHook( 'ShopPayment', 'registerPaymentBackend', $this, array() );
		$this->addData( 'paymentBackends', $this->backends );

		if( $this->modelCart->get( 'positions' ) ){
			foreach( $this->modelCart->get( 'positions' ) as $position ){
				$this->cartTotal	+= $position->article->price->all;
			}
		}
		$this->addData( 'cartTotal', $this->cartTotal );
	}

	public function address( $addressId, $type = NULL, $remove = NULL ){
		$customerMode	= $this->modelCart->get( 'customerMode' );
		$countries		= $this->env->getLanguage()->getWords( 'countries' );
		switch( $customerMode ){
			case Model_Shop_CART::CUSTOMER_MODE_GUEST:
				$relationId		= $this->modelCart->get( 'customerId' );
				$relationType	= 'customer';
				break;
			case Model_Shop_CART::CUSTOMER_MODE_ACCOUNT:
				$relationId		= $this->modelCart->get( 'userId' );
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
		if( $mode === 'account' )
			$mode	= Model_Shop_CART::CUSTOMER_MODE_ACCOUNT;
		else if( $mode === 'guest' )
			$mode	= Model_Shop_CART::CUSTOMER_MODE_GUEST;
		if( is_int( $mode ) && $mode > 0 ){
			$logicShop	= new Logic_Shop( $this->env );
			$this->modelCart->set( 'customerMode', (int) $mode );
			$this->restart( NULL, TRUE );
		}
		if( !$this->modelCart->get( 'positions' ) ){
			$this->messenger->noteNotice( $this->words->errorCustomerEmptyCart );
			$this->restart( 'shop/cart' );
		}
		if( $this->env->getModules()->has( 'Resource_Authentication' ) ){
			$logicAuth	= Logic_Authentication::getInstance( $this->env );
			if( $logicAuth->isAuthenticated() )
				$this->modelCart->set( 'customerMode', Model_Shop_CART::CUSTOMER_MODE_ACCOUNT );
			if( !$this->modelCart->get( 'customerMode' ) )
				$this->modelCart->set( 'customerMode', Model_Shop_CART::CUSTOMER_MODE_ACCOUNT );
		}
		$this->addData( 'cart', $this->modelCart );
		switch( $this->modelCart->get( 'customerMode' ) ){
			case Model_Shop_CART::CUSTOMER_MODE_ACCOUNT:
				$this->handleAccount();
				break;
			case Model_Shop_CART::CUSTOMER_MODE_GUEST:
			default:
				$this->handleGuest();
				break;
		}
	}

	protected function handleGuest(){
		$countries	= $this->env->getLanguage()->getWords( 'countries' );
		$this->addData( 'mode', Model_Shop_CART::CUSTOMER_MODE_GUEST );
		$this->addData( 'userId', 0 );

		$customerId		= $this->modelCart->get( 'customerId' );
		if( !$customerId ){
			$model		= new Model_Shop_Customer( $this->env );
			$customerId	= $model->add( array() );
			$this->modelCart->set( 'customerId', $customerId );
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

		if( !$addressBilling || !$addressDelivery ){
			$this->modelCart->set( 'orderStatus', Model_Shop_Order::STATUS_NEW );
		}

		if( $this->request->has( 'save' ) && $addressBilling && $addressDelivery ){
			$this->modelCart->set( 'orderStatus', Model_Shop_Order::STATUS_AUTHENTICATED );
			$this->modelCart->set( 'customerId', $customerId );
			$this->modelCart->set( 'userId', 0 );
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
					$this->modelCart->set( 'orderStatus', Model_Shop_Order::STATUS_AUTHENTICATED );
					$this->modelCart->set( 'userId', $userId );
					$this->modelCart->set( 'customerId', 0 );
					$this->restart( 'shop/conditions' );
				}
				if( !array_key_exists( $user->country, $countries ) )
					$user->country	= 'DE';
				$this->addData( 'countries', $countries );
				$this->addData( 'user', $user );
				$this->addData( 'addressBilling', $addressBilling );
				$this->addData( 'addressDelivery', $addressDelivery );
			}
		}
//		$this->addData( 'mode', Model_Shop_CART::CUSTOMER_MODE_ACCOUNT );
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
