<?php
class View_Helper_Panel_Mangopay_Wallets extends View_Helper_Panel_Mangopay{

	public function __construct( $env ){
		parent::__construct( $env );
		$this->setOptions( array(
			'linkItem'	=> './manage/my/mangopay/wallet/view/%s',
			'linkBack'	=> '',
			'linkAdd'	=> '',
		) );
	}

	public function render(){
		$rows		= array();
		foreach( $this->data as $wallet ){
			$link	= UI_HTML_Tag::create( 'a', $wallet->Id, array( 'href' => sprintf( $this->options->get( 'linkItem' ), $wallet->Id ) ) );
			$rows[]	= UI_HTML_Tag::create( 'tr', array(
				UI_HTML_Tag::create(' td', $link, array( 'class' => 'cell-wallet-id' ) ),
				UI_HTML_Tag::create(' td', $wallet->Description, array( 'class' => 'cell-wallet-title' ) ),
				UI_HTML_Tag::create(' td', self::formatMoney( $wallet->Balance ), array( 'class' => 'cell-wallet-balance' ) ),
			) );
		}
		$colgroup	= UI_HTML_Elements::ColumnGroup( "120", "", "120" );
		$thead		= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array( 'ID', 'Wallet Name', 'Betrag' ) ) );
		$tbody		= UI_HTML_Tag::create( 'tbody', $rows );
		$table		= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-striped' ) );
		return '
		<div class="content-panel">
			<h3>Wallets</h3>
			<div class="content-panel-inner">
				'.$table.'
			</div>
		</div>';
	}
}
?>
