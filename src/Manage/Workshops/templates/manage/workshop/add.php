<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as Environment;

/** @var Environment $env */
/** @var array<string,array<string,string>> $words */

$iconCancel		= HtmlTag::create( 'i', '', ['class' => "fa fa-fw fa-arrow-left"] );
$iconSave		= HtmlTag::create( 'i', '', ['class' => "fa fa-fw fa-check"] );
$iconOpen		= HtmlTag::create( 'i', '', ['class' => "fa fa-fw fa-folder-open"] );
$iconView		= HtmlTag::create( 'i', '', ['class' => "fa fa-fw fa-eye"] );
$iconExists		= HtmlTag::create( 'i', '', ['class' => "fa fa-fw fa-2x fa-check"] );
$iconMissing	= HtmlTag::create( 'i', '', ['class' => "fa fa-fw fa-2x fa-warning"] );
$iconRemove		= HtmlTag::create( 'i', '', ['class' => "fa fa-fw fa-remove"] );

$modalImage		= new View_Helper_Input_Resource( $env );
$modalImage->setModalId( 'modal-manage-workshop-select-image' );
$modalImage->setInputId( 'input_image' );
$trigger	= new View_Helper_Input_ResourceTrigger( $env );
$trigger->setLabel( $iconOpen );
$trigger->setModalId( 'modal-manage-workshop-select-image' );
$trigger->setInputId( 'input_image' );

//print_m( $workshop );die;
print_m( $words );die;

$optStatus		= array_diff_key( $words['statuses'], [-2, -1, 2, 3] );
$optStatus		= HtmlElements::Options( $optStatus, $workshop->status );

$optRank		= array_diff_key( $words['ranks'], [0] );
$optRank		= HtmlElements::Options( $optRank, $workshop->rank );

$optImageAlignH	= array_diff_key( $words['image-align-h'], [0] );
$optImageAlignH	= HtmlElements::Options( $optImageAlignH, $workshop->imageAlignH );

$optImageAlignV	= array_diff_key( $words['image-align-v'], [0] );
$optImageAlignV	= HtmlElements::Options( $optImageAlignV, $workshop->imageAlignV );

$panelEdit	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', 'neuer Workshop' ),
	HtmlTag::create( 'div', array(
		HtmlTag::create( 'form', array(

			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', 'Titel', ['for' => 'input_title'] ),
					HtmlTag::create( 'input', NULL, [
						'type'		=> 'text',
						'name'		=> 'title',
						'id'		=> 'input_title',
						'value'		=> $workshop->title,
						'class'		=> 'span12',
					] ),
				), ['class' => 'span7'] ),
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', 'Zustand', ['for' => 'input_status'] ),
					HtmlTag::create( 'select', $optStatus, [
						'name'		=> 'status',
						'id'		=> 'input_status',
						'class'		=> 'span12',
					] ),
				), ['class' => 'span3'] ),
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', 'Rang', ['for' => 'input_rank'] ),
					HtmlTag::create( 'select', $optRank, [
						'name'		=> 'rank',
						'id'		=> 'input_rank',
						'class'		=> 'span12',
					] ),
				), ['class' => 'span2'] ),
			), ['class' => 'row-fluid'] ),

			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', '<acronym title="Inhalt der Detailansicht">Inhalt</acronym>', ['for' => 'input_description'] ),
					HtmlTag::create( 'textarea', $workshop->description, [
						'name'		=> 'description',
						'id'		=> 'input_description',
						'class'		=> 'span12 '.$tinyMceAutoClass,
						'rows'		=> '15',
					], [
						'tinymce-mode'	=> $tinyMceAutoMode,
					] ),
				), ['class' => 'span12'] ),
			), ['class' => 'row-fluid'] ),

			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', '<acronym title="Kurzbeschreibung für Darstellung in der Übersicht, wird in Detailansicht nicht verwendet">Abstrakt</acronym>', ['for' => 'input_abstract'] ),
					HtmlTag::create( 'textarea', $workshop->abstract, [
						'name'		=> 'abstract',
						'id'		=> 'input_abstract',
						'class'		=> 'span12 '.$tinyMceAutoClass,
						'rows'		=> '3',
					], [
						'tinymce-mode'	=> 'minimal',
					] ),
				), ['class' => 'span12'] ),
			), ['class' => 'row-fluid'] ),

			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', 'Bild', ['for' => 'input_image'] ),
					HtmlTag::create( 'input', NULL, [
						'type'		=> 'text',
						'name'		=> 'image',
						'id'		=> 'input_image',
						'value'		=> $workshop->image,
						'class'		=> 'span12',
					] ),
				), ['class' => 'span7'] ),
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', '&nbsp;' ),
					$trigger
				), ['class' => 'span1'] ),
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', 'Horizontal', ['for' => 'input_imageAlignH'] ),
					HtmlTag::create( 'select', $optImageAlignH, [
						'name'		=> 'imageAlignH',
						'id'		=> 'input_imageAlignH',
						'class'		=> 'span12',
					] ),
				), ['class' => 'span2'] ),
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', 'Vertikal', ['for' => 'input_imageAlignV'] ),
					HtmlTag::create( 'select', $optImageAlignV, [
						'name'		=> 'imageAlignV',
						'id'		=> 'input_imageAlignV',
						'class'		=> 'span12',
					] ),
				), ['class' => 'span2'] ),
			), ['class' => 'row-fluid'] ),



			HtmlTag::create( 'div', join( ' ', array(
				HtmlTag::create( 'a', $iconCancel.'&nbsp;zurück', ['href' => './manage/workshop', 'class' => 'btn'] ),
				HtmlTag::create( 'button', $iconSave.'&nbsp;speichern', ['type' => 'submit', 'name' => 'save', 'class' => 'btn btn-primary'] ),
			) ), ['class' => 'buttonbar'] ),

		), ['method' => 'post', 'action' => './manage/workshop/add'] ),
	), ['class' => 'content-panel-inner'] ),
), ['class' => 'content-panel'] );

return $panelEdit.$modalImage;
