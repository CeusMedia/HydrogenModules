<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Alg\Text\CamelCase as TextCamelCase;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

class Controller_Shop_Customer extends Controller
{
	protected HttpRequest $request;
	protected MessengerResource $messenger;
	protected Dictionary $moduleConfig;
	protected object $words;

	/**	@var	Model_Shop_Payment_Register					$backends			List of available payment backends */
	protected Model_Shop_Payment_Register $backends;

	/**	@var	float					$cartTotal			Total price of cart */
	protected float $cartTotal			= .0;

	/**	@var	Model_Address			$modelAddress		Model for address objects*/
	protected Model_Address $modelAddress;

	/**	@var	Model_Shop_Cart			$modelCart			Model for shopping carts */
	protected Model_Shop_Cart $modelCart;

	/**	@var	Model_User				$modelUser			Model for user accounts */
	protected Model_User $modelUser;

	/**	@var	boolean					$useAuth			Flag: Shop allows user registration and login */
	protected bool $useAuth				= FALSE;

	/** @var	Logic_Authentication	$logicAuth			Instance of authentication logic, if available */
	protected Logic_Authentication $logicAuth;

	/**
	 *	...
	 *	@access		public
	 *	@param		string		$addressId		ID of address to edit
	 *	@param		integer		$type			...
	 *	@param		boolean		$remove			Flag: remove address and return
	 *	@return		void
	 */
	public function address( string $addressId, $type = NULL, bool $remove = NULL ): void
	{
		$type			= (int) $type;
		$customerMode	= $this->modelCart->get( 'customerMode' );
		$countries		= $this->env->getLanguage()->getWords( 'countries' );
		$relationType	= 'user';
		$relationId		= $this->modelCart->get( 'userId' );
		if( !$relationId )
			$this->restart( NULL, TRUE );

		if( $addressId && $remove ){
			$this->modelAddress->removeByIndices( [
				'addressId'		=> $addressId,
 				'relationId'	=> $relationId,
				'relationType'	=> $relationType,
				'type'			=> $type,
			] );
			$this->restart( NULL, TRUE );
		}
		$data		= $this->request->getAll( NULL, TRUE );
		$country	= 'DE';
		if( $data->get( 'country' ) )
			$country	= $data->get( 'country' );
		if( $this->request->has( 'save' ) ){
			if( $addressId > 0 ){
				$address	= $this->modelAddress->getByIndices( [
					'addressId'		=> $addressId,
	 				'relationId'	=> $relationId,
					'relationType'	=> $relationType
				] );
				if( !$address ){
				//	@todo: handle this situation!
				}
				$mismatchingType	= $relationType === $address->relationType;
				$mismatchingId		= $address->relationId == $relationId;
				if( !$mismatchingType || !$mismatchingId ){
					$this->messenger->noteError( 'Access to address denied.' );
					$this->restart( NULL, TRUE );
				}
//				$data->set( 'country', $countryKey );
				$data->set( 'modifiedAt', time() );
				$this->modelAddress->edit( $addressId, $data->getAll() );
			}
			else{
				if( !$type || !in_array( (int) $type, [Model_Address::TYPE_DELIVERY, Model_Address::TYPE_BILLING] ) ){
				//	@todo: handle this situation!
				}
				$mandatory	= [
					'firstname',
					'surname',
					'email',
					'country',
					'city',
					'postcode',
					'street',
				];
				$labels		= $this->getWords( 'customer' );
				foreach( $mandatory as $name ){
					if( !$data->get( $name ) ){
						$label	= TextCamelCase::convert( $name, FALSE );
						$this->messenger->noteError(
							$this->words->errorFieldEmpty,
							'billing_'.$name,
							$labels['labelBilling'.$label]
						);
					}
				}
				if( $this->messenger->gotError() )
					$this->restart( NULL, TRUE );
				$data->set( 'relationId', $relationId );
				$data->set( 'relationType', $relationType );
				$data->set( 'type', $type );
				$data->set( 'createdAt', time() );
				$data->set( 'country', $country );
				$addressId	= $this->modelAddress->add( $data->getAll() );
			}
			if( $customerMode === Model_Shop_CART::CUSTOMER_MODE_GUEST ){
				if( $type === Model_Address::TYPE_BILLING ){
					$address	= $this->modelAddress->get( $addressId );
					$this->modelUser->edit( $relationId, [
						'firstname'	=> $address->firstname,
						'surname'	=> $address->surname,
						'email'		=> $address->email,
						'country'	=> $address->country,
					] );
				}
			}
			$payload	= [
				'address'		=> $this->modelAddress->get( $addressId ),
				'relationId'	=> $relationId,
				'relationType'	=> $relationType,
			];
			$this->env->getCaptain()->callHook( 'Shop', 'updateAddress', $this, $payload );
			$this->restart( NULL, TRUE );
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

	/**
	 *	...
	 *	@access		public
	 *	@param		string		$mode		Optional: Customer mode to set (account|guest)
	 *	@return		void
	 */
	public function index( $mode = NULL )
	{
		if( $mode === 'account' && $this->useAuth )
			$mode	= Model_Shop_Cart::CUSTOMER_MODE_ACCOUNT;
		else if( $mode === 'guest' )
			$mode	= Model_Shop_Cart::CUSTOMER_MODE_GUEST;
		else if( $mode === 'reset' )
			$mode	= Model_Shop_Cart::CUSTOMER_MODE_UNKNOWN;
		if( is_int( $mode ) ){
			$logicShop	= new Logic_Shop( $this->env );
			$this->modelCart->set( 'customerMode', (int) $mode );
			$this->restart( NULL, TRUE );
		}
		if( !$this->modelCart->get( 'positions' ) ){
			$this->messenger->noteNotice( $this->words->errorCustomerEmptyCart );
			$this->restart( 'shop/cart' );
		}
		if( $this->useAuth ){
			if( $this->logicAuth->isAuthenticated() )
				$this->modelCart->set( 'customerMode', Model_Shop_Cart::CUSTOMER_MODE_ACCOUNT );
			if( !$this->modelCart->get( 'customerMode' ) )
				$this->modelCart->set( 'customerMode', Model_Shop_Cart::CUSTOMER_MODE_ACCOUNT );
		}
		$this->addData( 'cart', $this->modelCart );
		switch( $this->modelCart->get( 'customerMode' ) ){
			case Model_Shop_Cart::CUSTOMER_MODE_ACCOUNT:
				$this->handleAccount();
				break;
			case Model_Shop_Cart::CUSTOMER_MODE_GUEST:
			default:
				$this->handleGuest();
				break;
		}
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
		$this->modelUser	= new Model_User( $this->env );
		$this->modelAddress	= new Model_Address( $this->env );
		$this->modelCart	= new Model_Shop_Cart( $this->env );
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.shop.', TRUE );
		$this->words		= (object) $this->env->getLanguage()->getWords( 'shop' )['msg'];

		if( $this->env->getModules()->has( 'Resource_Authentication' ) ){
			$this->useAuth		= TRUE;
			$this->logicAuth	= Logic_Authentication::getInstance( $this->env );
		}

		$captain	= $this->env->getCaptain();
		$payload	= ['register' => new Model_Shop_Payment_Register( $this->env )];
		$captain->callHook( 'ShopPayment', 'registerPaymentBackend', $this, $payload );
		$this->backends	= $payload['register'];
		$this->addData( 'paymentBackends', $payload['register'] );

		if( $this->modelCart->get( 'positions' ) ){
			foreach( $this->modelCart->get( 'positions' ) as $position ){
				$this->cartTotal	+= $position->article->price->all;
			}
		}
		// @todo  implement shipping and options (insurance, handling etc)

		$this->addData( 'cartTotal', $this->cartTotal );
	}

	/**
	 *	Handle customer having a user account.
	 *	@access		protected
	 *	@return		void
	 */
	protected function handleAccount()
	{
		$countries	= $this->env->getLanguage()->getWords( 'countries' );
		$userId		= 0;
		if( $this->logicAuth->isAuthenticated() ){
			$userId	= $this->logicAuth->getCurrentUserId();
			if( $userId ){
				if( !$this->modelCart->get( 'userId' ) )
					$this->modelCart->set( 'userId', $userId );
				$user		= $this->modelUser->get( $userId );
				$addressDelivery	= $this->modelAddress->getByIndices( [
					'relationType'	=> 'user',
					'relationId'	=> $userId,
					'type'			=> Model_Address::TYPE_DELIVERY,
				] );
				$addressBilling		= $this->modelAddress->getByIndices( [
					'relationType'	=> 'user',
					'relationId'	=> $userId,
					'type'			=> Model_Address::TYPE_BILLING,
				] );
				if( $this->request->has( 'save' ) && $addressDelivery && $addressBilling ){
					$this->modelCart->set( 'orderStatus', Model_Shop_Order::STATUS_AUTHENTICATED );
					$this->modelCart->set( 'userId', $userId );
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

	/**
	 *	Handle customer having a guest account.
	 *	@access		protected
	 *	@return		void
	 */
	protected function handleGuest()
	{
		$countries	= $this->env->getLanguage()->getWords( 'countries' );
		$this->addData( 'mode', Model_Shop_Cart::CUSTOMER_MODE_GUEST );
		$this->addData( 'userId', 0 );

		$userId		= $this->modelCart->get( 'userId' );
		if( !$userId ){
			$userId		= $this->modelUser->add( [
				'username'		=> 'Guest User '.uniqid(),
				'password'		=> '-1',
				'status'		=> Model_User::STATUS_UNCONFIRMED,
				'roleId'		=> $this->moduleConfig->get( 'customerRoleId' ),
				'createdAt'		=> time(),
				'modifiedAt'	=> time(),
			] );
			$logicAuth	= $this->env->getLogic()->get( 'Authentication' );
			$logicAuth->setIdentifiedUser( $this->modelUser->get( $userId ) );
			$this->modelCart->set( 'userId', $userId );
		}
		$addressDelivery	= $this->modelAddress->getByIndices( [
			'relationType'	=> 'user',
			'relationId'	=> $userId,
			'type'			=> Model_Address::TYPE_DELIVERY,
		] );
		$addressBilling		= $this->modelAddress->getByIndices( [
			'relationType'	=> 'user',
			'relationId'	=> $userId,
			'type'			=> Model_Address::TYPE_BILLING,
		] );
		$this->addData( 'addressBilling', $addressBilling );
		$this->addData( 'addressDelivery', $addressDelivery );

		if( !$addressBilling || !$addressDelivery ){
			$this->modelCart->set( 'orderStatus', Model_Shop_Order::STATUS_NEW );
		}

		if( $this->request->has( 'save' ) && $addressBilling && $addressDelivery ){
			$this->modelCart->set( 'orderStatus', Model_Shop_Order::STATUS_AUTHENTICATED );
			$this->modelCart->set( 'userId', $userId );
			$this->restart( 'shop/conditions' );
		}

		$user	= (object) [
			'firstname'	=> NULL,
			'surname'	=> NULL,
			'email'		=> NULL,
			'street'	=> NULL,
			'city'		=> NULL,
			'postcode'	=> NULL,
			'country'	=> NULL,
			'region'	=> NULL,
			'phone'		=> NULL,
		];
		if( $addressBilling && !$addressDelivery ){
			$user	= $addressBilling;
		}
		$this->addData( 'user', $user );
	}
}
