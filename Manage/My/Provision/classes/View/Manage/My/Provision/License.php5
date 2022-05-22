<?php

use CeusMedia\HydrogenFramework\Environment;

class View_Manage_My_Provision_License extends CMF_Hydrogen_View
{
	public function add(){}
	public function assign(){}
	public function index(){}
	public function view(){}

	public static function ___onRegisterTab( Environment $env, $context, $module, $data )
	{
		$logicAuth		= Logic_Authentication::getInstance( $env );
		$logicProvision = Logic_User_Provision::getInstance( $env );
		$nrLicenses	= count( $logicProvision->getUserLicensesFromUser( $logicAuth->getCurrentUserId() ) );
		$nrKeys		= count( $logicProvision->getUserLicenseKeysFromUser( $logicAuth->getCurrentUserId() ) );

		$context->registerTab( '', self::renderTabLabel( $env, 'index', $nrLicenses, 'euro' ) );
		$context->registerTab( 'key', self::renderTabLabel( $env, 'keys', $nrKeys, 'key' ) );
		$context->registerTab( 'add', self::renderTabLabel( $env, 'add', 0, 'plus' ) );
	}
/*
	public static function ___onMyUserRegisterTab( Environment $env, $context, $module, $data ){
		$logicAuth		= Logic_Authentication::getInstance( $env );
		$logicProvision = Logic_Accounting::getInstance( $env );
		$nrLicenses	= count( $logicProvision->getUserLicensesFromUser( $logicAuth->getCurrentUserId() ) );
		$nrKeys		= count( $logicProvision->getUserLicenseKeysFromUser( $logicAuth->getCurrentUserId() ) );

		$context->registerTab( '../license', self::renderTabLabel( $env, 'index', $nrLicenses, 'euro' ) );
		$context->registerTab( '../license/key', self::renderTabLabel( $env, 'keys', $nrKeys, 'key' ) );
	}*/

	public static function renderDefinitionList( $data )
	{
		if( !count( $data ) )
			return '';
		$list	= [];
		foreach( $data as $key => $value ){
			$list[]	= UI_HTML_Tag::create( 'dt', $key );
			$list[]	= UI_HTML_Tag::create( 'dd', $value );
		}
		return UI_HTML_Tag::create( 'dl', $list, array( 'class' => 'dl-horizontal' ) );
	}

	public function renderLicenseFacts( $productLicense, $columns = [] )
	{
		$words	= $this->getWords();
		$list = [];
		$facts	= array( 'productTitle', 'licenseTitle', 'users', 'duration', 'price' );
		foreach( $facts as $fact ){
			if( $columns && !in_array( $fact, $columns ) )
				continue;
			switch( $fact ){
				case 'duration':
					$value	= $words['durations'][$productLicense->duration];
					break;
				case 'users':
					$unitUsers		= $productLicense->users == 1 ? $words['add']['unitUsersOne'] : $words['add']['unitUsersMany'];
					$value	= $productLicense->users.' '.$unitUsers;
					break;
				case 'price':
					$value	= $productLicense->price.' &euro;';
					if( !$productLicense->price )
						$value	= '<span class="for-free">kostenlos</span> <small class="muted">(0 &euro;)</small>';
					break;
				case 'productTitle':
					$value	= $productLicense->product->title;
					break;
				case 'licenseTitle':
					$value	= $productLicense->title;
					break;
				default:
					$value	= $productLicense->$fact;
			}
			$list[$words['add']["label".ucFirst( $fact )]]	= $value;

//			$list[]	= UI_HTML_Tag::create( 'dt', $words['add']["label".ucFirst( $fact )] );
//			$list[]	= UI_HTML_Tag::create( 'dd', $value );
		}
		if( $list )
			return self::renderDefinitionList( $list );
//			return UI_HTML_Tag::create( 'dl', $list, array( 'class' => 'dl-horizontal' ) );
	}

	public static function renderTabs( Environment $env, $current = 0 )
	{
		$tabs	= new View_Helper_Navigation_Bootstrap_Tabs( $env );

//		$tabs->setBasePath( './manage/my/user/' );
//		$env->getModules()->callHook( "MyUser", "registerTabs", $tabs/*, $data*/ );		//  call tabs to be registered
//		return UI_HTML_Tag::create( 'div', $tabs->renderTabs( $current ), array( 'id' => 'tabs-manage-my-user' ) );

		$tabs->setBasePath( './manage/my/provision/' );
		$env->getModules()->callHook( "ManageMyProvision", "registerTabs", $tabs/*, $data*/ );		//  call tabs to be registered
		return UI_HTML_Tag::create( 'div', $tabs->renderTabs( $current ), array( 'id' => 'tabs-manage-my-provision' ) );
	}

	protected function __onInit()
	{
		$this->env->getPage()->addThemeStyle( 'module.manage.my.provision.css' );
	}

	protected static function renderTabLabel( Environment $env, $labelKey, $count = 0, $icon = NULL )
	{
		$words	= (object) $env->getLanguage()->getWords( 'manage/my/provision' );					//  load words
		$label	= $words->tabs[$labelKey];
		if( $count )
			$label	.= '&nbsp;&nbsp;<span class="badge badge-info">'.$count.'</span>&nbsp;';
		if( $icon ){
			$icon	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-'.$icon ) );
			$label	= $icon.'&nbsp;'.$label;
		}
		return $label;
	}
}
