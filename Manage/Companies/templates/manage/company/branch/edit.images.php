<?php

$w	= (object) $words['edit-images'];

$iconCancel	= HTML::Icon( 'arrow-left' );
$iconSave	= HTML::Icon( 'ok', TRUE );
$iconRemove	= HTML::Icon( 'trash', TRUE );
$iconOpen	= HTML::Icon( 'folder-open', TRUE );

$pathImages	= $frontend->getPath( 'images' );

$listImages	= $w->noEntries;
if( $branch->images ){
	$listImages	= array();
	foreach( $branch->images as $image ){
		$urlImage		= $pathImages.'branches/'.$image->filename;
		$urlRemove		= './manage/company/branch/removeImage/'.$branch->branchId.'/'.$image->branchImageId;
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
						'title'	=> $w->buttonRemove,
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

$style		= '<style>
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
</style>';


return HTML::DivClass( 'content-panel',
	HTML::H3( $w->legend ).
	HTML::DivClass( 'content-panel-inner',
		$listImages
	).
	HTML::HR.
	HTML::Form( './manage/company/branch/addImage/'.$branch->branchId, 'my_branch_image_add',
		HTML::Label( 'image_title', $w->labelImage, '' ).
		HTML::DivClass( 'row-fluid',
			View_Helper_Input_File::render( 'image', $iconOpen, 'Datei auswählen...' )
		).
		HTML::DivClass( 'row-fluid',
			HTML::DivClass( 'span12',
				HTML::Label( 'image_title', $w->labelTitle, '' ).
				HTML::Input( 'image_title', $request->get( 'image_title' ), 'span12' )
			)
		).
		HTML::Buttons(
			UI_HTML_Elements::Button( 'doUpload', $iconSave.'&nbsp;'.$w->buttonUpload, 'btn btn-primary btn-small' )
		)
	)
).$style;


return $panelImages.$panelAddImage.$style;

return HTML::DivClass( 'row-fluid',
	HTML::DivClass( 'span7',
		$panelImages
	).
	HTML::DivClass( 'span5',
		$panelAddImage
	)
).$style;
?>
