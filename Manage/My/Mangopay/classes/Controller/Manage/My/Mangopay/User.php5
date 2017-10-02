<?php
class Controller_Manage_My_Mangopay_User extends Controller_Manage_My_Mangopay_Abstract{

	protected $user;

	public function __onInit(){
		parent::__onInit();
		$this->user	= $this->logic->getUser( $this->userId );
		$this->addData( 'user', $this->user );
	}

	public function edit(){
		$user	= $this->user;
		if( $this->request->has( 'save' ) ){
//			print_m( $this->request->getAll() );die;
			$birthday	= $this->request->get( 'birthday' );
			$user->Address->Country			= $this->request->get( 'country' );
			$user->Address->Region			= $this->request->get( 'region' );
			$user->Address->PostalCode		= $this->request->get( 'postalCode' );
			$user->Address->City			= $this->request->get( 'city' );
			$user->Address->AddressLine1	= $this->request->get( 'addressLine1' );
			$user->Address->AddressLine2	= $this->request->get( 'addressLine2' );
			$user->Birthday					= strtotime( $birthday );

			$this->logic->updateUser( $user );
			$this->restart( NULL, TRUE );
		}
	}

	public function index(){
		try{
			$pagination	= $this->mangopay->getDefaultPagination();
			$sorting	= $this->mangopay->getDefaultSorting();
			$sorting->AddField( 'CreationDate', 'ASC' );
			$this->addData( 'bankAccounts', $this->mangopay->Users->GetBankAccounts( $this->userId, $pagination, $sorting ) );

			$pagination	= $this->mangopay->getDefaultPagination();
			$sorting	= $this->mangopay->getDefaultSorting();
			$sorting->AddField( 'CreationDate', 'ASC' );
			$this->addData( 'transactions', $this->mangopay->Users->GetTransactions( $this->userId, $pagination, $sorting ) );

			$pagination	= $this->mangopay->getDefaultPagination();
			$sorting	= $this->mangopay->getDefaultSorting();
			$sorting->AddField( 'CreationDate', 'ASC' );
			$this->addData( 'wallets', $this->mangopay->Users->GetWallets( $this->userId, $pagination, $sorting ) );

		}
		catch( Exception $e ){
			$this->env->getMessenger()->noteError( 'Invalid User ID' );
			$this->restart( NULL, TRUE );
		}

	}

	public function view( $userId ){

	}
}
