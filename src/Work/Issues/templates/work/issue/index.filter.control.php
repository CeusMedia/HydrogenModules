<?php

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;


//  --  FILTER  --  //

/**
 *	Append quantity (found in numbers by key) to key label.
 *	@param		array		$words
 *	@param		array		$numbers
 *	@return		array
 */
function quantifyWords( array $words, array $numbers = [] ): array
{
	foreach( $words as $key => $label ){
		if( !strlen( $key ) ){
			$number	= ' ('.array_sum( $numbers ).')';
		}
		else{
			$number	= '(?)';
			if( isset( $numbers[$key] ) ){
				$number	= ' ('.$numbers[$key].')';
				if( $numbers[$key] == 0 )
					$number	= '';# (-)';
			}
		}
		$words[$key]	= $label.$number;
	}
	return $words;
}

/** @var Environment $env */
/** @var View $view */
/** @var array $words */
/** @var array $numberTypes */
/** @var array $numberPriorities */
/** @var array $numberStates */
/** @var array $numberProjects */

$session	= $env->getSession();

$title		= $session->get( 'filter-issue-title' );
$limit		= $session->get( 'filter-issue-limit' ) ?: 10;
$issueId	= $session->get( 'filter-issue-issueId' );
$order		= $session->get( 'filter-issue-order', '' );
$direction	= 'DESC';#$session->get( 'filter-issue-direction' );


$optOrder	= ['' => '-'];
foreach( $words['indexFilterOrders'] as $column => $label )
	$optOrder[$column]	= $label;
$optOrder['_selected']	= $order;

$optDirection	= [];
foreach( $words['indexFilterDirections'] as $key => $label ){
	$selected	= $key == $direction;
	$class		= 'direction direction'.$key;
	$optDirection[]	= HtmlElements::Option( $key, $label, $selected, FALSE, $class );
}
$optDirection	= join( $optDirection );

$words['types']			= quantifyWords( ['' => '- alle -'] + $words['types'], $numberTypes );
$words['severities']	= quantifyWords( ['' => '- alle -'] + $words['severities'] );
$words['priorities']	= quantifyWords( ['' => '- alle -'] + $words['priorities'], $numberPriorities );
$words['states']		= quantifyWords( ['' => '- alle -'] + $words['states'], $numberStates );

$optType		= $view->renderOptions( $words['types'], 'type', $session->get( 'filter-issue-type' ), 'issue-type type-%1$d');
$optSeverity	= $view->renderOptions( $words['severities'], 'severity', $session->get( 'filter-issue-severity' ), 'issue-severity severity-%1$d');
$optPriority	= $view->renderOptions( $words['priorities'], 'priority', $session->get( 'filter-issue-priority' ), 'issue-priority priority-%1$d');
$optStatus		= $view->renderOptions( $words['states'], 'status', $session->get( 'filter-issue-status' ), 'issue-status status-%1$d');

$optRelation	= ['' => 'in einem meiner Projekte', '1' => 'von mir berichtet', '2' => 'mir zugewiesen'];
$optRelation	= HtmlElements::Options( $optRelation, $session->get( 'filter-issue-relation' ) );

$filters		= [];

$filters[]	= HTML::DivClass( 'row-fluid',
	HTML::DivClass( 'span8',
		HTML::Label( 'title', $words['indexFilter']['labelTitle'] ).
		HTML::Input( 'title', $title, '-max span12' )
	).
	HTML::DivClass( 'span4',
		HTML::Label( 'issueId', $words['indexFilter']['labelIssueId'] ).
		HTML::Input( 'issueId', $issueId, '-max numeric span12' )
	)
);
$filters[]	= HTML::DivClass( 'row-fluid',
	HTML::DivClass( 'span12',
		HTML::Label( 'relation', $words['indexFilter']['labelRelation'] ).
		HtmlTag::create( 'select', $optRelation, array(
			'id'		=> 'input_relation',
			'name'		=> 'relation',
			'class'		=> 'span12',
//			'size'		=> 3,
			'onchange'	=> 'this.form.submit()'
		) )
	)
);
if( !empty( $projects ) ){
	$optProject	= [];
	/** @var object $project */
	foreach( $projects as $project )
//		if( $numberProjects[$project->projectId] > 0 )
			$optProject[$project->projectId]	= $project->title;

	$optProject		= quantifyWords( ['' => '- alle -'] + $optProject, $numberProjects );
	$optProject		= $view->renderOptions( $optProject, 'projectId', $session->get( 'filter-issue-projectId' ), 'issue-project');

	$filters[]	= HTML::DivClass( 'row-fluid', array(
		HTML::DivClass( 'span12',
			HTML::Label( 'projectId', $words['indexFilter']['labelProject'] ).
			HtmlTag::create( 'select', $optProject, array(
				'id'		=> 'input_projectId',
				'name'		=> 'projectId',
				'class'		=> 'span12',
				'onchange'	=> 'this.form.submit()',
			) ) )
		)
	);
}
$filters[]	= HTML::DivClass( 'row-fluid',
	HTML::DivClass( 'span12',
		HTML::Label( 'status', $words['indexFilter']['labelStatus'] ).
	//	HTML::Select( 'status[]', $optStatus, 'span12 -max rows-8', NULL, 'this.form.submit()' )
		HtmlTag::create( 'select', $optStatus, array(
			'id'		=> 'input_status',
			'name'		=> 'status[]',
			'multiple'	=> 'multiple',
			'class'		=> 'span12',
			'size'		=> 8,
			'onchange'	=> 'this.form.submit()' )
		)
	)
);
$filters[]	= HTML::DivClass( 'row-fluid',
	HTML::DivClass( 'span12',
		HTML::Label( 'type', $words['indexFilter']['labelType'] ).
	//	HTML::Select( 'type[]', $optType, '-max span12 rows-4', NULL, 'this.form.submit()' )
		HtmlTag::create( 'select', $optType, array(
			'id'		=> 'input_type',
			'name'		=> 'type[]',
			'multiple'	=> 'multiple',
			'class'		=> 'span12',
			'size'		=> 4,
			'onchange'	=> 'this.form.submit()' )
		)
	)
);
$filters[]	= HTML::DivClass( 'row-fluid',
	HTML::DivClass( 'span12',
		HTML::Label( 'priority', $words['indexFilter']['labelPriority'] ).
	//	HTML::Select( 'priority[]', $optPriority, '-max span12 rows-7', NULL, 'this.form.submit()' )
		HtmlTag::create( 'select', $optPriority, array(
			'id'		=> 'input_priority',
			'name'		=> 'priority[]',
			'multiple'	=> 'multiple',
			'class'		=> 'span12',
			'size'		=> 7,
			'onchange'	=> 'this.form.submit()' )
		)
	)
);
$filters[]	= HTML::DivClass( 'row-fluid',
	HTML::DivClass( 'span12',
		HTML::Label( 'order', $words['indexFilter']['labelOrder'] ).
		HTML::Select( 'order', $optOrder, '-max span12 rows-1', NULL, 'this.form.submit()' )
	)
);
$filters[]	= HTML::DivClass( 'row-fluid',
	HTML::DivClass( 'span8',
		HTML::Label( 'direction', $words['indexFilter']['labelDirection'] ).
		HTML::Select( 'direction', $optDirection, '-max span12 rows-1', NULL, 'this.form.submit()' )
	).
	HTML::DivClass( 'span4',
		HTML::Label( 'limit', $words['indexFilter']['labelLimit'] ).
		HTML::Input( 'limit', $limit, 'span12' )
	)
);

return '
<div class="content-panel">
	<h3 class="filter">'.$words['indexFilter']['legend'].'</h3>
	<div class="content-panel-inner">
		<form id="form_filter-issue" name="filterIssues" action="./work/issue/filter" method="post">
			'.join( $filters ).'
			<div class="buttonbar">
				<button type="submit" class="btn btn-small btn-primary" name="filter"><i class="icon-search icon-white"></i> '.$words['indexFilter']['buttonFilter'].'</button>
				<a href="./work/issue/filter/reset" class="btn btn-small btn-inverse"><i class="icon-zoom-out icon-white"></i> '.$words['indexFilter']['buttonReset'].'</a>
			</div>
		</form>
	</div>
</div>';
