<?php
class Controller_Manage_My_Mangopay extends Controller_Manage_My_Mangopay_Abstract{

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
