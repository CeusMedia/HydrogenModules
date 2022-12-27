<?php

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Panel_Mangopay_Transactions extends View_Helper_Panel_Mangopay{

	public function __construct( Environment $env, $options = [] ){
		parent::__construct( $env, array_merge( array(
			'linkItem'	=> './manage/my/mangopay/transaction/view/%s',
			'linkBack'	=> '',
			'linkAdd'	=> '',
		), $options ) );
	}

	public function render(){
		$rows		= [];
		foreach( $this->data as $transaction ){
//			print_m( $transaction );die;
			$statusClass	= 'label-warning';
			if( $transaction->Status === "SUCCEEDED" )
				$statusClass	= 'label-success';
			else if( $transaction->Status === "FAILED" )
				$statusClass	= 'label-important';
			$link	= HtmlTag::create( 'a', $transaction->Id, ['href' => sprintf( $this->options->get( 'linkItem' ), $transaction->Id )] );
			$amount	= $transaction->DebitedFunds;
			if( in_array( $transaction->Type, ['PAYOUT', 'REFUND'] ) ){
				$amount	= $transaction->CreditedFunds;
			}
			$amount	= HtmlTag::create( 'big', self::formatMoney( $amount ) );
			$fees	= HtmlTag::create( 'small', '&minus;'.self::formatMoney( $transaction->Fees ), ['class' => 'muted'] );
			$date	= HtmlTag::create( 'span', date( 'Y-m-d', $transaction->CreationDate ), ['class' => ''] );
			$time	= HtmlTag::create( 'small', date( 'H:i:s', $transaction->CreationDate ), ['class' => 'muted'] );
			$status	= HtmlTag::create( 'span', $transaction->Status, ['class' => 'label '.$statusClass, 'title' => $transaction->ResultMessage] );
			$iconType	= HtmlTag::create( 'i', '', ['class' => 'fa fa-exchange'] );
			if( $transaction->Type === "PAYIN" )
				$iconType	= HtmlTag::create( 'i', '', ['class' => 'fa fa-download'] );
			if( $transaction->Type === "PAYOUT" )
				$iconType	= HtmlTag::create( 'i', '', ['class' => 'fa fa-upload'] );
			if( $transaction->Type === "REFUND" )
				$iconType	= HtmlTag::create( 'i', '', ['class' => 'fa fa-undo'] );
			$rows[]	= HtmlTag::create( 'tr', array(
				HtmlTag::create(' td', $link, ['class' => 'cell-transaction-id'] ),
				HtmlTag::create(' td', $date.'<br/>'.$time, ['class' => 'cell-transaction-date'] ),
				HtmlTag::create(' td', $iconType.'&nbsp;'.$transaction->Type.'<br/>'.$transaction->Nature, ['class' => 'cell-transaction-type'] ),
				HtmlTag::create(' td', $status, ['class' => 'cell-transaction-status'] ),
				HtmlTag::create(' td', $amount.'<br/>'.$fees, ['class' => 'cell-transaction-amount', 'style' => 'text-align: right'] ),
			) );
		}
		$colgroup	= HtmlElements::ColumnGroup( "100", "", "120", "120" );
		$thead		= HtmlTag::create( 'thead', HtmlTag::create( 'tr', array(
			HtmlTag::create( 'th', 'ID' ),
			HtmlTag::create( 'th', 'Zeitpunkt' ),
			HtmlTag::create( 'th', 'Typ' ),
			HtmlTag::create( 'th', 'Status' ),
			HtmlTag::create( 'th', 'Betrag', ['style' => 'text-align: right'] ),
		) ) );
		$tbody		= HtmlTag::create( 'tbody', $rows );
		$table		= HtmlTag::create( 'table', $colgroup.$thead.$tbody, ['class' => 'table table-striped'] );
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
			$id			= HtmlTag::create( 'small', $item->Id.':', ['class' => 'muted'] );
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
