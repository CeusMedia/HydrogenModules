<?php

$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => "fa fa-fw fa-arrow-left" ) );
$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => "fa fa-fw fa-check" ) );
$iconOpen		= UI_HTML_Tag::create( 'i', '', array( 'class' => "fa fa-fw fa-folder-open" ) );
$iconView		= UI_HTML_Tag::create( 'i', '', array( 'class' => "fa fa-fw fa-eye" ) );
$iconExists		= UI_HTML_Tag::create( 'i', '', array( 'class' => "fa fa-fw fa-2x fa-check" ) );
$iconMissing	= UI_HTML_Tag::create( 'i', '', array( 'class' => "fa fa-fw fa-2x fa-warning" ) );
$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => "fa fa-fw fa-remove" ) );

$modalImage		= new View_Helper_Input_Resource( $env );
$modalImage->setModalId( 'modal-manage-workshop-select-image' );
$modalImage->setInputId( 'input_image' );
$trigger	= new View_Helper_Input_ResourceTrigger( $env );
$trigger->setLabel( $iconOpen );
$trigger->setModalId( 'modal-manage-workshop-select-image' );
$trigger->setInputId( 'input_image' );

//print_m( $workshop );die;

$optStatus		= UI_HTML_Elements::Options( $words['statuses'], $workshop->status );
$optRank		= UI_HTML_Elements::Options( $words['ranks'], $workshop->rank );
$optImageAlignH	= array_diff_key( $words['image-align-h'], array( 0 ) );
$optImageAlignV	= array_diff_key( $words['image-align-v'], array( 0 ) );
$optImageAlignH	= UI_HTML_Elements::Options( $optImageAlignH, $workshop->imageAlignH );
$optImageAlignV	= UI_HTML_Elements::Options( $optImageAlignV, $workshop->imageAlignV );

$panelEdit	= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'Workshop ändern' ),
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'form', array(

			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'label', 'Titel', array( 'for' => 'input_title' ) ),
					UI_HTML_Tag::create( 'input', NULL, array(
						'type'		=> 'text',
						'name'		=> 'title',
						'id'		=> 'input_title',
						'value'		=> $workshop->title,
						'class'		=> 'span12',
					) ),
				), array( 'class' => 'span7' ) ),
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'label', 'Zustand', array( 'for' => 'input_status' ) ),
					UI_HTML_Tag::create( 'select', $optStatus, array(
						'name'		=> 'status',
						'id'		=> 'input_status',
						'class'		=> 'span12',
					) ),
				), array( 'class' => 'span3' ) ),
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'label', 'Rang', array( 'for' => 'input_rank' ) ),
					UI_HTML_Tag::create( 'select', $optRank, array(
						'name'		=> 'rank',
						'id'		=> 'input_rank',
						'class'		=> 'span12',
					) ),
				), array( 'class' => 'span2' ) ),
			), array( 'class' => 'row-fluid' ) ),

			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'label', '<acronym title="Inhalt der Detailansicht">Inhalt</acronym>', array( 'for' => 'input_description' ) ),
					UI_HTML_Tag::create( 'textarea', $workshop->description, array(
						'name'		=> 'description',
						'id'		=> 'input_description',
						'class'		=> 'span12 '.$tinyMceAutoClass,
						'rows'		=> '20',
					), array(
						'tinymce-mode'	=> $tinyMceAutoMode,
					) ),
				), array( 'class' => 'span12' ) ),
			), array( 'class' => 'row-fluid' ) ),

			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'label', '<acronym title="Kurzbeschreibung für Darstellung in der Übersicht, wird in Detailansicht nicht verwendet">Abstrakt</acronym>', array( 'for' => 'input_abstract' ) ),
					UI_HTML_Tag::create( 'textarea', $workshop->abstract, array(
						'name'		=> 'abstract',
						'id'		=> 'input_abstract',
						'class'		=> 'span12 '.$tinyMceAutoClass,
						'rows'		=> '4',
					), array(
						'tinymce-mode'	=> 'minimal',
					) ),
				), array( 'class' => 'span12' ) ),
			), array( 'class' => 'row-fluid' ) ),

			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'label', 'Bild', array( 'for' => 'input_image' ) ),
					UI_HTML_Tag::create( 'input', NULL, array(
						'type'		=> 'text',
						'name'		=> 'image',
						'id'		=> 'input_image',
						'value'		=> $workshop->image,
						'class'		=> 'span12',
					) ),
				), array( 'class' => 'span7' ) ),
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'label', '&nbsp;' ),
					$trigger
				), array( 'class' => 'span1' ) ),
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'label', 'Horizontal', array( 'for' => 'input_imageAlignH' ) ),
					UI_HTML_Tag::create( 'select', $optImageAlignH, array(
						'name'		=> 'imageAlignH',
						'id'		=> 'input_imageAlignH',
						'class'		=> 'span12',
					) ),
				), array( 'class' => 'span2' ) ),
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'label', 'Vertikal', array( 'for' => 'input_imageAlignV' ) ),
					UI_HTML_Tag::create( 'select', $optImageAlignV, array(
						'name'		=> 'imageAlignV',
						'id'		=> 'input_imageAlignV',
						'class'		=> 'span12',
					) ),
				), array( 'class' => 'span2' ) ),
			), array( 'class' => 'row-fluid' ) ),



			UI_HTML_Tag::create( 'div', join( ' ', array(
				UI_HTML_Tag::create( 'a', $iconCancel.'&nbsp;zurück', array( 'href' => './manage/workshop', 'class' => 'btn' ) ),
				UI_HTML_Tag::create( 'button', $iconSave.'&nbsp;speichern', array( 'type' => 'submit', 'name' => 'save', 'class' => 'btn btn-primary' ) ),
				UI_HTML_Tag::create( 'a', $iconRemove.'&nbsp;entfernen', array( 'href' => './manage/workshop/remove/'.$workshop->workshopId, 'class' => 'btn btn-danger btn-small' ) ),
			) ), array( 'class' => 'buttonbar' ) ),

		), array( 'method' => 'post', 'action' => './manage/workshop/edit/'.$workshop->workshopId ) ),
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );

return $panelEdit.$modalImage;
