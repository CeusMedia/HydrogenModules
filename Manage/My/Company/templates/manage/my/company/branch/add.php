<?php
$w		= (object) $words['add'];

$iconCancel	= HTML::Icon( 'arrow-left' );
$iconSave	= HTML::Icon( 'ok', TRUE );

extract( $view->populateTexts( array( 'top', 'info', 'bottom' ), 'html/manage/my/branch/add/' ) );

$optCompany	= array();
foreach( $companies as $company )
	$optCompany[$company->companyId]	= $company->title;

$panelAdd	= '
<div class="content-panel">
	'.HTML::H3( $w->legend, 'branch add' ).'
	<div class="content-panel-inner">
		<form action="./manage/my/company/branch/add" method="post">'.
			HTML::DivClass( 'row-fluid',
				HTML::DivClass( 'span6',
					HTML::Label( 'title', $w->labelTitle, 'mandatory' ).
					HTML::Input( 'title', $branch->title, 'span12 mandatory' )
				).
				HTML::DivClass( 'span6',
					HTML::Label( 'companyId', $w->labelCompany, 'mandatory' ).
					HTML::Select( 'companyId', $optCompany, 'span12 mandatory' )
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
				HTML::DivClass( 'span6',
					HTML::Label( 'url', $w->labelUrl ).
					HTML::Input( 'url', $branch->url, 'span12' )
				).
				HTML::DivClass( 'span3',
					HTML::Label( 'phone', $w->labelPhone ).
					HTML::Input( 'phone', $branch->phone, 'span12' )
				).
				HTML::DivClass( 'span3',
					HTML::Label( 'fax', $w->labelFax ).
					HTML::Input( 'fax', $branch->fax, 'span12' )
				)
			).
			HTML::DivClass( 'buttonbar',
				HTML::LinkButton( './manage/my/company/branch', $iconCancel.'&nbsp;'.$w->buttonCancel, 'btn btn-small' ).
				'&nbsp;|&nbsp'.
				HTML::Button( 'save', $iconSave.'&nbsp;'.$w->buttonSave, 'btn btn-success' )
			).'
		</form>
	</div>
</div>';

return '
<div class="row-fluid">
	<div class="span8">
		'.$panelAdd.'
	</div>
	<div class="span4">
		'.$textInfo.'
	</div>
</div>';
?>
