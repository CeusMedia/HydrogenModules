<?php
class Mail_Work_Mission_Daily extends Mail_Abstract{

	protected function generate( $data = array() ){
		$config		= $this->env->getConfig();
		$baseUrl	= $config->get( 'app.base.url' );
#		$words			= $this->env->getLanguage()->getWords( 'auth', 'mails' );
#		$data['config']	= $this->env->getConfig()->getAll();

#		$subject	= $words['mails']['onRegister'];
		$subject	= "Aufgaben";

		$salutes	= array(
			"Hau rein!", "Kopf hoch!", "Carpe Diem!", "Lass krachen!", "Viel Erfolg!", "Alles Gute!", "Wird schon!", "Nervt wa? Aber muss ja ;)", "Na super!", "Mach was draus!"
		);
		$salute	= $salutes[array_rand( $salutes )];

		$indicator	= new UI_HTML_Indicator();
		$tasks		= "";#"<em><small>Keine.</small></em>";
		if( count( $data['tasks'] ) ){
			$tasks		= array();
			foreach( $data['tasks'] as $task ){
				$title		= Alg_Text_Trimmer::trimCentric( $task->content, 80, '...' );
				$title		= htmlentities( $title, ENT_QUOTES, 'UTF-8' );
				$link		= UI_HTML_Tag::create( 'a', $title, array( 'href' => $baseUrl.'work/mission/edit/'.$task->missionId ) );
				$attributes	= array( 'class' => 'row-priority priority-'.$task->priority );
				$graph		= $indicator->build( $task->status, 4 );
				$cellGraph	= UI_HTML_Tag::create( 'td', $graph, array( 'class' => 'cell-graph' ) );
				$cellTitle	= UI_HTML_Tag::create( 'td', $link, array( 'class' => 'cell-title' ) );
				$cells		= UI_HTML_Tag::create( 'td', $graph ).UI_HTML_Tag::create( 'td', $link );
				$tasks[]	= UI_HTML_Tag::create( 'tr', $cellGraph.$cellTitle, $attributes );
			}
			$colgroup	= UI_HTML_Elements::ColumnGroup( "125", "" );
			$table		= UI_HTML_Tag::create( 'table', $colgroup.join( "\n", $tasks ), array( 'class' => 'table-mail table-mail-tasks' ) );
			$tasks		= '<h4>Du hast heute folgende Aufgaben:</h4>'.$table;
		}

		$events		= "";#"<em><small>Keine.</small></em>";
		if( count( $data['events'] ) ){
			$events		= array();
			foreach( $data['events'] as $event ){
				$title		= Alg_Text_Trimmer::trimCentric( $event->content, 80, '...' );
				$title		= htmlentities( $title, ENT_QUOTES, 'UTF-8' );
				$link		= UI_HTML_Tag::create( 'a', $title, array( 'href' => $baseUrl.'work/mission/edit/'.$event->missionId ) );
				$attributes	= array( 'class' => 'row-priority priority-'.$event->priority );
				$graph		= $indicator->build( $event->status, 4 );
				$times		= date( 'H:i', strtotime( $event->timeStart ) ).' - '.date( 'H:i', strtotime( $event->timeEnd ) ).' Uhr';
				$cellTime	= UI_HTML_Tag::create( 'td', $times, array( 'class' => 'cell-time' ) );
				$cellTitle	= UI_HTML_Tag::create( 'td', $link, array( 'class' => 'cell-title' ) );
				$events[]	= UI_HTML_Tag::create( 'tr', $cellTime.$cellTitle, $attributes );
			}
			$colgroup	= UI_HTML_Elements::ColumnGroup( "125", "" );
			$table		= UI_HTML_Tag::create( 'table', $colgroup.join( "\n", $events ), array( 'class' => 'table-mail table-mail-events' ) );
			$events		= '<h4>Du hast heute diese Termine:</h4>'.$table;
		}

		$body	= '
<style>
.table-mail td.cell-title a {
	text-decoration: none;
	font-size: 1.1em;	
	}
.table-mail td.cell-time {
	font-weight: bold;
	font-size: 1.1em;
	text-align: center;
	}
.table-mail {
	width: 600px;
	border-radius: 0.4em;
	overflow: hidden;
	border: 1px solid gray;
	box-shadow: 2px 2px 3px #BBB
}
</style>
<!--<h2>Aufgaben und Termine</h2>-->
<div class="text-greeting">
	Hallo <span class="text-username">'.$data['user']->username.'</span>, heute ist der <span class="text-date">'.date( 'j.n.', time() ).'</span>.
</div>
<div class="tasks">
	'.$tasks.'
</div>
<div class="events">
	'.$events.'
</div>
<br/>
<div class="text-salute">
	'.$salute.'
</div>
<div class="text-signature">
	<em>dein Ceus Media Office</em>
</div>
';

		$page		= new UI_HTML_PageFrame();
		$page->setBaseHref( $baseUrl );

		$pathTheme	= $config->get( 'path.themes' ).$config->get( 'layout.theme' ).'/';
		$pathPrimer	= $config->get( 'path.themes' ).$config->get( 'layout.primer' ).'/';
		$styles	= array(
			'css/layout.css',
		);
		foreach( $styles as $style ){
			$style	= File_Reader::load( $pathPrimer.$style );
			$style	= str_replace( '(/lib/', '(http://'.getEnv( 'HTTP_HOST' ).'/lib/', $style );
			$page->addHead( UI_HTML_Tag::create( 'style', $style ) );
		}
		$styles	= array(
			'css/mail.min.css',
			'css/layout.css',
			'css/site.mission.css',
			'css/site.user.css',
			'css/indicator.css',
		);
		foreach( $styles as $style ){
			$style	= File_Reader::load( $pathTheme.$style );
			$style	= str_replace( '(/lib/', '(http://'.getEnv( 'HTTP_HOST' ).'/lib/', $style );
			$page->addHead( UI_HTML_Tag::create( 'style', $style ) );
		}


		$page->addBody( UI_HTML_Tag::create( 'script', File_Reader::load( $config->get( 'path.scripts' ).'mail.min.js' ) ) );
		$page->addBody( $body );
		$page	= $page->build( array( 'class' => 'moduleMission controller-work-mission action-mail siteWorkMissionMail' ) );
		print( $page );
die;

		$mail	= new Net_Mail_Body(  base64_encode( $page ), Net_Mail_Body::TYPE_HTML );
		$mail->setContentEncoding( 'base64' );
		$this->mail->addBody( $mail );


		xmp( $body );
		die;


		$this->mail->setSubject( $subject );
		$this->mail->addBody( new Net_Mail_Body( $body, Net_Mail_Body::TYPE_PLAIN ) );
	}
}
?>

