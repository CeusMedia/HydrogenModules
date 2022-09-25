<?php

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

class View_Work_Bill extends View
{
	public function add()
	{
	}

	public function edit()
	{
	}

	public function index()
	{
	}

	public function remove()
	{
	}

	public function graph()
	{
	}

	public static function ___onRegisterTab( Environment $env, $context, $module, $data )
	{
		$words	= (object) $env->getLanguage()->getWords( 'work/bill' );							//  load words
		$context->registerTab( '', $words->tabs['list'], 0 );										//  register main tab
		$context->registerTab( 'graph', $words->tabs['graph'], 5 );										//  register graph tab
	}

	public static function renderTabs( Environment $env, $current = 0 )
	{
		$tabs	= new View_Helper_Navigation_Bootstrap_Tabs( $env );
		$tabs->setBasePath( './work/bill/' );
		$env->getModules()->callHook( "Work:Bills", "registerTabs", $tabs/*, $data*/ );						//  call tabs to be registered
		return $tabs->renderTabs( $current );
	}

	public function renderPrice( $price, $type, $suffix = NULL )
	{
		$price	= number_format( $price, 2, ',', '' ).$suffix;
		if( $type )
			$price	= '<span class="negative">-'.$price.'</span>';
		else
			$price	= '<span class="positive">+'.$price.'</span>';
		return $price;
	}

	public function renderTable( $bills, $path = NULL, $colored = TRUE )
	{
		$words		= $this->getWords();
		$table		= '<div><em class="muted">Keine Einträge vorhanden.</em></div><br/>';
		if( $bills ){
			$iconIn	= HtmlTag::create( 'i', '', array( 'class' => 'icon-arrow-right', 'title' => 'an andere' ) );
			$iconOut	= HtmlTag::create( 'i', '', array( 'class' => 'icon-arrow-left', 'title' => 'von anderen' ) );
			$rows		= [];
			$helper		= new View_Helper_TimePhraser( $this->env );
			$format		= CMF_Hydrogen_View_Helper_Timestamp::$formatDatetime;
			CMF_Hydrogen_View_Helper_Timestamp::$formatDatetime = "d.m.Y";
			foreach( $bills as $bill ){
				$date	= strtotime( substr( $bill->date, 0, 4 ).'-'.substr( $bill->date, 4, 2).'-'.substr( $bill->date, 6, 2 ) );
				$label	= ( $bill->type ? $iconOut : $iconIn ) . '&nbsp;'.$bill->title;
				$link	= HtmlTag::create( 'a', $label, array( 'href' => './work/bill/edit/'.$bill->billId ) );
				$price	= $this->renderPrice( $bill->price, $bill->type, '&nbsp;&euro;' );
				$date	= strtotime( $bill->date );
				$date	= $bill->date < date( "Ymd" ) ? $helper->convert( $date, TRUE, 'vor' ) : date( 'd.m.Y', $date );
				$action	= "";
				if( $bill->status < 1 ){
					$url	= './work/bill/setStatus/'.$bill->billId.'/1';
					if( $path )
						$url	.= '?from='.$path;
					$label	= '<i class="icon-ok icon-white"></i>&nbsp;bezahlt';
					$action	= HtmlTag::create( 'a', $label, array(
						'class' => 'btn btn-mini btn-success',
						'href'	=> $url
					) );
				}
				else{
					$url	= './work/bill/setStatus/'.$bill->billId.'/0';
					if( $path )
						$url	.= '?from='.$path;
					$label	= '<i class="icon-remove icon-white"></i>&nbsp;storniert';
					$action	= HtmlTag::create( 'a', $label, array(
						'class' => 'btn btn-mini btn-danger',
						'href'	=> $url
					) );
				}
				$class	= 'bill-type-'.$bill->type;
				if( $colored )
					$class	.= ' '.( $bill->status ? 'success' : 'warning' );
				$rows[]	= HtmlTag::create( 'tr', array(
					HtmlTag::create( 'td', $link, array( 'class' => 'title' ) ),
					HtmlTag::create( 'td', $price ),
					HtmlTag::create( 'td', $words['states'][$bill->status] ),
					HtmlTag::create( 'td', $date ),
					HtmlTag::create( 'td', $action ),
				), array( 'class' => $class ) );
			}
			$thead		= HtmlTag::create( 'thead', HtmlElements::TableHeads( array(
				'Title',
				'Betrag',
				'Zustand',
				'Fälligkeit',
			) ) );
			$colgroup	= HtmlElements::ColumnGroup( '40', '15%', '15%', '15%', '15%' );
			$tbody		= HtmlTag::create( 'tbody', $rows );
			$table		= HtmlTag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-striped table-condensed' ) );
			CMF_Hydrogen_View_Helper_Timestamp::$formatDatetime	= $format;
		}
		return $table;
	}

	protected function __onInit()
	{
		parent::__onInit();
		$page			= $this->env->getPage();
		$session		= $this->env->getSession();
		$monthsLong		= array_values( (array) $this->getWords( 'months' ) );
		$monthsShort	= array_values( (array) $this->getWords( 'months-short' ) );
		$page->js->addScript( 'var monthNames = '.json_encode( $monthsLong).';' );
		$page->js->addScript( 'var monthNamesShort = '.json_encode( $monthsShort).';' );
	}
}
