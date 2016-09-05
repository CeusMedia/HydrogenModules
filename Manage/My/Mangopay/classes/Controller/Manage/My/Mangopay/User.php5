<?php
class Controller_Manage_My_Mangopay_User extends CMF_Hydrogen_Controller{

	public function index(){
		$mangopay		= Resource_Mangopay::getInstance( $this->env );

		$pagination	= new \MangoPay\Pagination();
		$sorting	= new \MangoPay\Sorting();
		$sorting->AddField( 'CreationDate', 'DESC' );
		$this->addData( 'users', $mangopay->Users->GetAll( $pagination, $sorting ));
	}

	public function view( $userId ){
		$mangopay		= Resource_Mangopay::getInstance( $this->env );
		try{
			$user	= $mangopay->Users->Get( $userId );
			$this->addData( 'userId', $userId );
			$this->addData( 'user', $user );

			$pagination	= $mangopay->getDefaultPagination();
			$sorting	= $mangopay->getDefaultSorting();
			$sorting->AddField( 'CreationDate', 'ASC' );
			$this->addData( 'bankAccounts', $mangopay->Users->GetBankAccounts( $userId, $pagination, $sortings ) );
		}
		catch( Exception $e ){
			$this->env->getMessenger()->noteError( 'Invalid User ID' );
			$this->restart( NULL, TRUE );
		}

	}
}
