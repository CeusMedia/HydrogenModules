<?php
class View_Helper_Panel_Mangopay_Cards extends View_Helper_Panel_Mangopay{

	public function __construct( $env, $options = array() ){
		parent::__construct( $env, array_merge( array(
			'linkItem'	=> './manage/my/mangopay/card/view/%s',
			'linkBack'	=> './manage/my/mangopay/card/add',
			'linkAdd'	=> './manage/my/mangopay/card/add',
		), $options ) );
	}

	public function render(){
		$rows		= array();
		foreach( $this->data as $item ){
		//	if( !$item->Active )
		//		continue;
			$link	= UI_HTML_Tag::create( 'a', $item->Id, array( 'href' => sprintf( $this->options->get( 'linkItem' ), $item->Id ) ) );
			$number	= View_Helper_Panel_Mangopay::renderCardNumber( $item->Alias );
			$rows[]	= UI_HTML_Tag::create( 'tr', array(
				UI_HTML_Tag::create(' td', $link, array( 'class' => 'cell-card-id' ) ),
				UI_HTML_Tag::create(' td', $item->CardProvider, array( 'class' => 'cell-card-provider' ) ),
				UI_HTML_Tag::create(' td', $number, array( 'class' => 'cell-card-title' ) ),
				UI_HTML_Tag::create(' td', $item->Currency, array( 'class' => 'cell-card-currency' ) ),
				UI_HTML_Tag::create(' td', $item->Active ? 'aktiv' : 'inaktiv', array( 'class' => 'cell-card-status' ) ),
			) );
		}
		$colgroup	= UI_HTML_Elements::ColumnGroup( "60", "60", "", "90", "90" );
		$thead		= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array( 'ID', 'Provider', 'Card Number <small class="muted">(anonymisiert)</small>', 'Currency', 'Status' ) ) );
		$tbody		= UI_HTML_Tag::create( 'tbody', $rows );
		$table		= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-striped' ) );
		return '
		<div class="content-panel">
			<h3>Credit Cards <small class="muted"></small></h3>
			<div class="content-panel-inner">
				'.$table.'
				<div class="buttonbar">
					<a href="'.$this->options->get( 'linkAdd' ).'" class="btn btn-success"><b class="fa fa-plus"></b>&nbsp;add</a>
					<a href="./manage/my/mangopay/card/refresh" class="btn btn-small"><b class="fa fa-refresh"></b>&nbsp;reload</a>
				</div>
			</div>
		</div>';
	}
}
?>
