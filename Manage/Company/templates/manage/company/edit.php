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
	HTML::Buttons( UI_HTML_Elements::LinkButton( './manage/branch/add', $w->buttonAdd, 'button add' ) )
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
	HTML::Buttons( UI_HTML_Elements::LinkButton( './manage/user/add/'.$company->companyId, $w->buttonAdd, 'button add' ) )
);

//  --  PANEL: EDIT  --  //
$w			= (object) $words['edit'];
$optStatus	= HTML::Options( $words['states'], $company->status );
$panelEdit	= HTML::Form( './manage/company/edit/'.$company->companyId, 'company_edit',
	HTML::Fields(
		HTML::Legend( $w->legend, 'company edit' ).
		HTML::DivClass( 'row-fluid',
			HTML::DivClass( 'span9',
				HTML::Label( 'title', $w->labelTitle, 'mandatory' ).
				HTML::Input( 'title', $company->title, 'span12 mandatory required' )
			).
			HTML::DivClass( 'span3',
				HTML::Label( 'status', $w->labelStatus ).
				HTML::Select( 'status', $optStatus, 'span12' )
			)
		).
		HTML::HR.
		HTML::DivClass( 'row-fluid',
			HTML::DivClass( 'span2',
				HTML::Label( 'postcode', $w->labelPostcode, 'mandatory' ).
				HTML::Input( 'postcode', $company->postcode, 'span12 mandatory' )
			).
			HTML::DivClass( 'span4',
				HTML::Label( 'city', $w->labelCity, 'mandatory' ).
				HTML::Input( 'city', $company->city, 'span12 mandatory' )
			).
			HTML::DivClass( 'span4',
				HTML::Label( 'street', $w->labelStreet, 'mandatory' ).
				HTML::Input( 'street', $company->street, 'span12 mandatory' )
			).
			HTML::DivClass( 'span2',
				HTML::Label( 'number', $w->labelNumber, 'mandatory' ).
				HTML::Input( 'number', $company->number, 'span12 mandatory' )
			)
		).
		HTML::DivClass( 'row-fluid',
			HTML::DivClass( 'span3',
				HTML::Label( 'phone', $w->labelPhone ).
				HTML::Input( 'phone', $company->phone, 'span12' )
			).
			HTML::DivClass( 'span3',
				HTML::Label( 'fax', $w->labelFax ).
				HTML::Input( 'fax', $company->fax, 'span12' )
			).
			HTML::DivClass( 'span6',
				HTML::Label( 'url', $w->labelUrl ).
				HTML::Input( 'url', $company->url, 'span12' )
			)
		).
		HTML::HR.
		HTML::DivClass( 'row-fluid',
			HTML::DivClass( 'span12',
				HTML::Label( 'sector', $w->labelSector ).
				HTML::Input( 'sector', $company->sector, 'span12' )
			)
		).
		HTML::Buttons(
			UI_HTML_Elements::LinkButton( './manage/company', $w->buttonCancel, 'btn btn-small' ).
			'&nbsp;|&nbsp'.
			UI_HTML_Elements::Button( 'doEdit', $w->buttonSave, 'btn btn-success' )
#				'&nbsp;|&nbsp'.
#				HTML::LinkButton( './manage/company/delete/'.$company->companyId, $w['buttonRemove'], 'button delete' ).
		)
	)
);

return //	UI_HTML_Tag::create( 'h2', $w->heading ).
HTML::DivClass( 'row-fluid',
	HTML::DivClass( 'span8',
		$panelEdit
	).
	HTML::DivClass( 'span4',
		$panelBranches.
		$panelUsers
	)
)
?>
