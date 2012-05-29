<?php
//  --  PANEL: MAP  --  //
$w				= (object) $words['map'];
$panelMap		= '';
if( $branch->longitude && $branch->latitude ){
	$panelMap	= '
		<div style="border: 1px solid black; width: 375px; height: 375px">
			<div id="map_canvas" style="width: 100%; height: 100%;" data-longitude="'.$branch->longitude.'" data-latitude="'.$branch->latitude.'" data-zoom="14" data-marker-title="'.$branch->title.'"></div>
		</div>';
}


//  --  PANEL: COUPONS  --  //
$panelCoupons	= "";
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



$w	= (object) $words['edit'];
$text	= $view->populateTexts( array( 'top', 'info', 'bottom' ), 'html/manage/my/branch.edit.' );

$listImages	= array();
foreach( $branch->images as $image ){
	$urlImage		= 'images/branches/'.$image->filename;
	$urlRemove		= './manage/my/branch/removeImage/'.$branch->branchId.'/'.$image->imageId;
	$img			= new UI_Image( $urlImage );
	$listImages[]	= HTML::Li(
		HTML::DivClass( 'column-left-40',
			HTML::Image( $urlImage, $image->title, 'medium' )
		).
		HTML::DivClass( 'column-right-60',
			HTML::DivClass( 'image-item',
				HTML::H4( $image->title ).
				HTML::UlClass( 'image-info',
					HTML::Li( 'Datum: '.date( 'd.m.Y H:i', $image->uploadedAt ) ).
					HTML::Li( 'Größe: '.$img->getWidth().' x '.$img->getHeight() )
				).
				HTML::LinkButton( $urlRemove, $words['images']['buttonRemove'], 'button remove' )
			)
		).
		HTML::DivClass( 'column-clear' )
	);
}
$listImages	= $listImages ? join( $listImages ) : HTML::Li( $words['images']['noEntries'] );
$imageList	= HTML::UlClass( 'images', $listImages );


$optStatus	= HTML::Options( $words['states'], $branch->status );

$panelEdit	= HTML::Form( './manage/my/branch/edit/'.$branch->branchId, 'branch_edit',
	HTML::Fields(
		HTML::Legend( $w->legend, 'icon edit branch my' ).
		HTML::UlClass( 'input',
			HTML::Li(
				HTML::DivClass( 'column-left-50',
					HTML::Label( 'title', $w->labelTitle, 'mandatory' ).HTML::BR.
					HTML::Input( 'title', $branch->title, 'max mandatory' )
				).
				HTML::DivClass( 'column-clear' )
			).
			HTML::Li(
				HTML::DivClass( 'column-left-20',
					HTML::Label( 'postcode', $w->labelPostcode, 'mandatory' ).HTML::BR.
					HTML::Input( 'postcode', $branch->postcode, 'max mandatory' )
				).
				HTML::DivClass( 'column-left-30',
					HTML::Label( 'city', $w->labelCity, 'mandatory' ).HTML::BR.
					HTML::Input( 'city', $branch->city, 'max mandatory' )
				).
				HTML::DivClass( 'column-left-30',
					HTML::Label( 'street', $w->labelStreet, 'mandatory' ).HTML::BR.
					HTML::Input( 'street', $branch->street, 'max mandatory' )
				).
				HTML::DivClass( 'column-left-20',
					HTML::Label( 'number', $w->labelNumber, 'mandatory' ).HTML::BR.
					HTML::Input( 'number', $branch->number, 'max mandatory' )
				).
				HTML::DivClass( 'column-clear' )
			).
			HTML::Li(
				HTML::DivClass( 'column-left-50',
					HTML::Label( 'url', $w->labelUrl ).HTML::BR.
					HTML::Input( 'url', $branch->url, 'max' )
				).
				HTML::DivClass( 'column-left-25',
					HTML::Label( 'phone', $w->labelPhone ).HTML::BR.
					HTML::Input( 'phone', $branch->phone, 'max' )
				).
				HTML::DivClass( 'column-left-25',
					HTML::Label( 'fax', $w->labelFax ).HTML::BR.
					HTML::Input( 'fax', $branch->fax, 'max' )
				).
				HTML::DivClass( 'column-clear' )
			)
		).
		HTML::Buttons(
			UI_HTML_Elements::LinkButton( './manage/my/branch', $w->buttonCancel, 'button cancel' ).
			'&nbsp;|&nbsp'.
			UI_HTML_Elements::Button( 'doEdit', $w->buttonSave, 'button save' )
#			'&nbsp;|&nbsp'.
#			UI_HTML_Elements::LinkButton( './manage/branch/delete/'.$branch->branchId, $w->buttonRemove, 'button delete' )
		)
	)
);
$panelImages	= HTML::Fields(
	HTML::Legend( $words['images']['legend'], 'icon view image' ).
	$imageList
);

$panelAddImage	= 	HTML::Form( './manage/my/branch/addImage/'.$branch->branchId, 'my_branch_image_add',
	HTML::Fields(
		HTML::Legend( $words['addImage']['legend'], 'icon add' ).
		HTML::UlClass( 'input',
			HTML::Li(
				HTML::DivClass( 'column-left-50',
					HTML::Label( 'image', $words['addImage']['labelImage'] ).HTML::BR.
					HTML::File( 'image', NULL, 'max' )
				).
				HTML::DivClass( 'column-left-50',
					HTML::Label( 'image_title', $words['addImage']['labelTitle'], '' ).HTML::BR.
					HTML::Input( 'image_title', $request->get( 'image_title' ), 'max' )
				).
				HTML::DivClass( 'column-clear' )
			)
		).
		HTML::Buttons(
			UI_HTML_Elements::Button( 'doUpload', $words['addImage']['buttonUpload'], 'button upload add' )
		)
	)
);


return HTML::DivClass( 'column-left-60',
#	UI_HTML_Tag::create( 'h2', $w->heading ).
	$panelEdit.
	$panelAddImage.
	$panelImages
).
HTML::DivClass( 'column-left-40',
	$panelCoupons.
	$panelMap.
	$text['info']
).
HTML::DivClass( 'column-left' );
?>