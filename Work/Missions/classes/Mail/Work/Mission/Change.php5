<?php
abstract class Mail_Work_Mission_Change extends Mail_Abstract{

	protected $baseUrl;
	protected $facts				= array();
	protected $indicator;
	protected $labelsMonthNames;
	protected $labelsWeekdays;
	protected $labelsTypes;
	protected $labelsStates;
	protected $labelsPriorities;
	protected $labels;
	protected $languageSection		= NULL;
	protected $modelUser;
	protected $salute;
	protected $changedFactClassPos	= 'label label-success';
	protected $changedFactClassNeg	= 'label label-important';

	protected function enlistFact( $key, $value, $class = NULL ){
		$labelKey	= 'label'.ucfirst( $key );
		if( !is_null( $class ) ){
			if( $class === TRUE )
				$class	= $this->changedFactClassPos;
			else if( $class === FALSE )
				$class	= $this->changedFactClassNeg;
			$value	= UI_HTML_Tag::create( 'span', $value, array( 'class' => $class ) );
		}
		$term		= UI_HTML_Tag::create( 'dt', $this->labels->$labelKey );
		$definition	= UI_HTML_Tag::create( 'dd', $value );
		$this->facts[$key]   =   $term.$definition;
	}

	protected function generate( $data = array() ){
		$this->baseUrl			= $this->env->getConfig()->get( 'app.base.url' );
		$this->words			= (object) $this->getWords( 'work/mission', $this->languageSection );
		$this->labelsMonthNames	= (array) $this->getWords( 'work/mission', 'months' );
		$this->labelsWeekdays	= (array) $this->getWords( 'work/mission', 'days' );
		$this->labelsTypes		= (array) $this->getWords( 'work/mission', 'types' );
		$this->labelsStates		= (array) $this->getWords( 'work/mission', 'states' );
		$this->labelsPriorities	= (array) $this->getWords( 'work/mission', 'priorities' );
		$this->labels			= (object) $this->getWords( 'work/mission', 'add' );
		$this->salutes			= (array) $this->getWords( 'work/mission', 'mail-salutes' );
		$this->indicator		= new UI_HTML_Indicator();
		$this->modelUser		= new Model_User( $this->env );

//		$this->addThemeStyle( 'layout.panels.css' );
		$this->addThemeStyle( 'site.user.css' );
		$this->addThemeStyle( 'site.mission.css' );
		$this->addThemeStyle( 'indicator.css' );
	}

	protected function renderUser( $user, $link = FALSE ){

		if( $this->env->getModules()->has( 'Members' ) ){
			$helper	= new View_Helper_Member( $this->env );
			$helper->setUser( $user );
			$helper->setMode( 'inline' );
			$helper->setLinkUrl( 'member/view/'.$user->userId );
			$userLabel	= $helper->render();
		}
		else{
			$iconUser	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'not_icon-user fa fa-fw fa-user' ) );
			$fullname	= '('.$user->firstname.' '.$user->surname.')';
			$fullname	= UI_HTML_Tag::create( 'small', $fullname, array( 'class' => 'muted' ) );
			$userLabel	= $iconUser.'&nbsp;'.$user->username.'&nbsp;'.$fullname;
		}
		return $userLabel;
	}

	protected function renderLinkedTitle( $mission ){
		return UI_HTML_Tag::create( 'a', $mission->title, array(
			'href'	=> './work/mission/view/'.$mission->missionId
		) );
	}

	protected function setSubjectFromMission( $mission ){
		$prefix		= $this->env->getConfig()->get( 'module.resource_mail.subject.prefix' );
		$subject	= $this->words->subject . ': ' . $mission->title;
		$this->mail->setSubject( ( $prefix ? $prefix.' ' : '' ) . $subject );
	}
}
?>
