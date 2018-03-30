<?php

$optProduct	= array( '' => ' - alle - ' );
foreach( $products as $item )
	$optProduct[$item->productId]	= $item->title;
$optProduct	= UI_HTML_Elements::Options( $optProduct, $filterProductId );

$optProductLicense	= array( '' => '- alle -' );
foreach( $productLicenses as $item )
	$optProductLicense[$item->productLicenseId]	= $item->title;
$optProductLicense	= UI_HTML_Elements::Options( $optProductLicense, $filterProductLicenseId );

return '
<div class="content-panel content-panel-filter">
	<h3>Filter</h3>
	<div class="content-panel-inner">
		<form action="./manage/catalog/provision/license/filter" method="post">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_productId">Produkt</label>
					<select name="productId" id="input_productId">'.$optProduct.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_productLicenseId">Produktlizenz</label>
					<select name="productLicenseId" id="input_productLicenseId">'.$optProductLicense.'</select>
				</div>
			</div>
			<div class="buttonbar">
				<button type="submit" name="filter" class="btn btn-info btn-small"><i class="icon-zoom-in icon-white"></i> filtern</button>
				<a href="./manage/catalog/provision/license/filter/reset" class="btn btn-inverse btn-small"><i class="icon-zoom-out icon-white"></i> zurücksetzen</a>
			</div>
		</form>
	</div>
</div>';
