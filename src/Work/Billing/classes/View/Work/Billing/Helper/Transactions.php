<?php

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Work_Billing_Helper_Transactions
{
	const MODE_NONE				= 0;
	const MODE_CORPORATION		= 1;
	const MODE_PERSON			= 2;

	const MODES					= [
		self::MODE_NONE,
		self::MODE_CORPORATION,
		self::MODE_PERSON,
	];

	protected Environment $env;
	protected Logic_Billing $logic;
	protected Model_Billing_Bill $modelBill;
	protected Model_Billing_Expense $modelExpense;
	protected string $buttons			= '';
	protected string $heading			= 'Transaktionen';
	protected int $mode					= 0;
	protected array $transactions		= [];
	protected string $filterPrefix		= '';
	protected string $filterUrl			= '';

	public function __construct( Environment $env )
	{
		$this->env	= $env;
		$this->logic	= new Logic_Billing( $this->env );
		$this->modelBill	= new Model_Billing_Bill( $this->env );
		$this->modelExpense	= new Model_Billing_Expense( $this->env );
	}

	public function setButtons( string $buttons ): self
	{
		$this->buttons	= $buttons;
		return $this;
	}

	public function setFilterPrefix( string $prefix ): self
	{
		$this->filterPrefix	= $prefix;
		return $this;
	}

	public function setFilterUrl( $url ): self
	{
		$this->filterUrl	= $url;
		return $this;
	}

	public function setHeading( string $heading ): self
	{
		$this->heading	= $heading;
		return $this;
	}

	public function setMode( int $mode ): self
	{
		$this->mode	= $mode;
		return $this;
	}

	public function setTransactions( array $transactions ): self
	{
		$this->transactions	= $transactions;
		return $this;
	}

	public function render(): string
	{
		$modelPerson		= new Model_Billing_Person( $this->env );
		$modelCorporation	= new Model_Billing_Corporation( $this->env );

		$iconBill		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-file-o'] );
		$iconPerson		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-user-o'] );
		$iconCompany	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-building-o'] );

		$list	= HtmlTag::create( 'div', HtmlTag::create( 'em', 'Keine gefunden.', ['class' => 'muted'] ), ['class' => 'alert alert-info'] );
		if( $this->transactions ){
			$sum	= 0;
			$list	= [];
			foreach( $this->transactions as $transaction ){
				$sum	+= (float) $transaction->amount;
				$from	= HtmlTag::create( 'em', 'extern', ['class' => 'muted'] );
				switch( $transaction->fromType ){
					case Model_Billing_Transaction::TYPE_PERSON:
						$sender	= $this->logic->getPerson( $transaction->fromId );
						$from	= HtmlTag::create( 'a', $iconPerson.'&nbsp;'.$sender->firstname.' '.$sender->surname, [
							'href'	=> './work/billing/person/edit/'.$transaction->fromId,
						] );
						break;
					case Model_Billing_Transaction::TYPE_CORPORATION:
						$sender	= $this->logic->getCorporation( $transaction->fromId );
						$from	= HtmlTag::create( 'a', $iconCompany.'&nbsp;'.$sender->title, [
							'href'	=> './work/billing/corporation/edit/'.$transaction->fromId,
						] );
						break;
					case Model_Billing_Transaction::TYPE_BILL:
						$sender	= $this->logic->getBill( $transaction->fromId );
						$from	= HtmlTag::create( 'a', $iconBill.'&nbsp;'.$sender->number, [
							'href'	=> './work/billing/edit/'.$transaction->fromId,
						] );
						break;
				}

				$to		= HtmlTag::create( 'em', 'extern', ['class' => 'muted'] );
				switch( $transaction->toType ){
					case Model_Billing_Transaction::TYPE_PERSON:
						$sender	= $this->logic->getPerson( $transaction->toId );
						$to		= HtmlTag::create( 'a', $iconPerson.'&nbsp;'.$sender->firstname.' '.$sender->surname, [
							'href'	=> './work/billing/person/edit/'.$transaction->toId,
						] );
						break;
					case Model_Billing_Transaction::TYPE_CORPORATION:
						$sender	= $this->logic->getCorporation( $transaction->toId );
						$to		= HtmlTag::create( 'a', $iconCompany.'&nbsp;'.$sender->title, [
							'href'	=> './work/billing/corporation/edit/'.$transaction->toId,
						] );
						break;
				}

				$title	= $this->transformRelationToTitle( $transaction );
				$title	= $title ? $title : $transaction->title;

				$year	= HtmlTag::create( 'small', date( 'y', strtotime( $transaction->dateBooked ) ), ['class' => 'muted'] );
				$date	= date( 'd.m.', strtotime( $transaction->dateBooked ) ).$year;

				$id		= HtmlTag::create( 'small', $transaction->transactionId );
				$list[]	= HtmlTag::create( 'tr', [
				/*	HtmlTag::create( 'td', $id, ['class' => 'cell-number'] ),*/
					HtmlTag::create( 'td', $title ),
					HtmlTag::create( 'td', $from ),
					HtmlTag::create( 'td', $to ),
					HtmlTag::create( 'td', $date, ['class' => 'cell-number'] ),
					HtmlTag::create( 'td', number_format( $transaction->amount, 2, ',', '.' ).'&nbsp;&euro;', ['class' => 'cell-number'] ),
				], ['class' => $transaction->amount > 0 ? 'success' : 'error'] );
			}

			$tfoot	= HtmlTag::create( 'tfoot', HtmlTag::create( 'tr', [
			/*	HtmlTag::create( 'td', $id, ['class' => 'cell-number'] ),*/
				HtmlTag::create( 'td', '<strong>Gesamt</strong>' ),
				HtmlTag::create( 'td', '' ),
				HtmlTag::create( 'td', '' ),
				HtmlTag::create( 'td', '' ),
				HtmlTag::create( 'td', number_format( $sum, 2, ',', '.' ).'&nbsp;&euro;', ['class' => 'cell-number'] ),
			] ) );
			if( count( $this->transactions ) < 2 )
				$tfoot		= '';

			$colgroup	= HtmlElements::ColumnGroup( [/*'45', */'', '200', '200', '100', '100'] );
			$thead		= HtmlTag::create( 'thead', HtmlTag::create( 'tr', [
/*				HtmlTag::create( 'th', 'ID', ['class' => 'cell-number'] ),*/
				HtmlTag::create( 'th', 'Vorgang' ),
				HtmlTag::create( 'th', 'Zu Lasten' ),
				HtmlTag::create( 'th', 'Zu Gunsten' ),
				HtmlTag::create( 'th', 'Datum', ['class' => 'cell-number'] ),
				HtmlTag::create( 'th', 'Betrag', ['class' => 'cell-number'] ),
			] ) );
			$tbody	= HtmlTag::create( 'tbody', $list );
			$list	= HtmlTag::create( 'table', $colgroup.$thead.$tbody.$tfoot, ['class' => 'table table-fixed table-condensed'] );
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

	protected function renderFilter(): string
	{
		if( !$this->filterPrefix || !$this->filterUrl )
			return '';

		$filter	= new View_Work_Billing_Helper_Filter( $this->env );
		$filter->setFilters( ['year', 'month'] );
		$filter->setSessionPrefix( $this->filterPrefix );
		$filter->setUrl( $this->filterUrl );
		return $filter->render();
	}

	protected function transformDateInTitle( $title ): string
	{
		if( preg_match( '/\[date\.Y\]/', $title ) )
			$title	= preg_replace( '/\[date.Y\]/', date( 'Y' ), $title );
		if( preg_match( '/\[date.m\]/', $title ) )
			$title	= preg_replace( '/\[date.m\]/', date( 'm' ), $title );
		if( preg_match( '/\[date.d\]/', $title ) )
			$title	= preg_replace( '/\[date.d\]/', date( 'd' ), $title );
		return $title;
	}

	protected function transformRelationToTitle( $transaction ): string
	{
		$parts		= [];
		$title		= '';
		$relation	= $transaction->relation;
		if( preg_match( '/\|billShare:([0-9]+)\|/', $relation ) ){
			$billShareId	= preg_replace( '/\|billShare:([0-9]+)\|/', '\\1', $relation );
			$billShare		= $this->logic->getBillShare( $billShareId );
			$bill			= $this->logic->getBill( $billShare->billId );
			$linkBill		= HtmlTag::create( 'a', $bill->title, ['href' => './work/billing/bill/edit/'.$bill->billId] );
			$title			= 'Anteil aus Rechnung '.$linkBill;
		}
		else if( preg_match( '/\|billReserve:([0-9]+)\|/', $relation ) ){
			$billReserveId	= preg_replace( '/\|billReserve:([0-9]+)\|/', '\\1', $relation );
			$billReserve	= $this->logic->getBillReserve( $billReserveId );
			$bill			= $this->logic->getBill( $billReserve->billId );
			$linkReserve	= HtmlTag::create( 'a', $billReserve->title, ['href' => './work/billing/reserve/edit/'.$billReserve->reserveId] );
			$linkBill		= HtmlTag::create( 'a', $bill->title, ['href' => './work/billing/bill/edit/'.$bill->billId] );
			$prefix			= HtmlTag::create( 'small', 'Rücklage '.$linkReserve.' aus Rechnung: ', ['class' => 'muted'] );
			$title			= $prefix.$linkBill;
		}
/*		else if( preg_match( '/^bill:([0-9]+)$/', $relation ) ){
			$id			= preg_replace( '/^bill:([0-9]+)$/', '\\1', $relation );
			$bill		= $this->modelBill->get( $id );
			$link		= HtmlTag::create( 'a', 'RNr.'.$bill->number, ['href' => './work/billing/bill/edit/'.$id] );
			$relation	= $link;
		}*/
		else if( preg_match( '/\|expense:([0-9]+)\|/', $relation ) ){
			$id			= preg_replace( '/\|expense:([0-9]+)\|/', '\\1', $relation );
			$expense	= $this->modelExpense->get( $id );
			$prefix		= HtmlTag::create( 'small', 'Ausgabe: ', ['class' => 'muted'] );
			$link		= HtmlTag::create( 'a', $prefix.$transaction->title, ['href' => './work/billing/expense/edit/'.$id] );
			$title		= $link;
		}
		else if( preg_match( '/\|payin\|/', $relation ) ){
			$prefix		= HtmlTag::create( 'small', 'Einzahlung: ', ['class' => 'muted'] );
			$title		= $prefix.$transaction->title;
		}
		else if( preg_match( '/\|payout\|/', $relation ) ){
			$prefix		= HtmlTag::create( 'small', 'Auszahlung: ', ['class' => 'muted'] );
			$title		= $prefix.$transaction->title;
		}
		return $title;
	}
}
