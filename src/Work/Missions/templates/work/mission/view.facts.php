<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\View;

/** @var WebEnvironment $env */
/** @var View $view */
/** @var array $words */
/** @var Entity_Mission $mission */
/** @var object[] $missionUsers */

$phraser	= new View_Helper_TimePhraser( $env );

$canEditProject	= $acl->hasRight( $userRoleId, 'manage_project', 'edit' );

/*
print_m( $mission );
print_m( $mission->creator );
print_m( $mission->worker );
die;
*/

function renderUserLabel( ?object $user = NULL ): string
{
	if( !$user )
		return "-";
	$iconUser	= HtmlTag::create( 'i', '', ['class' => 'icon-user'] );
	$spanClass	= 'user role role'.$user->roleId;
	$fullname	= $user->firstname.' '.$user->surname;
	$username	= HtmlTag::create( 'abbr', $user->username, ['title' => $fullname] );
	$label		= $iconUser.'&nbsp;'.$username;
	return HtmlTag::create( 'span', $label, ['class' => $spanClass] );
}

$viewers	= [];
foreach( $missionUsers as $user )
	$viewers[]	= renderUserLabel( $user );
$viewers	= join( '<br/>', $viewers );

$w	= (object) $words['view-facts'];

$priorities	= $words['priorities'];

$priority		= HtmlTag::create( 'span', $words['priorities'][(string) $mission->priority], ['class' => 'mission priority'.$mission->priority] );
$status			= HtmlTag::create( 'span', $words['states'][(string) $mission->status], ['class' => 'mission status'.$mission->status] );
$creator		= renderUserLabel( $mission->creator );
$worker			= renderUserLabel( $mission->worker );
$modifier		= renderUserLabel( $mission->modifier );

$project		= $mission->project->title;
if( $canEditProject )
	$project	= HtmlTag::create( 'a', $project, ['href' => './manage/project/edit/'.$mission->projectId] );

$hoursProjected		= floor( $mission->minutesProjected / 60 );
$minutesProjected	= str_pad( $mission->minutesProjected - $hoursProjected * 60, 2, 0, STR_PAD_LEFT );

$hoursRequired		= floor( $mission->minutesRequired / 60 );
$minutesRequired	= str_pad( $mission->minutesRequired - $hoursRequired * 60, 2, 0, STR_PAD_LEFT );

$iconCancel			= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-list'] );
$iconEdit			= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-pencil'] );
$iconCopy			= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-clone'] );
$iconRevamp			= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-recycle'] );

/*
$helper			= new View_Helper_TimePhraser( $env );
function renderDateTimePhrase( $helper, $date, $time = NULL ){
	$stampStart		= strtotime( $date.' '.$time );
	$futureStart	= $stampStart > time();
	$prefixStart	= $futureStart ? 'in ' : 'vor ';
	$phraseStart	= $prefixStart.$helper->convert( $stampStart );
	$dateStart		= date( "d.m.Y" ).( $time ? ' '.$time : '' );
	$labelStart		= $phraseStart.' <small class="muted">('.$dateStart.')</small>';
	return $labelStart;
}

$dateTimeStart	= renderDateTimePhrase( $helper, $mission->dayStart, $mission->timeStart );
$dateTimeEnd	= renderDateTimePhrase( $helper, $mission->dayEnd, $mission->timeEnd );
//$dateTimeStart	= renderDateTimePhrase( $helper, $mission->dayStart, $mission->timeStart );
*/
/*
$stampStart		= strtotime( $mission->dayStart.' '.$mission->timeStart );
$futureStart	= $stampStart > time();
$prefixStart	= $futureStart ? 'in ' : 'vor ';
$phraseStart	= $prefixStart.$helper->convert( $stampStart );
$dateStart		= date( "d.m.Y" ).( $mission->timeStart ? ' '.$mission->timeStart : '' );
$labelStart		= $phraseStart.' <small class="muted">('.$dateStart.')</small>';
print_m( $stampStart );
xmp( $phraseStart );
xmp( $dateStart );
xmp( $labelStart );
die;
*/

function renderDuration( $minutes, bool $useTimerHelper = FALSE ): string
{
	if( $useTimerHelper )
		return View_Helper_Work_Time::formatSeconds( $minutes * 60 );
	$hours	= floor( $minutes / 60 );
	$mins	= floor( $minutes - $hours * 60 );
	return $hours.':'.str_pad( $mins, 2, 0, STR_PAD_LEFT );
}

/*  --  FACTS: PROJECTED, TRACKED AND REQUIRED TIME */
$list	= [];
$totalMinsProjected	= $hoursProjected * 60 + $minutesProjected;
$totalMinsRequired	= $hoursRequired * 60 + $minutesRequired;
$totalMinsTracked	= 0;
if( $useTimer ){
	$totalMinsTracked	= ceil( View_Helper_Work_Time::sumTimersOfModuleId( $env, 'Work_Missions', $mission->missionId ) / 60 );
//	$totalMinsRequired	= max( $totalMinsTracked, $totalMinsRequired );
}

$isOverrunRequired	= $totalMinsProjected && $totalMinsRequired > $totalMinsProjected || $totalMinsTracked && $totalMinsRequired > $totalMinsTracked;
$isOverrunTracked	= $totalMinsProjected && $totalMinsTracked > $totalMinsProjected;


if( $totalMinsProjected )
	$list[]	= HtmlTag::create( 'dd', 'geplant: '.renderDuration( $totalMinsProjected, $useTimer ), [] );
if( $totalMinsTracked ){
	$diff	= View_Work_Mission::formatSeconds( abs( $totalMinsProjected - $totalMinsTracked ) * 60 );
	$diff	= HtmlTag::create( 'small', '('.$diff.')', ['class' => 'muted'] );
	$time	= View_Work_Mission::formatSeconds( $totalMinsTracked * 60 );
	$list[]	= HtmlTag::create( 'dd', 'erfasst: '.$time.' '.$diff, [
		'class' => $isOverrunTracked ? 'warning' : NULL,
	] );
}
if( $totalMinsRequired ){
	$diff	= View_Work_Mission::formatSeconds( abs( $totalMinsProjected - $totalMinsRequired ) );
	$diff	= HtmlTag::create( 'small', '('.$diff.')', ['class' => 'muted'] );
	$time	= View_Work_Mission::formatSeconds( $totalMinsRequired );
	$list[]	= HtmlTag::create( 'dd', 'benötigt: '.$time.' '.$diff, [
		'class' => $isOverrunRequired ? 'warning' : NULL,
	] );
}
$factHours	= $list ? '<dt>'.$w->labelHours.'</dt>'.join( $list ) : '';

$buttonCancel	= HtmlTag::create( 'a', $iconCancel.' '.$w->buttonCancel, ['href' => './work/mission', 'class' => 'btn btn-small'] );
$buttonEdit		= HtmlElements::LinkButton( './work/mission/edit/'.$mission->missionId, $iconEdit.' '.$w->buttonEdit, 'btn btn-primary' );
$buttonCopy		= HtmlElements::LinkButton( './work/mission/add/'.$mission->missionId, $iconCopy.' '.$w->buttonCopy, 'btn btn-small btn-small' );
$buttonRevamp	= HtmlElements::LinkButton( './work/mission/setStatus/'.$mission->missionId.'/2/1', $iconRevamp.' '.$w->buttonRevamp, 'btn btn-small' );

if( in_array( $mission->status, [-1, 0, 1, 2, 3] ) )
	$buttonRevamp	= "";
else
	$buttonEdit		= "";

return '
<style>
dl dd.warning {
	color: red;
	font-weight: bold;
	}
</style>
<div class="row-fluid">
	<div class="span12">
		<h3><span class="muted">'.$words['types'][$mission->type].': </span> '.htmlentities( $mission->title, ENT_QUOTES, 'UTF-8' ).'</h3>
		<div class="content-panel">
			<h4>'.$w->heading.'</h4>
			<div class="content-panel-inner">
				<div class="row-fluid">
					<div style="float: left; width: 50%">
						<dl class="dl-horizontal">
							<dt>'.$w->labelProjectId.'</dt>
							<dd>'.$project.'</dd>
							<dt>'.$w->labelType.'</dt>
							<dd>'.$words['types'][$mission->type].'</dd>
							<dt>'.$w->labelPriority.'</dt>
							<dd>'.$priority.'</dd>
							<dt>'.$w->labelStatus.'</dt>
							<dd>'.$status.'</dd>
							<dt>'.$w->labelDayStart.' <small class="muted">/ '.$w->labelTimeStart.'</small></dt>
<!--							<dd>'./*$dateTimeStart.*/'</dd>-->
							<dd>'.date( "d.m.Y", strtotime( $mission->dayStart ) ).' '.$mission->timeStart.'</dd>
							<dt>'.$w->labelDayEnd.' <small class="muted">/ '.$w->labelTimeEnd.'</small></dt>
							<dd>'.( $mission->dayEnd ? date( "d.m.Y", strtotime( $mission->dayEnd ) ) : "" ).' '.$mission->timeEnd.'</dd>
							'.$factHours.'
<!--							<dt>'.$w->labelHours.'</dt>
							<dd>geplant: '.( (int) $hoursProjected || (int) $minutesProjected ? $hoursProjected.':'.$minutesProjected : '-' ).'</dd>
							<dd>benötigt: '.$hoursRequired.':'.$minutesRequired.'</dd>-->
<!--							<dt>'.$w->labelLocation.'</dt>
							<dd>'.htmlentities( $mission->location ?: '-', ENT_QUOTES, 'UTF-8' ).'</dd>
							<dt>'.$w->labelReference.'</dt>
							<dd>'.htmlentities( $mission->reference ?: '-', ENT_QUOTES, 'UTF-8' ).'</dd>-->
						</dl>
					</div>
					<div style="float: left; width: 50%">
						<dl class="dl-horizontal">
							<dt>erstellt am</dt>
							<dd>'.date( "d.m.Y", $mission->createdAt ).'</dd>
							<dt>'.$w->labelCreator.'</dt>
							<dd>'.$creator.'</span></dd>
							<dt>'.$w->labelChanged.'</dt>
							<dd><span class="date">'.( $mission->modifiedAt ? date( 'd.m.Y H:i', $mission->modifiedAt ) : '-' ).'</span></dd>
							<dt>'.$w->labelModifier.'</dt>
							<dd>'.$modifier.'</dd>
							<dt>'.$w->labelWorker.'</dt>
							<dd>'.$worker.'</dd>
							<dt>'.$w->labelViewers.'</dt>
							<dd>'.$viewers.'</dd>
						</dl>
					</div>
				</div>
				<div class="buttonbar">
					'.$buttonCancel.'
					'.$buttonEdit.'
					&nbsp;&nbsp;|&nbsp;&nbsp;
					'.$buttonRevamp.'
					'.$buttonCopy.'
				</div>
			</div>
		</div>
	</div>
</div>';
