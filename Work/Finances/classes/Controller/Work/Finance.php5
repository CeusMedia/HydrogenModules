<?php
class Controller_Work_Finance extends CMF_Hydrogen_Controller{

	/**	@var	CMF_Hydrogen_Environment_Resource_Messenger		$messenger		Shortcut to messenger object */
	protected $messenger;

	protected function __onInit(){
		$this->messenger	= $this->env->getMessenger();
	}

	public function filter(){
		$request	= $this->env->getRequest();
		$session	= $this->env->getSession();
		if( $request->get( 'filter' ) ){
			$session->remove( 'filter_finance_type' );
			$session->remove( 'filter_finance_scope' );
			if( $request->get( 'type' ) )
				$session->set( 'filter_finance_type', $request->get( 'type' ) );
			if( $request->get( 'scope' ) )
				$session->set( 'filter_finance_scope', $request->get( 'scope' ) );
		}
		$this->restart( './work/finance' );
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
		$session		= $this->env->getSession();
		$userId			= $this->env->getSession()->get( 'userId' );
		$modelBank		= new Model_Finance_Bank( $this->env );
		$modelAccount	= new Model_Finance_Bank_Account( $this->env );
		$modelFund		= new Model_Finance_Fund( $this->env );
		$modelPrice		= new Model_Finance_FundPrice( $this->env );
		
		$conditions		= array( 'userId' => $userId );
		$banks			= $modelBank->getAll( $conditions );
		foreach( $banks as $nr => $bank ){
			$conditions		= array( 'bankId' => $bank->bankId );
			if( (int) $session->get( 'filter_finance_type' ) )
				$conditions['type']		= $session->get( 'filter_finance_type' );
			if( (int) $session->get( 'filter_finance_scope' ) )
				$conditions['scope']	= $session->get( 'filter_finance_scope' );
			$accounts	= $modelAccount->getAll( $conditions );
			if( $accounts )
				$banks[$nr]->accounts	= $accounts;
			else
				unset( $banks[$nr] );
		}
		if( $session->get( 'filter_finance_type' ) != 1 ){
			$conditions		= array(/* 'bankId' => $bank->bankId*/ );
			if( (int) $session->get( 'filter_finance_scope' ) )
				$conditions['scope']	= $session->get( 'filter_finance_scope' );
			$funds	= $modelFund->getAll( $conditions );
			if( $funds ){
				$bank	= (object) array( 'title' => 'Fonds' );
				foreach( $funds as $nr => $fund ){
					$empty	= (object) array( 'fundId' => $fund->fundId, 'price' => 0, 'timestamp' => 0 );
					$price	= $modelPrice->getAll(
						array( 'fundId' => $fund->fundId ),
						array( 'timestamp' => 'DESC' ),
						array( 0, 1 )
					);
					$funds[$nr]->price	= $price ? $price[0] : $empty;
				}
				$bank->funds	= $funds;
				$banks[]	= $bank;
			}
		}
		$this->addData( 'banks', $banks );
	}
}
?>