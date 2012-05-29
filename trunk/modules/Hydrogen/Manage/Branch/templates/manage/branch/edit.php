<?php
$w	= (object) $words['edit'];

$optStatus	= HTML::Options( $words['states'], $branch->status );

$optCompany	= HTML::Options( $companies, $branch->companyId, array( 'companyId', 'title' ) ); 


$list	= array();
foreach( $images as $image ){
	$urlImage	= 'images/branches/'.$image->filename;
	$urlRemove	= './manage/branch/removeImage/'.$branch->branchId.'/'.$image->imageId;
	$img		= new UI_Image( $urlImage );
	$list[]	= HTML::Li(
		HTML::DivClass( 'column-left-50',
			HTML::Image( $urlImage, $image->title )
		).
		HTML::DivClass( 'column-right-50',
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
$imageList	= HTML::UlClass( 'images', join( $list ) );

return HTML::DivClass( 'column-left-50',
#	UI_HTML_Tag::create( 'h2', $w->heading ).
	HTML::Form( './manage/branch/edit/'.$branch->branchId, 'branch_edit',
		HTML::Fields(
			HTML::Legend( $w->legend, 'icon branch edit' ).
			HTML::UlClass( 'input',
				HTML::Li(
					HTML::Label( 'title', $w->labelTitle, 'mandatory' ).HTML::BR.
					HTML::Input( 'title', $branch->title, 'max mandatory' )
				).
				HTML::Li(
					HTML::DivClass( 'column-left-70',
						HTML::Label( 'companyId', $w->labelCompany ).HTML::BR.
						HTML::Select( 'companyId', $optCompany, 'max' )
					).
					HTML::DivClass( 'column-left-30',
						HTML::Label( 'status', $w->labelStatus ).HTML::BR.
						HTML::Select( 'status', $optStatus, 'max' )
					).
					HTML::DivClass( 'column-clear' )
				).
				HTML::Li(
					HTML::DivClass( 'column-left-20',
						HTML::Label( 'postcode', $w->labelPostcode, 'mandatory' ).HTML::BR.
						HTML::Input( 'postcode', $branch->postcode, 'max mandatory' )
					).
					HTML::DivClass( 'column-left-80',
						HTML::Label( 'city', $w->labelCity, 'mandatory' ).HTML::BR.
						HTML::Input( 'city', $branch->city, 'max mandatory' )
					).
					HTML::DivClass( 'column-clear' )
				).
				HTML::Li(
					HTML::DivClass( 'column-left-80',
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
						HTML::Label( 'phone', $w->labelPhone ).HTML::BR.
						HTML::Input( 'phone', $branch->phone, 'max' )
					).
					HTML::DivClass( 'column-left-50',
						HTML::Label( 'fax', $w->labelFax ).HTML::BR.
						HTML::Input( 'fax', $branch->fax, 'max' )
					).
					HTML::DivClass( 'column-clear' )
				).
				HTML::Li(
					HTML::Label( 'url', $w->labelUrl ).HTML::BR.
					HTML::Input( 'url', $branch->url, 'max' )
				)
			).
			HTML::Buttons(
				UI_HTML_Elements::LinkButton( './manage/branch', $w->buttonCancel, 'button cancel' ).
				'&nbsp;|&nbsp'.
				UI_HTML_Elements::Button( 'doEdit', $w->buttonSave, 'button save' )
#				'&nbsp;|&nbsp'.
#				UI_HTML_Elements::LinkButton( './manage/branch/delete/'.$branch->branchId, $w->buttonRemove, 'button delete' )
			)
		)
	)
).
HTML::DivClass( 'column-left-50',
	HTML::Form( './manage/branch/addImage/'.$branch->branchId, 'branch_image_add',
		HTML::Fields(
			HTML::Legend( $words['images']['legend'], 'icon branch image view' ).
			$imageList
		).
		HTML::Fields(
			HTML::Legend( $words['addImage']['legend'], 'icon branch add' ).
			HTML::UlClass( 'input',
				HTML::Li(
					HTML::Label( 'image', $words['addImage']['labelImage'] ).HTML::BR.
					HTML::File( 'image', NULL, 'max' )
				).
				HTML::Li(
					HTML::Label( 'image_title', $words['addImage']['labelTitle'], '' ).HTML::BR.
					HTML::Input( 'image_title', $request->get( 'title' ), 'max' )
				)
			).
			HTML::Buttons(
				UI_HTML_Elements::Button( 'doUpload', $words['addImage']['buttonUpload'], 'button upload add' )
			)
		)
	)
).
HTML::DivClass( 'column-left' );
?>