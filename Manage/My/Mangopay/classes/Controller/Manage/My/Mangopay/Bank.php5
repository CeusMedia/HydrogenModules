<?php
class Controller_Manage_My_Mangopay_Bank extends Controller_Manage_My_Mangopay{

	public function add(){
		throw new RuntimeException( 'Not implemented yet' );
	}

	public function index(){
		$pagination	= new \MangoPay\Pagination();
		$sorting	= new \MangoPay\Sorting();
		$sorting->AddField( 'CreationDate', 'DESC' );
		$this->addData( 'bankAccounts', $this->mangopay->Users->GetBankAccounts( $this->userId, $pagination, $sorting ));
	}

	public function view( $bankAccountId ){
		try{
			$bankAccount	= $this->mangopay->Users->GetBankAccount( $this->userId, $bankAccountId );
			$this->addData( 'bankAccountId', $bankAccountId );
			$this->addData( 'userId', $userId );
			$this->addData( 'bankAccount', $bankAccount );

/*			$pagination	= $this->mangopay->getDefaultPagination();
			$sorting	= $this->mangopay->getDefaultSorting();
			$sorting->AddField( 'CreationDate', 'ASC' );
			$this->addData( 'transactions', $this->mangopay->Wallets->GetTransactions( $walletId, $pagination, $sorting ) );
*/

		}
		catch( Exception $e ){
			$this->env->getMessenger()->noteError( 'Invalid User ID' );
			$this->restart( NULL, TRUE );
		}

	}
}
