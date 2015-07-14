<?php
$w	= (object) $words['add'];

$iconCancel	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-arrow-left' ) );
$iconSave	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-ok icon-white' ) );

$panelAdd	= HTML::DivClass( 'content-panel',
	HTML::H3( $w->legend ).
	HTML::DivClass( 'content-panel-inner',
		HTML::Form( './manage/company/add', 'company_add',
			HTML::DivClass( 'row-fluid',
				HTML::DivClass( 'span8',
					HTML::Label( 'title', $w->labelTitle, 'mandatory' ).
					HTML::Input( 'title', $company->title, 'span12 mandatory' )
				).
				HTML::DivClass( 'span4',
					HTML::Label( 'sector', $w->labelSector ).
					HTML::Input( 'sector', $company->sector, 'span12' )
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
			HTML::DivClass( 'buttonbar',
				HTML::DivClass( 'btn-toolbar',
					UI_HTML_Elements::LinkButton( './manage/company', $iconCancel.'&nbsp;'.$w->buttonCancel, 'btn btn-small' ).
					UI_HTML_Elements::Button( 'save', $iconSave.'&nbsp;'.$w->buttonSave, 'btn btn-primary' )
				)
			)
		)
	)
);

return '
<div class="row-fluid">
	<div class="span8">
		'.$panelAdd.'
	</div>
</div>';
?>
