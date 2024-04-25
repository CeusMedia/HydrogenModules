<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Net\HTTP\Request;
use CeusMedia\HydrogenFramework\Controller;

class Controller_Work_Billing_Corporation extends Controller
{
	protected Request $request;
	protected Dictionary $session;
	protected Logic_Billing $logic;
	protected Model_Billing_Corporation $modelCorporation;

	public function add(): void
	{
		if( $this->request->has( 'save' ) ){
			$corporationId		= $this->modelCorporation->add( [
				'status'	=> Model_Billing_Corporation::STATUS_NEW,
				'title'		=> $this->request->get( 'title' ),
				'balance'	=> $this->request->get( 'balance' ),
				'iban'		=> $this->request->get( 'iban' ),
				'bic'		=> $this->request->get( 'bic' ),
			] );
			$this->restart( 'edit/'.$corporationId, TRUE );
		}
	}

	public function edit( string $corporationId ): void
	{
		if( $this->request->has( 'save' ) ){
			$this->modelCorporation->edit( $corporationId, [
				'title'		=> $this->request->get( 'title' ),
				'iban'		=> $this->request->get( 'iban' ),
				'bic'		=> $this->request->get( 'bic' ),
			] );
			$this->restart( 'edit/'.$corporationId, TRUE );
		}
		$this->addData( 'corporation', $this->modelCorporation->get( $corporationId ) );
	}

	public function index(): void
	{
		$corporations	= $this->modelCorporation->getAll();
		$this->addData( 'corporations', $corporations );
	}

	protected function __onInit(): void
	{
		$this->request	= $this->env->getRequest();
		$this->session	= $this->env->getSession();
		$this->logic	= new Logic_Billing( $this->env );
		$this->modelCorporation	= new Model_Billing_Corporation( $this->env );
	}
}
