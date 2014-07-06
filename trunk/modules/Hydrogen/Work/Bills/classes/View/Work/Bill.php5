<?php
class View_Work_Bill extends CMF_Hydrogen_View{

	public function __onInit(){
		parent::__onInit();
		$page			= $this->env->getPage();
		$session		= $this->env->getSession();
		$monthsLong		= array_values( (array) $this->getWords( 'months' ) );
		$monthsShort	= array_values( (array) $this->getWords( 'months-short' ) );
		$page->js->addScript( 'var monthNames = '.json_encode( $monthsLong).';' );
		$page->js->addScript( 'var monthNamesShort = '.json_encode( $monthsShort).';' );
	}

	public function add(){}
	public function edit(){}
	public function index(){}
	public function remove(){}
	public function graph(){}

	public static function ___onRegisterTab( $env, $context, $module, $data ){
		$words	= (object) $env->getLanguage()->getWords( 'work/bill' );							//  load words
		$context->registerTab( '', $words->tabs['list'], 0 );										//  register main tab
		$context->registerTab( 'graph', $words->tabs['graph'], 5 );										//  register graph tab
	}

	public static function renderTabs( CMF_Hydrogen_Environment_Abstract $env, $current = 0 ){
		$tabs	= new View_Helper_Navigation_Bootstrap_Tabs( $env );
		$tabs->setBasePath( './work/bill/' );
		$env->getModules()->callHook( "Work:Bills", "registerTabs", $tabs/*, $data*/ );						//  call tabs to be registered
		return $tabs->renderTabs( $current );
	}

	public function renderPrice( $price, $type, $suffix = NULL ){
		$price	= number_format( $price, 2, ',', '' ).$suffix;
		if( $type )
			$price	= '<span class="negative">-'.$price.'</span>';
		else
			$price	= '<span class="positive">+'.$price.'</span>';
		return $price;
	}

	public function renderTable( $bills, $path = NULL ){
		$words		= $this->getWords();
		$table		= '<div><em class="muted">Keine Einträge vorhanden.</em></div><br/>';
		if( $bills ){
			$iconIn	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-arrow-right', 'title' => 'an andere' ) );
			$iconOut	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-arrow-left', 'title' => 'von anderen' ) );
			$rows		= array();
			$helper		= new View_Helper_TimePhraser( $this->env );
			$format		= CMF_Hydrogen_View_Helper_Timestamp::$formatDatetime;
			CMF_Hydrogen_View_Helper_Timestamp::$formatDatetime = "d.m.Y";
			foreach( $bills as $bill ){
				$date	= strtotime( substr( $bill->date, 0, 4 ).'-'.substr( $bill->date, 4, 2).'-'.substr( $bill->date, 6, 2 ) );
				$label	= ( $bill->type ? $iconOut : $iconIn ) . '&nbsp;'.$bill->title;
				$link	= UI_HTML_Tag::create( 'a', $label, array( 'href' => './work/bill/edit/'.$bill->billId ) );
				$price	= $this->renderPrice( $bill->price, $bill->type, '&nbsp;&euro;' );
				$date	= strtotime( $bill->date );
				$date	= $bill->date < date( "Ymd" ) ? $helper->convert( $date, TRUE, 'vor' ) : date( 'd.m.Y', $date );
				$action	= "";
				if( $bill->status < 1 ){
					$url	= './work/bill/setStatus/'.$bill->billId.'/1';
					if( $path )
						$url	.= '?from='.$path;
					$label	= '<i class="icon-ok icon-white"></i>&nbsp;bezahlt';
					$action	= UI_HTML_Tag::create( 'a', $label, array(
						'class' => 'btn btn-small btn-success',
						'href'	=> $url
					) );
				}
				else{
					$url	= './work/bill/setStatus/'.$bill->billId.'/0';
					if( $path )
						$url	.= '?from='.$path;
					$label	= '<i class="icon-remove icon-white"></i>&nbsp;storniert';
					$action	= UI_HTML_Tag::create( 'a', $label, array(
						'class' => 'btn btn-small btn-danger',
						'href'	=> $url
					) );
				}
				$rows[]	= UI_HTML_Tag::create( 'tr', array(
					UI_HTML_Tag::create( 'td', $link, array( 'class' => 'title' ) ),
					UI_HTML_Tag::create( 'td', $price ),
					UI_HTML_Tag::create( 'td', $words['states'][$bill->status] ),
					UI_HTML_Tag::create( 'td', $date ),
					UI_HTML_Tag::create( 'td', $action ),
				), array( 'class' => 'bill-type-'.$bill->type.' '.( $bill->status ? 'success' : 'warning' ) ) );
			}
			$thead		= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array(
				'Title',
				'Betrag',
				'Zustand',
				'Fälligkeit',
			) ) );
			$colgroup	= UI_HTML_Elements::ColumnGroup( '40', '15%', '15%', '15%', '15%' );
			$tbody		= UI_HTML_Tag::create( 'tbody', $rows );
			$table		= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table' ) );
			CMF_Hydrogen_View_Helper_Timestamp::$formatDatetime	= $format;
		}
		return $table;
	}

}
?>
