<?php
/* ----------------------------------------------------------------------
 * contentDeliveryMenuPlugin.php : provides functionality to show a dedicatd menu for LIBIS data transfer operations
 * ----------------------------------------------------------------------
 * CollectiveAccess
 * Open-source collections management software
 * ----------------------------------------------------------------------
 *
 * Software by Whirl-i-Gig (http://www.whirl-i-gig.com)
 * Copyright 2009-2013 Whirl-i-Gig
 *
 * For more information visit http://www.CollectiveAccess.org
 *
 * This program is free software; you may redistribute it and/or modify it under
 * the terms of the provided license as published by Whirl-i-Gig
 *
 * CollectiveAccess is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTIES whatsoever, including any implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
 *
 * This source code is free and modifiable under the terms of 
 * GNU General Public License. (http://www.gnu.org/copyleft/gpl.html). See
 * the "license.txt" file for details, or visit the CollectiveAccess web site at
 * http://www.CollectiveAccess.org
 *
 * ----------------------------------------------------------------------
 */
 
	class contentDeliveryMenuPlugin extends BaseApplicationPlugin {
		# -------------------------------------------------------
		public function __construct($ps_plugin_path) {
			$this->description = _t('Shows a list of operations provided by the LIBIS Content Delivery System');
			parent::__construct();
		}
		# -------------------------------------------------------
		/**
		 * Override checkStatus() to return true - the contentDeliveryMenu plugin always initializes ok
		 */
		public function checkStatus() {
			return array(
				'description' => $this->getDescription(),
				'errors' => array(),
				'warnings' => array(),
				'available' => true
			);
		}

		# -------------------------------------------------------
		/**
		 * Insert activity menu
		 */
		public function hookRenderMenuBar($pa_menu_bar) {
			
			$menu_1 = _t('Content List');
			$va_menu_items[$menu_1] = array(
				'displayName' => _t($menu_1),
				"default" => array(
					'module' => 'contentDeliveryMenu', 
					'controller' => 'ContentDelivery', 
					'action' => 'Index/universe/'.$menu_1
				)
			);				
			
			$menu_2 = _t('PID Generation');
			$va_menu_items[$menu_2] = array(
				'displayName' => _t($menu_2),
				"default" => array(
					'module' => 'contentDeliveryMenu', 
					'controller' => 'ContentDelivery', 
					'action' => 'Index/universe/'.$menu_2
				)
			);							

			$menu_3 = _t('ECK Core');
			$va_menu_items[$menu_3] = array(
				'displayName' => _t($menu_3),
				"default" => array(
					'module' => 'contentDeliveryMenu',
					'controller' => 'ContentDelivery',
					'action' => 'Index/universe/'.$menu_3
				)
			);

            $menu_4 = _t('Set Manager');
            $va_menu_items[$menu_4] = array(
                'displayName' => _t($menu_4),
                "default" => array(
                    'module' => 'contentDeliveryMenu',
                    'controller' => 'ContentDelivery',
                    'action' => 'Index/universe/'.$menu_4
                )
            );

            $menu_5 = _t('Validation');
            $va_menu_items[$menu_5] = array(
                'displayName' => _t($menu_5),
                "default" => array(
                    'module' => 'contentDeliveryMenu',
                    'controller' => 'ContentDelivery',
                    'action' => 'Index/universe/'.$menu_5
                )
            );

            $menu_6 = _t('Preview');
            $va_menu_items[$menu_6] = array(
                'displayName' => _t($menu_6),
                "default" => array(
                    'module' => 'contentDeliveryMenu',
                    'controller' => 'ContentDelivery',
                    'action' => 'Index/universe/'.$menu_6
                )
            );

            $menu_7 = _t('Data Push');
            $va_menu_items[$menu_7] = array(
                'displayName' => _t($menu_7),
                "default" => array(
                    'module' => 'contentDeliveryMenu',
                    'controller' => 'ContentDelivery',
                    'action' => 'Index/universe/'.$menu_7,
                )
            );

            $menu_8 = _t('Datapush Result');
            $va_menu_items[$menu_8] = array(
                'displayName' => _t($menu_8),
                "default" => array(
                    'module' => 'contentDeliveryMenu',
                    'controller' => 'ContentDelivery',
                    'action' => 'Index/universe/'.$menu_8
                )
            );

            $pa_menu_bar['libiscode_menu'] = array(
				'displayName' => _t('LibisCoDe'),
				'navigation' => $va_menu_items
			);			

			return $pa_menu_bar;
		}
		# -------------------------------------------------------
		/**
		 * Get plugin user actions
		 */
		static public function getRoleActionList() {
			return array();
		}
		# -------------------------------------------------------

	}


?>