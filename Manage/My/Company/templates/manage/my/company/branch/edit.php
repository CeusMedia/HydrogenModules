<?php

$iconCancel	= HTML::Icon( 'arrow-left' );
$iconSave	= HTML::Icon( 'ok', TRUE );
$iconRemove	= HTML::Icon( 'trash', TRUE );

//  --  PANEL: MAP  --  //
$w				= (object) $words['map'];
$panelMap		= '';
if( $branch->longitude && $branch->latitude ){
	$panelMap	= '
		<div style="border: 1px solid black; width: 100%; height: 400px">
			<div id="map_canvas" style="width: 100%; height: 100%;" data-longitude="'.$branch->longitude.'" data-latitude="'.$branch->latitude.'" data-zoom="14" data-marker-title="'.$branch->title.'"></div>
		</div>
<script>
$(document).ready(function(){loadMap("map_canvas")});
</script>';
}

//  --  PANEL: COUPONS  --  //
/*$panelCoupons	= "";
if( $env->getModules()->has( 'Manage_Coupon' ) ){
	$w				= (object) $words['coupons'];
	$listCoupons	= array();
	foreach( $coupons as $coupon ){
		$url	= './manage/my/coupon/edit/'.$coupon->couponId;
		$listCoupons[]	= HTML::Li( HTML::Link( $url, $coupon->title ), 'coupon' );
	}
	$panelCoupons	= HTML::Fields(
		HTML::Legend( $w->legend, 'list company coupons' ).
		HTML::UlClass( 'list-branches', $listCoupons ? join( $listCoupons ) : $w->noEntries ).
		HTML::Buttons( HTML::LinkButton( './manage/my/coupon/add', $w->buttonAdd, 'button add' ) )
	);
}
*/


$w	= (object) $words['edit'];
extract( $view->populateTexts( array( 'top', 'info', 'bottom' ), 'html/manage/my/branch/edit/' ) );

$optStatus	= HTML::Options( $words['states'], $branch->status );
$optCompany	= array();
foreach( $companies as $company )
	$optCompany[$company->companyId]	= $company->title;

$panelEdit	= HTML::DivClass( 'content-panel',
	HTML::H3( $w->legend, 'icon edit branch my' ).
	HTML::DivClass( 'content-panel-inner',
		HTML::Form( './manage/my/company/branch/edit/'.$branch->branchId, 'branch_edit',
			HTML::DivClass( 'row-fluid',
				HTML::DivClass( 'span6',
					HTML::Label( 'title', $w->labelTitle, 'mandatory' ).
					HTML::Input( 'title', $branch->title, 'span12 mandatory' )
				).
				HTML::DivClass( 'span6',
					HTML::Label( 'companyId', $w->labelCompany, 'mandatory' ).
					HTML::Select( 'companyId', $optCompany, 'span12 mandatory' )
				)
			).
			HTML::DivClass( 'row-fluid',
				HTML::DivClass( 'span2',
					HTML::Label( 'postcode', $w->labelPostcode, 'mandatory' ).
					HTML::Input( 'postcode', $branch->postcode, 'span12 mandatory' )
				).
				HTML::DivClass( 'span4',
					HTML::Label( 'city', $w->labelCity, 'mandatory' ).
					HTML::Input( 'city', $branch->city, 'span12 mandatory' )
				).
				HTML::DivClass( 'span4',
					HTML::Label( 'street', $w->labelStreet, 'mandatory' ).
					HTML::Input( 'street', $branch->street, 'span12 mandatory' )
				).
				HTML::DivClass( 'span2',
					HTML::Label( 'number', $w->labelNumber, 'mandatory' ).
					HTML::Input( 'number', $branch->number, 'span12 mandatory' )
				)
			).
			HTML::DivClass( 'row-fluid',
				HTML::DivClass( 'span6',
					HTML::Label( 'url', $w->labelUrl ).
					HTML::Input( 'url', $branch->url, 'span12' )
				).
				HTML::DivClass( 'span3',
					HTML::Label( 'phone', $w->labelPhone ).
					HTML::Input( 'phone', $branch->phone, 'span12' )
				).
				HTML::DivClass( 'span3',
					HTML::Label( 'fax', $w->labelFax ).
					HTML::Input( 'fax', $branch->fax, 'span12' )
				)
			).
			HTML::DivClass( 'row-fluid',
				HTML::DivClass( 'span12',
					HTML::Label( 'url', 'Beschreibung' ).
					UI_HTML_Tag::create( 'textarea', $branch->description, array(
						'name'	=> 'description',
						'id'	=> 'input_description',
						'class' => 'span12',
						'rows' => '10'
					) )
				)
			).
			HTML::DivClass( 'buttonbar',
				UI_HTML_Elements::LinkButton( './manage/my/company/branch', $iconCancel.'&nbsp;'.$w->buttonCancel, 'btn btn-small' ).
				'&nbsp;|&nbsp'.
				UI_HTML_Elements::Button( 'save', $iconSave.'&nbsp;'.$w->buttonSave, 'btn btn-success' )
#				'&nbsp;|&nbsp'.
#				UI_HTML_Elements::LinkButton( './manage/branch/delete/'.$branch->branchId, $w->buttonRemove, 'button delete' )
			)
		)
	)
);

$panelImages	= $view->loadTemplateFile( 'manage/my/company/branch/edit.images.php' );

return HTML::DivClass( 'row-fluid',
	HTML::DivClass( 'span8',
		$panelEdit
	).
	HTML::DivClass( 'span4',
//		$panelCoupons.
		$panelMap.
		$textInfo
	)
).
HTML::DivClass( 'row-fluid',
	HTML::DivClass( 'span12',
		$panelImages
	)
);
?>
