<?php

$iconFilter		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-search-plus' ) );
$iconReset		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-search-minus' ) );
$iconFilter		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-search' ) );
$iconReset		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-times-circle' ) );

$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
//$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );
$iconOrder		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-shopping-cart' ) );

$w	= (object) $words['index.filter'];

$optProduct	= array( '' => ' - alle - ' );
foreach( $products as $item )
	$optProduct[$item->productId]	= $item->title;
$optProduct	= UI_HTML_Elements::Options( $optProduct, $filterProductId );

return '
<div class="content-panel content-panel-filter">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<form action="./manage/my/provision/license/key/filter" method="post">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_productId">'.$w->labelProductId.'</label>
					<select name="productId" id="input_productId" class="span12">'.$optProduct.'</select>
				</div>
			</div>
<!--			<div class="row-fluid">
				<div class="span12">
					<label for="input_productId">'.$w->labelProductId.'</label>
					<select name="productId" id="input_productId" class="span12">'.$optProduct.'</select>
				</div>
			</div>-->
			<div class="buttonbar">
				<div class="btn-group">
					<button type="submit" name="filter" class="btn btn-info not-btn-small">'.$iconFilter.'<!--&nbsp;'.$w->buttonFilter.'--></button>
					<a href="./manage/my/provision/license/key/filter/reset" class="btn btn-inverse not-btn-small">'.$iconReset.'<!--&nbsp;'.$w->buttonReset.'--></a>
				</div>
			</div>
		</form>
	</div>
</div>';
