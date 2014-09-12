<?php
class Controller_Work_Mission_Archive_Calendar extends Controller_Work_Mission_Archive{

	protected function __onInit(){
		parent::__onInit();
		$this->session->set( $this->filterKeyPrefix.'mode', 'calendar' );
	}
}
?>
