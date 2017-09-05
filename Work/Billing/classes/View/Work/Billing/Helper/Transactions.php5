<?php
class View_Work_Billing_Helper_Transactions{

	const MODE_NONE				= 0;
	const MODE_CORPORATION		= 1;
	const MODE_PERSON			= 2;

	protected $buttons;
	protected $heading			= 'Transaktionen';
	protected $mode				= 0;
	protected $transactions;
	protected $filterPrefix;
	protected $filterUrl;

	public function __construct( $env ){
		$this->env	= $env;
		$this->logic	= new Logic_Billing( $this->env );
		$this->modelBill	= new Model_Billing_Bill( $this->env );
		$this->modelExpense	= new Model_Billing_Expense( $this->env );
	}

	public function setButtons( $buttons ){
		$this->buttons	= $buttons;
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

	protected function transformDateInTitle( $title ){
		if( preg_match( '/\[date\.Y\]/', $title ) )
			$title	= preg_replace( '/\[date.Y\]/', date( 'Y' ), $title );
		if( preg_match( '/\[date.m\]/', $title ) )
			$title	= preg_replace( '/\[date.m\]/', date( 'm' ), $title );
		if( preg_match( '/\[date.d\]/', $title ) )
			$title	= preg_replace( '/\[date.d\]/', date( 'd' ), $title );
		return $title;
	}

	protected function transformRelationToTitle( $transaction ){
		$parts		= array();
		$title		= '';
		$relation	= $transaction->relation;
		if( preg_match( '/\|billShare:([0-9]+)\|/', $relation ) ){
			$billShareId	= preg_replace( '/\|billShare:([0-9]+)\|/', '\\1', $relation );
			$billShare		= $this->logic->getBillShare( $billShareId );
			$bill			= $this->logic->getBill( $billShare->billId );
			$linkBill		= UI_HTML_Tag::create( 'a', $bill->title, array( 'href' => './work/billing/bill/edit/'.$bill->billId ) );
			$title			= 'Anteil aus Rechnung '.$linkBill;
		}
		else if( preg_match( '/\|billReserve:([0-9]+)\|/', $relation ) ){
			$billReserveId	= preg_replace( '/\|billReserve:([0-9]+)\|/', '\\1', $relation );
			$billReserve	= $this->logic->getBillReserve( $billReserveId );
			$bill			= $this->logic->getBill( $billReserve->billId );
			$linkReserve	= UI_HTML_Tag::create( 'a', $billReserve->title, array( 'href' => './work/billing/reserve/edit/'.$billReserve->reserveId ) );
			$linkBill		= UI_HTML_Tag::create( 'a', $bill->title, array( 'href' => './work/billing/bill/edit/'.$bill->billId ) );
			$title			= 'Rücklage '.$linkReserve.' aus Rechnung '.$linkBill;
		}
/*		else if( preg_match( '/^bill:([0-9]+)$/', $relation ) ){
			$id			= preg_replace( '/^bill:([0-9]+)$/', '\\1', $relation );
			$bill		= $this->modelBill->get( $id );
			$link		= UI_HTML_Tag::create( 'a', 'RNr.'.$bill->number, array( 'href' => './work/billing/bill/edit/'.$id ) );
			$relation	= $link;
		}*/
		else if( preg_match( '/\|expense:([0-9]+)\|/', $relation ) ){
			$id			= preg_replace( '/\|expense:([0-9]+)\|/', '\\1', $relation );
			$expense	= $this->modelExpense->get( $id );
			$link		= $this->transformDateInTitle( $expense->title );
			$link		= UI_HTML_Tag::create( 'a', $link, array( 'href' => './work/billing/expense/edit/'.$id ) );
			$title		= $link;
		}
		else if( preg_match( '/\|payin\|/', $relation ) ){
			$title		= 'Einzahlung: '.$transaction->title;
		}
		else if( preg_match( '/\|payout\|/', $relation ) ){
			$title		= 'Auszahlung: '.$transaction->title;
		}
		return $title;
	}

	public function render(){

		$modelPerson		= new Model_Billing_Person( $this->env );
		$modelCorporation	= new Model_Billing_Corporation( $this->env );

		$iconBill		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-file-o' ) );
		$iconPerson		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-user-o' ) );
		$iconCompany	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-building-o' ) );

		$list	= UI_HTML_Tag::create( 'div', UI_HTML_Tag::create( 'em', 'Keine gefunden.', array( 'class' => 'muted' ) ), array( 'class' => 'alert alert-info' ) );
		if( $this->transactions ){
			$list	= array();
			foreach( $this->transactions as $transaction ){
				$from	= UI_HTML_Tag::create( 'em', 'extern', array( 'class' => 'muted' ) );
				switch( $transaction->fromType ){
					case Model_Billing_Transaction::TYPE_PERSON:
						$sender	= $this->logic->getPerson( $transaction->fromId );
						$from	= UI_HTML_Tag::create( 'a', $iconPerson.'&nbsp;'.$sender->firstname.' '.$sender->surname, array(
							'href'	=> './work/billing/person/edit/'.$transaction->fromId,
						) );
						break;
					case Model_Billing_Transaction::TYPE_CORPORATION:
						$sender	= $this->logic->getCorporation( $transaction->fromId );
						$from	= UI_HTML_Tag::create( 'a', $iconCompany.'&nbsp;'.$sender->title, array(
							'href'	=> './work/billing/corporation/edit/'.$transaction->fromId,
						) );
						break;
					case Model_Billing_Transaction::TYPE_BILL:
						$sender	= $this->logic->getBill( $transaction->fromId );
						$from	= UI_HTML_Tag::create( 'a', $iconBill.'&nbsp;'.$sender->number, array(
							'href'	=> './work/billing/edit/'.$transaction->fromId,
						) );
						break;
				}

				$to		= UI_HTML_Tag::create( 'em', 'extern', array( 'class' => 'muted' ) );
				switch( $transaction->toType ){
					case Model_Billing_Transaction::TYPE_PERSON:
						$sender	= $this->logic->getPerson( $transaction->toId );
						$to		= UI_HTML_Tag::create( 'a', $iconPerson.'&nbsp;'.$sender->firstname.' '.$sender->surname, array(
							'href'	=> './work/billing/person/edit/'.$transaction->toId,
						) );
						break;
					case Model_Billing_Transaction::TYPE_CORPORATION:
						$sender	= $this->logic->getCorporation( $transaction->toId );
						$to		= UI_HTML_Tag::create( 'a', $iconCompany.'&nbsp;'.$sender->title, array(
							'href'	=> './work/billing/corporation/edit/'.$transaction->toId,
						) );
						break;
				}

				$title	= $this->transformRelationToTitle( $transaction );
				$title	= $title ? $title : $transaction->title;

				$year	= UI_HTML_Tag::create( 'small', date( 'y', strtotime( $transaction->dateBooked ) ), array( 'class' => 'muted' ) );
				$date	= date( 'd.m.', strtotime( $transaction->dateBooked ) ).$year;

				$id		= UI_HTML_Tag::create( 'small', $transaction->transactionId );
				$list[]	= UI_HTML_Tag::create( 'tr', array(
					UI_HTML_Tag::create( 'td', $id, array( 'class' => 'cell-number' )  ),
					UI_HTML_Tag::create( 'td', $title ),
					UI_HTML_Tag::create( 'td', $from ),
					UI_HTML_Tag::create( 'td', $to ),
					UI_HTML_Tag::create( 'td', $date, array( 'class' => 'cell-number' ) ),
					UI_HTML_Tag::create( 'td', number_format( $transaction->amount, 2, ',', '.' ).'&nbsp;&euro;', array( 'class' => 'cell-number' ) ),
				), array( 'class' => $transaction->amount > 0 ? 'success' : 'error' ) );
			}
			$colgroup	= UI_HTML_Elements::ColumnGroup( array( '45', '', '160', '160', '80', '80' ) );
			$thead	= UI_HTML_Tag::create( 'thead', UI_HTML_Tag::create( 'tr', array(
				UI_HTML_Tag::create( 'th', 'ID', array( 'class' => 'cell-number' ) ),
				UI_HTML_Tag::create( 'th', 'Vorgang' ),
				UI_HTML_Tag::create( 'th', 'Zu Lasten' ),
				UI_HTML_Tag::create( 'th', 'Zu Gunsten' ),
				UI_HTML_Tag::create( 'th', 'Datum', array( 'class' => 'cell-number' ) ),
				UI_HTML_Tag::create( 'th', 'Betrag', array( 'class' => 'cell-number' ) ),
			) ) );
			$tbody	= UI_HTML_Tag::create( 'tbody', $list );
			$list = UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-fixed table-condensed' ) );
		}

		$buttonbar	= '';
		if( $this->buttons ){
			$buttonbar	= '<div class="buttonbar">
				'.$this->buttons.'
			</div>';
		}

		return '
		<div class="content-panel">
			<h3>'.$this->heading.'</h3>
			<div class="content-panel-inner">
				'.$this->renderFilter().'
				'.$list.'
				'.$buttonbar.'
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
