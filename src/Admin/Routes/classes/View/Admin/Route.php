<?php

use CeusMedia\HydrogenFramework\View;

class View_Admin_Route extends View
{
	public static $availableHttpCodes	= [
		200, 201, 202, 203, 204,
		300, 301, 303, 304, 307, 308,
		400, 401, 403, 404, 409, 410, 415, 423, 429, 451, 495, 496, 497,
		501, 502, 503, 508,
	];

	public function add(): void
	{
	}

	public function edit(): void
	{
	}

	public function index(): void
	{
	}
}
