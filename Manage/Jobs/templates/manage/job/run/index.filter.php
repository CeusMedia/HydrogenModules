<?php

$iconFilter		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-search' ) );
$iconReset		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-search-minus' ) );

$optStatus		= UI_HTML_Elements::Options( $wordsGeneral['job-run-statuses'], $filterStatus );
$optType		= UI_HTML_Elements::Options( array_merge( array( '' => '- alle -' ), $wordsGeneral['job-run-types'] ), $filterType );

$optJobId	= array( '' => '- alle -' );
foreach( $definitions as $jobId => $definition )
	$optJobId[$jobId]	= $definition->identifier;
$optJobId	= UI_HTML_Elements::Options( $optJobId, $filterJobId );

$panelFilter	= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', $words['filter']['heading'] ),
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'form', array(
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'label', $words['filter']['labelJobId'], array( 'for' => 'input_jobId' ) ),
					UI_HTML_Tag::create( 'select', $optJobId, array(
						'name' 		=> 'jobId',
						'id'		=> 'input_jobId',
						'class' 	=> 'span12',
//						'oninput'	=> 'this.form.submit();',
					) ),
				), array( 'class' => 'span12' ) ),
			), array( 'class' => 'row-fluid' ) ),
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'label', $words['filter']['labelStatus'], array( 'for' => 'input_status' ) ),
					UI_HTML_Tag::create( 'select', $optStatus, array(
						'name' 		=> 'status[]',
						'id'		=> 'input_status',
						'class' 	=> 'span12',
//						'oninput'	=> 'this.form.submit();',
						'multiple'	=> 'multiple',
						'size'		=> '7',
						'style'		=> 'overflow-y: hidden;'
					) ),
				), array( 'class' => 'span12' ) ),
			), array( 'class' => 'row-fluid' ) ),
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'label', $words['filter']['labelType'], array( 'for' => 'input_type' ) ),
					UI_HTML_Tag::create( 'select', $optType, array(
						'name' 		=> 'type',
						'id'		=> 'input_type',
						'class' 	=> 'span12',
//						'oninput'	=> 'this.form.submit();',
					) ),
				), array( 'class' => 'span12' ) ),
			), array( 'class' => 'row-fluid' ) ),
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'button', $iconFilter.'&nbsp;'.$words['filter']['buttonFilter'], array(
						'type'	=> 'submit',
						'name'	=> 'filter',
						'class'	=> 'btn btn-small btn-primary',
					) ),
					UI_HTML_Tag::create( 'a', $iconReset.'&nbsp;'.$words['filter']['buttonReset'], array(
						'href'	=> './manage/job/run/filter/reset',
						'class'	=> 'btn btn-small btn-inverse',
					) ),
				), array( 'class' => 'btn-group' ) ),
			), array( 'class' => 'buttonbar' ) ),
		), array( 'action' => './manage/job/run/filter', 'method' => 'POST' ) ),
	), array( 'class' => 'content-panel-inner' ) )
), array( 'class' => 'content-panel' ) );;

return $panelFilter;
