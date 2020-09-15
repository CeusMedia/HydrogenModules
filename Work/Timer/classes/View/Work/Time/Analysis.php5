<?php
class View_Work_Time_Analysis extends CMF_Hydrogen_View {

	protected function __onInit(){
		$this->modelTimer		= new Model_Work_Timer( $this->env );
		$this->modelProject		= new Model_Project( $this->env );

		$monthsLong		= array_values( (array) $this->getWords( 'months' ) );
		$monthsShort	= array_values( (array) $this->getWords( 'months-short' ) );

		$page		= $this->env->getPage();
		$page->js->addScript( 'var monthNames = '.json_encode( $monthsLong).';' );
		$page->js->addScript( 'var monthNamesShort = '.json_encode( $monthsShort).';' );
	}

	public static function ___onRegisterTab( CMF_Hydrogen_Environment $env, $context, $module, $data ){
		$words	= (object) $env->getLanguage()->getWords( 'work/time' );							//  load words
//		$context->registerTab( '', $words->tabs['dashboard'], 0 );									//  register main tab
		$context->registerTab( 'analysis', $words->tabs['analysis'], 2 );							//  register main tab
//		$context->registerTab( 'report', $words->tabs['report'], 2 );								//  register main tab
	}

	public function add(){}

	public function edit(){}

	public function index(){}
}
?>
