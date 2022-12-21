<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconFilter		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-search'] );
$iconReset		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-search-minus'] );

$optStatus	= ['' => $wordsGeneral['list']['optAll']];
foreach( $wordsGeneral['job-definition-statuses'] as $key => $value )
	$optStatus[$key]	= $value;
$optStatus	= HtmlElements::Options( $optStatus, $filterStatus );

$optMode	= ['' => $wordsGeneral['list']['optAll']];
foreach( $wordsGeneral['job-definition-modes'] as $key => $value )
	$optMode[$key]	= $value;
$optMode	= HtmlElements::Options( $optMode, $filterMode );

$optClass	= ['' => $wordsGeneral['list']['optAll']];
foreach( $classNames as $key => $value )
	$optClass[$value]	= $value;
$optClass	= HtmlElements::Options( $optClass, $filterClass );

$optMethod	= ['' => $wordsGeneral['list']['optAll']];
foreach( $methodNames as $key => $value )
	$optMethod[$value]	= $value;
$optMethod	= HtmlElements::Options( $optMethod, $filterMethod );

$panelFilter	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', $words['filter']['heading'] ),
	HtmlTag::create( 'div', array(
		HtmlTag::create( 'form', array(
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', $words['filter']['labelStatus'], ['for' => 'input_status'] ),
					HtmlTag::create( 'select', $optStatus, array(
						'name' 		=> 'status',
						'id'		=> 'input_status',
						'class' 	=> 'span12',
					) ),
				), ['class' => 'span12'] ),
			), ['class' => 'row-fluid'] ),
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', $words['filter']['labelMode'], ['for' => 'input_mode'] ),
					HtmlTag::create( 'select', $optMode, array(
						'name' 		=> 'mode',
						'id'		=> 'input_mode',
						'class' 	=> 'span12',
					) ),
				), ['class' => 'span12'] ),
			), ['class' => 'row-fluid'] ),
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', $words['filter']['labelClass'], ['for' => 'input_class'] ),
					HtmlTag::create( 'select', $optClass, array(
						'name' 		=> 'class',
						'id'		=> 'input_class',
						'class' 	=> 'span12',
					) ),
				), ['class' => 'span12'] ),
			), ['class' => 'row-fluid'] ),
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', $words['filter']['labelMethod'], ['for' => 'input_method'] ),
					HtmlTag::create( 'select', $optMethod, array(
						'name' 		=> 'method',
						'id'		=> 'input_method',
						'class' 	=> 'span12',
					) ),
				), ['class' => 'span12'] ),
			), ['class' => 'row-fluid'] ),
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
				), ['class' => 'btn-group'] ),
			), ['class' => 'buttonbar'] ),
		), ['action' => './manage/job/definition/filter', 'method' => 'POST'] ),
	), ['class' => 'content-panel-inner'] )
), ['class' => 'content-panel'] );

return $panelFilter;
