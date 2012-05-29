<?php
//  --  PANEL: BRANCHES  --  //
$w				= (object) $words['branches'];
$listBranches	= array();
foreach( $company->branches as $branch ){
	$url	= './manage/branch/edit/'.$branch->branchId;
	$listBranches[]	= HTML::Li( HTML::Link( $url, $branch->title ), 'branch' );
}
$panelBranches	= HTML::Fields(
	HTML::Legend( $w->legend, 'list company branches' ).
	HTML::UlClass( 'list-branches', $listBranches ? join( $listBranches ) : $w->noEntries ).
	HTML::Buttons( HTML::LinkButton( './manage/branch/add', $w->buttonAdd, 'button add' ) )
);

//  --  PANEL: USERS  --  //
$w			= (object) $words['users'];
$listUsers	= array();
foreach( $company->users as $user ){
	$url	= './manage/user/edit/'.$user->userId;
	$label	= HTML::Link( $url, $user->username, 'user' );
	if( $user->firstname || $user->surname )
		$label	.= ' ('.$user->salutation.' '.$user->firstname.' '.$user->surname.')';
 	$listUsers[]	= HTML::Li( $label, 'user' );
}
$panelUsers	= HTML::Fields(
	HTML::Legend( $w->legend, 'list company users' ).
	HTML::UlClass( 'list-users', $listUsers ? join( $listUsers ) : $w->noEntries ).
	HTML::Buttons( HTML::LinkButton( './manage/user/add/'.$company->companyId, $w->buttonAdd, 'button add' ) )
);

//  --  PANEL: EDIT  --  //
$w			= (object) $words['edit'];
$optStatus	= HTML::Options( $words['states'], $company->status );
$panelEdit	= HTML::Form( './manage/company/edit/'.$company->companyId, 'company_edit',
	HTML::Fields(
		HTML::Legend( $w->legend, 'company edit' ).
		HTML::UlClass( 'input',
			HTML::Li(
				HTML::Label( 'title', $w->labelTitle, 'mandatory' ).HTML::BR.
				HTML::Input( 'title', $company->title, 'max mandatory' )		
			).
			HTML::Li(
				HTML::Label( 'status', $w->labelStatus ).HTML::BR.
				HTML::Select( 'status', $optStatus, 'm' )
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
			HTML::Button( 'doEdit', $w->buttonSave, 'button save' )
#				'&nbsp;|&nbsp'.
#				HTML::LinkButton( './manage/company/delete/'.$company->companyId, $w['buttonRemove'], 'button delete' ).
		)
	)
);

return //	UI_HTML_Tag::create( 'h2', $w->heading ).
	HTML::DivClass( 'column-right-33',
	$panelBranches.
	$panelUsers
).
HTML::DivClass( 'column-left-66',
	$panelEdit
).
HTML::DivClass( 'column-left' );
?>