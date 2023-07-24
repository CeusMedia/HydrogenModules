<?php
class Controller_Manage_My_Mangopay_Bank extends Controller_Manage_My_Mangopay_Abstract
{
	protected array $words;
	protected string $sessionPrefix;

	public function add()
	{
		$this->saveBackLink( 'from', 'from', TRUE );
		if( $this->request->has( 'save' ) ){
			try{
				$created	= $this->logic->createBankAccount(
					$this->userId,
					$this->request->get( 'iban' ),
					$this->request->get( 'bic' ),
					$this->request->get( 'title' )
				);
				$this->followBackLink( 'from' );
				$this->restart( 'view/'.$created->Id, TRUE );
			}
			catch( Exception $e ){
				$this->handleMangopayResponseException( $e );
			}
//			$this->logic->createBankAccount();
//			throw new RuntimeException( 'Not implemented yet' );
		}
		$this->addData( 'backwardTo', $this->request->get( 'backwardTo' ) );
		$this->addData( 'forwardTo', $this->request->get( 'forwardTo' ) );
	}

	public function mandate( $bankAccountId )
	{
		try{
			$mandate	= $this->logic->createMandate( $bankAccountId, $this->env->url.'manage/my/mangopay/bank/view/'.$bankAccountId );
			$this->restart( $mandate->RedirectURL, FALSE, TRUE );
		}
		catch( Exception $e ){
			$this->handleMangopayResponseException( $e );
		}
	}

	public function deactivate( $bankAccountId )
	{
		if( $this->request->getMethod() === "POST" ){									//  form has been executed
			$password		= $this->request->get( 'password' );
			$localUserId	= $this->session->get( 'auth_user_id' );
			$logicAuth		= $this->env->logic->authentication;
			if( $logicAuth->checkPassword( $localUserId, $password ) ){
				try{
					$this->logic->deactivateBankAccount( $this->userId, $bankAccountId );
					$this->restart( NULL, TRUE );
				}
				catch( Exception $e ){
					$this->handleMangopayResponseException( $e );
				}
			}
			else
				$this->messenger->noteError( 'Invalid password.' );
		}
		$this->restart( 'view/'.$bankAccountId, TRUE );
	}

	public function payOut( $bankAccountId )
	{
		if( $this->request->has( 'save' ) ){
			throw new RuntimeException( 'Not implemented yet' );
		}
	}

	public function index()
	{
		$this->addData( 'bankAccounts', $this->logic->getBankAccounts( $this->userId ) );
	}

	public function view( $bankAccountId )
	{
		$bankAccount	= $this->checkIsOwnBankAccount( $bankAccountId );
		try{
			$this->addData( 'mandates', $this->logic->getBankAccountMandates( $this->userId, $bankAccountId ) );
		}
		catch( Exception $e ){
			$this->handleMangopayResponseException( $e );
		}
		$this->addData( 'bankAccountId', $bankAccountId );
		$this->addData( 'bankAccount', $bankAccount );
		$this->addData( 'wallets', $this->logic->getUserWallets( $this->userId ) );
		$this->addData( 'backwardTo', $this->request->get( 'backwardTo' ) );
		$this->addData( 'forwardTo', $this->request->get( 'forwardTo' ) );
	}

	protected function __onInit(): void
	{
		parent::__onInit();
		$this->words			= $this->getWords( 'add', 'manage/my/mangopay/card' );
		$this->sessionPrefix	= 'manage_my_mangopay_bank_';
	}
}
