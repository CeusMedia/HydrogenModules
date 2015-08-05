<?php
$helperIndicator	= new UI_HTML_Indicator();
$helperTime			= new View_Helper_TimePhraser( $env );

$iconList		= new UI_HTML_Tag( 'i', '', array( 'class' => 'icon-list' ) );
$iconEdit		= new UI_HTML_Tag( 'i', '', array( 'class' => 'icon-pencil icon-white' ) );
$iconDefault	= new UI_HTML_Tag( 'i', '', array( 'class' => 'icon-star' ) );
if( $env->getModules()->has( 'UI_Font_FontAwesome' ) ){
//	$iconList	= new UI_HTML_Tag( 'b', '', array( 'class' => 'fa fa-list fa-fw' ) );
	$iconDefault	= new UI_HTML_Tag( 'b', '', array( 'class' => 'fa fa-star fa-fw' ) );
	$iconEdit		= new UI_HTML_Tag( 'b', '', array( 'class' => 'fa fa-pencil fa-fw' ) );
}

function renderUserBlock( $user ){
	$label	= $user->username;
	$sub	= '<br/><small class="muted">'.$user->firstname.'&nbsp;'.$user->surname.'</small>';
	$link	= UI_HTML_Tag::create( 'a', $label, array( 'href' => './user/edit/'.$user->userId ) );
	return UI_HTML_Tag::create( 'div', $link.$sub );
}

function renderUserInline( $user ){
	$icon	= new UI_HTML_Tag( 'i', '', array( 'class' => 'icon-user' ) );
	$label	= $user->username;
	$sub	= '<small class="muted">('.$user->firstname.'&nbsp;'.$user->surname.')</small>';
	$link	= UI_HTML_Tag::create( 'a', $icon.'&nbsp;'.$label.'&nbsp;'.$sub, array( 'href' => './user/edit/'.$user->userId ) );
	return $link;
	$span	= UI_HTML_Tag::create( 'span', $icon.'&nbsp;'.$link.'&nbsp;'.$sub );
	return $span;
}

/*  --  COWORKERS  --  */
$list	= '<div class="muted"><em>'.$words['view.coworkers']['noEntries'].'</em><br/></div>';
if( $project->users ){
	$list	= array();
	foreach( $project->users as $worker ){
//print_m( $coworker );die;
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', renderUserBlock( $worker ) ),
		) );
	}
	$list	= new UI_HTML_Tag( 'table', $list, array( 'class' => 'table table-condensed table-striped' ) );
}
$panelWorkers	= '
<div class="content-panel">
	<h3>'.$words['view.coworkers']['heading'].'</h3>
	<div class="content-panel-inner">
		'.$list.'
	</div>
</div>';

/*  --  FACTS  --  */

$buttonList		= new UI_HTML_Tag( 'a', $iconList.'&nbsp'.$words['view']['buttonList'], array(
	'href'		=> './manage/project',
	'class'		=> 'btn btn-small',
) );

$buttonEdit		= new UI_HTML_Tag( 'a', $iconEdit.'&nbsp'.$words['view']['buttonEdit'], array(
	'href'		=> '#',
	'class'		=> 'btn btn-primary',
	'disabled'	=> 'disabled',
) );


if( 1 || $canEdit ){
	$buttonEdit		= new UI_HTML_Tag( 'a', $iconEdit.'&nbsp'.$words['view']['buttonEdit'], array(
		'href'		=> './manage/project/edit/'.$project->projectId,
		'class'		=> 'btn btn-primary',
	) );
}

$graph		= $helperIndicator->build( $project->status + 2, 5, '150' );
$status		= htmlentities( $words['states'][$project->status], ENT_QUOTES, 'UTF-8' );
$priority	= htmlentities( $words['priorities'][$project->priority], ENT_QUOTES, 'UTF-8' );
$dateChange	= max( $project->createdAt, $project->modifiedAt );

$factUrl	= $project->url ? new UI_HTML_Tag( 'a', htmlentities( $project->url, ENT_QUOTES, 'UTF-8' ), array(
	'href'		=> $project->url,
	'target'	=> "_blank",
	'class'		=> "external",
) ) : '-';

$panelFacts		= '
<div class="content-panel">
	<h3><a href="./manage/project" class="muted">Projekt:</a> '.htmlentities( $project->title, ENT_QUOTES, 'UTF-8' ).'</h3>
	<div class="content-panel-inner">
		<div>'.$view->renderContent( $project->description, 'markdown' ).'</div>
		<br/>
		<dl class="dl-horizontal">
			<dt>'.$words['view']['labelProgress'].'</dt>
			<dd>'.$graph.'&nbsp;</dd>
			<dt>'.$words['view']['labelStatus'].'</dt>
			<dd>'.$status.'</dd>
			<dt>'.$words['view']['labelPriority'].'</dt>
			<dd>'.$priority.'</dd>
			<dt>'.$words['view']['labelCreator'].'</dt>
			<dd>'.( $project->creator ? renderUserInline( $project->creator ) : '-' ).'&nbsp;</dd>
			<dt>'.$words['view']['labelCreatedAt'].'</dt>
			<dd>'.$helperTime->convert( $project->createdAt, TRUE, $words['view']['labelCreatedAt_prefix'], $words['view']['labelCreatedAt_suffix'] ).'&nbsp;</dd>
			<dt>'.$words['view']['labelChangedAt'].'</dt>
			<dd>'.$helperTime->convert( $dateChange, TRUE, $words['view']['labelChangedAt_prefix'], $words['view']['labelChangedAt_suffix'] ).'</dd>
			<dt>'.$words['view']['labelUrl'].'</dt>
			<dd>'.$factUrl.'&nbsp;</dd>
		</dl>
		<br/>
		<div class="buttonbar">
			'.$buttonList.'
			'.$buttonEdit.'
		</div>
	</div>
</div>';


//  --  RELATED ITEMS  --  //
$panelRelations		= '';
$helperRelations	= new View_Helper_ItemRelationLister( $this->env );
$helperRelations->callForRelations( 'Project', 'listRelations', array( 'projectId' => $project->projectId ) );
if( $helperRelations->hasRelations() ){
	$relations	= $helperRelations->renderRelations();
	$panelRelations	= '<div class="content-panel">
	<h4>'.$words['view.relations']['heading'].'</h4>
	<div class="content-panel-inner">
		'.$relations.'
	</div>
</div>';
}

return '
<div class="row-fluid">
	<div class="span12">
		<div class="row-fluid">
			<div class="span8">
				'.$panelFacts.'
			</div>
			<div class="span4">
				'.$panelWorkers.'
			</div>
		</div>
	</div>
</div>
<div class="row-fluid">
	<div class="span12">
		'.$panelRelations.'
	</div>
</div>';
/*
remark( "Users: ".count( $project->users ) );
remark( "Coworkers: ".count( $project->coworkers ) );

print_m( $project );
die;
*/
?>
