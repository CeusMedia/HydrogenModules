<?php
class Job_Billing extends Job_Abstract{

	protected $pathLocks	= 'config/locks/';

	public function __onInit(){
//		$this->options	= $this->env->getConfig()->getAll( 'module.resource_cache.', TRUE );
		$this->logic	= new Logic_Billing( $this->env );
	}

	public function bookExpenses(){
		$dayOfMonth	= (int) date( 'j' );

		$dayOfMonth	= 1;

		$modelCorporation				= new Model_Billing_Corporation( $this->env );
		$modelCorporationTransaction	= new Model_Billing_Corporation_Transaction( $this->env );
		$modelPerson					= new Model_Billing_Person( $this->env );
		$modelPersonTransaction			= new Model_Billing_Person_Transaction( $this->env );

		if( $dayOfMonth === 1 ){
			$expenses	= $this->logic->getExpenses( array( 'frequency' => 3 ) );
			foreach( $expenses as $expense ){
				if( $expense->corporationId ){
					$modelCorporationTransaction->add( array(
						'status'		=> Model_Billing_Corporation_Transaction::STATUS_BOOKED,
						'corporationId'	=> $expense->corporationId,
						'relation'		=> '|expense:'.$expense->expenseId.'|',
						'amount'		=> -1 * $expense->amount,
						'dateBooked'	=> date( 'Y-m-d' ),
					) );
					$corporation		= $this->logic->getCorporation( $expense->corporationId );
					$modelCorporation->edit( $expense->corporationId, array(
						'balance'	=> $corporation->balance - $expense->amount,
					) );
				}
				else if( $expense->personId ){
					$title	= $expense->title;
					if( preg_match( '/\[date\.Y\]/', $title ) )
						$title	= preg_replace( '/\[date.Y\]/', date( 'Y' ), $title );
					if( preg_match( '/\[date.m\]/', $title ) )
						$title	= preg_replace( '/\[date.m\]/', date( 'm' ), $title );
					$this->logic->addPersonExpense(
						$expense->personId,
						$expense->expenseId,
						$expense->amount,
						$title
					);
/*					$modelPersonTransaction->add( array(
						'status'		=> Model_Billing_Person_Transaction::STATUS_BOOKED,
						'personId'		=> $expense->personId,
						'relation'		=> 'expense:'.$expense->expenseId,
						'amount'		=> -1 * $expense->amount,
						'dateBooked'	=> date( 'Y-m-d' ),
					) );
					$person		= $this->logic->getPerson( $expense->personId );
					$modelPerson->edit( $expense->personId, array(
						'balance'	=> $person->balance - $expense->amount,
					) );*/
				}
			}
		}
		$this->out( 'test' );
	}
}
?>
