<?php
class Controller_Work_Mission_Archive_List extends Controller_Work_Mission_Archive{

	protected function __onInit(){
		parent::__onInit();
		$this->session->set( $this->filterKeyPrefix.'mode', 'list' );
	}

	public function index( $missionId = NULL ){
		if( strlen( trim( $missionId ) ) )
			$this->restart( './work/mission/'.$missionId );
		$this->assignFilters();
	}

	public function ajaxRenderContent(){
		$userId		= $this->session->get( 'userId' );
		$missions	= $this->getFilteredMissions( $userId );
		$this->addData( 'missions', $missions );
		$data	= array(
			'lists' => array(
				'large'	=> $this->view->ajaxRenderContent()
			)
		);
		print( json_encode( $data ) );
		exit;
	}
}
?>
