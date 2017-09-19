<?php

class Logic_Billing{

	protected $env;
	protected $modelBill;

	public function __construct( $env ){
		$this->env	= $env;
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

	public function addTransaction( $amount, $fromType, $fromId, $toType, $toId, $relation, $title, $date = NULL ){
		$date	= $date ? $date : date( 'Y-m-d' );
		$data	= array(
			'status'		=> Model_Billing_Transaction::STATUS_NEW,
			'dateBooked'	=> $date,
			'fromType'		=> $fromType,
			'fromId'		=> $fromId,
			'toType'		=> $toType,
			'toId'			=> $toId,
			'amount'		=> $amount,
			'relation'		=> $relation,
			'title'			=> $title,
		);
	 	$transactionId	= $this->modelTransaction->add( $data );
		$this->realizeTransactions();
		return $transactionId;
	}

	public function getTransactions( $conditions = array(), $orders = array(), $limits = array() ){
		return $this->modelTransaction->getAll( $conditions, $orders, $limits );
	}

	public function realizeTransactions(){
		$transactions	= $this->modelTransaction->getAll( array(
			'status' => Model_Billing_Transaction::STATUS_NEW,
		), array( 'dateBooked' => 'ASC', 'transactionId' => 'ASC' ) );
		foreach( $transactions as $transaction ){
			$this->env->getDatabase()->beginTransaction();
			try{
				switch( $transaction->toType ){
					case Model_Billing_Transaction::TYPE_PERSON:
						$receiver	= $this->getPerson( $transaction->toId );
						$this->modelPerson->edit( $transaction->toId, array(
							'balance'	=> $receiver->balance + $transaction->amount,
						) );
						break;
					case Model_Billing_Transaction::TYPE_CORPORATION:
						$receiver	= $this->getCorporation( $transaction->toId );
						$this->modelCorporation->edit( $transaction->toId, array(
							'balance'	=> $receiver->balance + $transaction->amount,
						) );
						break;
				}
				switch( $transaction->fromType ){
					case Model_Billing_Transaction::TYPE_PERSON:
						$sender	= $this->getPerson( $transaction->fromId );
						$this->modelPerson->edit( $transaction->fromId, array(
							'balance'	=> $sender->balance - $transaction->amount,
						) );
						break;
					case Model_Billing_Transaction::TYPE_CORPORATION:
						$sender	= $this->getCorporation( $transaction->fromId );
						$this->modelCorporation->edit( $transaction->fromId, array(
							'balance'	=> $sender->balance - $transaction->amount,
						) );
						break;
				}
				$this->modelTransaction->edit( $transaction->transactionId, array(
					'status'	=> Model_Billing_Transaction::STATUS_BOOKED,
				) );
				$this->env->getDatabase()->commit();
			}
			catch( Exception $e ){
				$this->env->getDatabase()->rollBack();
			}
		}
	}


	public function addBill( $number, $title, $taxRate, $amountNetto = 0, $amountTaxed = 0 ){
		if( $amountNetto && !$amountTaxed )
			$amountTaxed	= $amountNetto * ( 1 + $taxRate / 100 );
		else if( $amountTaxed && !$amountNetto )
			$amountNetto	= $amountTaxed / ( 1 + $taxRate / 100 );
		return $this->modelBill->add( array(
			'number'	=> $number,
			'status'	=> Model_Billing_Bill::STATUS_NEW,
			'title'		=> $title,
			'amountNetto'	=> $amountNetto,
			'amountTaxed'	=> $amountTaxed,
			'taxRate'		=> $taxRate,
		) );
	}

	public function addBillExpense( $billId, $status, $amount, $title = NULL ){
		$this->modelBillExpense->add( array(
			'billId'	=> $billId,
			'status'	=> $status,
			'amount'	=> $amount,
			'title'		=> $title,
		) );
		$this->_updateBillReserves( $billId );
		$this->_updateBillShares( $billId );
		$this->_updateBillAssignedAmount( $billId );
	}

	public function addBillReserve( $billId, $reserveId ){
		$reserve	= $this->getReserve( $reserveId );
		$this->modelBillReserve->add( array(
			'billId'		=> $billId,
			'reserveId'		=> $reserveId,
			'status'		=> Model_Billing_Bill_Reserve::STATUS_NEW,
			'corporationId'	=> $reserve->corporationId,
			'personalize'	=> $reserve->personalize,
			'percent'		=> $reserve->percent,
			'amount'		=> $reserve->amount,
			'title'			=> $reserve->title,
		) );
		$this->_updateBillReserves( $billId );
		$this->_updateBillShares( $billId );
		$this->_updateBillAssignedAmount( $billId );
	}

	public function addBillShare( $billId, $personId, $amount = 0, $percent = 0 ){
		if( !$amount && $percent ){
			$bill		= $this->getBill( $billId );
			$amountLeft	= $this->_getBillAmountAfterExpensesAndReserves( $billId );
			$amount	= $amountLeft * $percent / 100;
		}
		$shareId	= $this->modelBillShare->add( array(
			'status'	=> Model_Billing_Bill_Share::STATUS_NEW,
			'billId'	=> $billId,
			'personId'	=> $personId,
			'percent'	=> (float) $percent,
			'amount'	=> (float) $amount,
		) );
		$this->_updateBillAssignedAmount( $billId );
		return $shareId;
	}

	public function addCorporationExpense( $corporationId, $amount, $title, $date ){
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

	public function addCorporationPayin( $corporationId, $amount, $title, $date ){
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

	public function addCorporationPayout( $corporationId, $amount, $title, $date ){
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

	public function addExpense( $title, $amount, $corporationId = 0, $personId = 0, $frequency = 0, $dayOfMonth = 0 ){
		return $this->modelExpense->add( array(
			'corporationId'	=> $corporationId,
			'personId'		=> $personId,
			'amount'		=> $amount,
			'frequency'		=> $frequency,
			'dayOfMonth'	=> $dayOfMonth,
			'title'			=> $title,
		) );
	}

	public function addPerson( $status, $firstname, $surname, $balance = 0 ){
		return $this->modelPerson->add( array(
			'status'	=> $status,
			'firstname'	=> $firstname,
			'surname'	=> $surname,
			'balance'	=> $balance,
		) );
	}

	public function addPersonExpense( $personId, $amount, $title, $date ){
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

	public function addPersonPayin( $personId, $amount, $title, $date ){
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

	public function addPersonPayout( $personId, $amount, $title, $date ){
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

	public function addReserve( $title, $percent = 0, $amount = 0, $corporationId = 0 ){
		return $this->modelReserve->add( array(
			'corporationId'	=> $corporationId,
			'title'		=> $title,
			'percent'	=> $percent,
			'amount'	=> $amount,
		) );
	}

	public function editBill( $billId, $data ){
		$this->modelBill->edit( $billId, $data );
		$this->_updateBillReserves( $billId );
		$this->_updateBillShares( $billId );
		$this->_updateBillAssignedAmount( $billId );
	}

	public function editCorporation( $corporationId, $data ){
		if( isset( $data['balance'] ) )
			unset( $data['balance'] );
		$this->modelCorporation->edit( $corporationId, $data );
	}

	public function editPerson( $personId, $data ){
		if( isset( $data['balance'] ) )
			unset( $data['balance'] );
		$this->modelPerson->edit( $personId, $data );
	}

	public function editReserve( $reserveId, $data ){
		$this->modelReserve->edit( $reserveId, $data );
	}

	public function closeBill( $billId ){
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
				$this->modelBillReserve->edit( $billReserve->billReserveId, array(
					'status'	=> Model_Billing_Bill_Reserve::STATUS_BOOKED,
				) );
			}
			foreach( $billShares as $billShare ){
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
				$this->modelBillShare->edit( $billShare->billShareId, array(
					'status'	=> Model_Billing_Bill_Share::STATUS_BOOKED,
				) );
			}
			$this->modelBill->edit( $billId, array( 'status' => Model_Billing_Bill::STATUS_BOOKED ) );
			$this->env->getDatabase()->commit();
		}
		catch( Exception $e ){
			$this->env->getDatabase()->rollBack();
			$this->env->getMessenger()->noteFailure( $e->getMessage() );
		}
	}

	public function getBill( $billId ){
		return $this->modelBill->get( $billId );
	}

	public function getBills( $conditions, $orders = array(), $limits = array() ){
		return $this->modelBill->getAll( $conditions, $orders, $limits );
	}

	public function getBillCorporationTransactions( $billId, $conditions = array(), $orders = array(), $limits = array() ){
		$conditions	= array_merge( $conditions, array(
			'toType'	=> Model_Billing_Transaction::TYPE_CORPORATION,
			'relation'	=> '%|bill:'.$billId.'|%' ) );
		$orders		= $orders ? $orders : array( 'transactionId' => 'ASC' );
		return $this->modelTransaction->getAll( $conditions, $orders, $limits );
	}

	public function getBillExpense( $expenseId ){
		return $this->modelBillExpense->get( $expenseId );
	}

	public function getBillExpenses( $billId ){
		return $this->modelBillExpense->getAll( array( 'billId' => $billId ), array( 'billExpenseId' => 'ASC' ) );
	}

	public function getBillPersonTransactions( $billId, $conditions = array(), $orders = array(), $limits = array() ){
		$conditions	= array_merge( $conditions, array(
			'toType'	=> Model_Billing_Transaction::TYPE_PERSON,
			'relation'	=> '%|bill:'.$billId.'|%' ) );
		$orders		= $orders ? $orders : array( 'transactionId' => 'ASC' );
		return $this->modelTransaction->getAll( $conditions, $orders, $limits );
	}

	public function getBillReserve( $billReserveId ){
		$relation	= $this->modelBillReserve->get( $billReserveId );
		if( $relation )
			$relation->reserve	= $this->getReserve( $relation->reserveId );
		return $relation;
	}

	public function getBillReserves( $billId, $conditions = array(), $orders = array(), $limits = array() ){
		$conditions	= array_merge( array( 'billId' => $billId ), $conditions );
		$orders		= $orders ? $orders : array( 'billReserveId' => 'ASC' );
		$relations	= $this->modelBillReserve->getAll( $conditions, $orders, $limits );
		foreach( $relations as $relation )
			$relation->reserve	= $this->getReserve( $relation->reserveId );
		return $relations;
	}

	public function getBillShare( $shareId ){
		return $this->modelBillShare->get( $shareId );
	}

	public function getBillShares( $billId, $conditions = array(), $orders = array(), $limits = array() ){
		$conditions	= array_merge( array( 'billId' => $billId ), $conditions );
		$orders		= $orders ? $orders : array( 'billShareId' => 'ASC' );
		return $this->modelBillShare->getAll( $conditions, $orders, $limits );
	}

	public function getCorporation( $corporationId ){
		return $this->modelCorporation->get( $corporationId );
	}

	public function getCorporations( $conditions = array(), $orders = array(), $limits = array() ){
		return $this->modelCorporation->getAll( $conditions, $orders, $limits );
	}

	public function getCorporationExpenses( $corporationId, $conditions = array(), $orders = array(), $limits = array() ){
		$conditions	= array_merge( $conditions, array(
			'fromType'	=> Model_Billing_Transaction::TYPE_CORPORATION,
			'fromId'	=> $corporationId,
			'toType'	=> array(
				Model_Billing_Transaction::TYPE_EXPENSE,
				Model_Billing_Transaction::TYPE_NONE,
			),
			'relation'	=> '%|expense%'
		) );
		$orders		= $orders ? $orders : array( 'transactionId' => 'ASC' );
		return $this->modelTransaction->getAll( $conditions, $orders, $limits );
	}

	public function getCorporationPayins( $corporationId, $conditions = array(), $orders = array(), $limits = array() ){
		$conditions	= array_merge( $conditions, array(
			'fromType'	=> Model_Billing_Transaction::TYPE_PAYIN,
			'toType'	=> Model_Billing_Transaction::TYPE_CORPORATION,
			'toId'		=> $corporationId,
		) );
		$orders		= $orders ? $orders : array( 'transactionId' => 'ASC' );
		return $this->modelTransaction->getAll( $conditions, $orders, $limits );
	}

	public function getCorporationPayouts( $corporationId, $conditions = array(), $orders = array(), $limits = array() ){
		$conditions	= array_merge( $conditions, array(
			'fromType'	=> Model_Billing_Transaction::TYPE_CORPORATION,
			'fromId'	=> $corporationId,
			'toType'	=> Model_Billing_Transaction::TYPE_PAYOUT,
		) );
		$orders		= $orders ? $orders : array( 'transactionId' => 'ASC' );
		return $this->modelTransaction->getAll( $conditions, $orders, $limits );
	}

	public function getCorporationReserves( $corporationId, $conditions = array(), $orders = array(), $limits = array() ){
		$conditions		= array_merge( $conditions, array(
			'fromType'	=> array(
				Model_Billing_Transaction::TYPE_BILL,
				Model_Billing_Transaction::TYPE_RESERVE,
			),
			'toType'	=> Model_Billing_Transaction::TYPE_CORPORATION,
			'toId'		=> $corporationId,
		) );
		$orders		= $orders ? $orders : array( 'transactionId' => 'ASC' );
		return $this->modelTransaction->getAll( $conditions, $orders, $limits );
	}

	public function getExpense( $expenseId ){
		return $this->modelExpense->get( $expenseId );
	}

	public function getExpenses( $conditions = array(), $orders = array(), $limits = array() ){
		return $this->modelExpense->getAll( $conditions, $orders, $limits );
	}


	public function getPerson( $personId ){
		return $this->modelPerson->get( $personId );
	}

	public function getPersonBillShares( $personId, $conditions = array(), $orders = array(), $limits = array() ){
		$conditions	= array_merge( array( 'personId' => $personId ), $conditions );
		$orders		= $orders ? $orders : array( 'billShareId' => 'ASC' );
		return $this->modelBillShare->getAll( $conditions, $orders, $limits );
	}

	public function getPersonExpenses( $personId, $conditions = array(), $orders = array(), $limits = array() ){
		$conditions	= array_merge( $conditions, array(
			'fromType'	=> Model_Billing_Transaction::TYPE_PERSON,
			'fromId'	=> $personId,
			'toType'	=> array(
				Model_Billing_Transaction::TYPE_CORPORATION,
				Model_Billing_Transaction::TYPE_EXPENSE,
			),
			'relation'	=> '%|expense%'
		) );
		$orders		= $orders ? $orders : array( 'transactionId' => 'ASC' );
		return $this->modelTransaction->getAll( $conditions, $orders, $limits );
	}

	public function getPersonReserves( $personId, $conditions = array(), $orders = array(), $limits = array() ){
		$conditions	= array_merge( $conditions, array(
			'fromType'	=> array(
				Model_Billing_Transaction::TYPE_BILL,
				Model_Billing_Transaction::TYPE_RESERVE,
			),
			'toType'	=> Model_Billing_Transaction::TYPE_PERSON,
			'toId'		=> $personId,
		) );
		$orders		= $orders ? $orders : array( 'transactionId' => 'ASC' );
		return $this->modelTransaction->getAll( $conditions, $orders, $limits );
	}

	public function getPersonPayins( $personId, $conditions = array(), $orders = array(), $limits = array() ){
		$conditions	= array_merge( $conditions, array(
			'fromType'	=> Model_Billing_Transaction::TYPE_PAYIN,
			'toType'	=> Model_Billing_Transaction::TYPE_PERSON,
			'toId'	=> $personId,
		) );
		$orders		= $orders ? $orders : array( 'transactionId' => 'ASC' );
		return $this->modelTransaction->getAll( $conditions, $orders, $limits );
	}

	public function getPersonPayouts( $personId, $conditions = array(), $orders = array(), $limits = array() ){
		$conditions	= array_merge( $conditions, array(
			'fromType'	=> Model_Billing_Transaction::TYPE_PERSON,
			'fromId'	=> $personId,
			'toType'	=> Model_Billing_Transaction::TYPE_PAYOUT,
		) );
		$orders		= $orders ? $orders : array( 'transactionId' => 'ASC' );
		return $this->modelTransaction->getAll( $conditions, $orders, $limits );
	}

	public function getPersons( $conditions = array(), $orders = array(), $limits = array() ){
		return $this->modelPerson->getAll( $conditions, $orders, $limits );
	}

	public function getReserve( $reserveId ){
		return $this->modelReserve->get( $reserveId );
	}

	public function getReserves( $conditions = array(), $orders = array(), $limits = array() ){
		return $this->modelReserve->getAll( $conditions, $orders, $limits );
	}

	public function removeBillExpense( $billExpenseId ){
		$billExpense	= $this->modelBillExpense->get( $billExpenseId );
		$this->modelBillExpense->remove( $billExpenseId );
		$this->_updateBillReserves( $billExpense->billId );
		$this->_updateBillShares( $billExpense->billId );
		$this->_updateBillAssignedAmount( $billExpense->billId );
	}

	public function removeBillShare( $billShareId ){
		$billShare	= $this->modelBillShare->get( $billShareId );
		$this->modelBillShare->remove( $billShareId );
		$this->_updateBillAssignedAmount( $billShare->billId );
	}

	public function removeBillReserve( $billReserveId ){
		$billReserve	= $this->modelBillReserve->get( $billReserveId );
		$this->modelBillReserve->remove( $billReserveId );
		$this->_updateBillReserves( $billReserve->billId );
		$this->_updateBillShares( $billReserve->billId );
		$this->_updateBillAssignedAmount( $billReserve->billId );
	}

	protected function _getBillAmountAfterExpensesAndReserves( $billId ){
		$bill		= $this->getBill( $billId );
		$amount		= (float) $bill->amountNetto;
		$expenses	= $this->getBillExpenses( $billId );
		foreach( $expenses as $expense ){
			$amount	-= (float) $expense->amount;
		}
		$reserves	= $this->getBillReserves( $billId );
		$substract	= 0;
		foreach( $reserves as $reserve ){
			$substract	+= (float) $reserve->amount;
		}
		return $amount - $substract;
	}

	public function _updateBillAssignedAmount( $billId ){
		$bill		= $this->getBill( $billId );
		$amount		= $this->_getBillAmountAfterExpensesAndReserves( $billId );
		$billShares	= $this->getBillShares( $billId );
		foreach( $billShares as $billShare ){
			if( $billShare->percent )
				$amount -= $billShare->amount;
		}
		$this->modelBill->edit( $billId, array(
			'amountAssigned'	=> $bill->amountNetto - $amount
		) );
	}

	public function _updateBillReserves( $billId ){
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
			else if( $reserve->amount )											//  otherwise absolute reserve
				$reservesAmount		+= $reserve->amount;						//  then sum amount
		}
		$reducedAmount	= $amount - $reservesAmount;							//  subtract absolute reserves
		$reducedAmount	= $reducedAmount / ( 1 + $reservesPercent / 100 );		//  real core value depending on relative reserves

		foreach( $reserves as $reserve ){										//  iterate reserves
			if( (float) $reserve->percent ){									//  if relative reserve
				$this->modelBillReserve->edit( $reserve->billReserveId, array(	//  calculate absolute amount
					'amount'	=> $reducedAmount * $reserve->percent / 100		//  by percent WITHIN core value
				) );
			}
		}
	}

	public function _updateBillShares( $billId ){
		$bill		= $this->getBill( $billId );
		$amount		= $this->_getBillAmountAfterExpensesAndReserves( $billId );
		$billShares	= $this->getBillShares( $billId );
		foreach( $billShares as $billShare ){
			if( $billShare->percent ){
				$this->modelBillShare->edit( $billShare->billShareId, array(
					'amount'	=> $amount * $billShare->percent / 100
				) );
			}
		}
	}
}
