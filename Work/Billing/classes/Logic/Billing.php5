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
		$this->modelCorporationPayout		= new Model_Billing_Corporation_Payout( $this->env );
		$this->modelCorporationReserve		= new Model_Billing_Corporation_Reserve( $this->env );
		$this->modelCorporationTransaction	= new Model_Billing_Corporation_Transaction( $this->env );
		$this->modelPerson					= new Model_Billing_Person( $this->env );
		$this->modelPersonTransaction		= new Model_Billing_Person_Transaction( $this->env );
		$this->modelPersonPayin				= new Model_Billing_Person_Payin( $this->env );
		$this->modelPersonPayout			= new Model_Billing_Person_Payout( $this->env );
//		$this->modelPersonPayoutBill		= new Model_Billing_Person_Payout_Bill( $this->env );
		$this->modelReserve					= new Model_Billing_Reserve( $this->env );
		$this->modelExpense					= new Model_Billing_Expense( $this->env );
		$this->modelPersonExpense			= new Model_Billing_Person_Expense( $this->env );
//		$this->modelCorporationExpense		= new Model_Billing_Expense( $this->env );
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
			'percent'	=> $percent,
			'amount'	=> $amount,
		) );
//		$this->_updatePayoutAmountByBill( $billId );
		$this->_updateBillAssignedAmount( $billId );
		return $shareId;
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

	public function addPersonExpense( $personId, $expenseId = 0, $amount, $title ){
		$this->modelPersonExpense->add( array(
			'personId'		=> $personId,
			'expenseId'		=> $expenseId,
			'status'		=> Model_Billing_Person_Expense::STATUS_NEW,
			'amount'		=> $amount,
			'title'			=> $title,
			'dateBooked'	=> date( 'Y-m-d' ),
		) );
		$this->_bookPersonExpenses( $personId );
	}

	public function addPersonPayin( $status, $personId, $amount ){
		$payoutId	= $this->modelPersonPayin->add( array(
			'status'		=> $status,
			'personId'		=> $personId,
			'amount'		=> $amount,
			'dateBooked'	=> date( 'Y-m-d' ),
		) );
		$this->_bookPersonPayins( $personId );
		return $payoutId;
	}

	public function addPersonPayout( $status, $personId, $amount ){
		$payoutId	= $this->modelPersonPayout->add( array(
			'status'		=> $status,
			'personId'		=> $personId,
			'amount'		=> $amount,
			'dateBooked'	=> date( 'Y-m-d' ),
		) );
		$this->_bookPersonPayouts( $personId );
		return $payoutId;
	}

	public function addPersonPayoutBill( $payoutId, $billId ){
		$this->modelPersonPayoutBill->add( array(
			'payoutId'	=> $payoutId,
			'billId'	=> $billId,
		) );
		$this->_updatePersonPayoutAmountByBill( $billId );
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
//		$this->_updatePayoutAmountByBill( $billId );
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
				if( !(float)$billShare->percent )
					$billShare->percent	= $billShare->amount / $amount * 100;

			$billReserves	= $this->getBillReserves( $billId );
			foreach( $billReserves as $billReserve ){
				if( $billReserve->corporationId ){
					if( $billReserve->personalize ){
						foreach( $billShares as $billShare ){
							$this->modelCorporationReserve->add( array(
								'corporationId'	=> $billReserve->corporationId,
								'reserveId'		=> $billReserve->reserveId,
								'billId'		=> $billId,
								'personId'		=> $billShare->personId,
								'status'		=> Model_Billing_Corporation_Reserve::STATUS_BOOKED,
								'amount'		=> $billReserve->amount * $billShare->percent / 100,
								'dateBooked'	=> date( 'Y-m-d' ),
							) );
							$this->modelCorporationTransaction->add( array(
								'corporationId'	=> $billReserve->corporationId,
								'status'		=> Model_Billing_Corporation_Transaction::STATUS_NEW,
								'amount'		=> $billReserve->amount * $billShare->percent / 100,
								'relation'		=> '|billReserve:'.$billReserve->billReserveId.'|bill:'.$billReserve->billId.'|',
								'dateBooked'	=> date( 'Y-m-d' ),
							) );
						}
					}
					else{
						$this->modelCorporationReserve->add( array(
							'corporationId'	=> $billReserve->corporationId,
							'reserveId'		=> $billReserve->reserveId,
							'billId'		=> $billId,
							'personId'		=> 0,
							'status'		=> Model_Billing_Corporation_Reserve::STATUS_BOOKED,
							'amount'		=> $billReserve->amount,
							'dateBooked'	=> date( 'Y-m-d' ),
						) );
						$this->modelCorporationTransaction->add( array(
							'corporationId'	=> $billReserve->corporationId,
							'status'		=> Model_Billing_Corporation_Transaction::STATUS_NEW,
							'amount'		=> $billReserve->amount,
							'relation'		=> '|billReserve:'.$billReserve->billReserveId.'|bill:'.$billReserve->billId.'|',
							'dateBooked'	=> date( 'Y-m-d' ),
						) );
					}
				}
				else{
					foreach( $billShares as $billShare ){
						$this->modelPersonTransaction->add( array(
							'personId'		=> $billShare->personId,
							'status'		=> Model_Billing_Person_Transaction::STATUS_NEW,
							'amount'		=> $billReserve->amount * $billShare->percent / 100,
							'relation'		=> '|billReserve:'.$billReserve->billReserveId.'|bill:'.$billReserve->billId.'|',
							'dateBooked'	=> date( 'Y-m-d' ),
						) );
					}
				}
				$this->modelBillReserve->edit( $billReserve->billReserveId, array(
					'status'		=> Model_Billing_Bill_Reserve::STATUS_BOOKED,
				) );
			}
			foreach( $billShares as $billShare ){
				$this->modelPersonTransaction->add( array(
					'personId'		=> $billShare->personId,
					'status'		=> Model_Billing_Person_Transaction::STATUS_NEW,
					'amount'		=> $billShare->amount,
					'relation'		=> '|billShare:'.$billShare->billShareId.'|bill:'.$billShare->billId.'|',
					'dateBooked'	=> date( 'Y-m-d' ),
				) );
				$this->modelBillShare->edit( $billShare->billShareId, array(
					'status'	=> Model_Billing_Bill_Share::STATUS_BOOKED,
				) );
				$this->_realizePersonTransactions( $billShare->personId );
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
		$conditions	= array_merge( array( 'relation' => '%|bill:'.$billId.'|%' ), $conditions );
		$orders		= $orders ? $orders : array( 'corporationTransactionId' => 'ASC' );
		return $this->modelCorporationTransaction->getAll( $conditions, $orders, $limits );
	}

	public function getBillExpense( $expenseId ){
		return $this->modelBillExpense->get( $expenseId );
	}

	public function getBillExpenses( $billId ){
		return $this->modelBillExpense->getAll( array( 'billId' => $billId ), array( 'billExpenseId' => 'ASC' ) );
	}

	public function getBillPersonTransactions( $billId, $conditions = array(), $orders = array(), $limits = array() ){
		$conditions	= array_merge( array( 'relation' => '%|bill:'.$billId.'|%' ), $conditions );
		$orders		= $orders ? $orders : array( 'personTransactionId' => 'ASC' );
		return $this->modelPersonTransaction->getAll( $conditions, $orders, $limits );
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

	public function getCorporationPayout( $payoutId ){
		return $this->modelCorporationPayout->get( $payoutId );
	}

	public function getCorporationPayouts( $corporationId, $conditions = array(), $orders = array(), $limits = array() ){
		$conditions	= array_merge( array( 'corporationId' => $corporationId ), $conditions );
		$orders		= $orders ? $orders : array( 'corporationPayoutId' => 'ASC' );
		return $this->modelCorporationPayout->getAll( $conditions, $orders, $limits );
	}


	public function getCorporationReserves( $corporationId, $conditions = array(), $orders = array(), $limits = array() ){
		$conditions	= array_merge( array( 'corporationId' => $corporationId ), $conditions );
		$orders		= $orders ? $orders : array( 'corporationReserveId' => 'ASC' );
		return $this->modelCorporationReserve->getAll( $conditions, $orders, $limits );
	}


	public function getCorporationTransactions( $corporationId, $conditions = array(), $orders = array(), $limits = array() ){
		$conditions	= array_merge( array( 'corporationId' => $corporationId ), $conditions );
		$orders		= $orders ? $orders : array( 'corporationTransactionId' => 'ASC' );
		return $this->modelCorporationTransaction->getAll( $conditions, $orders, $limits );
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

	public function getPersonExpense( $expenseId ){
		return $this->modelPersonExpense->get( $expenseId );
	}

	public function getPersonExpenses( $personId, $conditions = array(), $orders = array(), $limits = array() ){
		$conditions	= array_merge( array( 'personId' => $personId ), $conditions );
		$orders		= $orders ? $orders : array( 'personExpenseId' => 'ASC' );
		return $this->modelPersonExpense->getAll( $conditions, $orders, $limits );
	}

	public function getPersonPayin( $payinId ){
		return $this->modelPersonPayin->get( $payinId );
	}

	public function getPersonPayins( $personId, $conditions = array(), $orders = array(), $limits = array() ){
		$conditions	= array_merge( array( 'personId' => $personId ), $conditions );
		$orders		= $orders ? $orders : array( 'personPayinId' => 'ASC' );
		return $this->modelPersonPayin->getAll( $conditions, $orders, $limits );
	}

	public function getPersonPayout( $payoutId ){
		return $this->modelPersonPayout->get( $payoutId );
	}

	public function getPersonPayouts( $personId, $conditions = array(), $orders = array(), $limits = array() ){
		$conditions	= array_merge( array( 'personId' => $personId ), $conditions );
		$orders		= $orders ? $orders : array( 'personPayoutId' => 'ASC' );
		return $this->modelPersonPayout->getAll( $conditions, $orders, $limits );
	}

	public function getPersons( $conditions = array(), $orders = array(), $limits = array() ){
		return $this->modelPerson->getAll( $conditions, $orders, $limits );
	}

	public function getPersonTransactions( $personId, $conditions = array(), $orders = array() ){
		$conditions	= array_merge( array( 'personId' => $personId ), $conditions );
		$orders		= $orders ? $orders : array( 'personTransactionId' => 'ASC' );
		return $this->modelPersonTransaction->getAll( $conditions, $orders );
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

	public function _updatePayoutAmountByBill( $billId ){
		$payoutBills	= $this->modelPersonPayoutBill->getAll( array( 'billId' => $billId ) );
		$payoutIds		= array();
		foreach( $payoutBills as $payoutBill )
			$payoutIds[]	= $payoutBill->payoutId;

		if( !$payoutIds )
			return;
		$payouts	= $this->modelPersonPayout->getAll( array( 'payoutId' => $payoutIds ) );
		foreach( $payouts as $payout ){
			$bills	= $this->modelPersonPayoutBill->getAll( array( 'payoutId' => $payout->payoutId ) );
			$amount	= 0;
			foreach( $bills as $bill ){
				$shares	= $this->modelBillShare->getAll( array(
					'billId'	=> $bill->billId,
					'personId'	=> $payout->personId,
				) );
				foreach( $shares as $share )
					$amount	+= $share->amount;
			}
			$this->modelPersonPayout->edit( $payout->payoutId, array( 'amount' => $amount ) );
		}
	}

	public function _bookPersonExpenses( $personId ){
		$expenses	= $this->getPersonExpenses( $personId, array( 'status' => Model_Billing_Person_Expense::STATUS_NEW ) );
		$this->env->getDatabase()->beginTransaction();
		try{
			foreach( $expenses as $expense ){
				$relation	= '|personExpense:'.$expense->personExpenseId.'|';
				if( $expense->expenseId )
					$relation	.= 'expense:'.$expense->expenseId.'|';
				$this->modelPersonTransaction->add( array(
					'status'		=> Model_Billing_Person_Transaction::STATUS_NEW,
					'personId'		=> $expense->personId,
					'relation'		=> $relation,
					'amount'		=> -1 * $expense->amount,
					'dateBooked'	=> $expense->dateBooked,
				) );
				$this->modelPersonExpense->edit( $expense->personExpenseId, array(
					'status' => Model_Billing_Person_Expense::STATUS_BOOKED,
				) );
			}
			$this->env->getDatabase()->commit();
			$this->_realizePersonTransactions( $personId );
		}
		catch( Exception $e ){
			$this->env->getDatabase()->rollBack();
			$this->env->getMessenger()->noteFailure( $e->getMessage() );
		}
	}

	public function _bookPersonPayins( $personId ){
		$payins		= $this->getPersonPayins( $personId, array( 'status' => Model_Billing_Person_Payin::STATUS_NEW ) );
		$this->env->getDatabase()->beginTransaction();
		try{
			foreach( $payins as $payin ){
				$this->modelPersonTransaction->add( array(
					'status'		=> Model_Billing_Person_Transaction::STATUS_NEW,
					'personId'		=> $payin->personId,
					'relation'		=> '|personPayin:'.$payin->personPayinId.'|',
					'amount'		=> $payin->amount,
					'dateBooked'	=> $payin->dateBooked,
				) );
				$this->modelPersonPayin->edit( $payin->personPayinId, array(
					'status' => Model_Billing_Person_Payin::STATUS_BOOKED,
				) );
			}
			$this->env->getDatabase()->commit();
			$this->_realizePersonTransactions( $personId );
		}
		catch( Exception $e ){
			$this->env->getDatabase()->rollBack();
			$this->env->getMessenger()->noteFailure( $e->getMessage() );
		}
	}

	public function _bookPersonPayouts( $personId ){
		$payouts	= $this->getPersonPayouts( $personId, array( 'status' => Model_Billing_Person_Payout::STATUS_NEW ) );
		$this->env->getDatabase()->beginTransaction();
		try{
			foreach( $payouts as $payout ){
				$this->modelPersonTransaction->add( array(
					'status'		=> Model_Billing_Person_Transaction::STATUS_NEW,
					'personId'		=> $payout->personId,
					'relation'		=> '|personPayout:'.$payout->personPayoutId.'|',
					'amount'		=> '-'.$payout->amount,
					'dateBooked'	=> $payout->dateBooked,
				) );
				$this->modelPersonPayout->edit( $payout->personPayoutId, array(
					'status' => Model_Billing_Person_Payout::STATUS_BOOKED,
				) );
			}
			$this->env->getDatabase()->commit();
			$this->_realizePersonTransactions( $personId );
		}
		catch( Exception $e ){
			$this->env->getDatabase()->rollBack();
			$this->env->getMessenger()->noteFailure( $e->getMessage() );
		}
	}

	public function _bookCorporationPayouts( $corporationId ){
		$payouts	= $this->getCorporationPayouts( $corporationId, array( 'status' => Model_Billing_Corporation_Payout::STATUS_NEW ) );
		$this->env->getDatabase()->beginTransaction();
		try{
			foreach( $payouts as $payout ){
				$this->modelCorporationTransaction->add( array(
					'status'		=> Model_Billing_Corporation_Transaction::STATUS_NEW,
					'corporationId'	=> $payout->corporationId,
					'relation'		=> '|corporationPayout:'.$payout->corporationPayoutId.'|',
					'amount'		=> '-'.$payout->amount,
					'dateBooked'	=> $payout->dateBooked,
				) );
				$this->modelCorporationPayout->edit( $payout->corporationPayoutId, array(
					'status' => Model_Billing_Corporation_Payout::STATUS_BOOKED,
				) );
			}
			$this->env->getDatabase()->commit();
			$this->_realizeCorporationTransactions( $corporationId );
		}
		catch( Exception $e ){
			$this->env->getDatabase()->rollBack();
			$this->env->getMessenger()->noteFailure( $e->getMessage() );
		}
	}

	protected function _getBillAmountAfterExpensesAndReserves( $billId ){
		$bill		= $this->getBill( $billId );
		$amount		= (float) $bill->amountNetto;
		$expenses	= $this->getBillExpenses( $billId );
		foreach( $expenses as $expense ){
			$amount	-= $expense->amount;
		}
		$reserves	= $this->getBillReserves( $billId );
		$substract	= 0;
		foreach( $reserves as $reserve ){
			if( $reserve->percent )
				$substract	+= $amount * $reserve->percent / 100;
			else
				$substract	+= $reserve->amount;
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
		foreach( $reserves as $reserve ){
			if( $reserve->percent ){
				$this->modelBillReserve->edit( $reserve->billReserveId, array(
					'amount'	=> $amount * $reserve->percent / 100
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

	public function _realizePersonTransactions( $personId ){
		$person		= $this->modelPerson->get( $personId );
		$conditions		= array(
			'personId'	=> $personId,
			'status' 	=> Model_Billing_Person_Transaction::STATUS_NEW
		);
		$this->env->getDatabase()->beginTransaction();
		try{
			$transactions	= $this->modelPersonTransaction->getAll( $conditions );
			foreach( $transactions as $transaction ){
				$person->balance += $transaction->amount;
				$this->modelPersonTransaction->edit( $transaction->personTransactionId, array(
					'status'	=> Model_Billing_Person_Transaction::STATUS_BOOKED,
				) );
			}
			$this->modelPerson->edit( $person->personId, array(
				'balance'	=> $person->balance,
			) );
			$this->env->getDatabase()->commit();
		}
		catch( Exception $e ){
			$this->env->getDatabase()->rollBack();
			$this->env->getMessenger()->noteFailure( $e->getMessage() );
		}
	}

	public function _realizeCorporationTransactions( $corporationId ){
		$corporation	= $this->modelCorporation->get( $corporationId );
		$conditions		= array(
			'corporationId'	=> $corporationId,
			'status' 		=> Model_Billing_Corporation_Transaction::STATUS_NEW
		);
		$this->env->getDatabase()->beginTransaction();
		try{
			$transactions	= $this->modelCorporationTransaction->getAll( $conditions );
			foreach( $transactions as $transaction ){
				$corporation->balance += $transaction->amount;
				$this->modelCorporationTransaction->edit( $transaction->corporationTransactionId, array(
					'status'	=> Model_Billing_Corporation_Transaction::STATUS_BOOKED,
				) );
			}
			$this->modelCorporation->edit( $corporation->corporationId, array(
				'balance'	=> $corporation->balance,
			) );
			$this->env->getDatabase()->commit();
		}
		catch( Exception $e ){
			$this->env->getDatabase()->rollBack();
			$this->env->getMessenger()->noteFailure( $e->getMessage() );
		}
	}


}
