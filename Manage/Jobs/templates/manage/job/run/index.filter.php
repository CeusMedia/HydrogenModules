<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconFilter		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-search' ) );
$iconReset		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-search-minus' ) );


$optStatus	= array( '' => $wordsGeneral['list']['optAll'] );
foreach( $wordsGeneral['job-run-statuses'] as $key => $value )
	$optStatus[(string) $key]	= $value;
$optStatus	= UI_HTML_Elements::Options( $optStatus, $filterStatus );

$optType	= array( '' => $wordsGeneral['list']['optAll'] );
foreach( $wordsGeneral['job-run-types'] as $key => $value )
	$optType[$key]	= $value;
$optType	= UI_HTML_Elements::Options( $optType, $filterType );

$optJobId	= array( '' => $wordsGeneral['list']['optAll'] );
foreach( $definitions as $jobId => $definition )
	$optJobId[$jobId]	= $definition->identifier;
$optJobId	= UI_HTML_Elements::Options( $optJobId, $filterJobId );

$optClassName	= array( '' => $wordsGeneral['list']['optAll'] );
foreach( $definitions as $jobId => $definition )
	$optClassName[$definition->className]	= str_replace( '_', ': ', $definition->className );
ksort( $optClassName );
$optClassName	= UI_HTML_Elements::Options( $optClassName, $filterClassName );

$optArchived	= array( 0 => 'no', 1 => 'yes' );
$optArchived	= UI_HTML_Elements::Options( $optArchived, $filterArchived );

$panelFilter	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', $words['filter']['heading'] ),
	HtmlTag::create( 'div', array(
		HtmlTag::create( 'form', array(
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', $words['filter']['labelJobId'], array( 'for' => 'input_jobId' ) ),
					HtmlTag::create( 'select', $optJobId, array(
						'name' 		=> 'jobId',
						'id'		=> 'input_jobId',
						'class' 	=> 'span12',
//						'oninput'	=> 'this.form.submit();',
					) ),
				), array( 'class' => 'span12' ) ),
			), array( 'class' => 'row-fluid' ) ),
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', $words['filter']['labelClassName'], array( 'for' => 'input_className' ) ),
					HtmlTag::create( 'select', $optClassName, array(
						'name' 		=> 'className',
						'id'		=> 'input_className',
						'class' 	=> 'span12',
//						'oninput'	=> 'this.form.submit();',
					) ),
				), array( 'class' => 'span12' ) ),
			), array( 'class' => 'row-fluid' ) ),
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', $words['filter']['labelStatus'], array( 'for' => 'input_status' ) ),
					HtmlTag::create( 'select', $optStatus, array(
						'name' 		=> 'status[]',
						'id'		=> 'input_status',
						'class' 	=> 'span12',
//						'oninput'	=> 'this.form.submit();',
						'multiple'	=> 'multiple',
						'size'		=> '8',
						'style'		=> 'overflow-y: hidden;'
					) ),
				), array( 'class' => 'span12' ) ),
			), array( 'class' => 'row-fluid' ) ),
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', $words['filter']['labelType'], array( 'for' => 'input_type' ) ),
					HtmlTag::create( 'select', $optType, array(
						'name' 		=> 'type',
						'id'		=> 'input_type',
						'class' 	=> 'span12',
//						'oninput'	=> 'this.form.submit();',
					) ),
				), array( 'class' => 'span8' ) ),
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', $words['filter']['labelArchived'], array( 'for' => 'input_archived' ) ),
					HtmlTag::create( 'select', $optArchived, array(
						'name' 		=> 'archived',
						'id'		=> 'input_archived',
						'class' 	=> 'span12',
//						'oninput'	=> 'this.form.submit();',
					) ),
				), array( 'class' => 'span4' ) ),
			), array( 'class' => 'row-fluid' ) ),
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', $words['filter']['labelStartFrom'], array( 'for' => 'input_startFrom' ) ),
					HtmlTag::create( 'input', NULL, array(
						'type'		=> 'date',
						'name' 		=> 'startFrom',
						'id'		=> 'input_startFrom',
						'class' 	=> 'span12',
						'value'		=> $filterStartFrom,
//						'oninput'	=> 'this.form.submit();',
					) ),
				), array( 'class' => 'not-span12 span6' ) ),
//			), array( 'class' => 'row-fluid' ) ),
//			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', $words['filter']['labelStartTo'], array( 'for' => 'input_startTo' ) ),
					HtmlTag::create( 'input', NULL, array(
						'type'		=> 'date',
						'name' 		=> 'startTo',
						'id'		=> 'input_startTo',
						'class' 	=> 'span12',
						'value'		=> $filterStartTo,
//						'oninput'	=> 'this.form.submit();',
					) ),
				), array( 'class' => 'not-span12 span6' ) ),
			), array( 'class' => 'row-fluid' ) ),
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'button', $iconFilter.'&nbsp;'.$words['filter']['buttonFilter'], array(
						'type'	=> 'submit',
						'name'	=> 'filter',
						'class'	=> 'btn not-btn-small btn-info not-btn-primary',
					) ),
//					HtmlTag::create( 'a', $iconReset.'&nbsp;'.$words['filter']['buttonReset'], array(
					HtmlTag::create( 'a', $iconReset, array(
						'href'	=> './manage/job/run/filter/reset',
						'class'	=> 'btn not-btn-small btn-inverse',
					) ),
				), array( 'class' => 'btn-group' ) ),
			), array( 'class' => 'buttonbar' ) ),
		), array( 'action' => './manage/job/run/filter', 'method' => 'POST' ) ),
	), array( 'class' => 'content-panel-inner' ) )
), array( 'class' => 'content-panel' ) );

return $panelFilter;
