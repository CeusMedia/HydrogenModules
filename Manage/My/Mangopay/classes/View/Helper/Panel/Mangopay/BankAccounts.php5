<?php
class View_Helper_Panel_Mangopay_BankAccounts extends View_Helper_Panel_Mangopay{

	public function render(){
		$list	= array();
		foreach( $this->data as $bankAccount ){
			$link	= UI_HTML_Tag::create( 'a', $bankAccount->Id, array( 'href' => './manage/my/mangopay/bank/view/'.$bankAccount->Id ) );
			$list[]	= UI_HTML_Tag::create( 'tr', array(
				UI_HTML_Tag::create( 'td', $link ),
			) );
		}
		$tbody	= UI_HTML_Tag::create( 'tbody', $list );
		$table	= UI_HTML_Tag::create( 'table', $tbody, array( 'class' => 'table table-striped' ) );
		return '
		<div class="content-panel">
			<h3>Bank Accounts</h3>
			<div class="content-panel-inner">
				'.$table.'
				<div class="buttonbar">
					<a href="./manage/my/mangopay/bank/add" class="btn btn-success">add</a>
				</div>
			</div>
		</div>';
	}
}
?>
