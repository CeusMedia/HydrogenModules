<?php
class Controller_Manage_My_Mangopay_Bank extends Controller_Manage_My_Mangopay{

	protected $words;

	public function __onInit(){
		parent::__onInit();
		$this->words			= $this->getWords( 'add', 'manage/my/mangopay/card' );
		$this->sessionPrefix	= 'manage_my_mangopay_bank_';
	}

	public function add(){
		if( $this->request->has( 'save' ) ){
			throw new RuntimeException( 'Not implemented yet' );
		}
		$this->addData( 'backwardTo', $this->request->get( 'backwardTo' ) );
		$this->addData( 'forwardTo', $this->request->get( 'forwardTo' ) );
	}

	public function payIn( $bankAccountId ){
		if( $this->request->has( 'save' ) ){
//			$this->mangopay->Users->
			throw new RuntimeException( 'Not implemented yet' );
		}
		$bankAccount	= $this->mangopay->Users->GetBankAccount( $this->userId, $bankAccountId );
		$wallets		= $this->logic->getUserWallets( $this->userId );
		$this->addData( 'wallets', $wallets );
		$this->addData( 'bankAccountId', $bankAccountId );
		$this->addData( 'bankAccount', $bankAccount );
		$this->addData( 'from', $this->request->get( 'from' ) );
	}

	public function payOut( $bankAccountId ){
		if( $this->request->has( 'save' ) ){
			throw new RuntimeException( 'Not implemented yet' );
		}
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
			$this->addData( 'bankAccount', $bankAccount );
			$this->addData( 'backwardTo', $this->request->get( 'backwardTo' ) );
			$this->addData( 'forwardTo', $this->request->get( 'forwardTo' ) );

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
