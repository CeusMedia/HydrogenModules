<?php
$w			= (object) $words['index'];

#if( !class_exists( "XHTML" ) )
#	new CMF_Hydrogen_View_Helper_HTML();

$optGender	= HTML::Options( $words['gender'], $user->gender );

/* TO BE USED LATER FOR STATUS INFO
$indicator	= new UI_HTML_Indicator();
$indicator->setIndicatorClass( 'indicator-small' );
$ind1		= $indicator->build( 75, 100 );
*/

$panelInfo		= $view->loadTemplateFile( 'manage/my/user/index.info.php' );
$panelPassword	= $view->loadTemplateFile( 'manage/my/user/index.password.php' );

$panelEdit	= '
<div class="content-panel">
	<h3>'.$w->legend.'</h3>
	<div class="content-panel-inner">'.
		HTML::Form( './manage/my/user/edit', 'my_user_edit',
			HTML::DivClass( 'row-fluid panel',
				HTML::DivClass( 'span4',
					HTML::Label( 'username', $w->labelUsername, 'mandatory' ).
					HTML::DivClass( 'input-prepend',
						HTML::SpanClass( 'add-on', '<i class="icon-user"></i>' ).
		//				HTML::Input( 'username', $user->username, 'span11 mandatory' )
						UI_HTML_Tag::create( 'input', NULL, array(
							'name'		=> 'username',
							'id'		=> 'input_username',
							'value'		=> htmlentities( $user->username, ENT_QUOTES, 'UTF-8' ),
							'class'		=> 'span11',
							'required'	=> 'required',
							'type'		=> 'text',
						) )
					)
				).
				HTML::DivClass( 'span8',
					HTML::Label( 'email', $w->labelEmail, $mandatoryEmail ? 'mandatory' : '' ).
					HTML::DivClass( 'input-prepend span12',
						HTML::SpanClass( 'add-on', '<i class="icon-envelope"></i>' ).
		//				HTML::Input( 'email', $user->email, 'span11 mandatory' )
						UI_HTML_Tag::create( 'input', NULL, array(
							'name'		=> 'email',
							'id'		=> 'input_email',
							'value'		=> htmlentities( $user->email, ENT_QUOTES, 'UTF-8' ),
							'class'		=> 'span11',
							'required'	=> $mandatoryEmail ? 'required' : NULL,
							'type'		=> 'text',
						) )
					)
				)
			).
			'<hr/>'.
			HTML::DivClass( 'row-fluid',
				HTML::DivClass( 'span2',
					HTML::Label( 'gender', $w->labelGender, '' ).
					HTML::Select( 'gender', $optGender, 'span12' )
				).
				HTML::DivClass( 'span2',
					HTML::Label( 'salutation', $w->labelSalutation, '' ).
					HTML::Input( 'salutation', $user->salutation, 'span12' )
				).
				HTML::DivClass( 'span4',
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
			).
			HTML::DivClass( 'row-fluid',
				HTML::DivClass( 'span2',
					HTML::Label( 'postcode', $w->labelPostcode, '' ).
					HTML::Input( 'postcode', $user->postcode, 'span12 numeric' )
				).
				HTML::DivClass( 'span3',
					HTML::Label( 'city', $w->labelCity, '' ).
					HTML::Input( 'city', $user->city, 'span12' )
				).
				HTML::DivClass( 'span5',
					HTML::Label( 'street', $w->labelStreet, '' ).
					HTML::Input( 'street', $user->street, 'span12' )
				).
				HTML::DivClass( 'span2',
					HTML::Label( 'number', $w->labelNumber, '' ).
					HTML::Input( 'number', $user->number, 'span12 numeric' )
				)
			).
			HTML::DivClass( 'row-fluid',
				HTML::DivClass( 'span3',
					HTML::Label( 'phone', $w->labelPhone ).
					HTML::Input( 'phone', $user->phone, 'span12' )
				).
				HTML::DivClass( 'span3',
					HTML::Label( 'fax', $w->labelFax ).
					HTML::Input( 'fax', (string) $user->fax, 'span12' )
				)
			).
		/*	'<hr/>'.
			HTML::DivClass( 'row-fluid',
				HTML::DivClass( 'span3',
					HTML::Label( 'password', $w->labelPassword, 'mandatory' ).
				)
			).
		*/	HTML::Buttons(
				HTML::DivClass( 'row-fluid',
					HTML::DivClass( 'span6',
						HTML::DivClass( 'input-prepend input-append',
							HTML::SpanClass( 'add-on', '<i class="icon-lock"></i>' ).
							'<input type="password" name="password" id="input_password" class="span7" required placeholder="'.$w->labelPassword.'" value="" autocomplete="off"/>'.
		//					HTML::Password( 'password', 'span11 mandatory' )
							UI_HTML_Elements::Button( 'saveUser', '<i class="icon-ok icon-white"></i> '.$w->buttonSave, 'btn btn-success' )
						)
					)
				)
		//		HTML::Button( 'saveUser', $w->buttonSave, 'button save' )
			)
		).'
	</div>
</div>';

$tabs	= View_Manage_My_User::renderTabs( $env );

return $tabs.HTML::DivClass( 'row-fluid', 
	HTML::DivClass( 'span8',
		$panelEdit
	).
	HTML::DivClass( 'span4',
		$panelInfo.
		$panelPassword
	)
);
?>
