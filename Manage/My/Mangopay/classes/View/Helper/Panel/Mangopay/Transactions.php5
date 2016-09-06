<?php
class View_Helper_Panel_Mangopay_Transactions extends View_Helper_Panel_Mangopay{

	public function render(){
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
	}
}
?>
