<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Net\HTTP\Request;
use CeusMedia\HydrogenFramework\Controller;

class Controller_Work_Billing_Corporation_Transaction extends Controller
{
	protected Request $request;
	protected Dictionary $session;
	protected Logic_Billing $logic;
	protected string $filterPrefix;

	public function filter( string $corporationId, $reset = FALSE ): void
	{
		if( $reset ){
			$this->session->remove( $this->filterPrefix.'year' );
			$this->session->remove( $this->filterPrefix.'month' );
		}
		else{
			$this->session->set( $this->filterPrefix.'year', $this->request->get( 'year' ) );
			$this->session->set( $this->filterPrefix.'month', $this->request->get( 'month' ) );
		}
		$this->restart( $corporationId, TRUE );
	}

	public function index( string $corporationId ): void
	{
		$filterYear		= $this->session->get( $this->filterPrefix.'year' );
		$filterMonth	= $this->session->get( $this->filterPrefix.'month' );

		$conditions	= [];
		if( $filterYear || $filterMonth ){
			if( $filterYear && $filterMonth )
				$conditions['dateBooked']	= $filterYear.'-'.$filterMonth.'-%';
			else if( $filterYear )
				$conditions['dateBooked']	= $filterYear.'-%';
			else if( $filterMonth )
				$conditions['dateBooked']	= '%-'.$filterMonth.'-%';
		}
		$transactions	= $this->logic->getCorporationTransactions( $corporationId, $conditions );
		$this->addData( 'transactions', $transactions );
		$this->addData( 'corporation', $this->logic->getCorporation( $corporationId ) );
		$this->addData( 'corporationId', $corporationId );
		$this->addData( 'filterYear', $filterYear );
		$this->addData( 'filterMonth', $filterMonth );
	}

	protected function __onInit(): void
	{
		$this->request			= $this->env->getRequest();
		$this->session			= $this->env->getSession();
		$this->logic			= new Logic_Billing( $this->env );
		$this->filterPrefix		= 'filter_work_billing_corporation_transaction_';

		if( !$this->session->has( $this->filterPrefix.'year' ) )
			$this->session->set( $this->filterPrefix.'year', date( 'Y' ) );
		if( !$this->session->has( $this->filterPrefix.'month' ) )
			$this->session->set( $this->filterPrefix.'month', date( 'm' ) );
		$this->addData( 'filterSessionPrefix', $this->filterPrefix );
	}
}
