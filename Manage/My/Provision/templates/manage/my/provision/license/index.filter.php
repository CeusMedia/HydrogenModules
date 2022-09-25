<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w	= (object) $words['index.filter'];

$iconFilter		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-search-plus' ) );
$iconReset		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-search-minus' ) );
$iconFilter		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-search' ) );
$iconReset		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-times-circle' ) );

$optProduct	= array( '' => ' - alle - ' );
foreach( $products as $item )
	$optProduct[$item->productId]	= $item->title;
$optProduct	= HtmlElements::Options( $optProduct, $filterProductId );

return '
<div class="content-panel content-panel-filter">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<form action="./manage/my/provision/license/filter" method="post">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_productId">'.$w->labelProductId.'</label>
					<select name="productId" id="input_productId" class="span12">'.$optProduct.'</select>
				</div>
			</div>
			<div class="buttonbar">
				<div class="btn-group">
					<button type="submit" name="filter" class="btn btn-info not-btn-small">'.$iconFilter.'<!--&nbsp;'.$w->buttonFilter.'--></button>
					<a href="./manage/my/provision/license/filter/reset" class="btn btn-inverse not-btn-small">'.$iconReset.'<!--&nbsp;'.$w->buttonReset.'--></a>
				</div>
			</div>
		</form>
	</div>
</div>';
