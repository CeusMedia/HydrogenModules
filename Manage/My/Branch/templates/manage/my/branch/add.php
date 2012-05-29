<?php
$w	= (object) $words['add'];

$text	= $view->populateTexts( array( 'top', 'info', 'bottom' ), 'html/manage/my/branch.add.' );

$panelAdd	= HTML::Form( './manage/my/branch/add', 'branch_add',
	HTML::Fields(
		HTML::Legend( $w->legend, 'branch add' ).
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
			HTML::LinkButton( './manage/my/branch', $w->buttonCancel, 'button cancel' ).
			'&nbsp;|&nbsp'.
			HTML::Button( 'doAdd', $w->buttonSave, 'button save' )
		)
	)
);

return #UI_HTML_Tag::create( 'h2', $w->heading ).
HTML::DivClass( 'column-left-60',
	$panelAdd
).
HTML::DivClass( 'column-left-40',
	$text['info']
).HTML::DivClass( 'column-left' );
?>