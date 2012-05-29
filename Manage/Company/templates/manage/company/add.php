<?php
$w	= (object) $words['add'];

return HTML::DivClass( 'column-left-50',
//	UI_HTML_Tag::create( 'h2', $w->heading ).
	HTML::Form( './manage/company/add', 'company_add',
		HTML::Fields(
			HTML::Legend( $w->legend, 'company add' ).
			HTML::UlClass( 'input',
				HTML::Li(
					HTML::Label( 'title', $w->labelTitle, 'mandatory' ).HTML::BR.
					HTML::Input( 'title', $company->title, 'max mandatory' )		
				).
				HTML::Li(
					HTML::Label( 'sector', $w->labelSector ).HTML::BR.
					HTML::Input( 'sector', $company->sector, 'max' )
				).
				HTML::Li(
					HTML::DivClass( 'column-left-20',
						HTML::Label( 'postcode', $w->labelPostcode, 'mandatory' ).HTML::BR.
						HTML::Input( 'postcode', $company->postcode, 'max mandatory' )
					).
					HTML::DivClass( 'column-left-80',
						HTML::Label( 'city', $w->labelCity, 'mandatory' ).HTML::BR.
						HTML::Input( 'city', $company->city, 'max mandatory' )
					).
					HTML::DivClass( 'column-clear' )
				).
				HTML::Li(
					HTML::DivClass( 'column-left-80',
						HTML::Label( 'street', $w->labelStreet, 'mandatory' ).HTML::BR.
						HTML::Input( 'street', $company->street, 'max mandatory' )
					).
					HTML::DivClass( 'column-left-20',
						HTML::Label( 'number', $w->labelNumber, 'mandatory' ).HTML::BR.
						HTML::Input( 'number', $company->number, 'max mandatory' )
					).
					HTML::DivClass( 'column-clear' )
				).
				HTML::Li(
					HTML::DivClass( 'column-left-50',
						HTML::Label( 'phone', $w->labelPhone ).HTML::BR.
						HTML::Input( 'phone', $company->phone, 'max' )
					).
					HTML::DivClass( 'column-left-50',
						HTML::Label( 'fax', $w->labelFax ).HTML::BR.
						HTML::Input( 'fax', $company->fax, 'max' )
					).
					HTML::DivClass( 'column-clear' )
				).
				HTML::Li(
					HTML::Label( 'url', $w->labelUrl ).HTML::BR.
					HTML::Input( 'url', $company->url, 'max' )
				)
			).
			HTML::Buttons(
				HTML::LinkButton( './manage/company', $w->buttonCancel, 'button cancel' ).
				'&nbsp;|&nbsp'.
				HTML::Button( 'doAdd', $w->buttonSave, 'button save' )
			)
		)
	)
).
HTML::DivClass( 'column-left' );
?>
