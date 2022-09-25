<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconCancel		= HtmlTag::create( 'i', '', array( 'class' => "fa fa-fw fa-arrow-left" ) );
$iconSave		= HtmlTag::create( 'i', '', array( 'class' => "fa fa-fw fa-check" ) );
$iconOpen		= HtmlTag::create( 'i', '', array( 'class' => "fa fa-fw fa-folder-open" ) );
$iconView		= HtmlTag::create( 'i', '', array( 'class' => "fa fa-fw fa-eye" ) );
$iconExists		= HtmlTag::create( 'i', '', array( 'class' => "fa fa-fw fa-2x fa-check" ) );
$iconMissing	= HtmlTag::create( 'i', '', array( 'class' => "fa fa-fw fa-2x fa-warning" ) );
$iconRemove		= HtmlTag::create( 'i', '', array( 'class' => "fa fa-fw fa-remove" ) );

$modalImage		= new View_Helper_Input_Resource( $env );
$modalImage->setModalId( 'modal-manage-workshop-select-image' );
$modalImage->setInputId( 'input_image' );
$trigger	= new View_Helper_Input_ResourceTrigger( $env );
$trigger->setLabel( $iconOpen );
$trigger->setModalId( 'modal-manage-workshop-select-image' );
$trigger->setInputId( 'input_image' );

//print_m( $workshop );die;
print_m( $words );die;

$optStatus		= array_diff_key( $words['statuses'], array( -2, -1, 2, 3 ) );
$optStatus		= UI_HTML_Elements::Options( $optStatus, $workshop->status );

$optRank		= array_diff_key( $words['ranks'], array( 0 ) );
$optRank		= UI_HTML_Elements::Options( $optRank, $workshop->rank );

$optImageAlignH	= array_diff_key( $words['image-align-h'], array( 0 ) );
$optImageAlignH	= UI_HTML_Elements::Options( $optImageAlignH, $workshop->imageAlignH );

$optImageAlignV	= array_diff_key( $words['image-align-v'], array( 0 ) );
$optImageAlignV	= UI_HTML_Elements::Options( $optImageAlignV, $workshop->imageAlignV );

$panelEdit	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', 'neuer Workshop' ),
	HtmlTag::create( 'div', array(
		HtmlTag::create( 'form', array(

			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', 'Titel', array( 'for' => 'input_title' ) ),
					HtmlTag::create( 'input', NULL, array(
						'type'		=> 'text',
						'name'		=> 'title',
						'id'		=> 'input_title',
						'value'		=> $workshop->title,
						'class'		=> 'span12',
					) ),
				), array( 'class' => 'span7' ) ),
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', 'Zustand', array( 'for' => 'input_status' ) ),
					HtmlTag::create( 'select', $optStatus, array(
						'name'		=> 'status',
						'id'		=> 'input_status',
						'class'		=> 'span12',
					) ),
				), array( 'class' => 'span3' ) ),
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', 'Rang', array( 'for' => 'input_rank' ) ),
					HtmlTag::create( 'select', $optRank, array(
						'name'		=> 'rank',
						'id'		=> 'input_rank',
						'class'		=> 'span12',
					) ),
				), array( 'class' => 'span2' ) ),
			), array( 'class' => 'row-fluid' ) ),

			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', '<acronym title="Inhalt der Detailansicht">Inhalt</acronym>', array( 'for' => 'input_description' ) ),
					HtmlTag::create( 'textarea', $workshop->description, array(
						'name'		=> 'description',
						'id'		=> 'input_description',
						'class'		=> 'span12 '.$tinyMceAutoClass,
						'rows'		=> '15',
					), array(
						'tinymce-mode'	=> $tinyMceAutoMode,
					) ),
				), array( 'class' => 'span12' ) ),
			), array( 'class' => 'row-fluid' ) ),

			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', '<acronym title="Kurzbeschreibung für Darstellung in der Übersicht, wird in Detailansicht nicht verwendet">Abstrakt</acronym>', array( 'for' => 'input_abstract' ) ),
					HtmlTag::create( 'textarea', $workshop->abstract, array(
						'name'		=> 'abstract',
						'id'		=> 'input_abstract',
						'class'		=> 'span12 '.$tinyMceAutoClass,
						'rows'		=> '3',
					), array(
						'tinymce-mode'	=> 'minimal',
					) ),
				), array( 'class' => 'span12' ) ),
			), array( 'class' => 'row-fluid' ) ),

			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', 'Bild', array( 'for' => 'input_image' ) ),
					HtmlTag::create( 'input', NULL, array(
						'type'		=> 'text',
						'name'		=> 'image',
						'id'		=> 'input_image',
						'value'		=> $workshop->image,
						'class'		=> 'span12',
					) ),
				), array( 'class' => 'span7' ) ),
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', '&nbsp;' ),
					$trigger
				), array( 'class' => 'span1' ) ),
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', 'Horizontal', array( 'for' => 'input_imageAlignH' ) ),
					HtmlTag::create( 'select', $optImageAlignH, array(
						'name'		=> 'imageAlignH',
						'id'		=> 'input_imageAlignH',
						'class'		=> 'span12',
					) ),
				), array( 'class' => 'span2' ) ),
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', 'Vertikal', array( 'for' => 'input_imageAlignV' ) ),
					HtmlTag::create( 'select', $optImageAlignV, array(
						'name'		=> 'imageAlignV',
						'id'		=> 'input_imageAlignV',
						'class'		=> 'span12',
					) ),
				), array( 'class' => 'span2' ) ),
			), array( 'class' => 'row-fluid' ) ),



			HtmlTag::create( 'div', join( ' ', array(
				HtmlTag::create( 'a', $iconCancel.'&nbsp;zurück', array( 'href' => './manage/workshop', 'class' => 'btn' ) ),
				HtmlTag::create( 'button', $iconSave.'&nbsp;speichern', array( 'type' => 'submit', 'name' => 'save', 'class' => 'btn btn-primary' ) ),
			) ), array( 'class' => 'buttonbar' ) ),

		), array( 'method' => 'post', 'action' => './manage/workshop/add' ) ),
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );

return $panelEdit.$modalImage;
