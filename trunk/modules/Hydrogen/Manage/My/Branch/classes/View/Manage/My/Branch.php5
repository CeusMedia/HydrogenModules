<?php
class View_Manage_My_Branch extends CMF_Hydrogen_View{

	public function index(){}
	public function add(){}
	public function edit(){}
}
?>


INSERT INTO `roles` (`roleId`, `access`, `register`, `title`, `description`, `createdAt`, `modifiedAt`) VALUES
(1, 128, 0, 'Superuser', '', 1294083736, 0),
(2, 128, 0, 'Entwickler', '', 1294083736, 0),
(3, 64, 0, 'Administrator', '', 1294083928, 0),
(4, 64, 0, 'Manager', '', 1294083948, 0),
(5, 64, 64, 'Anbieter', 'Geschäftskunde mit Angeboten', 1294083995, 0),
(6, 64, 128, 'Nutzer', 'Coupons-Nutzer', 1294084004, 0);
