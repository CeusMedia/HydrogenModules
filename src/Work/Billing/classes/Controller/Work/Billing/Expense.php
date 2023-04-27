<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Controller;

class Controller_Work_Billing_Expense extends Controller
{
	protected Dictionary $request;
	protected Dictionary $session;
	protected Logic_Billing $logic;
	protected Model_Billing_Expense $modelExpense;

	public function add(): void
	{
		if( $this->request->has( 'save' ) ){
			$data	= [
				'status'		=> $this->request->get( 'status' ),
				'title'			=> $this->request->get( 'title' ),
				'amount'		=> $this->request->get( 'amount' ),
				'frequency'		=> $this->request->get( 'frequency' ),
			];
			if( $this->request->get( 'fromType' ) == 2 )
				$data['fromCorporationId']	= $this->request->get( 'fromCorporationId' );
			else if( $this->request->get( 'fromType' ) == 1 )
				$data['fromPersonId']		= $this->request->get( 'fromPersonId' );
			if( $this->request->get( 'toType' ) == 2 )
				$data['toCorporationId']	= $this->request->get( 'toCorporationId' );
			else if( $this->request->get( 'toType' ) == 1 )
				$data['toPersonId']	= $this->request->get( 'toPersonId' );
			$expenseId		= $this->modelExpense->add( $data );
			$this->restart( 'edit/'.$expenseId, TRUE );
		}
		$this->addData( 'corporations', $this->logic->getCorporations() );
		$this->addData( 'persons', $this->logic->getPersons() );
	}

	public function edit( string $expenseId ): void
	{
		if( $this->request->has( 'save' ) ){
			$data	= [
				'title'				=> $this->request->get( 'title' ),
				'amount'			=> $this->request->get( 'amount' ),
				'frequency'			=> $this->request->get( 'frequency' ),
				'status'			=> $this->request->get( 'status' ),
				'fromCorporationId'	=> 0,
				'fromPersonId'		=> 0,
				'toCorporationId'	=> 0,
				'toPersonId'		=> 0,
			];
			if( $this->request->get( 'fromType' ) == 2 )
				$data['fromCorporationId']	= $this->request->get( 'fromCorporationId' );
			else if( $this->request->get( 'fromType' ) == 1 )
				$data['fromPersonId']		= $this->request->get( 'fromPersonId' );
			if( $this->request->get( 'toType' ) == 2 )
				$data['toCorporationId']	= $this->request->get( 'toCorporationId' );
			else if( $this->request->get( 'toType' ) == 1 )
				$data['toPersonId']	= $this->request->get( 'toPersonId' );
			$this->modelExpense->edit( $expenseId, $data );
			$this->restart( NULL, TRUE );
//			$this->restart( './work/billing/expense/edit/'.$expenseId );
		}
		$this->addData( 'expense', $this->logic->getExpense( $expenseId ) );
		$this->addData( 'corporations', $this->logic->getCorporations() );
		$this->addData( 'persons', $this->logic->getPersons() );
	}

	public function index(): void
	{
		$expenses	= $this->logic->getExpenses();
		$this->addData( 'expenses', $expenses );

		$corporations	= [];
		foreach( $this->logic->getCorporations() as $corporation )
			$corporations[$corporation->corporationId]	= $corporation;
		$this->addData( 'corporations', $corporations );

		$persons	= [];
		foreach( $this->logic->getPersons() as $person )
			$persons[$person->personId]	= $person;
		$this->addData( 'persons', $persons );
	}

	public function remove( string $expenseId ): void
	{
		$expense	= $this->modelExpense->get( $expenseId );
		$this->modelExpense->remove( $expenseId );
		$this->restart( NULL, TRUE );
	}

	protected function __onInit(): void
	{
		$this->request	= $this->env->getRequest();
		$this->session	= $this->env->getSession();
		$this->logic	= new Logic_Billing( $this->env );
		$this->modelExpense		= new Model_Billing_Expense( $this->env );
	}
}
