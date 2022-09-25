<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconFilter		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-search' ) );
$iconReset		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-search-minus' ) );

$optStatus	= array( '' => $wordsGeneral['list']['optAll'] );
foreach( $wordsGeneral['job-definition-statuses'] as $key => $value )
	$optStatus[$key]	= $value;
$optStatus	= UI_HTML_Elements::Options( $optStatus, $filterStatus );

$optMode	= array( '' => $wordsGeneral['list']['optAll'] );
foreach( $wordsGeneral['job-definition-modes'] as $key => $value )
	$optMode[$key]	= $value;
$optMode	= UI_HTML_Elements::Options( $optMode, $filterMode );

$optClass	= array( '' => $wordsGeneral['list']['optAll'] );
foreach( $classNames as $key => $value )
	$optClass[$value]	= $value;
$optClass	= UI_HTML_Elements::Options( $optClass, $filterClass );

$optMethod	= array( '' => $wordsGeneral['list']['optAll'] );
foreach( $methodNames as $key => $value )
	$optMethod[$value]	= $value;
$optMethod	= UI_HTML_Elements::Options( $optMethod, $filterMethod );

$panelFilter	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', $words['filter']['heading'] ),
	HtmlTag::create( 'div', array(
		HtmlTag::create( 'form', array(
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', $words['filter']['labelStatus'], array( 'for' => 'input_status' ) ),
					HtmlTag::create( 'select', $optStatus, array(
						'name' 		=> 'status',
						'id'		=> 'input_status',
						'class' 	=> 'span12',
					) ),
				), array( 'class' => 'span12' ) ),
			), array( 'class' => 'row-fluid' ) ),
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', $words['filter']['labelMode'], array( 'for' => 'input_mode' ) ),
					HtmlTag::create( 'select', $optMode, array(
						'name' 		=> 'mode',
						'id'		=> 'input_mode',
						'class' 	=> 'span12',
					) ),
				), array( 'class' => 'span12' ) ),
			), array( 'class' => 'row-fluid' ) ),
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', $words['filter']['labelClass'], array( 'for' => 'input_class' ) ),
					HtmlTag::create( 'select', $optClass, array(
						'name' 		=> 'class',
						'id'		=> 'input_class',
						'class' 	=> 'span12',
					) ),
				), array( 'class' => 'span12' ) ),
			), array( 'class' => 'row-fluid' ) ),
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', $words['filter']['labelMethod'], array( 'for' => 'input_method' ) ),
					HtmlTag::create( 'select', $optMethod, array(
						'name' 		=> 'method',
						'id'		=> 'input_method',
						'class' 	=> 'span12',
					) ),
				), array( 'class' => 'span12' ) ),
			), array( 'class' => 'row-fluid' ) ),
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'button', $iconFilter.'&nbsp;'.$words['filter']['buttonFilter'], array(
						'type'	=> 'submit',
						'name'	=> 'filter',
						'class'	=> 'btn not-btn-small not-btn-primary btn-info',
					) ),
//					HtmlTag::create( 'a', $iconReset.'&nbsp;'.$words['filter']['buttonReset'], array(
					HtmlTag::create( 'a', $iconReset, array(
						'href'	=> './manage/job/definition/filter/reset',
						'class'	=> 'btn not-btn-small btn-inverse',
					) ),
				), array( 'class' => 'btn-group' ) ),
			), array( 'class' => 'buttonbar' ) ),
		), array( 'action' => './manage/job/definition/filter', 'method' => 'POST' ) ),
	), array( 'class' => 'content-panel-inner' ) )
), array( 'class' => 'content-panel' ) );

return $panelFilter;
