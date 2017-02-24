<?php
class View_Database_Lock extends CMF_Hydrogen_View{

	public function ajaxRenderDashboardPanel(){
		$context	= new View_Helper_Work_Time_Timer( $this->env );
		$this->env->getCaptain()->callHook( 'Work_Timer', 'registerModule', $context, array() );
		$modules	= $context->getRegisteredModules();

		$locks		= $this->getData( 'locks' );
		$content	= UI_HTML_Tag::create( 'div', 'Keine vorhanden.', array( 'class' => 'alert alert-info' ) );
		if( $locks ){
			$rows	= array();
			foreach( $locks as $lock ){

				$module	= $modules[$lock->subject];
				$entry	= $module->model->get( $lock->entryId );
				if( !$entry )
					throw new Exception( 'Relation between timer and module is invalid' );
				$lock->type				= $module->typeLabel;
				$lock->relation			= $entry;
				$lock->relationTitle	= $entry->{$module->column};
				$lock->relationLink		= str_replace( "{id}", $lock->entryId, $module->link );
				$link		= UI_HTML_Tag::create( 'a', $lock->relationTitle, array(
					'href' => $lock->relationLink,
				) );
				$time		= ceil( ( time() - $lock->timestamp ) / 60 ) * 60;
				$time		= View_Helper_Work_Time::formatSeconds( $time);
				$username	= UI_HTML_Tag::create( 'small', $lock->user->username );
				$rows[]	= UI_HTML_Tag::create( 'tr', array(
					UI_HTML_Tag::create( 'td', $username.'<br/>'.$link, array( 'class' => 'autocut' ) ),
					UI_HTML_Tag::create( 'td', $time, array( 'style' => 'text-align: right' ) ),
				) );
			}
			$colgroup	= UI_HTML_Elements::ColumnGroup( array( "", "100px" ) );
			$tbody		= UI_HTML_Tag::create( 'tbody', $rows );
			$content	= UI_HTML_Tag::create( 'table', $colgroup.$tbody, array( 'class' => 'table table-fixed' ) );
		}
		return $content;
	}

	public function index(){
		$this->setPageTitle();
	}
}
?>
