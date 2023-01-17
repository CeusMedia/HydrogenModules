<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconFilter		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-search'] );
$iconReset		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-search-minus'] );


$optStatus	= ['' => $wordsGeneral['list']['optAll']];
foreach( $wordsGeneral['job-run-statuses'] as $key => $value )
	$optStatus[(string) $key]	= $value;
$optStatus	= HtmlElements::Options( $optStatus, $filterStatus );

$optType	= ['' => $wordsGeneral['list']['optAll']];
foreach( $wordsGeneral['job-run-types'] as $key => $value )
	$optType[$key]	= $value;
$optType	= HtmlElements::Options( $optType, $filterType );

$optJobId	= ['' => $wordsGeneral['list']['optAll']];
foreach( $definitions as $jobId => $definition )
	$optJobId[$jobId]	= $definition->identifier;
$optJobId	= HtmlElements::Options( $optJobId, $filterJobId );

$optClassName	= ['' => $wordsGeneral['list']['optAll']];
foreach( $definitions as $jobId => $definition )
	$optClassName[$definition->className]	= str_replace( '_', ': ', $definition->className );
ksort( $optClassName );
$optClassName	= HtmlElements::Options( $optClassName, $filterClassName );

$optArchived	= [0 => 'no', 1 => 'yes'];
$optArchived	= HtmlElements::Options( $optArchived, $filterArchived );

$panelFilter	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', $words['filter']['heading'] ),
	HtmlTag::create( 'div', array(
		HtmlTag::create( 'form', array(
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', $words['filter']['labelJobId'], ['for' => 'input_jobId'] ),
					HtmlTag::create( 'select', $optJobId, array(
						'name' 		=> 'jobId',
						'id'		=> 'input_jobId',
						'class' 	=> 'span12',
//						'oninput'	=> 'this.form.submit();',
					) ),
				), ['class' => 'span12'] ),
			), ['class' => 'row-fluid'] ),
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', $words['filter']['labelClassName'], ['for' => 'input_className'] ),
					HtmlTag::create( 'select', $optClassName, array(
						'name' 		=> 'className',
						'id'		=> 'input_className',
						'class' 	=> 'span12',
//						'oninput'	=> 'this.form.submit();',
					) ),
				), ['class' => 'span12'] ),
			), ['class' => 'row-fluid'] ),
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', $words['filter']['labelStatus'], ['for' => 'input_status'] ),
					HtmlTag::create( 'select', $optStatus, array(
						'name' 		=> 'status[]',
						'id'		=> 'input_status',
						'class' 	=> 'span12',
//						'oninput'	=> 'this.form.submit();',
						'multiple'	=> 'multiple',
						'size'		=> '8',
						'style'		=> 'overflow-y: hidden;'
					) ),
				), ['class' => 'span12'] ),
			), ['class' => 'row-fluid'] ),
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', $words['filter']['labelType'], ['for' => 'input_type'] ),
					HtmlTag::create( 'select', $optType, array(
						'name' 		=> 'type',
						'id'		=> 'input_type',
						'class' 	=> 'span12',
//						'oninput'	=> 'this.form.submit();',
					) ),
				), ['class' => 'span8'] ),
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', $words['filter']['labelArchived'], ['for' => 'input_archived'] ),
					HtmlTag::create( 'select', $optArchived, array(
						'name' 		=> 'archived',
						'id'		=> 'input_archived',
						'class' 	=> 'span12',
//						'oninput'	=> 'this.form.submit();',
					) ),
				), ['class' => 'span4'] ),
			), ['class' => 'row-fluid'] ),
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', $words['filter']['labelStartFrom'], ['for' => 'input_startFrom'] ),
					HtmlTag::create( 'input', NULL, array(
						'type'		=> 'date',
						'name' 		=> 'startFrom',
						'id'		=> 'input_startFrom',
						'class' 	=> 'span12',
						'value'		=> $filterStartFrom,
//						'oninput'	=> 'this.form.submit();',
					) ),
				), ['class' => 'not-span12 span6'] ),
//			), ['class' => 'row-fluid'] ),
//			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', $words['filter']['labelStartTo'], ['for' => 'input_startTo'] ),
					HtmlTag::create( 'input', NULL, array(
						'type'		=> 'date',
						'name' 		=> 'startTo',
						'id'		=> 'input_startTo',
						'class' 	=> 'span12',
						'value'		=> $filterStartTo,
//						'oninput'	=> 'this.form.submit();',
					) ),
				), ['class' => 'not-span12 span6'] ),
			), ['class' => 'row-fluid'] ),
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'button', $iconFilter.'&nbsp;'.$words['filter']['buttonFilter'], [
						'type'	=> 'submit',
						'name'	=> 'filter',
						'class'	=> 'btn not-btn-small btn-info not-btn-primary',
					] ),
//					HtmlTag::create( 'a', $iconReset.'&nbsp;'.$words['filter']['buttonReset'], array(
					HtmlTag::create( 'a', $iconReset, [
						'href'	=> './manage/job/run/filter/reset',
						'class'	=> 'btn not-btn-small btn-inverse',
					] ),
				), ['class' => 'btn-group'] ),
			), ['class' => 'buttonbar'] ),
		), ['action' => './manage/job/run/filter', 'method' => 'POST'] ),
	), ['class' => 'content-panel-inner'] )
), ['class' => 'content-panel'] );

return $panelFilter;
