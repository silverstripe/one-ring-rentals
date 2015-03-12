<?php

class PropertyAdmin extends ModelAdmin {

	private static $menu_title = 'Properties';

	private static $url_segment = 'properties';

	private static $managed_models = array (
		'Property'
	);

	private static $menu_icon = 'mysite/icons/property.png';
}