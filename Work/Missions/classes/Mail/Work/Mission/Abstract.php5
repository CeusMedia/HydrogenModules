<?php
abstract class Mail_Work_Mission_Abstract extends Mail_Abstract{

	protected $indicator;
	protected $labelsMonthNames;
	protected $labelsWeekdays;
	protected $labelsTypes;
	protected $labelsStates;
	protected $labelsPriorities;
	protected $modelUser;
	protected $salute;

	protected function __onInit(){
		parent::__onInit();
		$this->addThemeStyle( 'module.work.missions.css' );
		$this->addThemeStyle( 'indicator.css' );
		$this->addThemeStyle( 'site.user.css' );
		$this->addBodyClass( 'moduleWorkMission' );
		$this->addBodyClass( 'jobWorkMission' );
//		$this->addThemeStyle( 'layout.panels.css' );

		$this->indicator		= new UI_HTML_Indicator();
		$this->modelUser		= new Model_User( $this->env );

		$this->labelsMonthNames	= (array) $this->getWords( 'work/mission', 'months' );
		$this->labelsWeekdays	= (array) $this->getWords( 'work/mission', 'days' );
		$this->labelsTypes		= (array) $this->getWords( 'work/mission', 'types' );
		$this->labelsStates		= (array) $this->getWords( 'work/mission', 'states' );
		$this->labelsPriorities	= (array) $this->getWords( 'work/mission', 'priorities' );
		$this->salutes			= (array) $this->getWords( 'work/mission', 'mail-salutes' );
	}
}
?>
