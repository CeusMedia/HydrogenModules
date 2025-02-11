<?php

use CeusMedia\HydrogenFramework\Environment;

class Logic_Billing
{
	protected Environment $env;
	protected Model_Billing_Bill $modelBill;
	protected Model_Billing_Bill_Share $modelBillShare;
	protected Model_Billing_Bill_Expense $modelBillExpense;
	protected Model_Billing_Bill_Reserve $modelBillReserve;
	protected Model_Billing_Corporation $modelCorporation;
	protected Model_Billing_Person $modelPerson;
	protected Model_Billing_Reserve $modelReserve;
	protected Model_Billing_Expense $modelExpense;
	protected Model_Billing_Transaction $modelTransaction;

	public function __construct( Environment $env )
	{
		$this->env	= $env;
		$this->__onInit();
	}

	public function addTransaction( $amount, $fromType, $fromId, $toType, $toId, $relation, $title, $date = NULL ): string
	{
		$date	= $date ?: date( 'Y-m-d' );
		$data	= [
			'status'		=> Model_Billing_Transaction::STATUS_NEW,
			'dateBooked'	=> $date,
			'fromType'		=> $fromType,
			'fromId'		=> $fromId,
			'toType'		=> $toType,
			'toId'			=> $toId,
			'amount'		=> $amount,
			'relation'		=> $relation,
			'title'			=> $title,
		];
	 	$transactionId	= $this->modelTransaction->add( $data );
		$this->realizeTransactions();
		return $transactionId;
	}

	public function getTransactions( array $conditions = [], array $orders = [], array $limits = [] ): array
	{
		return $this->modelTransaction->getAll( $conditions, $orders, $limits );
	}

	public function addBill( $number, string $title, $taxRate, $amountNetto = 0, $amountTaxed = 0 ): string
	{
		if( $amountNetto && !$amountTaxed )
			$amountTaxed	= $amountNetto * ( 1 + $taxRate / 100 );
		else if( $amountTaxed && !$amountNetto )
			$amountNetto	= $amountTaxed / ( 1 + $taxRate / 100 );
		return $this->modelBill->add( [
			'number'		=> $number,
			'status'		=> Model_Billing_Bill::STATUS_NEW,
			'title'			=> $title,
			'amountNetto'	=> $amountNetto,
			'amountTaxed'	=> $amountTaxed,
			'taxRate'		=> $taxRate,
		] );
	}

	public function addBillExpense( int|string $billId, $status, $amount, ?string $title = NULL ): void
	{
		$this->modelBillExpense->add( [
			'billId'	=> $billId,
			'status'	=> $status,
			'amount'	=> $amount,
			'title'		=> $title,
		] );
		$this->_updateBillReserves( $billId );
		$this->_updateBillShares( $billId );
		$this->_updateBillAssignedAmount( $billId );
	}

	public function addBillReserve( int|string $billId, int|string $reserveId ): void
	{
		$reserve	= $this->getReserve( $reserveId );
		$this->modelBillReserve->add( [
			'billId'		=> $billId,
			'reserveId'		=> $reserveId,
			'status'		=> Model_Billing_Bill_Reserve::STATUS_NEW,
			'corporationId'	=> $reserve->corporationId,
			'personalize'	=> $reserve->personalize,
			'percent'		=> $reserve->percent,
			'amount'		=> $reserve->amount,
			'title'			=> $reserve->title,
		] );
		$this->_updateBillReserves( $billId );
		$this->_updateBillShares( $billId );
		$this->_updateBillAssignedAmount( $billId );
	}

	public function addBillCorporationShare( int|string $billId, int|string $corporationId, $amount = 0, $percent = 0 ): string
	{
		$bill		= $this->getBill( $billId );
		$amountLeft	= $this->_getBillAmountAfterExpensesAndReserves( $billId );
		if( !$amount && $percent ){
			$amount	= $amountLeft * $percent / 100;
		}
		else if( $amount ){
			$percent	= $amount / $amountLeft * 100;
		}
		$shareId	= $this->modelBillShare->add( [
			'status'	=> Model_Billing_Bill_Share::STATUS_NEW,
			'billId'	=> $billId,
			'corporationId'	=> $corporationId,
			'percent'	=> (float) $percent,
			'amount'	=> (float) $amount,
		] );
		$this->_updateBillAssignedAmount( $billId );
		return $shareId;
	}

	public function addBillPersonShare( int|string $billId, int|string $personId, $amount = 0, $percent = 0 ): string
	{
		$bill		= $this->getBill( $billId );
		$amountLeft	= $this->_getBillAmountAfterExpensesAndReserves( $billId );
		if( !$amount && $percent ){
			$amount	= $amountLeft * $percent / 100;
		}
		else if( $amount ){
			$percent	= $amount / $amountLeft * 100;
		}
		$shareId	= $this->modelBillShare->add( [
			'status'	=> Model_Billing_Bill_Share::STATUS_NEW,
			'billId'	=> $billId,
			'personId'	=> $personId,
			'percent'	=> (float) $percent,
			'amount'	=> (float) $amount,
		] );
		$this->_updateBillAssignedAmount( $billId );
		return $shareId;
	}

	public function addCorporationExpense( int|string $corporationId, $amount, string $title, $date ): string
	{
		return $this->addTransaction(
			$amount,
			Model_Billing_Transaction::TYPE_CORPORATION,
			$corporationId,
			Model_Billing_Transaction::TYPE_EXPENSE,
			0,
			'|expense|',
			$title,
			$date
		);
	}

	public function addCorporationPayin( int|string $corporationId, $amount, string $title, $date ): string
	{
		return $this->addTransaction(
			$amount,
			Model_Billing_Transaction::TYPE_PAYIN,
			0,
			Model_Billing_Transaction::TYPE_CORPORATION,
			$corporationId,
			'|payin|',
			$title,
			$date
		);
	}

	public function addCorporationPayout( int|string $corporationId, $amount, string $title, $date ): string
	{
		return $this->addTransaction(
			$amount,
			Model_Billing_Transaction::TYPE_CORPORATION,
			$corporationId,
			Model_Billing_Transaction::TYPE_PAYOUT,
			0,
			'|payout|',
			$title,
			$date
		);
	}

	public function addExpense( string $title, $amount, int|string $corporationId = 0, int|string $personId = 0, $frequency = 0, $dayOfMonth = 0 ): string
	{
		return $this->modelExpense->add( [
			'corporationId'	=> $corporationId,
			'personId'		=> $personId,
			'amount'		=> $amount,
			'frequency'		=> $frequency,
			'dayOfMonth'	=> $dayOfMonth,
			'title'			=> $title,
		] );
	}

	public function addPerson( $status, string $firstname, string $surname, $balance = 0 ): string
	{
		return $this->modelPerson->add( [
			'status'	=> $status,
			'firstname'	=> $firstname,
			'surname'	=> $surname,
			'balance'	=> $balance,
		] );
	}

	public function addPersonExpense( int|string $personId, $amount, string $title, $date ): string
	{
		return $this->addTransaction(
			$amount,
			Model_Billing_Transaction::TYPE_PERSON,
			$personId,
			Model_Billing_Transaction::TYPE_EXPENSE,
			0,
			'|expense|',
			$title,
			$date
		);
	}

	public function addPersonPayin( int|string $personId, $amount, string $title, $date ): string
	{
		return $this->addTransaction(
			$amount,
			Model_Billing_Transaction::TYPE_PAYIN,
			0,
			Model_Billing_Transaction::TYPE_PERSON,
			$personId,
			'|payin|',
			$title,
			$date
		);
	}

	public function addPersonPayout( int|string $personId, $amount, string $title, $date ): string
	{
		return $this->addTransaction(
			$amount,
			Model_Billing_Transaction::TYPE_PERSON,
			$personId,
			Model_Billing_Transaction::TYPE_PAYOUT,
			0,
			'|payout|',
			$title,
			$date
		);
	}

	public function addReserve( string $title, $percent = 0, $amount = 0, $corporationId = 0 ): string
	{
		return $this->modelReserve->add( [
			'corporationId'	=> $corporationId,
			'title'		=> $title,
			'percent'	=> $percent,
			'amount'	=> $amount,
		] );
	}

	public function closeBill( int|string $billId ): void
	{
		$this->env->getDatabase()->beginTransaction();
		try{
			$bill		= $this->getBill( $billId );
			$amount		= $this->_getBillAmountAfterExpensesAndReserves( $billId );
			$billShares	= $this->getBillShares( $billId );
			foreach( $billShares as $billShare )
				if( !(float) $billShare->percent )
					$billShare->percent	= $billShare->amount / $amount * 100;

			$billReserves	= $this->getBillReserves( $billId );
			foreach( $billReserves as $billReserve ){
				if( $billReserve->corporationId ){
					if( $billReserve->personalize ){
						foreach( $billShares as $billShare ){
							$this->addTransaction(
								$billReserve->amount * $billShare->percent / 100,
								Model_Billing_Transaction::TYPE_BILL,
								$billId,
								Model_Billing_Transaction::TYPE_PERSON,
								$billShare->personId,
								'|billReserve:'.$billReserve->billReserveId.'|bill:'.$billReserve->billId.'|',
								NULL,
								$bill->dateBooked
							);
						}
					}
					else{
						$this->addTransaction(
							$billReserve->amount,
							Model_Billing_Transaction::TYPE_BILL,
							$billId,
							Model_Billing_Transaction::TYPE_CORPORATION,
							$billReserve->corporationId,
							'|billReserve:'.$billReserve->billReserveId.'|bill:'.$billReserve->billId.'|',
							NULL,
							$bill->dateBooked
						);
					}
				}
				else{
					foreach( $billShares as $billShare ){
						$this->addTransaction(
							$billReserve->amount * $billShare->percent / 100,
							Model_Billing_Transaction::TYPE_BILL,
							$billId,
							Model_Billing_Transaction::TYPE_PERSON,
							$billShare->personId,
							'|billReserve:'.$billReserve->billReserveId.'|bill:'.$billReserve->billId.'|',
							NULL,
							$bill->dateBooked
						);
					}
				}
				$this->modelBillReserve->edit( $billReserve->billReserveId, [
					'status'	=> Model_Billing_Bill_Reserve::STATUS_BOOKED,
				] );
			}
			foreach( $billShares as $billShare ){
				if( $billShare->personId ){
					$this->addTransaction(
						$billShare->amount,
						Model_Billing_Transaction::TYPE_BILL,
						$billShare->billId,
						Model_Billing_Transaction::TYPE_PERSON,
						$billShare->personId,
						'|billShare:'.$billShare->billShareId.'|bill:'.$billShare->billId.'|',
						NULL,
						$bill->dateBooked
					);
				}
				else{
					$this->addTransaction(
						$billShare->amount,
						Model_Billing_Transaction::TYPE_BILL,
						$billShare->billId,
						Model_Billing_Transaction::TYPE_CORPORATION,
						$billShare->corporationId,
						'|billShare:'.$billShare->billShareId.'|bill:'.$billShare->billId.'|',
						NULL,
						$bill->dateBooked
					);
				}
				$this->modelBillShare->edit( $billShare->billShareId, [
					'status'	=> Model_Billing_Bill_Share::STATUS_BOOKED,
				] );
			}
			$this->modelBill->edit( $billId, ['status' => Model_Billing_Bill::STATUS_BOOKED] );
			$this->env->getDatabase()->commit();
		}
		catch( Exception $e ){
			$this->env->getDatabase()->rollBack();
			$this->env->getMessenger()->noteFailure( $e->getMessage() );
		}
	}

	public function countBills( array $conditions ): int
	{
		return $this->modelBill->count( $conditions );
	}

	public function editBill( int|string $billId, array $data ): void
	{
		$this->modelBill->edit( $billId, $data );
		$this->_updateBillReserves( $billId );
		$this->_updateBillShares( $billId );
		$this->_updateBillAssignedAmount( $billId );
	}

	public function editCorporation( int|string $corporationId, array $data ): void
	{
		if( isset( $data['balance'] ) )
			unset( $data['balance'] );
		$this->modelCorporation->edit( $corporationId, $data );
	}

	public function editPerson( int|string $personId, array $data ): void
	{
		if( isset( $data['balance'] ) )
			unset( $data['balance'] );
		$this->modelPerson->edit( $personId, $data );
	}

	public function editReserve( int|string $reserveId, array $data ): void
	{
		$this->modelReserve->edit( $reserveId, $data );
	}

	public function getBill( int|string $billId ): object
	{
		return $this->modelBill->get( $billId );
	}

	public function getBills( array $conditions, array $orders = [], array $limits = [] ): array
	{
		return $this->modelBill->getAll( $conditions, $orders, $limits );
	}

	public function getBillCorporationTransactions( int|string $billId, array $conditions = [], array $orders = [], array $limits = [] ): array
	{
		$conditions	= array_merge( $conditions, [
			'toType'	=> Model_Billing_Transaction::TYPE_CORPORATION,
			'relation'	=> '%|bill:'.$billId.'|%'
		] );
		$orders		= $orders ?: ['transactionId' => 'ASC'];
		return $this->modelTransaction->getAll( $conditions, $orders, $limits );
	}

	public function getBillExpense( int|string $expenseId ): object
	{
		return $this->modelBillExpense->get( $expenseId );
	}

	public function getBillExpenses( int|string $billId ): array
	{
		return $this->modelBillExpense->getAll( ['billId' => $billId], ['billExpenseId' => 'ASC'] );
	}

	public function getBillPersonTransactions( int|string $billId, array $conditions = [], array $orders = [], array $limits = [] ): array
	{
		$conditions	= array_merge( $conditions, [
			'toType'	=> Model_Billing_Transaction::TYPE_PERSON,
			'relation'	=> '%|bill:'.$billId.'|%'
		] );
		$orders		= $orders ?: ['transactionId' => 'ASC'];
		return $this->modelTransaction->getAll( $conditions, $orders, $limits );
	}

	public function getBillReserve( int|string $billReserveId ): object
	{
		$relation	= $this->modelBillReserve->get( $billReserveId );
		if( $relation )
			$relation->reserve	= $this->getReserve( $relation->reserveId );
		return $relation;
	}

	public function getBillReserves( int|string $billId, array $conditions = [], array $orders = [], array $limits = [] ): array
	{
		$conditions	= array_merge( ['billId' => $billId], $conditions );
		$orders		= $orders ?: ['billReserveId' => 'ASC'];
		$relations	= $this->modelBillReserve->getAll( $conditions, $orders, $limits );
		foreach( $relations as $relation )
			$relation->reserve	= $this->getReserve( $relation->reserveId );
		return $relations;
	}

	public function getBillShare( int|string $shareId ): object
	{
		return $this->modelBillShare->get( $shareId );
	}

	public function getBillShares( int|string $billId, array $conditions = [], array $orders = [], array $limits = [] ): array
	{
		$conditions	= array_merge( ['billId' => $billId], $conditions );
		$orders		= $orders ?: ['billShareId' => 'ASC'];
		return $this->modelBillShare->getAll( $conditions, $orders, $limits );
	}

	public function getCorporation( int|string $corporationId ): object
	{
		return $this->modelCorporation->get( $corporationId );
	}

	public function getCorporations( array $conditions = [], array $orders = [], array $limits = [] ): array
	{
		return $this->modelCorporation->getAll( $conditions, $orders, $limits );
	}

	public function getCorporationExpenses( int|string $corporationId, array $conditions = [], array $orders = [], array $limits = [] ): array
	{
		$conditions	= array_merge( $conditions, [
			'fromType'	=> Model_Billing_Transaction::TYPE_CORPORATION,
			'fromId'	=> $corporationId,
			'toType'	=> [
				Model_Billing_Transaction::TYPE_EXPENSE,
				Model_Billing_Transaction::TYPE_NONE,
			],
			'relation'	=> '%|expense%'
		] );
		$orders		= $orders ?: ['transactionId' => 'ASC'];
		return $this->modelTransaction->getAll( $conditions, $orders, $limits );
	}

	public function getCorporationPayins( int|string $corporationId, array $conditions = [], array $orders = [], array $limits = [] ): array
	{
		$conditions	= array_merge( $conditions, [
			'fromType'	=> Model_Billing_Transaction::TYPE_PAYIN,
			'toType'	=> Model_Billing_Transaction::TYPE_CORPORATION,
			'toId'		=> $corporationId,
		] );
		$orders		= $orders ?: ['transactionId' => 'ASC'];
		return $this->modelTransaction->getAll( $conditions, $orders, $limits );
	}

	public function getCorporationPayouts( int|string $corporationId, array $conditions = [], array $orders = [], array $limits = [] ): array
	{
		$conditions	= array_merge( $conditions, [
			'fromType'	=> Model_Billing_Transaction::TYPE_CORPORATION,
			'fromId'	=> $corporationId,
			'toType'	=> Model_Billing_Transaction::TYPE_PAYOUT,
		] );
		$orders		= $orders ?: ['transactionId' => 'ASC'];
		return $this->modelTransaction->getAll( $conditions, $orders, $limits );
	}

	public function getCorporationReserves( int|string $corporationId, array $conditions = [], array $orders = [], array $limits = [] ): array
	{
		$conditions		= array_merge( $conditions, [
			'fromType'	=> [
				Model_Billing_Transaction::TYPE_BILL,
				Model_Billing_Transaction::TYPE_RESERVE,
			],
			'toType'	=> Model_Billing_Transaction::TYPE_CORPORATION,
			'toId'		=> $corporationId,
		] );
		$orders		= $orders ?: ['transactionId' => 'ASC'];
		return $this->modelTransaction->getAll( $conditions, $orders, $limits );
	}

	public function getExpense( int|string $expenseId ): object
	{
		return $this->modelExpense->get( $expenseId );
	}

	public function getExpenses( array $conditions = [], array $orders = [], array $limits = [] ): array
	{
		return $this->modelExpense->getAll( $conditions, $orders, $limits );
	}

	public function getPerson( int|string $personId ): object
	{
		return $this->modelPerson->get( $personId );
	}

	public function getPersonBillShares( int|string $personId, array $conditions = [], array $orders = [], array $limits = [] ): array
	{
		$conditions	= array_merge( ['personId' => $personId], $conditions );
		$orders		= $orders ?: ['billShareId' => 'ASC'];
		return $this->modelBillShare->getAll( $conditions, $orders, $limits );
	}

	public function getPersonExpenses( int|string $personId, array $conditions = [], array $orders = [], array $limits = [] ): array
	{
		$conditions	= array_merge( $conditions, [
			'fromType'	=> Model_Billing_Transaction::TYPE_PERSON,
			'fromId'	=> $personId,
			'toType'	=> [
				Model_Billing_Transaction::TYPE_CORPORATION,
				Model_Billing_Transaction::TYPE_EXPENSE,
			],
			'relation'	=> '%|expense%'
		] );
		$orders		= $orders ?: ['transactionId' => 'ASC'];
		return $this->modelTransaction->getAll( $conditions, $orders, $limits );
	}

	public function getPersonReserves( int|string $personId, array $conditions = [], array $orders = [], array $limits = [] ): array
	{
		$conditions	= array_merge( $conditions, [
			'fromType'	=> [
				Model_Billing_Transaction::TYPE_BILL,
				Model_Billing_Transaction::TYPE_RESERVE,
			],
			'toType'	=> Model_Billing_Transaction::TYPE_PERSON,
			'toId'		=> $personId,
		] );
		$orders		= $orders ?: ['transactionId' => 'ASC'];
		return $this->modelTransaction->getAll( $conditions, $orders, $limits );
	}

	public function getPersonPayins( int|string $personId, array $conditions = [], array $orders = [], array $limits = [] ): array
	{
		$conditions	= array_merge( $conditions, [
			'fromType'	=> Model_Billing_Transaction::TYPE_PAYIN,
			'toType'	=> Model_Billing_Transaction::TYPE_PERSON,
			'toId'	=> $personId,
		] );
		$orders		= $orders ?: ['transactionId' => 'ASC'];
		return $this->modelTransaction->getAll( $conditions, $orders, $limits );
	}

	public function getPersonPayouts( int|string $personId, array $conditions = [], array $orders = [], array $limits = [] ): array
	{
		$conditions	= array_merge( $conditions, [
			'fromType'	=> Model_Billing_Transaction::TYPE_PERSON,
			'fromId'	=> $personId,
			'toType'	=> Model_Billing_Transaction::TYPE_PAYOUT,
		] );
		$orders		= $orders ?: ['transactionId' => 'ASC'];
		return $this->modelTransaction->getAll( $conditions, $orders, $limits );
	}

	public function getPersons( array $conditions = [], array $orders = [], array $limits = [] ): array
	{
		return $this->modelPerson->getAll( $conditions, $orders, $limits );
	}

	public function getReserve( int|string $reserveId ): object
	{
		return $this->modelReserve->get( $reserveId );
	}

	public function getReserves( array $conditions = [], array $orders = [], array $limits = [] ): array
	{
		return $this->modelReserve->getAll( $conditions, $orders, $limits );
	}

	public function realizeTransactions(): void
	{
		$transactions	= $this->modelTransaction->getAll( [
			'status' => Model_Billing_Transaction::STATUS_NEW,
		], ['dateBooked' => 'ASC', 'transactionId' => 'ASC'] );
		foreach( $transactions as $transaction ){
			$this->env->getDatabase()->beginTransaction();
			try{
				switch( $transaction->toType ){
					case Model_Billing_Transaction::TYPE_PERSON:
						$receiver	= $this->getPerson( $transaction->toId );
						$this->modelPerson->edit( $transaction->toId, [
							'balance'	=> $receiver->balance + $transaction->amount,
						] );
						break;
					case Model_Billing_Transaction::TYPE_CORPORATION:
						$receiver	= $this->getCorporation( $transaction->toId );
						$this->modelCorporation->edit( $transaction->toId, [
							'balance'	=> $receiver->balance + $transaction->amount,
						] );
						break;
				}
				switch( $transaction->fromType ){
					case Model_Billing_Transaction::TYPE_PERSON:
						$sender	= $this->getPerson( $transaction->fromId );
						$this->modelPerson->edit( $transaction->fromId, [
							'balance'	=> $sender->balance - $transaction->amount,
						] );
						break;
					case Model_Billing_Transaction::TYPE_CORPORATION:
						$sender	= $this->getCorporation( $transaction->fromId );
						$this->modelCorporation->edit( $transaction->fromId, [
							'balance'	=> $sender->balance - $transaction->amount,
						] );
						break;
				}
				$this->modelTransaction->edit( $transaction->transactionId, [
					'status'	=> Model_Billing_Transaction::STATUS_BOOKED,
				] );
				$this->env->getDatabase()->commit();
			}
			catch( Exception $e ){
				$this->env->getDatabase()->rollBack();
			}
		}
	}

	public function removeBillExpense( int|string $billExpenseId ): void
	{
		$billExpense	= $this->modelBillExpense->get( $billExpenseId );
		$this->modelBillExpense->remove( $billExpenseId );
		$this->_updateBillReserves( $billExpense->billId );
		$this->_updateBillShares( $billExpense->billId );
		$this->_updateBillAssignedAmount( $billExpense->billId );
	}

	public function removeBillShare( int|string $billShareId ): void
	{
		$billShare	= $this->modelBillShare->get( $billShareId );
		$this->modelBillShare->remove( $billShareId );
		$this->_updateBillAssignedAmount( $billShare->billId );
	}

	public function removeBillReserve( int|string $billReserveId ): void
	{
		$billReserve	= $this->modelBillReserve->get( $billReserveId );
		$this->modelBillReserve->remove( $billReserveId );
		$this->_updateBillReserves( $billReserve->billId );
		$this->_updateBillShares( $billReserve->billId );
		$this->_updateBillAssignedAmount( $billReserve->billId );
	}

	public function revertTransaction( int|string $transactionId ): void
	{
		$transaction	= $this->modelTransaction->get( $transactionId );
		if( !$transaction )
			throw new RangeException( 'Invalid transaction ID: '.$transactionId );
		$this->env->getDatabase()->beginTransaction();
		try{
			switch( $transaction->toType ){
				case Model_Billing_Transaction::TYPE_PERSON:
					$receiver	= $this->getPerson( $transaction->toId );
					$this->modelPerson->edit( $transaction->toId, [
						'balance'	=> $receiver->balance - $transaction->amount,
					] );
					break;
				case Model_Billing_Transaction::TYPE_CORPORATION:
					$receiver	= $this->getCorporation( $transaction->toId );
					$this->modelCorporation->edit( $transaction->toId, [
						'balance'	=> $receiver->balance - $transaction->amount,
					] );
					break;
			}
			switch( $transaction->fromType ){
				case Model_Billing_Transaction::TYPE_PERSON:
					$sender	= $this->getPerson( $transaction->fromId );
					$this->modelPerson->edit( $transaction->fromId, [
						'balance'	=> $sender->balance + $transaction->amount,
					] );
					break;
				case Model_Billing_Transaction::TYPE_CORPORATION:
					$sender	= $this->getCorporation( $transaction->fromId );
					$this->modelCorporation->edit( $transaction->fromId, [
						'balance'	=> $sender->balance + $transaction->amount,
					] );
					break;
			}
			$this->modelTransaction->remove( $transaction->transactionId );
			$this->env->getDatabase()->commit();
		}
		catch( Exception $e ){
			$this->env->getDatabase()->rollBack();
		}
	}

	public function _updateBillAssignedAmount( int|string $billId ): void
	{
		$bill		= $this->getBill( $billId );
		$amount		= $this->_getBillAmountAfterExpensesAndReserves( $billId );
		$billShares	= $this->getBillShares( $billId );
		foreach( $billShares as $billShare ){
			if( (float) $billShare->percent )
				$amount -= $billShare->amount;
		}
		$this->modelBill->edit( $billId, [
			'amountAssigned'	=> $bill->amountNetto - $amount
		] );
	}

	public function _updateBillReserves( int|string $billId ): void
	{
		$bill		= $this->getBill( $billId );
		$amount		= $bill->amountNetto;
		$expenses	= $this->getBillExpenses( $billId );
		foreach( $expenses as $expense )
			$amount	-= $expense->amount;
		$reserves	= $this->getBillReserves( $billId );
		$reservesPercent	= 0;
		$reservesAmount		= 0;
		foreach( $reserves as $reserve ){										//  iterate reserves
			if( (float) $reserve->percent )										//  either relative reserve
				$reservesPercent	+= $reserve->percent;						//  then sum percentage
			else if( (float) $reserve->amount )									//  otherwise absolute reserve
				$reservesAmount		+= $reserve->amount;						//  then sum amount
		}
		$reducedAmount	= $amount - $reservesAmount;							//  subtract absolute reserves
		$reducedAmount	= $reducedAmount / ( 1 + $reservesPercent / 100 );		//  real core value depending on relative reserves

		foreach( $reserves as $reserve ){										//  iterate reserves
			if( (float) $reserve->percent ){									//  if relative reserve
				$this->modelBillReserve->edit( $reserve->billReserveId, [	//  calculate absolute amount
					'amount'	=> $reducedAmount * $reserve->percent / 100		//  by percent WITHIN core value
				] );
			}
		}
	}

	public function _updateBillShares( int|string $billId ): void
	{
		$bill		= $this->getBill( $billId );
		$amount		= $this->_getBillAmountAfterExpensesAndReserves( $billId );
		$billShares	= $this->getBillShares( $billId );
		foreach( $billShares as $billShare ){
			if( (float) $billShare->percent ){
				$this->modelBillShare->edit( $billShare->billShareId, [
					'amount'	=> $amount * $billShare->percent / 100
				] );
			}
		}
	}

	//  --  PROTECTED  --  //

	protected function __onInit(): void
	{
		$this->modelBill					= new Model_Billing_Bill( $this->env );
		$this->modelBillShare				= new Model_Billing_Bill_Share( $this->env );
		$this->modelBillExpense				= new Model_Billing_Bill_Expense( $this->env );
		$this->modelBillReserve				= new Model_Billing_Bill_Reserve( $this->env );
		$this->modelCorporation				= new Model_Billing_Corporation( $this->env );
		$this->modelPerson					= new Model_Billing_Person( $this->env );
		$this->modelReserve					= new Model_Billing_Reserve( $this->env );
		$this->modelExpense					= new Model_Billing_Expense( $this->env );
		$this->modelTransaction				= new Model_Billing_Transaction( $this->env );
	}

	protected function _getBillAmountAfterExpensesAndReserves( int|string $billId ): float
	{
		$bill		= $this->getBill( $billId );
		$amount		= (float) $bill->amountNetto;
		$expenses	= $this->getBillExpenses( $billId );
		foreach( $expenses as $expense ){
			$amount	-= (float) $expense->amount;
		}
		$reserves	= $this->getBillReserves( $billId );
		$substract	= 0;
		foreach( $reserves as $reserve )
			$substract	+= (float) $reserve->amount;
		return $amount - $substract;
	}
}
