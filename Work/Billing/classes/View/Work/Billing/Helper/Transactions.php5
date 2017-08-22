<?php
class View_Work_Billing_Helper_Transactions{

	const MODE_NONE			= 0;
	const MODE_CORPORATION	= 1;
	const MODE_PERSON		= 2;

	protected $heading				= 'Transaktionen';
	protected $mode					= 0;
	protected $transactions;

	public function __construct( $env ){
		$this->env	= $env;
		$this->logic	= new Logic_Billing( $this->env );
		$this->modelBill	= new Model_Billing_Bill( $this->env );
		$this->modelExpense	= new Model_Billing_Expense( $this->env );
	}

	public function setHeading( $heading ){
		$this->heading	= $heading;
	}

	public function setMode( $mode ){
		$this->mode	= $mode;
	}

	public function setTransactions( $transactions ){
		$this->transactions	= $transactions;
	}

	protected function transformRelationToTitle( $relation ){
		$parts	= array();
		if( preg_match( '/\|billShare:([0-9]+)\|/', $relation ) ){
			$billShareId	= preg_replace( '/\|billShare:([0-9]+)\|/', '\\1', $relation );
			$billShare		= $this->logic->getBillShare( $billShareId );
			$bill			= $this->logic->getBill( $billShare->billId );
			$linkBill		= UI_HTML_Tag::create( 'a', $bill->number.': '.$bill->title, array( 'href' => './work/billing/bill/edit/'.$bill->billId ) );
			$relation		= 'Anteil aus Rechnung '.$linkBill;
		}
		else if( preg_match( '/\|billReserve:([0-9]+)\|/', $relation ) ){
			$billReserveId	= preg_replace( '/\|billReserve:([0-9]+)\|/', '\\1', $relation );
			$billReserve	= $this->logic->getBillReserve( $billReserveId );
			$bill			= $this->logic->getBill( $billReserve->billId );
			$linkReserve	= UI_HTML_Tag::create( 'a', $billReserve->title, array( 'href' => './work/billing/reserve/edit/'.$billReserve->reserveId ) );
			$linkBill		= UI_HTML_Tag::create( 'a', $bill->number.': '.$bill->title, array( 'href' => './work/billing/bill/edit/'.$bill->billId ) );
			$relation		= 'Rücklage '.$linkReserve.' aus Rechnung '.$linkBill;
		}
/*		else if( preg_match( '/^bill:([0-9]+)$/', $relation ) ){
			$id			= preg_replace( '/^bill:([0-9]+)$/', '\\1', $relation );
			$bill		= $this->modelBill->get( $id );
			$link		= UI_HTML_Tag::create( 'a', 'RNr.'.$bill->number, array( 'href' => './work/billing/bill/edit/'.$id ) );
			$relation	= $link;
		}*/
		else if( preg_match( '/\|personExpense:[0-9]+\|/', $relation ) ){
			$id			= preg_replace( '/\|personExpense:([0-9]+)\|/', '\\1', $relation );
			$personExpense	= $this->logic->getPersonExpense( $id );
			$link		= $personExpense->title;
			if( $personExpense->expenseId && 0  ){
				$expense	= $this->logic->getExpense( $personExpense->expenseId );
				$link		= $expense->title;
			}
			$relation	= $link;
		}
		else if( preg_match( '/\|expense:([0-9]+)\|/', $relation ) ){
			$id			= preg_replace( '/\|expense:([0-9]+)\|/', '\\1', $relation );
			$expense	= $this->modelExpense->get( $id );
			$link		= UI_HTML_Tag::create( 'a', $expense->title, array( 'href' => './work/billing/expense/edit/'.$id ) );
			$relation	= $link;
		}
		else if( preg_match( '/\|personPayout:([0-9]+)\|/', $relation ) ){
			$id			= preg_replace( '/\|personPayout:([0-9]+)\|/', '\\1', $relation );
			$payout		= $this->logic->getPersonPayout( $id );
			$relation	= '<small>Auszahlung:</small> '.$payout->title;
		}
		else if( preg_match( '/\|personPayin:([0-9]+)\|/', $relation ) ){
			$id			= preg_replace( '/\|personPayin:([0-9]+)\|/', '\\1', $relation );
			$payin		= $this->logic->getPersonPayin( $id );
			$relation	= '<small>Einzahlung:</small> '.$payin->title;
		}
		else if( preg_match( '/\|corporationPayout:([0-9]+)\|/', $relation ) ){
			$id			= preg_replace( '/\|corporationPayout:([0-9]+)\|/', '\\1', $relation );
			$payout		= $this->logic->getCorporationPayout( $id );
			$relation	= '<small>Auszahlung:</small> '.$payout->title;
		}
		else if( preg_match( '/\|corporationPayin:([0-9]+)\|/', $relation ) ){
			$id			= preg_replace( '/\|corporationPayin:([0-9]+)\|/', '\\1', $relation );
			$payin		= $this->logic->getCorporationPayin( $id );
			$relation	= '<small>Einzahlung:</small> '.$payin->title;
		}
		return $relation;
	}

	public function render(){
		if( !$this->transactions )
			return;

		$modelPerson		= new Model_Billing_Person( $this->env );
		$modelCorporation	= new Model_Billing_Corporation( $this->env );

		foreach( $this->transactions as $transaction ){
			$title	= $this->transformRelationToTitle( $transaction->relation );

			if( $this->mode === static::MODE_PERSON ){
				$person	= $modelPerson->get( $transaction->personId );
				$target	= UI_HTML_Tag::create( 'a', $person->firstname.' '.$person->surname, array(
					'href'	=> './work/billing/person/edit/'.$person->personId,
				) );
			}
			else if( $this->mode === static::MODE_CORPORATION ){
				$corporation	= $modelCorporation->get( $transaction->corporationId );
				$target	= UI_HTML_Tag::create( 'a', $corporation->title, array(
					'href'	=> './work/billing/corporation/edit/'.$corporation->corporationId,
				) );
//				$target	= UI_HTML_Tag::create( 'small', $target );
			}
			else{
				$target	= '-';
			}

			$date	= date( "d.m.Y", strtotime( $transaction->dateBooked ) );
			$date	= UI_HTML_Tag::create( 'small', $date );

			$list[]	= UI_HTML_Tag::create( 'tr', array(
				UI_HTML_Tag::create( 'td', $title ),
				UI_HTML_Tag::create( 'td', $target ),
				UI_HTML_Tag::create( 'td', number_format( round( $transaction->amount, 2 ), 2 ).'&nbsp;&euro;', array( 'class' => 'cell-number' ) ),
				UI_HTML_Tag::create( 'td', $date, array( 'class' => 'cell-number' ) ),
			), array( 'class' => $transaction->amount > 0 ? 'success' : 'error' ) );
		}
		$colgroup	= UI_HTML_Elements::ColumnGroup( array( '', '160', '80', '80' ) );
		$thead	= UI_HTML_Tag::create( 'thread', UI_HTML_Elements::TableHeads( array( 'Vorgang', 'Empfänger', 'Betrag', 'Datum' ) ) );
		$tbody	= UI_HTML_Tag::create( 'tbody', $list );
		$list = UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-fixed table-condensed' ) );

		return '
		<div class="content-panel">
			<h3>'.$this->heading.'</h3>
			<div class="content-panel-inner">
				'.$list.'
<!--				<div class="buttonbar">
				</div>-->
			</div>
		</div>';
	}
}
