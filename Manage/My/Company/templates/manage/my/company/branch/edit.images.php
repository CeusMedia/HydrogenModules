<?php

$iconCancel	= HTML::Icon( 'arrow-left' );
$iconSave	= HTML::Icon( 'ok', TRUE );
$iconRemove	= HTML::Icon( 'trash', TRUE );
$iconOpen	= HTML::Icon( 'folder-open', TRUE );

$listImages	= $words['images']['noEntries'];
if( $branch->images ){
	$listImages	= array();
	foreach( $branch->images as $image ){
		$urlImage		= 'images/branches/'.$image->filename;
		$urlRemove		= './manage/my/company/branch/removeImage/'.$branch->branchId.'/'.$image->branchImageId;
		$img			= new UI_Image( $urlImage );
		$title			= $image->title ? $image->title : '<small class="muted"><em>Kein Titel.</em></small>';
		$listImages[]	= UI_HTML_Tag::create( 'tr',
			UI_HTML_Tag::create( 'td',
				UI_HTML_Tag::create( 'a', HTML::Image( $urlImage, $image->title, 'medium thumbnail' ), array(
 					'class'	=> 'fancybox-auto',
					'href'	=> $urlImage,
					'rel'	=> 'gallery',
					'title'	=> $image->title,
				) )
			).
			UI_HTML_Tag::create( 'td',
				HTML::DivClass( 'image-item',
					UI_HTML_Tag::create( 'big', $title, array( 'class' => 'autocut' ) ).
					HTML::UlClass( 'not-image-info unstyled',
						HTML::Li( 'Datum: '.date( 'd.m.Y H:i', $image->uploadedAt ) ).
						HTML::Li( 'Größe: '.$img->getWidth().' x '.$img->getHeight() )
					).
					UI_HTML_Tag::create( 'a', $iconRemove, array(
						'href'	=> $urlRemove,
						'title'	=> $words['images']['buttonRemove'],
						'class'	=> 'btn btn-mini btn-inverse'
					) )
				)
			)
		);
	}
	$colgroup	= UI_HTML_Elements::ColumnGroup( "30%", "70%" );
	$tbody		= UI_HTML_Tag::create( 'tbody', $listImages );
	$listImages	= UI_HTML_Tag::create( 'table', $colgroup.$tbody, array( 'class' => 'table', 'style' => 'table-layout: fixed' ) );
}
$panelImages	= HTML::DivClass( 'content-panel',
	HTML::H3( $words['images']['legend'] ).
	HTML::DivClass( 'content-panel-inner',
		$listImages
	)
);

$panelAddImage	= 	HTML::DivClass( 'content-panel',
	HTML::H3( $words['addImage']['legend'] ).
	HTML::DivClass( 'content-panel-inner',
		HTML::Form( './manage/my/company/branch/addImage/'.$branch->branchId, 'my_branch_image_add',
			HTML::Label( 'image_title', $words['addImage']['labelImage'], '' ).
			HTML::DivClass( 'row-fluid',
				View_Helper_Input_File::render( 'image', $iconOpen, 'Datei auswählen...' )
			).
			HTML::DivClass( 'row-fluid',
				HTML::DivClass( 'span12',
					HTML::Label( 'image_title', $words['addImage']['labelTitle'], '' ).
					HTML::Input( 'image_title', $request->get( 'image_title' ), 'span12' )
				)
			).
			HTML::Buttons(
				UI_HTML_Elements::Button( 'doUpload', $words['addImage']['buttonUpload'], 'btn btn-primary btn-small' )
			)
		)
	)
);

return HTML::DivClass( 'row-fluid',
	HTML::DivClass( 'span7',
		$panelImages
	).
	HTML::DivClass( 'span5',
		$panelAddImage
	)
).'
<style>
.image-item {
	position: relative;
	}
.image-item ul li {
	color: gray;
	}
.image-item a.btn.btn-inverse {
	position: absolute;
	bottom: 10px;
	right: 2px;
	}
.autocut {
	display: block;
	width: 100%;
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
	}
</style>
';
?>
