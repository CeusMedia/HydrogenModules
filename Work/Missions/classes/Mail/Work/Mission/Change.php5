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

		$this->addPrimerStyle( 'layout.css' );
		$this->addThemeStyle( 'layout.css' );
		$this->addThemeStyle( 'layout.panels.css' );
		$this->addThemeStyle( 'site.user.css' );
		$this->addThemeStyle( 'site.mission.css' );
		$this->addThemeStyle( 'indicator.css' );

		$html					= $this->renderBody( $data );
		$this->mail->addHtml( $html, 'utf-8', 'base64' );
		return $html;
	}

	protected function setSubjectFromMission( $mission ){
		$prefix		= $this->env->getConfig()->get( 'module.resource_mail.subject.prefix' );
		$subject	= $this->words->subject . ': ' . $mission->title;
		$this->mail->setSubject( ( $prefix ? $prefix.' ' : '' ) . $subject );
	}
}
?>
