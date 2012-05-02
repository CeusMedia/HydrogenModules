<?php
class Controller_Work_Finance extends CMF_Hydrogen_Controller{

	/**	@var	CMF_Hydrogen_Environment_Resource_Messenger		$messenger		Shortcut to messenger object */
	protected $messenger;

	protected function __onInit(){
		$this->messenger	= $this->env->getMessenger();
	}

	protected function getBanksWithAccounts(){
		$userId			= $this->env->getSession()->get( 'userId' );
		$modelBank		= new Model_Finance_Bank( $this->env );
		$modelAccount	= new Model_Finance_Bank_Account( $this->env );
		$banks			= $modelBank->getAllByIndex( 'userId', $userId );
		foreach( $banks as $nr => $bank ){
			$accounts	= $modelAccount->getAllByIndex( 'bankId', $bank->bankId );
			$banks[$nr]->accounts	= $accounts;
		}
		return $banks;
	}
	
	public function index(){
		$this->addData( 'banks', $this->getBanksWithAccounts() );
	}
}
?>