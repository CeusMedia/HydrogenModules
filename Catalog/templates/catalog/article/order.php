<?php

$w				= (object) $words['article'];
$iconCart		= '<i class="icon-shopping-cart icon-white"></i>';

$panelOrder		= '';
if( in_array( $article->status, array( -1, 0, 1 ) ) ){
	$buttonCart		= '&nbsp;<a href="./shop/cart" class="btn btn-success btn-small">'.$iconCart.' zum Warenkorb</a>';
	if( !$cart )
		$buttonCart		= '';

	$quantity		= max( 1, (int) $request->get( 'quantity' ) );
	$panelOrder		= '
	<div class="content-panel">
		<!--<h3>Bestellen</h3>-->
		<div class="content-panel-inner well alert alert-success" id="panel-catalog-article-order">
			<form action="./catalog/order" method="post" class="form-horizontal">
				<input type="hidden" name="articleId" value="'.$article->articleId.'"/>
				<label for="input_quantity">'.$w->quantity.'</label>
				<input type="text" name="quantity" id="input_quantity" class="span2 numeric" required="required" value="'.$quantity.'"/>
				<button type="submit" name="order" class="btn not-btn-small"><i class="icon-plus"></i> bestellen</button>
				'.$buttonCart.'
			</form>
		</div>
	</div>';
}

return $panelOrder;

?>
