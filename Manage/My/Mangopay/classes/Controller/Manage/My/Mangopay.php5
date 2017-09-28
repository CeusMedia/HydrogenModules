<?php
class Controller_Manage_My_Mangopay extends CMF_Hydrogen_Controller{

	protected $request;
	protected $mangopay;
	protected $messenger;
	protected $session;

	protected $currency		= "EUR";
	protected $factorFees	= 0.1;

	protected function __onInit(){
		parent::__onInit();
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
		$this->cache		= $this->env->getCache();
		$this->logic		= Logic_Payment_Mangopay::getInstance( $this->env );
		$this->mangopay		= Resource_Mangopay::getInstance( $this->env );
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.resource_mangopay.', TRUE );

		$modelAccount		= new Model_User_Payment_Account( $this->env );
		$localUserId		= $this->session->get( 'userId' );

		try{
			if( !$this->logic->hasPaymentAccount( $localUserId ) )
				$this->logic->createNaturalUserFromLocalUser( $localUserId );
			$this->userId	= $this->logic->getUserIdFromLocalUserId( $localUserId );
		}
		catch( RuntimeException $e ){
			$this->messenger->noteFailure( 'Registration on payment provider failed: '.$e->getMessage() );
		}
	}

	protected function checkIsOwnCard( $cardId, $strict = TRUE, $fallback = array() ){
		if( !is_array( $fallback ) || !count( $fallback ) )
			$fallback	= array( NULL, TRUE );
		$card	= $this->checkCard( $cardId, $fallback );
	//	@todo check card against user cards
		return $card;
	}

	protected function checkCard( $cardId, $fallback = array() ){
		try{
			if( !strlen( trim( $cardId ) ) )
				throw new InvalidArgumentException( 'No card ID given' );
			$card	= $this->mangopay->Cards->Get( $cardId );
			return $card;
		}
		catch( \MangoPay\Libraries\ResponseException $e ){
			$this->handleMangopayResponseException( $e );
		}
		catch( Exception $e ){
//			$this->messenger->noteNotice( "Exception: ".$e->getMessage( ) );
			$this->messenger->noteError( "Invalid card ID given." );
		}
		if( !is_array( $fallback ) || !count( $fallback ) )
			$fallback	= array( NULL, TRUE );
		if( count( $fallback ) == 1 )
			$fallback[1]	= FALSE;
		$this->restart( $fallback[0], $fallback[1] );
	}

	protected function checkWallet( $walletId ){
		try{
			$wallet	= $this->mangopay->Wallets->Get( $walletId );
			return $wallet;
		}
		catch( Exception $e ){
//			$this->messenger->noteNotice( "Exception: ".$e->getMessage( ) );
			$this->messenger->noteError( "Invalid wallet ID given." );
			$this->restart( NULL, TRUE );
		}
	}

	protected function checkWalletIsOwn( $walletId ){
		$wallet		= $this->checkWallet( $walletId );
		//	@todo check against list of user wallets
		return $wallet;
	}

	protected function handleMangopayResponseException( $e ){
		ob_start();
		print_r( $e->GetErrorDetails()->Errors );
		$details	= ob_get_clean();
		$message	= 'Response Exception "%s" (%s)<br/><small>%s</small>';
		$this->messenger->noteFailure( $message, $e->getMessage(), $e->getCode(), $details );
	}

	public function index(){

		try{
			$cacheKey	= 'user_'.$this->userId.'_bankaccounts';
			if( is_null( $bankAccounts = $this->cache->get( $cacheKey ) ) ){
				$pagination	= $this->mangopay->getDefaultPagination();
				$sorting	= $this->mangopay->getDefaultSorting();
				$sorting->AddField( 'CreationDate', 'ASC' );
				$bankAccounts	= $this->mangopay->Users->GetBankAccounts( $this->userId, $pagination, $sorting );
				$this->cache->set( $cacheKey, $bankAccounts );
			}
			$this->addData( 'bankAccounts', $bankAccounts );

			$cacheKey	= 'user_'.$this->userId.'_cards';
			if( is_null( $cards = $this->cache->get( $cacheKey ) ) ){
				$pagination	= $this->mangopay->getDefaultPagination();
				$sorting	= $this->mangopay->getDefaultSorting();
				$cards	= $this->mangopay->Users->GetCards( $this->userId, $pagination, $sorting );
				$this->cache->set( $cacheKey, $cards );
			}
			$this->addData( 'cards', $cards );

			$cacheKey	= 'user_'.$this->userId.'_wallets';
			if( is_null( $wallets = $this->cache->get( $cacheKey ) ) ){
				$pagination	= $this->mangopay->getDefaultPagination();
				$sorting	= $this->mangopay->getDefaultSorting();
				$sorting->AddField( 'CreationDate', 'ASC' );
				$wallets	= $this->mangopay->Users->GetWallets( $this->userId, $pagination, $sorting );
				$this->cache->set( $cacheKey, $wallets );
			}
			$this->addData( 'wallets', $wallets );

			$cacheKey	= 'user_'.$this->userId.'_transactions';
			if( 1 || is_null( $transactions = $this->cache->get( $cacheKey ) ) ){
				$pagination	= $this->mangopay->getDefaultPagination();
				$sorting	= $this->mangopay->getDefaultSorting();
				$sorting->AddField( 'CreationDate', 'DESC' );
				$transactions	= $this->mangopay->Users->GetTransactions( $this->userId, $pagination, $sorting );
				$this->cache->set( $cacheKey, $transactions );
			}
			$this->addData( 'transactions', $transactions );
		}
		catch( \MangoPay\ResponseException $e ){
			$this->handleMangopayResponseException( $e );
			$this->restart( NULL );
		}
		catch( Exception $e ){
			$this->messenger->noteError( "Exception: ".$e->getMessage() );
			$this->restart( NULL );
		}
	}
}
?>
