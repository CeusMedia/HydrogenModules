<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

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
		$rows		= [];
		foreach( $this->data as $wallet ){
			$link	= HtmlTag::create( 'a', $wallet->Id, ['href' => sprintf( $this->options->get( 'linkItem' ), $wallet->Id )] );
			$rows[]	= HtmlTag::create( 'tr', array(
				HtmlTag::create(' td', $link, ['class' => 'cell-wallet-id'] ),
				HtmlTag::create(' td', $wallet->Description, ['class' => 'cell-wallet-title'] ),
				HtmlTag::create(' td', self::formatMoney( $wallet->Balance ), ['class' => 'cell-wallet-balance'] ),
			) );
		}
		$colgroup	= HtmlElements::ColumnGroup( "120", "", "120" );
		$thead		= HtmlTag::create( 'thead', HtmlElements::TableHeads( ['ID', 'Wallet Name', 'Betrag'] ) );
		$tbody		= HtmlTag::create( 'tbody', $rows );
		$table		= HtmlTag::create( 'table', $colgroup.$thead.$tbody, ['class' => 'table table-striped'] );
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
