<?php
$w	= (object) $words['filter'];

$filterCustomer	= !empty( $filters['customer'] ) ? $filters['customer'] : "";

$optStatus	= array(/* '' => '- alle -' */);
foreach( $words['states'] as $key => $value )
	$optStatus[$key]	= $value;
$optStatus	= UI_HTML_Elements::Options( $optStatus, $filters['status'] );

$optOrder	= $words['filter-orders'];
$optOrder	= UI_HTML_Elements::Options( $optOrder, $filters['order'] );

return '
<div class="content-panel">
	<h4>'.$w->heading.'</h4>
	<div class="content-panel-inner">
		<form action="./manage/shop/order/filter" method="post">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_customer">'.$w->labelCustomer.'</label>
					<input class="span12" type="text" name="customer" id="input_customer" value="'.$filterCustomer.'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_status">'.$w->labelStatus.'</label>
					<select class="span12" name="status[]" id="input_status" multiple="multiple" size="13">'.$optStatus.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_order">'.$w->labelOrder.'</label>
					<select class="span12" name="order" id="input_order">'.$optOrder.'</select>
				</div>
			</div>
			<div class="buttonbar">
				<button type="submit" class="btn btn-small btn-info"><i class="icon-search icon-white"></i> filtern</button>
				<a href="./manage/shop/order/filter/reset" class="btn btn-small btn-inverse"><i class="icon-remove-circle icon-white"></i> '.$w->buttonReset.'</a>
			</div>
		</div>
	</div>
</form>
';
?>
