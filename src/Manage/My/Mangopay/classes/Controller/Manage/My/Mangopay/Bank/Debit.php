<?php
/**
 *	@todo   			migrate index to add and implement payin history on index
 */
class Controller_Manage_My_Mangopay_Bank_Debit extends Controller_Manage_My_Mangopay_Abstract
{
	protected array $words;
	protected string $sessionPrefix;

	public function index( $bankAccountId, $walletId = NULL, $amount = NULL )
	{
	}

	protected function __onInit(): void
	{
		parent::__onInit();
//		$this->words			= $this->getWords( 'add', 'manage/my/mangopay/bank/payin' );
		$this->sessionPrefix	= 'manage_my_mangopay_bank_payin_';
	}
}
