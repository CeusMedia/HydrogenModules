<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class View_Helper_Panel_Mangopay_BankAccounts extends View_Helper_Panel_Mangopay{

	public function render(){
		$list	= [];
		foreach( $this->data as $bankAccount ){
			$link	= HtmlTag::create( 'a', $bankAccount->Id, ['href' => './manage/my/mangopay/bank/view/'.$bankAccount->Id] );
			$list[]	= HtmlTag::create( 'tr', array(
				HtmlTag::create( 'td', $link ),
			) );
		}
		$tbody	= HtmlTag::create( 'tbody', $list );
		$table	= HtmlTag::create( 'table', $tbody, ['class' => 'table table-striped'] );
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
