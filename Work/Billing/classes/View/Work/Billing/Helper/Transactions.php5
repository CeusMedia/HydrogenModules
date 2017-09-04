<?php
class View_Work_Billing_Helper_Transactions{

	const MODE_NONE			= 0;
	const MODE_CORPORATION	= 1;
	const MODE_PERSON		= 2;

	protected $heading				= 'Transaktionen';
	protected $mode					= 0;
	protected $transactions;
	protected $filterPrefix;
	protected $filterUrl;

	public function __construct( $env ){
		$this->env	= $env;
		$this->logic	= new Logic_Billing( $this->env );
		$this->modelBill	= new Model_Billing_Bill( $this->env );
		$this->modelExpense	= new Model_Billing_Expense( $this->env );
	}

	public function setFilterPrefix( $prefix ){
		$this->filterPrefix	= $prefix;
	}

	public function setFilterUrl( $url ){
		$this->filterUrl	= $url;
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
			if( $personExpense->expenseId ){
				$expense	= $this->logic->getExpense( $personExpense->expenseId );
				$link		= UI_HTML_Tag::create( 'a', 'Regelausgabe '.$expense->title, array(
					'href' => './work/billing/expense/edit/'.$personExpense->expenseId
				) );
			}
			else{
				$link		= '<small>Ausgabe:</small> '.$personExpense->title;
			}
			$relation	= $link;
		}
		else if( preg_match( '/\|corporationExpense:[0-9]+\|/', $relation ) ){
			$id			= preg_replace( '/\|corporationExpense:([0-9]+)\|/', '\\1', $relation );
			$corporationExpense	= $this->logic->getCorporationExpense( $id );
			if( $corporationExpense->expenseId ){
				$expense	= $this->logic->getExpense( $corporationExpense->expenseId );
				$link		= UI_HTML_Tag::create( 'a', 'Regelausgabe '.$expense->title, array(
					'href' => './work/billing/expense/edit/'.$corporationExpense->expenseId
				) );
			}
			else{
				$link		= '<small>Ausgabe:</small> '.$corporationExpense->title;
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

		$modelPerson		= new Model_Billing_Person( $this->env );
		$modelCorporation	= new Model_Billing_Corporation( $this->env );

		$iconPerson		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-user' ) );
		$iconCompany	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-building-o' ) );

		$list	= UI_HTML_Tag::create( 'div', UI_HTML_Tag::create( 'em', 'Keine gefunden.', array( 'class' => 'muted' ) ), array( 'class' => 'alert alert-info' ) );
		if( $this->transactions ){
			$list	= array();
			foreach( $this->transactions as $transaction ){
				$title	= $this->transformRelationToTitle( $transaction->relation );

				if( $this->mode === static::MODE_PERSON ){
					$person	= $modelPerson->get( $transaction->personId );
					$target	= UI_HTML_Tag::create( 'a', $iconPerson.'&nbsp;'.$person->firstname.' '.$person->surname, array(
						'href'	=> './work/billing/person/edit/'.$person->personId,
					) );
				}
				else if( $this->mode === static::MODE_CORPORATION ){
					$corporation	= $modelCorporation->get( $transaction->corporationId );
					$target	= UI_HTML_Tag::create( 'a', $iconCompany.'&nbsp;'.$corporation->title, array(
						'href'	=> './work/billing/corporation/edit/'.$corporation->corporationId,
					) );
	//				$target	= UI_HTML_Tag::create( 'small', $target );
				}
				else{
					$target	= '-';
				}

				$year	= UI_HTML_Tag::create( 'small', date( 'y', strtotime( $transaction->dateBooked ) ), array( 'class' => 'muted' ) );
				$date	= date( 'd.m.', strtotime( $transaction->dateBooked ) ).$year;


				$list[]	= UI_HTML_Tag::create( 'tr', array(
					UI_HTML_Tag::create( 'td', $title ),
					UI_HTML_Tag::create( 'td', $target ),
					UI_HTML_Tag::create( 'td', $date, array( 'class' => 'cell-number' ) ),
					UI_HTML_Tag::create( 'td', number_format( $transaction->amount, 2, ',', '.' ).'&nbsp;&euro;', array( 'class' => 'cell-number' ) ),
				), array( 'class' => $transaction->amount > 0 ? 'success' : 'error' ) );
			}
			$colgroup	= UI_HTML_Elements::ColumnGroup( array( '', '160', '80', '80' ) );
			$thead	= UI_HTML_Tag::create( 'thead', UI_HTML_Tag::create( 'tr', array(
				UI_HTML_Tag::create( 'th', 'Vorgang' ),
				UI_HTML_Tag::create( 'th', 'Empfänger' ),
				UI_HTML_Tag::create( 'th', 'Datum', array( 'class' => 'cell-number' ) ),
				UI_HTML_Tag::create( 'th', 'Betrag', array( 'class' => 'cell-number' ) ),
			) ) );
			$tbody	= UI_HTML_Tag::create( 'tbody', $list );
			$list = UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-fixed table-condensed' ) );
		}

		return '
		<div class="content-panel">
			<h3>'.$this->heading.'</h3>
			<div class="content-panel-inner">
				'.$this->renderFilter().'
				'.$list.'
<!--				<div class="buttonbar">
				</div>-->
			</div>
		</div>';
	}

	protected function renderFilter(){
		if( !$this->filterPrefix )
			return;
		if( !$this->filterUrl )
			return;

		$filter	= new View_Work_Billing_Helper_Filter( $this->env );
		$filter->setFilters( array( 'year', 'month' ) );
		$filter->setSessionPrefix( $this->filterPrefix );
		$filter->setUrl( $this->filterUrl );
		return $filter->render();
	}
}
