<?php
class Controller_Manage_My_Mangopay_Transaction extends Controller_Manage_My_Mangopay{

    public function filter( $reset = NULL ){
		$sessionPrefix	= 'filter_manage_my_mangopay_transaction_';
		if( $reset ){
			$this->session->remove( $sessionPrefix.'nature' );
		}
        else{
            $this->session->set( $sessionPrefix.'nature', $this->request->get( 'nature' ) );
//            $this->session->set( $sessionPrefix.'limit', 10 );
        }
        $this->restart( NULL, TRUE );
	}

	public function index(){
        $cacheKey	= 'user_'.$this->userId.'_transactions';
        if( 1 || is_null( $transactions = $this->cache->get( $cacheKey ) ) ){
            $pagination	= $this->mangopay->getDefaultPagination();
            $sorting	= $this->mangopay->getDefaultSorting();
            $sorting->AddField( 'CreationDate', 'DESC' );

            $filter	= new \MangoPay\FilterTransactions();
            $filter->Nature = 'REGULAR';

            try{
                $transactions	= $this->mangopay->Users->GetTransactions( $this->userId, $pagination, $filter, $sorting );
                $this->cache->set( $cacheKey, $transactions );
            }
    		catch( \MangoPay\Libraries\ResponseException $e ){
    			$this->handleMangopayResponseException( $e );
                $this->restart( 'manage/my/mangopay' );
    		}
    		catch( Exception $e ){
    			$this->env->getMessenger()->noteFailure( 'Exception: '.$e->getMessage() );
                $this->restart( 'manage/my/mangopay' );
    		}
        }
        $this->addData( 'transactions', $transactions );
	}

	public function view( $transactionId ){
		$this->addData( 'backwardTo', $this->request->get( 'backwardTo' ) );
		$this->addData( 'forwardTo', $this->request->get( 'forwardTo' ) );

		$transaction			= $this->checkTransactionIsOwn( $transactionId );
		try{
            $this->addData( 'userId', $userId );
			$this->addData( 'transactionId', $transactionId );
			$this->addData( 'transaction', $transaction );
		}
		catch( \MangoPay\Libraries\ResponseException $e ){
			$this->handleMangopayResponseException( $e );
			$this->restart( NULL, TRUE );
		}
		catch( Exception $e ){
			$this->env->getMessenger()->noteError( 'Invalid User ID' );
			$this->restart( NULL, TRUE );
		}
	}
}
