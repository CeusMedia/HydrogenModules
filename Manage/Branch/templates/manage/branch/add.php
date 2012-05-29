<?php
$w	= (object) $words['add'];

$optCompany	= HTML::Options( $companies, $request->get( 'companyId' ), array( 'companyId', 'title' ) ); 

return HTML::DivClass( 'column-left-50',
#	UI_HTML_Tag::create( 'h2', $w->heading ).
	HTML::Form( './manage/branch/add', 'branch_add',
		HTML::Fields(
			HTML::Legend( $w->legend, 'branch add' ).
			HTML::UlClass( 'input',
				HTML::Li(
					HTML::Label( 'title', $w->labelTitle, 'mandatory' ).HTML::BR.
					HTML::Input( 'title', $branch->title, 'max mandatory' )
				).
				HTML::Li(
					HTML::Label( 'companyId', $w->labelCompany ).HTML::BR.
					HTML::Select( 'companyId', $optCompany, 'l' )
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
				HTML::LinkButton( './manage/branch', $w->buttonCancel, 'button cancel' ).
				'&nbsp;|&nbsp'.
				HTML::Button( 'doAdd', $w->buttonSave, 'button save' )
			)
		)
	)
).
HTML::DivClass( 'column-left' );
?>