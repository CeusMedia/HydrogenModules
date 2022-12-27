<?php

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Indicator as HtmlIndicator;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\View\Helper\Abstraction;

class View_Helper_Work_Mission_Mail_Daily extends Abstraction
{
	public function render( $data )
	{
		$baseUrl		= $this->env->getConfig()->get( 'app.base.url' );
		$words			= $this->env->getLanguage()->getWords( 'work/mission' );
		$w				= (object) $words['mail-daily'];
		$monthNames		= (array) $words['months'];
		$weekdays		= (array) $words['days'];
		$salutes		= (array) $words['mail-salutes'];
		$salute			= $salutes ? $salutes[array_rand( $salutes )] : "";
		$indicator		= new HtmlIndicator();
		$titleLength	= 80;#$config->get( 'module.work_mission.mail.title.length' );
		$formatDate		= 'j.n.';#$config->get( 'module.work_mission.mail.format.date' );			//  @todo	realize date format in module config

//		$words			= $this->getWords( 'work/mission' );

		//  --  TASKS  --  //
		$tasks		= $w->textNoTasks;
		if( count( $data['tasks'] ) ){
			$helper		= new View_Helper_Work_Mission_List( $this->env, $data['tasks'], $words );
			$rows		= $helper->renderRows( 0 );
			$colgroup	= HtmlElements::ColumnGroup( "125", "" );
			$attributes	= ['class' => 'table-mail table-mail-tasks'];
			$table		= HtmlTag::create( 'table', $colgroup.$rows, $attributes );
			$heading	= $w->headingTasks ? HtmlTag::create( 'h4', $w->headingTasks ) : "";
			$tasks		= $heading.$table;
		}

		//  --  EVENTS  --  //
		$events		= $w->textNoEvents;

		if( count( $data['events'] ) ){
			$helper		= new View_Helper_Work_Mission_List( $this->env, $data['events'], $words );
			$rows		= $helper->renderRows( 0 );
			$colgroup	= HtmlElements::ColumnGroup( "125", "" );
			$attributes	= ['class' => 'table-mail table-mail-events'];
			$table		= HtmlTag::create( 'table', $colgroup.$rows, $attributes );
			$heading	= $w->headingEvents ? HtmlTag::create( 'h4', $w->headingEvents ) : "";
			$events		= $heading.$table;
		}

		$heading	= $w->heading ? HtmlTag::create( 'h3', $w->heading ) : "";
		$username	= $data['user']->username;
		$username	= HtmlTag::create( 'span', $username, ['class' => 'text-username'] );
		$dateFull	= $weekdays[date( 'w' )].', der '.date( "j" ).'.&nbsp;'.$monthNames[date( 'n' )];
		$dateFull	= HtmlTag::create( 'span', $dateFull, ['class' => 'text-date-full'] );
		$dateShort	= HtmlTag::create( 'span', date( $formatDate ), ['class' => 'text-date-short'] );
		$greeting	= sprintf( $w->greeting, $username, $dateFull, $dateShort );
		$body	= '
'.$heading.'
<div class="text-greeting">'.$greeting.'</div>
<div class="tasks">'.$tasks.'</div>
<div class="events">'.$events.'</div>
<div class="text-salute">'.$salute.'</div>
<div class="text-signature">'.$w->textSignature.'</div>';

		$this->addPrimerStyle( 'layout.css' );
		$this->addThemeStyle( 'layout.css' );
		$this->addThemeStyle( 'site.user.css' );
		$this->addThemeStyle( 'site.mission.css' );
		$this->addThemeStyle( 'indicator.css' );

		$this->page->addBody( $body );
		$class	= 'moduleWorkMission jobWorkMission job-work-mission-mail-daily';
		return $this->page->build( ['class' => $class] );
	}
}
