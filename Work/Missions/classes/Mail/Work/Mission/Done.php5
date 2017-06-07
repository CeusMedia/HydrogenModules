<?php
class Mail_Work_Mission_Done extends Mail_Abstract{

	protected function generate( $data = array() ){
		$w			= (object) $this->getWords( 'work/mission', 'mail-change' );
		$html		= $this->renderBody( $data );
		$body		= chunk_split( base64_encode( $html ), 78 );
		$mailBody	= new Net_Mail_Body( $body, Net_Mail_Body::TYPE_HTML );
		$mailBody->setContentEncoding( 'base64' );
		$prefix	= $this->env->getConfig()->get( 'module.resource_mail.subject.prefix' );
		$this->mail->setSubject( ( $prefix ? $prefix.' ' : '' ) . $w->subject );
		$this->mail->addBody( $mailBody );
		return $html;
	}

	public function renderBody( $data ){
		return "not implemented, yet";
/*		$baseUrl		= $this->env->getConfig()->get( 'app.base.url' );
		$w				= (object) $this->getWords( 'work/mission', 'mail-daily' );
		$monthNames		= (array) $this->getWords( 'work/mission', 'months' );
		$weekdays		= (array) $this->getWords( 'work/mission', 'days' );
		$salutes		= (array) $this->getWords( 'work/mission', 'mail-salutes' );
		$salute			= $salutes ? $salutes[array_rand( $salutes )] : "";
		$indicator		= new UI_HTML_Indicator();
		$titleLength	= 80;#$config->get( 'module.work_mission.mail.title.length' );
		$formatDate		= 'j.n.';#$config->get( 'module.work_mission.mail.format.date' );			//  @todo	kriss: realize date format in module config

		$words			= $this->getWords( 'work/mission' );

		//  --  TASKS  --  //
		$tasks		= $w->textNoTasks;
		if( count( $data['tasks'] ) ){
			$helper		= new View_Helper_Work_Mission_List( $this->env );
			$helper->setMissions( $data['tasks'] );
			$helper->setWords( $words );
			$rows		= $helper->renderDayList( 1, 0, TRUE, TRUE );
			$colgroup	= UI_HTML_Elements::ColumnGroup( "80", "100", "", "100" );
			$attributes	= array( 'class' => 'table-mail table-mail-tasks' );
			$table		= UI_HTML_Tag::create( 'table', $colgroup.$rows, $attributes );
			$heading	= $w->headingTasks ? UI_HTML_Tag::create( 'h4', $w->headingTasks ) : "";
			$tasks		= $heading.$table;
		}

		//  --  EVENTS  --  //
		$events		= $w->textNoEvents;

		if( count( $data['events'] ) ){
			$helper		= new View_Helper_Work_Mission_List( $this->env );
			$helper->setMissions( $data['events'] );
			$helper->setWords( $words );
			$rows		= $helper->renderDayList( 1, 0, TRUE, TRUE );
			$colgroup	= UI_HTML_Elements::ColumnGroup( "125", "" );
			$attributes	= array( 'class' => 'table-mail table-mail-events' );
			$table		= UI_HTML_Tag::create( 'table', $colgroup.$rows, $attributes );
			$heading	= $w->headingEvents ? UI_HTML_Tag::create( 'h4', $w->headingEvents ) : "";
			$events		= $heading.$table;
		}

		$heading	= $w->heading ? UI_HTML_Tag::create( 'h3', $w->heading ) : "";
		$username	= $data['user']->username;
		$username	= UI_HTML_Tag::create( 'span', $username, array( 'class' => 'text-username' ) );
		$dateFull	= $weekdays[date( 'w' )].', der '.date( "j" ).'.&nbsp;'.$monthNames[date( 'n' )];
		$dateFull	= UI_HTML_Tag::create( 'span', $dateFull, array( 'class' => 'text-date-full' ) );
		$dateShort	= UI_HTML_Tag::create( 'span', date( $formatDate ), array( 'class' => 'text-date-short' ) );
		$greeting	= sprintf( $w->greeting, $username, $dateFull, $dateShort );
		$body	= '
'.$heading.'
<div class="text-greeting text-info">'.$greeting.'</div>
<div class="tasks">'.$tasks.'</div>
<div class="events">'.$events.'</div>
<!--
<div class="text-salute">'.$salute.'</div>
-->';

		$this->addPrimerStyle( 'layout.css' );
		$this->addThemeStyle( 'bootstrap.css' );
		$this->addThemeStyle( 'layout.css' );
		$this->addThemeStyle( 'site.user.css' );
		$this->addThemeStyle( 'site.mission.css' );
		$this->addThemeStyle( 'indicator.css' );

		$this->addBodyClass( 'moduleWorkMission' );
		$this->addBodyClass( 'jobWorkMission' );
		$this->addBodyClass( 'job-work-mission-mail-done' );

		return $this->setHtml( $body );*/
	}
}
?>
