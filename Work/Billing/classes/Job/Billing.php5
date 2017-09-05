<?php
class Job_Billing extends Job_Abstract{

	public function __onInit(){
		$this->logic	= new Logic_Billing( $this->env );
	}

	public function bookExpenses(){
		$timestamp	= time();
		$dayOfWeek	= (int) date( 'w', $timestamp );
		$dayOfMonth	= (int) date( 'j', $timestamp );
		$dayOfYear	= (int) date( 'z', $timestamp ) + 1;
		$total		= 0;
		if( $dayOfYear === 1 ){
			$expenses	= $this->logic->getExpenses( array(
				'status'	=> Model_Billing_Expense::STATUS_ACTIVE,
				'frequency'	=> Model_Billing_Expense::FREQUENCY_YEARLY,
			) );
			if( $expenses ){
				$total	+= count( $expenses );
				$this->out( "Booking ".count( $expenses )." yearly expenses..." );
				foreach( $expenses as $expense )
					$this->_bookExpense( $expense );
			}
		}
		if( $dayOfMonth === 1 ){
			$expenses	= $this->logic->getExpenses( array(
				'status'	=> Model_Billing_Expense::STATUS_ACTIVE,
				'frequency'	=> Model_Billing_Expense::FREQUENCY_MONTHLY,
			) );
			if( $expenses ){
				$total	+= count( $expenses );
				$this->out( "Booking ".count( $expenses )." monthly expenses..." );
				foreach( $expenses as $expense )
					$this->_bookExpense( $expense );
			}
		}
		if( $dayOfWeek === 1 ){
			$expenses	= $this->logic->getExpenses( array(
				'status'	=> Model_Billing_Expense::STATUS_ACTIVE,
				'frequency'	=> Model_Billing_Expense::FREQUENCY_WEEKLY,
			) );
			if( $expenses ){
				$total	+= count( $expenses );
				$this->out( "Booking ".count( $expenses )." weekly expenses..." );
				foreach( $expenses as $expense )
					$this->_bookExpense( $expense );
			}
		}
		$expenses	= $this->logic->getExpenses( array(
			'status'	=> Model_Billing_Expense::STATUS_ACTIVE,
			'frequency'	=> Model_Billing_Expense::FREQUENCY_DAILY
		) );
		if( $expenses ){
			$total	+= count( $expenses );
			$this->out( "Booking ".count( $expenses )." daily expenses..." );
			foreach( $expenses as $expense )
				$this->_bookExpense( $expense );
		}
		if( $total )
			$this->out( 'Booked '.$total.' expenses.' );
	}

	protected function _bookExpense( $expense ){
		$title	= $expense->title;
		if( preg_match( '/\[date\.Y\]/', $title ) )
			$title	= preg_replace( '/\[date.Y\]/', date( 'Y' ), $title );
		if( preg_match( '/\[date.m\]/', $title ) )
			$title	= preg_replace( '/\[date.m\]/', date( 'm' ), $title );
		if( preg_match( '/\[date.d\]/', $title ) )
			$title	= preg_replace( '/\[date.d\]/', date( 'd' ), $title );

		$fromType	= Model_Billing_Transaction::TYPE_NONE;
		$fromId		= 0;
		$toType		= Model_Billing_Transaction::TYPE_NONE;
		$toId		= 0;
		if( $expense->fromCorporationId ){
			$fromType	= Model_Billing_Transaction::TYPE_CORPORATION;
			$fromId		= $expense->fromCorporationId;
		}
		else if( $expense->fromPersonId ){
			$fromType	= Model_Billing_Transaction::TYPE_PERSON;
			$fromId		= $expense->fromPersonId;
		}
		if( $expense->toCorporationId ){
			$toType	= Model_Billing_Transaction::TYPE_CORPORATION;
			$toId	= $expense->toCorporationId;
		}
		else if( $expense->toPersonId ){
			$toType	= Model_Billing_Transaction::TYPE_PERSON;
			$toId	= $expense->toPersonId;
		}
		return $this->logic->addTransaction(
			$expense->amount,
			$fromType,
			$fromId,
			$toType,
			$toId,
			'|expense:'.$expense->expenseId.'|',
			$title
		);
	}
}
?>
