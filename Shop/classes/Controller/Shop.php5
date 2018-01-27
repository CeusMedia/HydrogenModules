<?php
/**
 *	@todo	complete flow implementation, currently stoppted at method "pay"
 */
class Controller_Shop extends CMF_Hydrogen_Controller{

	/**	@var	Logic_ShopBridge		$brige */
	protected $bridge;
	/**	@var	Logic_Shop				$logic */
	protected $logic;
	/**	@var	ADT_List_Dictionary		$options */
	protected $options;

	protected $backends			= array();
	protected $servicePanels	= array();

	public function __onInit() {
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
		$this->logic		= new Logic_Shop( $this->env );
		$this->words		= (object) $this->getWords( 'msg' );
		$this->bridge		= new Logic_ShopBridge( $this->env );
		$this->options		= $this->env->getConfig()->getAll( 'module.shop.', TRUE );
		$this->orderId		= $this->session->get( 'shop.orderId' );

		if( !$this->session->get( 'shop.order' ) ){
			$this->session->set( 'shop.order', (object) array(
				'status'		=> 0,
				'rules'			=> FALSE,
				'paymentMethod'	=> NULL,
				'paymentId'		=> NULL,
				'currency'		=> 'EUR',
			) );
			$this->session->set( 'shop.order.customer', array() );
			$this->session->set( 'shop.order.billing', array() );
			$this->session->set( 'shop.order.positions', array() );
		}
		$this->addData( 'options', $this->options );
		$captain	= $this->env->getCaptain();
		$captain->callHook( 'ShopPayment', 'registerPaymentBackend', $this, array() );
		$this->addData( 'paymentBackends', $this->backends );
//		$this->orderId	= 41;
		$this->addData( 'orderId', (int) $this->orderId );
		if( (int) $this->orderId > 0 ){
			$this->addData( 'order', $this->logic->getOrder( $this->orderId ) );
		}
	}

	public function addArticle( $bridgeId, $articleId, $quantity = 1 ){
		$bridgeId		= (int) $bridgeId;
		$articleId		= (int) $articleId;
		$quantity		= abs( $quantity );
		$forwardUrl		= $this->request->get( 'forwardTo' );
		$positions		= $this->session->get( 'shop.order.positions' );
		if( array_key_exists( $articleId, $positions ) && $positions[$articleId]->quantity ){
			foreach( $positions as $nr => $position ){
				if( $position->bridgeId == $bridgeId && $position->articleId == $articleId ){
					$param	= '?forwardTo='.urlencode( $forwardUrl );
					$url	= 'changePositionQuantity/'.$bridgeId.'/'.$articleId.'/'.$quantity;
					$this->restart( $url.$param, TRUE );
				}
			}
		}

		$positions[$articleId]	= (object) array(
			'bridgeId'	=> $bridgeId,
			'articleId'	=> $articleId,
			'quantity'	=> $quantity,
		);
		$this->session->set( 'shop.order.positions', $positions );
		$order		= $this->session->get( 'shop.order' );
		if( $order->status == Model_Shop_Order::STATUS_NEW ){
			$order->status = Model_Shop_Order::STATUS_AUTHENTICATED;
			$this->session->set( 'shop.order', $order );
		}
		$title		= $this->bridge->getArticleTitle( $bridgeId, $articleId );
		$this->messenger->noteSuccess( $this->words->successAddedToCart, $title, $quantity );
		$this->restart( $forwardUrl ? $forwardUrl : 'shop/cart' );
	}

	public function address( $addressId, $type = NULL, $remove = NULL ){
		$userId		= $this->session->get( 'userId' );
		$from		= $this->request->get( 'from' );
		if( !$userId )
			$this->restart( 'customer', TRUE );

		$model		= new Model_Address( $this->env );
		$labels		= $this->getWords( 'customer' );
		$countries	= $this->env->getLanguage()->getWords( 'countries' );

		$mandatory	= array(
			'firstname',
			'surname',
			'email',
			'country',
			'city',
			'postcode',
			'street',
		);
		if( $addressId && $remove ){
			$model->removeByIndices( array(
				'addressId'		=> $addressId,
 				'relationId'	=> $userId,
				'relationType'	=> 'user',
				'type'			=> $type,
			) );
			$this->restart( 'customer', TRUE );
		}
		if( $this->request->has( 'save' ) ){
			if( $addressId > 0 ){
				$address	= $model->getByIndices( array(
					'addressId'		=> $addressId,
	 				'relationId'	=> $userId,
					'relationType'	=> 'user'
				) );
				if( !$address ){
				//	@todo: handle this situation!
				}
				$data	= $this->request->getAll( NULL, TRUE );
				$data->set( 'country', array_search( $data->get( 'country' ), $countries ) );
				$data->set( 'modifiedAt', time() );
				$model->edit( $addressId, $data->getAll() );
				$this->restart( 'customer', TRUE );
			}
			else{
				if( !$type || !in_array( (int) $type, array( Model_Address::TYPE_DELIVERY, Model_Address::TYPE_BILLING ) ) ){
				//	@todo: handle this situation!
				}
				$data	= $this->request->getAll( NULL, TRUE );
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
					$data->set( 'relationId', $userId );
					$data->set( 'relationType', 'user' );
					$data->set( 'type', $type );
					$data->set( 'createdAt', time() );
					$data->set( 'country', array_search( $data->get( 'country' ), $countries ) );
//					print_m( $data->getAll() );die;
					$addressId	= $model->add( $data->getAll() );
				}
				$this->restart( 'customer', TRUE );
			}
		}
		if( !$addressId ){
			$this->messenger->noteError( 'No address ID given.' );
			$this->restart( 'customer', TRUE );
		}
		$address	= $model->get( $addressId );
		if( !$address ){
			$this->messenger->noteError( 'Invalid address ID given.' );
			$this->restart( 'customer', TRUE );
		}
		if( !( $address->relationType === 'user' && $address->relationId == $userId ) ){
			$this->messenger->noteError( 'Access to address denied.' );
			$this->restart( 'customer', TRUE );
		}
		$this->addData( 'address', $address );
	}

	protected function calculateCharges(){
		if( !$this->env->getModules()->has( 'Shop_Shipping' ) )
			return 0;
		$customer	= $this->session->get( 'shop.order.customer', TRUE );

		$charges	= 0;
//		if( $customer->get( 'country' ) )
/*		$grade		= new Model_Shop_Shipping_Grade();
		$grade		= $grade->getGradeID( $total['weight'] );
		$zone		= new Model_Shop_Shipping_Country();
		$zone		= $zone->getZoneID( $udata['country'] );
		$price		= new Model_Shop_Shipping_Price();
		$charges	= $price->getPrice( $zone, $grade );
*/		$option		= new Model_Shop_Shipping_Option( $this->env );
		$options	= $option->getAll();
		if( count( $options ) ){
			$set_options	= explode( "|", $order['options'] );
			foreach( $options as $option )
				if( in_array( $option['shippingoption_id'], $set_options ) )
					$charges	+= (float)$option['price'];
		}
		return $charges;
	}

	public function cart(){
		$this->addData( 'order', $this->session->get( 'shop.order' ) );
		$this->addData( 'customer', $this->session->get( 'shop.order.customer' ) );
		$this->addData( 'billing', $this->session->get( 'shop.order.billing' ) );
		$positions	= $this->session->get( 'shop.order.positions' );
		foreach( $positions as $nr => $position ){
			$source		= $this->bridge->getBridgeObject( (int)$position->bridgeId );
			$article	= $source->get( $position->articleId, $position->quantity );
			$positions[$nr]->article	= $article;
		}
		$this->addData( 'positions', $positions );
	}

	public function changePositionQuantity( $bridgeId, $articleId, $quantity, $operation = NULL ){
		$bridgeId		= (int) $bridgeId;
		$articleId		= (int) $articleId;
		$quantity		= abs( $quantity );
		$forwardUrl		= $this->request->get( 'forwardTo' );
		$positions		= $this->session->get( 'shop.order.positions' );
		foreach( $positions as $nr => $position ){
			if( $position->bridgeId == $bridgeId && $position->articleId == $articleId ){
				switch( $operation ){
					case 'plus':
						$position->quantity	+= (int)$quantity;
						break;
					case 'minus':
						$position->quantity	-= (int)$quantity;
						break;
					default:
						$position->quantity	= (int)$quantity;
				}
				$positions[$nr]	= $position;
				$title			= $this->bridge->getArticleTitle( $bridgeId, $articleId );
				$link			= $this->bridge->getArticleLink( $bridgeId, $articleId );
				if( !$position->quantity ){
					unset( $positions[$nr] );
					$this->messenger->noteSuccess( $this->words->successRemovedFromCart, $title );
				}
				else{
					$this->messenger->noteSuccess( $this->words->successChangedQuantity, $title, $position->quantity );
				}
				$this->session->set( 'shop.order.positions', $positions );
				$this->restart( $forwardUrl ? $forwardUrl : 'shop/cart' );
			}
		}
		$this->restart( 'cart', TRUE );
	}

	public function checkout(){
		if( $this->request->has( 'save' ) && $this->request->isMethod( 'POST' ) ){
			$orderId	= $this->session->get( 'shop.orderId' );
			$orderId	= $this->logic->storeCartFromSession( $orderId );
			$this->session->set( 'shop.orderId', $orderId );
			$price      = $this->logic->calculateOrderTotalPrice( $orderId );
			$order		= $this->logic->getOrder( $orderId );

			if( $price && $this->backends ){
				if( count( $this->backends ) === 1 )
					$order->paymentMethod	= $this->backends[0]->key;
				if( $order->paymentMethod ){
					foreach( $this->backends as $backend ){
						if( $backend->key === $order->paymentMethod ){
//							$this->logic->setOrderPaymentMethod( $orderId, $backend->key );
							$this->restart( 'payment/'.$backend->path, TRUE );
						}
					}
				}
			}
			else{
				$this->logic->setOrderStatus( $orderId, Model_Shop_Order::STATUS_PAYED );
				$this->restart( 'finish', TRUE );
			}
		}

//		print_m( $this->session->getAll() );die;
/*		if( ( $orderId = $this->session->get( 'shop.orderId' ) ) ){
			$order		= $this->logic->getOrder( $orderId );
			$customer	= $this->logic->getCustomer( $order->userId );
			$language	= $this->env->getLanguage()->getLanguage();
			$logic		= new Logic_Mail( $this->env );
			$mail		= new Mail_Shop_Manager_Ordered( $this->env, array(
				'orderId'			=> $orderId,
				'paymentBackends'	=> $this->backends,
			) );
			print( $mail->contents['html'] );die;
			$logic->appendRegisteredAttachments( $mail, $language );
			$logic->handleMail( $mail, $customer, $language );
			die;
		}*/
/*		if( ( $orderId = $this->session->get( 'shop.orderId' ) ) ){
			$order		= $this->logic->getOrder( $orderId );
			$customer	= $this->logic->getCustomer( $order->userId );
			$language	= $this->env->getLanguage()->getLanguage();
			$logic		= new Logic_Mail( $this->env );
			$mail		= new Mail_Shop_Customer_Ordered( $this->env, array(
				'orderId'			=> $orderId,
				'paymentBackends'	=> $this->backends,
			) );
			print( $mail->contents['html'] );die;
			$logic->appendRegisteredAttachments( $mail, $language );
			$logic->handleMail( $mail, $customer, $language );
			die;
		}*/

		$order		= $this->session->get( 'shop.order' );
		$customerId	= $this->session->get( 'shop.order.customer' );
		$positions	= $this->session->get( 'shop.order.positions' );
		$billing	= $this->session->get( 'shop.order.billing' );
		if( !$positions ){
			$this->messenger->noteNotice( $this->words->errorCheckoutEmptyCart );
			$this->restart( 'cart', TRUE );
		}
		$this->addData( 'order', $order );
		$this->addData( 'positions', $positions );
		$this->addData( 'customer', $this->logic->getCustomer( $customerId ) );
		$this->addData( 'billing', $billing );
	}

	public function conditions(){
		$order		= $this->session->get( 'shop.order' );
		$positions	= $this->session->get( 'shop.order.positions' );
		$customer	= $this->session->get( 'shop.order.customer' );

		if( !$positions )
			$this->restart( 'cart', TRUE );

		if( !$this->session->get( 'shop.order.customer' ) )
			$this->restart( 'customer', TRUE );

		if( $this->request->has( 'saveConditions' ) ){
			if( !$this->request->get( 'accept_rules' ) ){
				$this->messenger->noteError( $this->words->errorRulesNotAccepted );
				$order->status	= -1;
				$this->session->set( 'shop.order', $order );
			}
			else{
//				$this->messenger->noteSuccess( $this->words->successRulesAccepted );
				$order->status	= 1;
				$order->rules	= TRUE;
				$this->session->set( 'shop.order', $order );
				$this->restart( 'payment', TRUE );
			}
		}

		$this->addData( 'charges', $this->calculateCharges() );
		$this->addData( 'order', $order );
	}

	public function customer(){
		$order		= $this->session->get( 'shop.order' );
		$positions	= $this->session->get( 'shop.order.positions' );
		$customer	= $this->session->get( 'shop.order.customer' );
		$billing	= $this->session->get( 'shop.order.billing' );

		if( !$positions ){
			$this->messenger->noteNotice( $this->words->errorCustomerEmptyCart );
			$this->restart( 'cart', TRUE );
		}

		$userId	= 0;
		if( $this->env->getModules()->has( 'Resource_Authentication' ) ){
			if( $this->session->has( 'userId' ) ){
				$logicAuth	= Logic_Authentication::getInstance( $this->env );
				$countries	= $this->env->getLanguage()->getWords( 'countries' );
				$userId		= $logicAuth->getCurrentUserId( FALSE );
				if( $userId ){
					if( $this->request->has( 'save' ) ){
						$this->session->set( 'shop.order.customer', $userId );
						$this->restart( 'conditions', TRUE );
					}

					$modelUser	= new Model_User( $this->env );
					$user		= $modelUser->get( $userId );
					if( $user ){
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
					}
					$model	= new Model_Address( $this->env );
					$indices	= array(
						'relationType'	=> 'user',
						'relationId'	=> $userId,
						'type'			=> Model_Address::TYPE_DELIVERY,
					);
					$addressDelivery	= $model->getByIndices( array(
						'relationType'	=> 'user',
						'relationId'	=> $userId,
						'type'			=> Model_Address::TYPE_DELIVERY,
					) );
					$addressBilling		= $model->getByIndices( array(
						'relationType'	=> 'user',
						'relationId'	=> $userId,
						'type'			=> Model_Address::TYPE_BILLING,
					) );
					$this->addData( 'customer', $customer );
					$this->addData( 'countries', $countries );
					$this->addData( 'user', $user );
					$this->addData( 'addressBilling', $addressBilling );
					$this->addData( 'addressDelivery', $addressDelivery );
				}
			}
		}
		$this->addData( 'userId', $userId );
		$this->addData( 'email', $this->request->get( 'email' ) );
	}

	public function finish(){
		$orderId	= $this->session->get( 'shop.orderId' );
		if( !$orderId ){
			$this->env->getMessenger()->noteError( $this->words->errorFinishEmptyCart );
			$this->restart( 'cart', TRUE );
		}

		$order	= $this->logic->getOrder( $orderId );
		if( $order->status < Model_Shop_Order::STATUS_ORDERED )
			$this->logic->setOrderStatus( $orderId, Model_Shop_Order::STATUS_ORDERED );
		$this->sentOrderMailCustomer( $orderId );
		$this->sentOrderMailManager( $orderId );
		$this->session->set( 'shop.lastOrderId', $orderId );
		$this->session->remove( 'shop.order' );
		$this->session->remove( 'shop.order.customer' );
		$this->session->remove( 'shop.order.billing' );
		$this->session->remove( 'shop.order.positions' );
		$this->session->remove( 'shop.orderId' );
		$this->env->getMessenger()->noteSuccess( $this->words->successFinished );
		$order	= $this->logic->getOrder( $orderId );
		$this->env->getModules()->callHook( 'Shop', 'onFinish', $this, array(
			'orderId'	=> $orderId,
			'order'		=> $order,
		) );
		$this->restart( 'service', TRUE );
	}

	public function index(){
		$this->redirect( 'shop', 'cart' );
	}

	public function login(){
		$logic		= new Logic_Authentication( $this->env );
		$modelUser	= new Model_User( $this->env );
		$email		= $this->request->get( 'email' );
		$password	= $this->request->get( 'password' );
		$user		= $modelUser->getByIndices( array(
			'roleId'	=> 4,
			'status'	=> 1,
			'email'		=> $email,
		) );						//  find user by email address
		if( !$user ){
			$this->messenger->noteError( 'Kein gültiges Benutzerkonto für diese E-Mail-Adresse gefunden.' );
			$this->restart( 'customer', TRUE );
		}
		if( !$logic->checkPassword( $user->userId, $password ) ){
			$this->messenger->noteError( 'Das Passwort ist ungültig.' );
			$this->restart( 'customer?email='.$email, TRUE );
		}
		$this->session->set( 'userId', $user->userId );
		$this->session->set( 'roleId', $user->roleId );
		$this->session->set( 'authBackend', 'Local' );
		$this->restart( 'customer', TRUE );
	}

	public function payment(){
		if( count( $this->backends ) === 1 ){
			$paymentBackend		= array_pop( $this->backends );
			$this->restart( 'setPaymentBackend/'.$paymentBackend->key, TRUE );
		}
	}

	public function setPaymentBackend( $paymentBackendKey = NULL ){
		if( $paymentBackendKey ){
			$orderId	= $this->session->get( 'shop.orderId' );
			$this->logic->setOrderPaymentMethod( $orderId, $paymentBackendKey );
			$this->restart( 'checkout', TRUE );
		}
	}

	public function register(){
		if( $this->request->has( 'save' ) ){
			$customer	= $this->request->getAll( 'customer_', TRUE );
			$labels		= $this->getWords( 'customer' );
			$mandatory	= array(
				'firstname',
				'lastname',
				'email',
				'country',
				'city',
				'postcode',
				'address',
			);
			foreach( $mandatory as $name ){
				if( !$customer->get( $name ) ){
					$label	= Alg_Text_CamelCase::convert( $name, FALSE );
					$this->messenger->noteError(
						$this->words->errorFieldEmpty,
						'customer_'.$name,
						$labels['labelCustomer'.$label]
					);
				}
			}
			if( !$this->messenger->gotError() )
				$this->restart( 'conditions', TRUE );

			$model	= new Model_User( $this->env );
			$model->add( $customer );
			$this->restart( 'address', TRUE );
		}
		$model		= new Model_User( $this->env );
		$customer	= (object) array();
		foreach( $model->getColumns() as $column )
			$customer->$column	= $this->request->get( $column );

		$model		= new Model_Address( $this->env );
		$address	= (object) array();
		foreach( $model->getColumns() as $column )
			$address->$column	= $this->request->get( $column );

		$address->alternative	= $this->request->get( 'billing_alternative' );

		$this->addData( 'customer', $customer );
		$this->addData( 'address', $address );
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

	public function registerServicePanel( $key, $content, $priority ){
		$this->servicePanels[$key]	= (object) array(
			'key'		=> $key,
			'content'	=> $content,
			'priority'	=> $priority,
		);
	}

	public function removeArticle( $articleId ){
		$forwardUrl		= $this->request->get( 'forwardTo' );
		$positions		= $this->session->get( 'shop.order.positions' );
		foreach( $positions as $nr => $position )
			if( $position->articleId == $articleId )
				unset( $positions[$nr] );
		$this->session->set( 'shop.order.positions', $positions );
		$this->restart( $forwardUrl ? $forwardUrl : 'shop/cart' );
	}

	public function rules(){
	}

	protected function sentOrderMailCustomer( $orderId ){
		$order		= $this->logic->getOrder( $orderId );
		$customer	= $this->logic->getCustomer( $order->userId );
		$language	= $this->env->getLanguage()->getLanguage();
		$language   = !empty( $customer->language ) ? $customer->language : $language;

		$logic		= new Logic_Mail( $this->env );
		$mail		= new Mail_Shop_Customer_Ordered( $this->env, array(
			'orderId'			=> $orderId,
			'paymentBackends'	=> $this->backends,
		) );
		$logic->appendRegisteredAttachments( $mail, $language );
		$logic->handleMail( $mail, $customer, $language );
	}

	protected function sentOrderMailManager( $orderId ){
		$language	= $this->env->getLanguage()->getLanguage();
		$email		= $this->env->getConfig()->get( 'module.shop.mail.manager' );
		$logic		= new Logic_Mail( $this->env );
		$mail		= new Mail_Shop_Manager_Ordered( $this->env, array( 'orderId' => $orderId ) );
		$logic->appendRegisteredAttachments( $mail, $language );
		$logic->handleMail( $mail, (object) array( 'email' => $email ), $language );
	}

	public function service(){
		$orderId	= $this->session->get( 'shop.lastOrderId' );
		if( !$orderId )
			$this->restart( 'cart', TRUE );
		$this->addData( 'orderId', $orderId );
		$this->addData( 'order', $this->logic->getOrder( $orderId, TRUE ) );

		$arguments	= array( 'orderId' => $orderId, 'paymentBackends' => $this->backends );
		$this->env->getModules()->callHook( 'Shop', 'renderServicePanels', $this, $arguments );
		$this->addData( 'servicePanels', $this->servicePanels );

		$arguments	= array( 'orderId' => $orderId );
		$this->addData( 'delivery', NULL );
		$this->env->getModules()->callHook( 'Shop', 'onPaymentSuccess', $this, $arguments );
	}

	protected function submitOrder(){
//		$this->acceptRules();
		$this->saveConditions();
		$this->closeOrder();
		$this->restart( 'cart', TRUE );
	}
}
?>
