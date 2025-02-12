<?php

use CeusMedia\Common\UI\HTML\Indicator as HtmlIndicator;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

/** @var Environment $env */
/** @var View $view */
/** @var array $words */
/** @var object $project */

$helperIndicator	= new HtmlIndicator();
$helperTime			= new View_Helper_TimePhraser( $env );

$iconList		= HtmlTag::create( 'i', '', ['class' => 'icon-list'] );
$iconEdit		= HtmlTag::create( 'i', '', ['class' => 'icon-pencil icon-white'] );
$iconDefault	= HtmlTag::create( 'i', '', ['class' => 'icon-star'] );
if( $env->getModules()->has( 'UI_Font_FontAwesome' ) ){
	$iconList		= HtmlTag::create( 'b', '', ['class' => 'fa fa-fw fa-list'] );
	$iconDefault	= HtmlTag::create( 'b', '', ['class' => 'fa fa-fw fa-star'] );
	$iconEdit		= HtmlTag::create( 'b', '', ['class' => 'fa fa-fw fa-pencil'] );
}

function renderUserBlock( Environment $env, object $user ): string
{
	if( $env->getModules()->has( 'Members' ) ){
		$helper	= new View_Helper_Member( $env );
		$helper->setUser( $user );
		$helper->setMode( 'bar' );
		$helper->setLinkUrl( 'member/view/%d' );
		return $helper->render();
	}
	$label	= $user->username;
	$sub	= '<br/><small class="muted">'.$user->firstname.'&nbsp;'.$user->surname.'</small>';
	$link	= HtmlTag::create( 'a', $label, ['href' => './user/edit/'.$user->userId] );
	return HtmlTag::create( 'div', $link.$sub );
}

function renderUserInline( Environment $env, object $user ): string
{
	if( $env->getModules()->has( 'Members' ) ){
		$helper	= new View_Helper_Member( $env );
		$helper->setUser( $user );
		$helper->setMode( 'inline' );
		$helper->setLinkUrl( 'member/view/%d' );
		return $helper->render();
	}
	$icon	= HtmlTag::create( 'i', '', ['class' => 'icon-user'] );
	$label	= $user->username;
	$sub	= '<small class="muted">('.$user->firstname.'&nbsp;'.$user->surname.')</small>';
	$link	= HtmlTag::create( 'a', $icon.'&nbsp;'.$label.'&nbsp;'.$sub, ['href' => './user/edit/'.$user->userId] );
	return $link;
	$span	= HtmlTag::create( 'span', $icon.'&nbsp;'.$link.'&nbsp;'.$sub );
	return $span;
}

/*  --  COWORKERS  --  */
$list	= '<div class="muted"><em>'.$words['view.coworkers']['noEntries'].'</em><br/></div>';
if( $project->users ){
	$list	= [];
	foreach( $project->users as $worker ){
		$list[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', renderUserBlock( $env, $worker ) ),
		) );
	}
	$list	= HtmlTag::create( 'table', $list, ['class' => 'table table-condensed table-striped'] );
}
$panelWorkers	= '
<div class="content-panel">
	<h3>'.$words['view.coworkers']['heading'].'</h3>
	<div class="content-panel-inner">
		'.$list.'
	</div>
</div>';

/*  --  FACTS  --  */

$buttonList		= HtmlTag::create( 'a', $iconList.'&nbsp'.$words['view']['buttonList'], [
	'href'		=> './manage/project',
	'class'		=> 'btn not-btn-small',
] );

$buttonEdit		= HtmlTag::create( 'a', $iconEdit.'&nbsp'.$words['view']['buttonEdit'], [
	'href'		=> '#',
	'class'		=> 'btn btn-primary',
	'disabled'	=> 'disabled',
] );


if( 1 || $canEdit ){
	$buttonEdit		= HtmlTag::create( 'a', $iconEdit.'&nbsp'.$words['view']['buttonEdit'], [
		'href'		=> './manage/project/edit/'.$project->projectId,
		'class'		=> 'btn btn-primary',
	] );
}

$graph		= $helperIndicator->build( $project->status + 2, 5, '150' );
$status		= htmlentities( $words['states'][$project->status], ENT_QUOTES, 'UTF-8' );
$priority	= htmlentities( $words['priorities'][$project->priority], ENT_QUOTES, 'UTF-8' );
$dateChange	= max( $project->createdAt, $project->modifiedAt );

$factUrl	= $project->url ? HtmlTag::create( 'a', htmlentities( $project->url, ENT_QUOTES, 'UTF-8' ), [
	'href'		=> $project->url,
	'target'	=> "_blank",
	'class'		=> "external",
] ) : '-';

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
			<dd>'.( $project->creator ? renderUserInline( $env, $project->creator ) : '-' ).'&nbsp;</dd>
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
$helperRelations->setHook( 'Project', 'listRelations', ['projectId' => $project->projectId] );
$helperRelations->setLinkable( TRUE );
$helperRelations->setActiveOnly( TRUE );
//$helperRelations->setTableClass( 'limited' );
//$helperRelations->setMode( 'list' );
if( $helperRelations->hasRelations() ){
	$relations	= $helperRelations->render();
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
