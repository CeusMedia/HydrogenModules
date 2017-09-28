<?php
class View_Helper_Panel_Mangopay_Transactions extends View_Helper_Panel_Mangopay{

	public function __construct( $env, $options = array() ){
		parent::__construct( $env, array_merge( array(
			'linkItem'	=> './manage/my/mangopay/transaction/view/%s',
			'linkBack'	=> '',
			'linkAdd'	=> '',
		), $options ) );
	}

	public function render(){
		$rows		= array();
		foreach( $this->data as $transaction ){
//			print_m( $transaction );die;
			$statusClass	= 'label-warning';
			if( $transaction->Status === "SUCCEEDED" )
				$statusClass	= 'label-success';
			else if( $transaction->Status === "FAILED" )
				$statusClass	= 'label-important';
			$link	= UI_HTML_Tag::create( 'a', $transaction->Id, array( 'href' => sprintf( $this->options->get( 'linkItem' ), $transaction->Id ) ) );
			$amount	= $transaction->DebitedFunds;
			if( in_array( $transaction->Type, array( 'PAYOUT', 'REFUND' ) ) ){
				$amount	= $transaction->CreditedFunds;
			}
			$amount	= UI_HTML_Tag::create( 'big', self::formatMoney( $amount ) );
			$fees	= UI_HTML_Tag::create( 'small', '&minus;'.self::formatMoney( $transaction->Fees ), array( 'class' => 'muted' ) );
			$date	= UI_HTML_Tag::create( 'span', date( 'Y-m-d', $transaction->CreationDate ), array( 'class' => '' ) );
			$time	= UI_HTML_Tag::create( 'small', date( 'H:i:s', $transaction->CreationDate ), array( 'class' => 'muted' ) );
			$status	= UI_HTML_Tag::create( 'span', $transaction->Status, array( 'class' => 'label '.$statusClass, 'title' => $transaction->ResultMessage ) );
			$iconType	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-exchange' ) );
			if( $transaction->Type === "PAYIN" )
				$iconType	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-download' ) );
			if( $transaction->Type === "PAYOUT" )
				$iconType	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-upload' ) );
			if( $transaction->Type === "REFUND" )
				$iconType	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-undo' ) );
			$rows[]	= UI_HTML_Tag::create( 'tr', array(
				UI_HTML_Tag::create(' td', $link, array( 'class' => 'cell-transaction-id' ) ),
				UI_HTML_Tag::create(' td', $date.'<br/>'.$time, array( 'class' => 'cell-transaction-date' ) ),
				UI_HTML_Tag::create(' td', $iconType.'&nbsp;'.$transaction->Type.'<br/>'.$transaction->Nature, array( 'class' => 'cell-transaction-type' ) ),
				UI_HTML_Tag::create(' td', $status, array( 'class' => 'cell-transaction-status' ) ),
				UI_HTML_Tag::create(' td', $amount.'<br/>'.$fees, array( 'class' => 'cell-transaction-amount', 'style' => 'text-align: right' ) ),
			) );
		}
		$colgroup	= UI_HTML_Elements::ColumnGroup( "100", "", "120", "120" );
		$thead		= UI_HTML_Tag::create( 'thead', UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'th', 'ID' ),
			UI_HTML_Tag::create( 'th', 'Zeitpunkt' ),
			UI_HTML_Tag::create( 'th', 'Typ' ),
			UI_HTML_Tag::create( 'th', 'Status' ),
			UI_HTML_Tag::create( 'th', 'Betrag', array( 'style' => 'text-align: right' ) ),
		) ) );
		$tbody		= UI_HTML_Tag::create( 'tbody', $rows );
		$table		= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-striped' ) );
		return '
		<div class="content-panel">
			<h3>Transactions</h3>
			<div class="content-panel-inner">
				'.$table.'
			</div>
		</div>';
	}

/*	public function render(){
		$list	= new View_Helper_Accordion( 'user-transations' );
		$list->setSingleOpen( TRUE );
		foreach( $this->data as $item ){
			$id			= UI_HTML_Tag::create( 'small', $item->Id.':', array( 'class' => 'muted' ) );
			$title		= $id.'&nbsp;'.self::formatMoney( $item->DebitedFunds );
			$content	= ltrim( print_m( $item, NULL, NULL, TRUE ), '<br/>' );
			$list->add( 'user-transation-'.$item->Id, $title, $content );
		}
		return '
		<div class="content-panel">
			<h3>Transactions</h3>
			<div class="content-panel-inner">
				'.$list->render().'
			</div>
		</div>';
	}*/
}
?>
