<?php

$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-arrow-left' ) );
$iconAdd		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-plus icon-white' ) );
$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-ok icon-white' ) );
$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-trash icon-white' ) );

//  --  PANEL: BRANCHES  --  //
$w				= (object) $words['branches'];
$listBranches	= array();
foreach( $company->branches as $branch ){
	$url	= './manage/company/branch/edit/'.$branch->branchId;
	$listBranches[]	= HTML::Li( HTML::Link( $url, $branch->title ), 'branch' );
}
$urlAdd		= './manage/company/branch/add/'.$company->companyId;
$panelBranches	= HTML::DivClass( 'content-panel',
	HTML::H3( $w->legend ).
	HTML::DivClass( 'content-panel-inner',
		HTML::UlClass( 'not-list-branches nav nav-pills nav-stacked', $listBranches ? join( $listBranches ) : $w->noEntries ).
		HTML::Buttons( UI_HTML_Elements::LinkButton( $urlAdd, $iconAdd.'&nbsp;'.$w->buttonAdd, 'btn btn-small btn-primary' ) )
	)
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
$panelUsers	= HTML::DivClass( 'content-panel',
	HTML::H3( $w->legend ).
	HTML::DivClass( 'content-panel-inner',
		HTML::UlClass( 'list-users', $listUsers ? join( $listUsers ) : $w->noEntries ).
		HTML::Buttons( UI_HTML_Elements::LinkButton( './manage/company/user/add/'.$company->companyId, $iconAdd.'&nbsp;'.$w->buttonAdd, 'btn btn-small btn-primary' ) )
	)
);

//  --  PANEL: EDIT  --  //


$w			= (object) $words['edit'];
$optStatus	= HTML::Options( $words['states'], $company->status );
$panelEdit	= HTML::Form( './manage/company/edit/'.$company->companyId, 'company_edit',
	HTML::DivClass( 'content-panel',
		HTML::H3( $w->legend ).
		HTML::DivClass( 'content-panel-inner',
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
			HTML::DivClass( 'row-fluid',
				HTML::DivClass( 'span12',
					HTML::Label( 'description', $w->labelDescription ).
					UI_HTML_Tag::create( 'textarea', $company->description, array(
						'name'	=> 'description',
						'id'	=> 'input_description',
						'class' => 'span12',
						'rows' => '10'
					) )
				)
			).
			HTML::Buttons(
				HTML::DivClass( 'btn-toolbar',
					UI_HTML_Elements::LinkButton( './manage/company', $iconCancel.'&nbsp;'.$w->buttonCancel, 'btn btn-small' ).
					UI_HTML_Elements::Button( 'save', $iconSave.'&nbsp;'.$w->buttonSave, 'btn btn-primary' ).
					HTML::LinkButton( './manage/company/remove/'.$company->companyId, $iconRemove.'&nbsp;'.$w->buttonRemove, 'btn btn-small btn-danger' )
				)
			)
		)
	)
);

$panelLogo	= $view->loadTemplateFile( 'manage/company/edit.logo.php' );

return //	UI_HTML_Tag::create( 'h2', $w->heading ).
HTML::DivClass( 'row-fluid',
	HTML::DivClass( 'span8',
		$panelEdit
	).
	HTML::DivClass( 'span4',
		$panelLogo
	)
).
HTML::DivClass( 'row-fluid',
	HTML::DivClass( 'span4',
		$panelBranches
	).
	HTML::DivClass( 'span4',
		$panelUsers
	)
);
?>
