<?php
class Controller_Manage_My_Mangopay_User extends Controller_Manage_My_Mangopay{

	public function index(){

		$this->addData( 'user', $this->mangopay->Users->Get( $this->userId ) );
/*
		$pagination	= new \MangoPay\Pagination();
		$sorting	= new \MangoPay\Sorting();
		$sorting->AddField( 'CreationDate', 'DESC' );
		$this->addData( 'users', $this->mangopay->Users->GetAll( $pagination, $sorting ));*/
	}

	public function view( $userId ){
		try{
			$user	= $this->mangopay->Users->Get( $userId );
			$this->addData( 'userId', $userId );
			$this->addData( 'user', $user );

			$pagination	= $this->mangopay->getDefaultPagination();
			$sorting	= $this->mangopay->getDefaultSorting();
			$sorting->AddField( 'CreationDate', 'ASC' );
			$this->addData( 'bankAccounts', $this->mangopay->Users->GetBankAccounts( $userId, $pagination, $sorting ) );

			$pagination	= $this->mangopay->getDefaultPagination();
			$sorting	= $this->mangopay->getDefaultSorting();
			$sorting->AddField( 'CreationDate', 'ASC' );
			$this->addData( 'transactions', $this->mangopay->Users->GetTransactions( $userId, $pagination, $sorting ) );

			$pagination	= $this->mangopay->getDefaultPagination();
			$sorting	= $this->mangopay->getDefaultSorting();
			$sorting->AddField( 'CreationDate', 'ASC' );
			$this->addData( 'wallets', $this->mangopay->Users->GetWallets( $userId, $pagination, $sorting ) );

		}
		catch( Exception $e ){
			$this->env->getMessenger()->noteError( 'Invalid User ID' );
			$this->restart( NULL, TRUE );
		}

	}
}
