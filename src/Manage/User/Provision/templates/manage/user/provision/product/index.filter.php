<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;

$w	= (object) $words['index.filter'];

$optProduct	= ['' => $w->optAll];
foreach( $products as $item )
	$optProduct[$item->productId]	= $item->title;
$optProduct	= HtmlElements::Options( $optProduct, $filterProductId );

$optProductLicense	= ['' => $w->optAll];
foreach( $productLicenses as $item )
	$optProductLicense[$item->productLicenseId]	= $item->title;
$optProductLicense	= HtmlElements::Options( $optProductLicense, $filterProductLicenseId );

return '
<div class="content-panel content-panel-filter">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<form action="./manage/catalog/provision/license/filter" method="post">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_productId">'.$w->labelProduct.'</label>
					<select name="productId" id="input_productId">'.$optProduct.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_productLicenseId">'.$w->labelProductLicense.'</label>
					<select name="productLicenseId" id="input_productLicenseId">'.$optProductLicense.'</select>
				</div>
			</div>
			<div class="buttonbar">
				<button type="submit" name="filter" class="btn btn-info btn-small"><i class="icon-zoom-in icon-white"></i> '.$w->buttonFilter.'</button>
				<a href="./manage/catalog/provision/license/filter/reset" class="btn btn-inverse btn-small"><i class="icon-zoom-out icon-white"></i> '.$w->buttonReset.'</a>
			</div>
		</form>
	</div>
</div>';
