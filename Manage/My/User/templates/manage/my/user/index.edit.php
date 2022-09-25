<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w		= (object) $words['edit'];

$optGender	= HTML::Options( $words['gender'], $user->gender );

return HTML::DivClass( 'content-panel content-panel-form', array(
	HtmlTag::create( 'h4', $w->heading ),
	HTML::DivClass( 'content-panel-inner', array(
		HTML::Form( './manage/my/user/edit', 'my_user_edit', array(
			HTML::DivClass( 'row-fluid',
				HTML::DivClass( 'span3',
					HTML::Label( 'gender', $w->labelGender, '' ).
					HTML::Select( 'gender', $optGender, 'span12' )
				).
				HTML::DivClass( 'span2',
					HTML::Label( 'salutation', $w->labelSalutation, '', $w->labelSalutation_title ).
					HtmlTag::create( 'input', NULL, array(
						'name'			=> 'salutation',
						'id'			=> 'input_salutation',
						'value'			=> htmlentities( $user->salutation, ENT_QUOTES, 'UTF-8' ),
						'class'			=> 'span12',
						'type'			=> 'text',
						'autocomplete'	=> 'honorific-prefix'
					) )
				).
				HTML::DivClass( 'span3',
					HTML::Label( 'firstname', $w->labelFirstname, $mandatoryFirstname ? 'mandatory' : '' ).
		//				HTML::Input( 'firstname', $user->firstname, 'span12' )
					HtmlTag::create( 'input', NULL, array(
						'name'			=> 'firstname',
						'id'			=> 'input_firstname',
						'value'			=> htmlentities( $user->firstname, ENT_QUOTES, 'UTF-8' ),
						'class'			=> 'span12',
						'required'		=> $mandatoryFirstname ? 'required' : NULL,
						'type'			=> 'text',
						'autocomplete'	=> 'given-name'
					) )
				).
				HTML::DivClass( 'span4',
					HTML::Label( 'surname', $w->labelSurname, $mandatorySurname ? 'mandatory' : '' ).
		//				HTML::Input( 'surname', $user->surname, 'span12' )
					HtmlTag::create( 'input', NULL, array(
						'type'			=> 'text',
						'name'			=> 'surname',
						'id'			=> 'input_surname',
						'value'			=> htmlentities( $user->surname, ENT_QUOTES, 'UTF-8' ),
						'class'			=> 'span12',
						'required'		=> $mandatorySurname ? 'required' : NULL,
						'autocomplete'	=> 'family-username'
					) )
				)
			),
//			HTML::HR,
			HTML::DivClass( 'row-fluid',
				HTML::DivClass( 'span3',
					HTML::Label( 'number', $w->labelCountry, $mandatoryAddress ? 'mandatory' : '' ).
					HtmlTag::create( 'select', HtmlElements::Options( $countries, $user->country ), array(
						'name'			=> 'country',
						'id'			=> 'input_country',
						'class'			=> 'span12',
						'required'		=> $mandatoryAddress ? 'required' : NULL,
						'autocomplete'	=> 'country'
					) )
/*					HtmlTag::create( 'input', NULL, array(
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
					HTML::Label( 'postcode', $w->labelPostcode, $mandatoryAddress ? 'mandatory' : '', $w->labelPostcode_title ).
					HtmlTag::create( 'input', NULL, array(
						'type'			=> 'text',
						'name'			=> 'postcode',
						'id'			=> 'input_postcode',
						'value'			=> htmlentities( $user->postcode, ENT_QUOTES, 'UTF-8' ),
						'class'			=> 'span12 numeric',
						'required'		=> $mandatoryAddress ? 'required' : NULL,
						'autocomplete'	=> 'postal-code'
					) )
				).
				HTML::DivClass( 'span3',
					HTML::Label( 'city', $w->labelCity, $mandatoryAddress ? 'mandatory' : '' ).
					HtmlTag::create( 'input', NULL, array(
						'type'			=> 'text',
						'name'			=> 'city',
						'id'			=> 'input_city',
						'value'			=> htmlentities( $user->city, ENT_QUOTES, 'UTF-8' ),
						'class'			=> 'span12',
						'required'		=> $mandatoryAddress ? 'required' : NULL,
						'autocomplete'	=> 'address-level2'
					) )
				).
				HTML::DivClass( 'span4',
					HTML::Label( 'street', $w->labelStreet, $mandatoryAddress ? 'mandatory' : '' ).
					HtmlTag::create( 'input', NULL, array(
						'type'			=> 'text',
						'name'			=> 'street',
						'id'			=> 'input_street',
						'value'			=> htmlentities( $user->street, ENT_QUOTES, 'UTF-8' ),
						'class'			=> 'span12',
						'required'		=> $mandatoryAddress ? 'required' : NULL,
						'autocomplete'	=> 'address-line1'
					) )
				)
			),
//			HTML::HR,
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
				HtmlTag::create( 'small', $w->labelPasswordCurrent_title, array( 'class' => 'not-muted' ) ),
				HTML::DivClass( 'row-fluid',
					HTML::DivClass( 'span6', array(
						HTML::DivClass( 'input-prepend input-append',
							HTML::SpanClass( 'add-on', '<i class="fa fa-fw fa-lock"></i>' ).
							HtmlTag::create( 'input', '', array(
								'type'			=> 'password',
								'name'			=> 'password',
								'id'			=> 'input_password',
								'class'			=> 'span7',
								'required'		=> 'required',
								'autocomplete'	=> 'current-password',
								'placeholder'	=> $w->labelPasswordCurrent,
							) ).
							HtmlElements::Button( 'saveUser', '<i class="fa fa-fw fa-check"></i> '.$w->buttonSave, 'btn btn-primary' )
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
