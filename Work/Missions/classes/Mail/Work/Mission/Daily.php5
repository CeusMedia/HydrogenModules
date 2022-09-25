<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class Mail_Work_Mission_Daily extends Mail_Work_Mission_Abstract
{
	protected function generate(): self
	{
		$w			= (object) $this->getWords( 'work/mission', 'mail-daily' );
		$this->setSubject( $w->subject );
		$this->addBodyClass( 'job-work-mission-mail-daily' );
		return $this->setHtml( $this->renderBody() );
	}

	public function renderBody(): string
	{
		$data			= $this->data;
		$baseUrl		= $this->env->getConfig()->get( 'app.base.url' );
		$w				= (object) $this->getWords( 'work/mission', 'mail-daily' );
		$monthNames		= (array) $this->getWords( 'work/mission', 'months' );
		$weekdays		= (array) $this->getWords( 'work/mission', 'days' );
		$salutes		= (array) $this->getWords( 'work/mission', 'mail-salutes' );
		$salute			= $salutes ? $salutes[array_rand( $salutes )] : "";
		$indicator		= new UI_HTML_Indicator();
		$formatDate		= 'j.n.';#$config->get( 'module.work_mission.mail.format.date' );			//  @todo	kriss: realize date format in module config

		$words			= $this->getWords( 'work/mission' );

		//  --  TASKS  --  //
		$tasks		= $w->textNoTasks;
		if( count( $data['tasks'] ) ){
			$helper		= new View_Helper_Work_Mission_List( $this->env );
			$helper->setMissions( $data['tasks'] );
			$helper->setWords( $words );
			$rows		= $helper->renderDayList( 1, 0, TRUE, TRUE );
			$colgroup	= HtmlElements::ColumnGroup( "80", "100", "", "100" );
			$attributes	= array( 'class' => 'table-mail table-mail-tasks' );
			$table		= HtmlTag::create( 'table', $colgroup.$rows, $attributes );
			$heading	= $w->headingTasks ? HtmlTag::create( 'h4', $w->headingTasks ) : "";
			$tasks		= $heading.$table;
		}

		//  --  EVENTS  --  //
		$events		= $w->textNoEvents;

		if( count( $data['events'] ) ){
			$helper		= new View_Helper_Work_Mission_List( $this->env );
			$helper->setMissions( $data['events'] );
			$helper->setWords( $words );
			$rows		= $helper->renderDayList( 1, 0, TRUE, TRUE );
			$colgroup	= HtmlElements::ColumnGroup( "125", "" );
			$attributes	= array( 'class' => 'table-mail table-mail-events' );
			$table		= HtmlTag::create( 'table', $colgroup.$rows, $attributes );
			$heading	= $w->headingEvents ? HtmlTag::create( 'h4', $w->headingEvents ) : "";
			$events		= $heading.$table;
		}

		$username	= $data['user']->username;
		$username	= HtmlTag::create( 'span', $username, array( 'class' => 'text-username' ) );
		$dateFull	= $weekdays[date( 'w' )].', '.date( "j" ).'.&nbsp;'.$monthNames[date( 'n' )];
		$dateFull	= HtmlTag::create( 'span', $dateFull, array( 'class' => 'text-date-full' ) );
		$dateShort	= HtmlTag::create( 'span', date( $formatDate ), array( 'class' => 'text-date-short' ) );
		$greeting	= sprintf( $w->greeting, $username, $dateFull, $dateShort );
		$body	= '
<!--<div class="text-greeting text-info">'.$greeting.'</div>-->
<big>Datum: '.$dateFull.'</big>
<div class="tasks">'.$tasks.'</div>
<div class="events">'.$events.'</div>
<!--
<div class="text-salute">'.$salute.'</div>
-->';
		return $body;
	}
}
