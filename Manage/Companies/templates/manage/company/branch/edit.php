<?php
$w	= (object) $words['edit'];

$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-arrow-left' ) );
$iconCompany	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-home' ) );
$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-ok icon-white' ) );
$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-trash icon-white' ) );

$iconActivate	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-check icon-white' ) );
$iconReject		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-remove icon-white' ) );
$iconDeactivate	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-remove icon-white' ) );


$optStatus	= HTML::Options( $words['states'], $branch->status );
$optCompany	= HTML::Options( $companies, $branch->companyId, array( 'companyId', 'title' ) );

$panelMap	= '';
if( $branch->longitude ){
	$geocoder	= new Net_API_Google_Maps_Geocoder( "" );
	$geocoder->setCachePath( 'cache/geo/' );
	$query		= $branch->street.' '.$branch->number.', '.$branch->postcode.' '.$branch->city.', Deutschland';
	$addr		= utf8_decode( $geocoder->getAddress( $query ) );
	$tags		= (object) $geocoder->getGeoTags( $query );

	$panelMap	= HTML::H3( 'Geoinformationen' ).'
	<div style="border: 1px solid black; width: 100%; height: 500px">
		'.UI_HTML_Tag::create( 'div', '', array(
			'id'	=> "map_canvas",
			'style'	=> "width:100%; height:100%",
			'data-longitude'	=> $branch->longitude,
			'data-latitude'		=> $branch->latitude,
			'data-marker-title'	=> htmlentities( $addr, ENT_QUOTES, 'UTF-8' )
		) ).'
	</div>
	<div>
		<dl class="dl-horizontal">
			<dt>Adresse</dt>
			<dd>'.$addr.'</dd>
			<dt>Breitengrad</dt>
			<dd>'.$branch->latitude.'</dd>
			<dt>Längengrad</dt>
			<dd>'.$branch->longitude.'</dd>
			<dt>Vollansicht</dt>
			<dd><a href="#">bei Google</a></dd>
		</dl>
	</div>
	<script>$(document).ready(function(){loadMap("map_canvas");});</script>
	';
}

$buttonActivate	= '';
if( in_array( $branch->status, array( 0, 1 ) ) ){
	$buttonActivate		= UI_HTML_Tag::create( 'a', $iconActivate.'&nbsp;'.$w->buttonActivate, array(
		'href'	=> './manage/company/branch/activate/'.$branch->branchId,
		'class'	=> 'btn btn-small btn-success',
	) );
}
$buttonReject	= '';
if( in_array( $branch->status, array( 0, 1 ) ) ){
	$buttonReject	= UI_HTML_Tag::create( 'a', $iconReject.'&nbsp;'.$w->buttonReject, array(
		'href'	=> './manage/company/branch/reject/'.$branch->branchId,
		'class'	=> 'btn btn-small btn-inverse',
	) );
}
$buttonDeactivate	= '';
/*if( in_array( $branch->status, array( 0, 1 ) ) ){
	$buttonDeactivate	= UI_HTML_Tag::create( 'a', $iconDeactivate.'&nbsp;'.$w->buttonDeactivate, array(
		'href'	=> './manage/company/branch/deactivate/'.$branch->branchId,
		'class'	=> 'btn btn-small btn-inverse',
	) );
}*/

$panelEdit	= HTML::DivClass( 'content-panel',
	UI_HTML_Tag::create( 'h3', '<a class="muted" href="./manage/company/branch/">'.$w->legend.'</a> '.$branch->title ).
	HTML::DivClass( 'content-panel-inner',
		HTML::Form( './manage/company/branch/edit/'.$branch->branchId, 'branch_edit',
			HTML::DivClass( 'row-fluid',
				HTML::DivClass( 'span5',
					HTML::Label( 'title', $w->labelTitle, 'mandatory' ).
					HTML::Input( 'title', $branch->title, 'span12 mandatory required' )
				).
				HTML::DivClass( 'span4',
					HTML::Label( 'title', $w->labelCompany, 'mandatory' ).
					HTML::Select( 'companyId', $optCompany, 'span12' )
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
			HTML::HR.
			HTML::DivClass( 'row-fluid',
				HTML::DivClass( 'span12',
					HTML::Label( 'url', $w->labelDescription ).
					UI_HTML_Tag::create( 'textarea', $branch->description, array(
						'name'	=> 'description',
						'id'	=> 'input_description',
						'class' => 'span12',
						'rows' => '10'
					) )
				)
			).
			HTML::DivClass( 'buttonbar',
				HTML::DivClass( 'btn-toolbar',
					UI_HTML_Elements::LinkButton( './manage/company/branch', $iconCancel.'&nbsp;'.$w->buttonCancel, 'btn btn-small' ).
					UI_HTML_Elements::LinkButton( './manage/company/edit/'.$branch->companyId, $iconCompany.'&nbsp;'.$w->buttonCompany, 'btn btn-small' ).
					UI_HTML_Elements::Button( 'save', $iconSave.'&nbsp;'.$w->buttonSave, 'btn btn-primary' ).
					$buttonActivate.
					$buttonReject.
					$buttonDeactivate.
					UI_HTML_Elements::LinkButton( './manage/company/branch/remove/'.$branch->branchId, $iconRemove.'&nbsp;'.$w->buttonRemove, 'btn btn-danger btn-small' )
				)
			)
		)
	)
);

$panelImages	= $view->loadTemplateFile( 'manage/company/branch/edit.images.php' );
$panelTags		= $view->loadTemplateFile( 'manage/company/branch/edit.tags.php' );

return HTML::DivClass( 'row-fluid',
#	UI_HTML_Tag::create( 'h2', $w->heading ).
	HTML::DivClass( 'span8',
		$panelEdit
	).
	HTML::DivClass( 'span4',
		$panelMap
	)
).
HTML::DivClass( 'row-fluid',
	HTML::DivClass( 'span7',
		$panelImages
	).
	HTML::DivClass( 'span5',
		$panelTags
	)
);
?>
