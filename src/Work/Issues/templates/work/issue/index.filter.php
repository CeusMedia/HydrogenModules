<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

//  --  FILTER  --  //

$session	= $this->env->getSession();

//$script	= '$(document).ready(function(){});';
//$this->env->page->js->addScript( $script );

$title		= $this->env->getSession()->get( 'filter-issue-title' );
$limit		= $this->env->getSession()->get( 'filter-issue-limit' );
$issueId	= $this->env->getSession()->get( 'filter-issue-issueId' );

$optOrder	= ['' => '-'];
foreach( $words['indexFilterOrders'] as $column => $label )
	$optOrder[$column]	= $label;
$optOrder['_selected']	= $this->env->getSession()->get( 'filter-issue-order' );

$optDirection	= [];
foreach( $words['indexFilterDirections'] as $key => $label ){
	$selected	= $key == $session->get( 'filter-issue-direction' );
	$class		= 'direction direction'.$key;
	$optDirection[]	= HtmlElements::Option( $key, $label, $selected, FALSE, $class );
}
$optDirection	= join( $optDirection );

$mode	= (int) $session->get( 'issue-filter-panel-mode' );

if( $mode == 1 ){
	$words['types']			= ['' => '- alle -'] + $words['types'];
	$words['severities']	= ['' => '- alle -'] + $words['severities'];
	$words['priorities']	= ['' => '- alle -'] + $words['priorities'];
	$words['states']		= ['' => '- alle -'] + $words['states'];

	$optType		= $this->renderOptions( $words['types'], 'type', $session->get( 'filter-issue-type' ), 'issue-type type-%1$d');
	$optSeverity	= $this->renderOptions( $words['severities'], 'severity', $session->get( 'filter-issue-severity' ), 'issue-severity severity-%1$d');
	$optPriority	= $this->renderOptions( $words['priorities'], 'priority', $session->get( 'filter-issue-priority' ), 'issue-priority priority-%1$d');
	$optStatus		= $this->renderOptions( $words['states'], 'status', $session->get( 'filter-issue-status' ), 'issue-status status-%1$d');

	return '
	<form id="form_filter-issue" name="filterIssues" action="./work/issue/filter" method="post">
		<fieldset style="position: relative">
			<legend class="filter">'.$words['indexFilter']['legend'].'</legend>
			<table>
				<colgroup>
					<col width="25%"/>
					<col width="25%"/>
					<col width="25%"/>
					<col width="25%"/>
				</colgroup>
				<tr>
					<td>
						<label for="title">'.$words['indexFilter']['labelTitle'].'</label><br/>
						'.HtmlElements::Input( 'title', $title, 'm' ).'
					</td>
					<td>
						<label for="issueId">'.$words['indexFilter']['labelIssueId'].'</label><br/>
						'.HtmlElements::Input( 'issueId', $issueId, 'xs numeric' ).'
					</td>
					<td>
<!--						<label for="project">'.$words['indexFilter']['labelProject'].'</label><br/>
						'.HtmlElements::Select( 'project', '<option>- alle -</option>', 'm', NULL, 'filter-issues' ).'
-->					</td>
					<td>

					</td>
				</tr>
				<tr>
					<td>
						<label for="status">'.$words['indexFilter']['labelStatus'].'</label><br/>
						'.HtmlTag::create( 'select', $optStatus, array(
							'name'		=> 'status[]',
							'id'		=> 'input_status',
							'multiple'	=> 'multiples',
							'class'		=> 'span12',
							'rows'		=> 8
						) ).'
<!--						'.HtmlElements::Select( 'status[]', $optStatus, 'm rows-8', NULL, 'filter-issues' ).'-->
					</td>
					<td>
						<label for="priority">'.$words['indexFilter']['labelPriority'].'</label><br/>
						'.HtmlElements::Select( 'priority[]', $optPriority, 'm rows-7', NULL, 'filter-issues' ).'
					</td>
					<td>
						<label for="severity">'.$words['indexFilter']['labelSeverity'].'</label><br/>
						'.HtmlElements::Select( 'severity[]', $optSeverity, 'm rows-5', NULL, 'filter-issues' ).'
					</td>
					<td>
						<label for="type">'.$words['indexFilter']['labelType'].'</label><br/>
						'.HtmlElements::Select( 'type[]', $optType, 'm rows-4', NULL, 'filter-issues' ).'
					</td>
				</tr>
				<tr>
					<td>
						<label for="order">'.$words['indexFilter']['labelOrder'].'</label><br/>
						'.HtmlElements::Select( 'order', $optOrder, 'm rows-1', NULL, 'filter-issues' ).'
					</td>
					<td>
						<label for="direction">'.$words['indexFilter']['labelDirection'].'</label><br/>
						'.HtmlElements::Select( 'direction', $optDirection, 'm', NULL, 'filter-issues' ).'
					</td>
					<td>
						<label for="limit">'.$words['indexFilter']['labelLimit'].'</label><br/>
						'.HtmlElements::Input( 'limit', $limit, 'xs numeric' ).'
					</td>
					<td></td>
				</tr>
			</table>
			<div class="buttonbar">
				'.HtmlElements::Button( 'filter', $words['indexFilter']['buttonFilter'], 'button filter' ).'
				'.HtmlElements::LinkButton( './work/issue/filter/reset', $words['indexFilter']['buttonReset'], 'button reset' ).'
				'.HtmlElements::LinkButton( './work/issue/filter/mode/0', $words['indexFilter']['buttonCollapse'], 'button less' ).'
			</div>
		</fieldset>
	</form>
	';
}
else
{
	return '
	<form id="form_filter-issues" name="filterIssues" action="./work/issue/filter" method="post">
		<fieldset style="position: relative">
			<legend class="filter">'.$words['indexFilter']['legend'].'</legend>
			<table>
				<colgroup>
					<col width="25%"/>
					<col width="25%"/>
					<col width="25%"/>
					<col width="25%"/>
				</colgroup>
				<tr>
					<td>
						<label for="title">'.$words['indexFilter']['labelTitle'].'</label><br/>
						'.HtmlElements::Input( 'title', $title, 'm' ).'
					</td>
					<td>
						<label for="issueId">'.$words['indexFilter']['labelIssueId'].'</label><br/>
						'.HtmlElements::Input( 'issueId', $issueId, 'xs numeric' ).'
					</td>
					<td>
						<label for="projectId">'.$words['indexFilter']['labelProject'].'</label><br/>
						'.HtmlElements::Select( 'projectId', '<option>- alle -</option>', 'm', NULL, 'filter-issues' ).'
					</td>
					<td></td>
				</tr>
				<tr>
					<td>
						<label for="order">'.$words['indexFilter']['labelOrder'].'</label><br/>
						'.HtmlElements::Select( 'order', $optOrder, 'm rows-1', NULL, 'filter-issues' ).'
					</td>
					<td>
						<label for="direction">'.$words['indexFilter']['labelDirection'].'</label><br/>
						'.HtmlElements::Select( 'direction', $optDirection, 'm', NULL, 'filter-issues' ).'
					</td>
					<td>
						<label for="limit">'.$words['indexFilter']['labelLimit'].'</label><br/>
						'.HtmlElements::Input( 'limit', $limit, 'xs numeric' ).'
					</td>
					<td></td>
				</tr>
			</table>
			<div class="buttonbar">
				'.HtmlElements::Button( 'filter', $words['indexFilter']['buttonFilter'], 'button filter' ).'
				'.HtmlElements::LinkButton( './work/issue/filter/reset', $words['indexFilter']['buttonReset'], 'button reset' ).'
				'.HtmlElements::LinkButton( './work/issue/filter/mode/1', $words['indexFilter']['buttonExpand'], 'button more' ).'
			</div>
		</fieldset>
	</form>
	';
}
