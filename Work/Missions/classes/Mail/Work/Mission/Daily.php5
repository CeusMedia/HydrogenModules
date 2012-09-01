<?php
class Mail_Work_Mission_Daily extends Mail_Abstract{

	protected function generate( $data = array() ){
		$config			= $this->env->getConfig();
		$language		= $this->env->getLanguage();
		$w				= (object) $language->getWords( 'work/mission', 'mail-daily' );
		$salutes		= $language->getWords( 'work/mission', 'mail-salutes' );
		$salute			= $salutes ? $salutes[array_rand( $salutes )] : "";
		$baseUrl		 = $config->get( 'app.base.url' );
		$indicator		= new UI_HTML_Indicator();
		$page			= new UI_HTML_PageFrame();
		$titleLength	= 80;#$config->get( 'module.work_mission.mail.title.length' );


		//  --  TASKS  --  //
		$tasks		= array();
		foreach( $data['tasks'] as $task ){
			$title		= Alg_Text_Trimmer::trimCentric( $task->content, $titleLength, '...' );
			$title		= htmlentities( $title, ENT_QUOTES, 'UTF-8' );
			$url		= $baseUrl.'work/mission/edit/'.$task->missionId;
			$link		= UI_HTML_Tag::create( 'a', $title, array( 'href' => $url ) );
			$attributes	= array( 'class' => 'row-priority priority-'.$task->priority );
			$graph		= $indicator->build( $task->status, 4 );
			$cellGraph	= UI_HTML_Tag::create( 'td', $graph, array( 'class' => 'cell-graph' ) );
			$cellTitle	= UI_HTML_Tag::create( 'td', $link, array( 'class' => 'cell-title' ) );
			$cells		= UI_HTML_Tag::create( 'td', $graph ).UI_HTML_Tag::create( 'td', $link );
			$tasks[]	= UI_HTML_Tag::create( 'tr', $cellGraph.$cellTitle, $attributes );
		}
		$colgroup	= UI_HTML_Elements::ColumnGroup( "125", "" );
		$rows		= join( "\n", $tasks );
		$attributes	= array( 'class' => 'table-mail table-mail-tasks' );
		$table		= UI_HTML_Tag::create( 'table', $colgroup.$rows, $attributes );
		$heading	= $w->headingTasks ? UI_HTML_Tag::create( 'h4', $w->headingTasks ) : "";
		$tasks		= $heading.$table;
		if( !count( $data['tasks'] ) )
			$tasks		= $w->textNoTasks;

		//  --  EVENTS  --  //
		$events		= array();
		foreach( $data['events'] as $event ){
			$title		= Alg_Text_Trimmer::trimCentric( $event->content, $titleLength, '...' );
			$title		= htmlentities( $title, ENT_QUOTES, 'UTF-8' );
			$url		= $baseUrl.'work/mission/edit/'.$event->missionId;
			$link		= UI_HTML_Tag::create( 'a', $title, array( 'href' => $url ) );
			$attributes	= array( 'class' => 'row-priority priority-'.$event->priority );
			$graph		= $indicator->build( $event->status, 4 );
			$timeStart	= date( 'H:i', strtotime( $event->timeStart ) );
			$timeEnd	= date( 'H:i', strtotime( $event->timeEnd ) );
			$times		= $timeStart.' - '.$timeEnd.' '.$w->suffixTime;
			$cellTime	= UI_HTML_Tag::create( 'td', $times, array( 'class' => 'cell-time' ) );
			$cellTitle	= UI_HTML_Tag::create( 'td', $link, array( 'class' => 'cell-title' ) );
			$events[]	= UI_HTML_Tag::create( 'tr', $cellTime.$cellTitle, $attributes );
		}
		$colgroup	= UI_HTML_Elements::ColumnGroup( "125", "" );
		$rows		= join( "\n", $events );
		$attributes	= array( 'class' => 'table-mail table-mail-events' );
		$table		= UI_HTML_Tag::create( 'table', $colgroup.$rows, $attributes );
		$heading	= $w->headingEvents ? UI_HTML_Tag::create( 'h4', $w->headingEvents ) : "";
		$events		= $heading.$table;
		if( !count( $data['events'] ) )
			$events		= $w->textNoEvents;

		$heading	= $w->heading ? UI_HTML_Tag::create( 'h3', $w->heading ) : "";
		$username	= $data['user']->username;
		$username	= UI_HTML_Tag::create( 'span', $username, array( 'class' => 'text-username' ) );
		$date		= date( 'j.n.', time() );
		$date		= UI_HTML_Tag::create( 'span', $date, array( 'class' => 'text-date' ) );
		$greeting	= sprintf( $w->greeting, $username, $date );
		$body	= '
'.$heading.'
<div class="text-greeting">'.$greeting.'</div>
<div class="tasks">'.$tasks.'</div>
<div class="events">'.$events.'</div>
<div class="text-salute">'.$salute.'</div>
<div class="text-signature">'.$w->textSignature.'</div>';

		$this->addPrimerStyle( 'layout.css' );
		$this->addThemeStyle( 'mail.min.css' );
		$this->addThemeStyle( 'layout.css' );
		$this->addThemeStyle( 'site.mission.css' );
		$this->addThemeStyle( 'site.user.css' );
		$this->addThemeStyle( 'indicator.css' );

		$page->addBody( UI_HTML_Tag::create( 'script', File_Reader::load( $config->get( 'path.scripts' ).'mail.min.js' ) ) );
		$page->addBody( $body );
		$page	= $page->build( array( 'class' => 'moduleMission controller-work-mission action-mail siteWorkMissionMail' ) );

		$mailBody	= new Net_Mail_Body(  base64_encode( $page ), Net_Mail_Body::TYPE_HTML );
		$mailBody->setContentEncoding( 'base64' );
		$this->mail->setSubject( $w->subject );
		$this->mail->addBody( $mailBody );
	}
}
?>