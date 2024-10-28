<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w	= (object) $words['add'];

$iconCancel	= HtmlTag::create( 'i', '', ['class' => 'icon-arrow-left'] );
$iconSave	= HtmlTag::create( 'i', '', ['class' => 'icon-ok icon-white'] );

$optCompany	= HTML::Options( $companies, $branch->companyId, ['companyId', 'title'] );

$panelAdd	= HTML::DivClass( 'content-panel',
	HTML::H3( $w->legend ).
	HTML::DivClass( 'content-panel-inner',
		HTML::Form( './manage/company/branch/add', 'branch_add',
			HTML::DivClass( 'row-fluid',
				HTML::DivClass( 'span6',
					HTML::Label( 'title', $w->labelTitle, 'mandatory' ).
					HtmlTag::create( 'input', NULL, [
						'type'		=> 'text',
						'name'		=> 'title',
						'id'		=> 'input_title',
						'value'		=> htmlentities( $branch->title, ENT_QUOTES, 'UTF-8' ),
						'class'		=> 'span12 mandatory',
						'required'	=> 'required',
					] )
				).
				HTML::DivClass( 'span6',
					HTML::Label( 'companyId', $w->labelCompany ).
					HTML::Select( 'companyId', $optCompany, 'span12' )
				)
			).
			HTML::DivClass( 'row-fluid',
				HTML::DivClass( 'span2',
					HTML::Label( 'postcode', $w->labelPostcode, 'mandatory' ).
					HTML::Input( 'postcode', $branch->postcode, 'span12 mandatory' )
				).
				HTML::DivClass( 'span4',
					HTML::Label( 'city', $w->labelCity, 'mandatory' ).
					HTML::Input( 'city', $branch->city, 'span12 mandatory' )
				).
				HTML::DivClass( 'span4',
					HTML::Label( 'street', $w->labelStreet, 'mandatory' ).
					HTML::Input( 'street', $branch->street, 'span12 mandatory' )
				).
				HTML::DivClass( 'span2',
					HTML::Label( 'number', $w->labelNumber, 'mandatory' ).
					HTML::Input( 'number', $branch->number, 'span12 mandatory' )
				)
			).
			HTML::DivClass( 'row-fluid',
				HTML::DivClass( 'span3',
					HTML::Label( 'phone', $w->labelPhone ).
					HTML::Input( 'phone', $branch->phone, 'span12' )
				).
				HTML::DivClass( 'span3',
					HTML::Label( 'fax', $w->labelFax ).
					HTML::Input( 'fax', $branch->fax, 'span12' )
				).
				HTML::DivClass( 'span6',
					HTML::Label( 'url', $w->labelUrl ).
					HTML::Input( 'url', $branch->url, 'span12' )
				)
			).
			HTML::DivClass( 'buttonbar',
				HTML::DivClass( 'btn-toolbar',
					HTML::LinkButton( './manage/company/branch', $iconCancel.'&nbsp;'.$w->buttonCancel, 'btn btn-small' ).
					HtmlTag::create( 'button', $iconSave.'&nbsp;'.$w->buttonSave, [
						'type'	=> 'submit',
						'name'	=> 'save',
						'class'	=> 'btn btn-primary'
					] )
				)
			)
		)
	)
);


return HTML::DivClass( 'row-fluid',
	HTML::DivClass( 'span8',
		$panelAdd
	).
	HTML::DivClass( 'span4',
		''
	)
);
