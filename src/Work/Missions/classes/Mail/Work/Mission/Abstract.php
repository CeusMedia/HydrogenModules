<?php

use CeusMedia\Common\UI\HTML\Indicator as HtmlIndicator;

abstract class Mail_Work_Mission_Abstract extends Mail_Abstract
{
	protected Model_User $modelUser;
	protected HtmlIndicator $indicator;
	protected array $labelsMonthNames;
	protected array $labelsWeekdays;
	protected array $labelsTypes;
	protected array $labelsStates;
	protected array $labelsPriorities;
	protected array $salutes;

	/**
	 *	@return		void
	 */
	protected function __onInit(): void
	{
		parent::__onInit();
		$this->addThemeStyle( 'module.work.missions.css' );
		$this->addThemeStyle( 'indicator.css' );
		$this->addThemeStyle( 'site.user.css' );
		$this->addBodyClass( 'moduleWorkMission' );
		$this->addBodyClass( 'jobWorkMission' );
//		$this->addThemeStyle( 'layout.panels.css' );

		$this->indicator		= new HtmlIndicator();
		$this->modelUser		= new Model_User( $this->env );

		$this->labelsMonthNames	= $this->getWords( 'work/mission', 'months' );
		$this->labelsWeekdays	= $this->getWords( 'work/mission', 'days' );
		$this->labelsTypes		= $this->getWords( 'work/mission', 'types' );
		$this->labelsStates		= $this->getWords( 'work/mission', 'states' );
		$this->labelsPriorities	= $this->getWords( 'work/mission', 'priorities' );
		$this->salutes			= $this->getWords( 'work/mission', 'mail-salutes' );
	}
}
