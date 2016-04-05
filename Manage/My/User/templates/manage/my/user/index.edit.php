<?php
$w		= (object) $words['edit'];

$optGender	= HTML::Options( $words['gender'], $user->gender );

return HTML::DivClass( 'content-panel content-panel-form', array(
	UI_HTML_Tag::create( 'h4', $w->heading ),
	HTML::DivClass( 'content-panel-inner', array(
		HTML::Form( './manage/my/user/edit', 'my_user_edit', array(
			HTML::DivClass( 'row-fluid',
				HTML::DivClass( 'span3',
					HTML::Label( 'gender', $w->labelGender, '' ).
					HTML::Select( 'gender', $optGender, 'span12' )
				).
				HTML::DivClass( 'span2',
					HTML::Label( 'salutation', $w->labelSalutation, '' ).
					HTML::Input( 'salutation', $user->salutation, 'span12' )
				).
				HTML::DivClass( 'span3',
					HTML::Label( 'firstname', $w->labelFirstname, $mandatoryFirstname ? 'mandatory' : '' ).
		//				HTML::Input( 'firstname', $user->firstname, 'span12' )
					UI_HTML_Tag::create( 'input', NULL, array(
						'name'		=> 'firstname',
						'id'		=> 'input_firstname',
						'value'		=> htmlentities( $user->firstname, ENT_QUOTES, 'UTF-8' ),
						'class'		=> 'span12',
						'required'	=> $mandatoryFirstname ? 'required' : NULL,
						'type'		=> 'text',
					) )
				).
				HTML::DivClass( 'span4',
					HTML::Label( 'surname', $w->labelSurname, $mandatorySurname ? 'mandatory' : '' ).
		//				HTML::Input( 'surname', $user->surname, 'span12' )
					UI_HTML_Tag::create( 'input', NULL, array(
						'name'		=> 'surname',
						'id'		=> 'input_surname',
						'value'		=> htmlentities( $user->surname, ENT_QUOTES, 'UTF-8' ),
						'class'		=> 'span12',
						'required'	=> $mandatorySurname ? 'required' : NULL,
						'type'		=> 'text',
					) )
				)
			),
			HTML::HR,
			HTML::DivClass( 'row-fluid',
				HTML::DivClass( 'span3',
					HTML::Label( 'number', $w->labelCountry, '' ).
					UI_HTML_Tag::create( 'select', UI_HTML_Elements::Options( $countries, $user->country ), array(
						'name'			=> 'country',
						'id'			=> 'input_country',
						'class'			=> 'span12',
					) )
/*					UI_HTML_Tag::create( 'input', NULL, array(
						'type'			=> 'text',
						'name'			=> 'country',
						'id'			=> 'input_country',
						'value'			=> $countries[$user->country],
						'class'			=> 'span12 typeahead',
						'data-provide'	=> 'typeahead',
						'autocomplete'	=> 'off'
					) )*/
				).
				HTML::DivClass( 'span2',
					HTML::Label( 'postcode', $w->labelPostcode, '', $w->labelPostcode_title ).
					HTML::Input( 'postcode', $user->postcode, 'span12 numeric' )
				).
				HTML::DivClass( 'span3',
					HTML::Label( 'city', $w->labelCity, '' ).
					HTML::Input( 'city', $user->city, 'span12' )
				).
				HTML::DivClass( 'span4',
					HTML::Label( 'street', $w->labelStreet, '' ).
					HTML::Input( 'street', $user->street, 'span12' )
				)
			),
			HTML::HR,
			HTML::DivClass( 'row-fluid',
				HTML::DivClass( 'span3',
					HTML::Label( 'phone', $w->labelPhone ).
					HTML::Input( 'phone', $user->phone, 'span12' )
				).
				HTML::DivClass( 'span3',
					HTML::Label( 'fax', $w->labelFax ).
					HTML::Input( 'fax', (string) $user->fax, 'span12' )
				)
			),
			HTML::Buttons( array(
				UI_HTML_Tag::create( 'small', 'Änderungen bitte mit dem Passwort bestätigen.', array( 'class' => 'not-muted' ) ),
				HTML::DivClass( 'row-fluid',
					HTML::DivClass( 'span6', array(
						HTML::DivClass( 'input-prepend input-append',
							HTML::SpanClass( 'add-on', '<i class="not-icon-lock fa fa-fw fa-lock"></i>' ).
							'<input type="password" name="password" id="input_password" class="span7" required placeholder="'.$w->labelPassword.'" value="" autocomplete="off"/>'.
							UI_HTML_Elements::Button( 'saveUser', '<i class="icon-ok icon-white"></i> '.$w->buttonSave, 'btn btn-primary' )
						)
					) )
				)
			) )
		), array( 'autocomplete' => 'off' ) )
	) )
) ).'
<script>
/*
$(document).ready(function(){
	$(".typeahead").typeahead({
		source: '.json_encode( array_values( $countries ) ).',
		items: 4
	});
});
*/
</script>
';
