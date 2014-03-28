<?php
/* ----------------------------------------------------------------------
 * plugins/contentDeliveryMenu/controllers/ContentDeliveryController.php :
 * ----------------------------------------------------------------------
 * CollectiveAccess
 * Open-source collections management software
 * ----------------------------------------------------------------------
 *
 * Software by Whirl-i-Gig (http://www.whirl-i-gig.com)
 * Copyright 2010 Whirl-i-Gig
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

 	require_once(__CA_LIB_DIR__.'/core/TaskQueue.php');
 	require_once(__CA_LIB_DIR__.'/core/Configuration.php');
 	require_once(__CA_MODELS_DIR__.'/ca_lists.php');
 	require_once(__CA_MODELS_DIR__.'/ca_objects.php');
 	require_once(__CA_MODELS_DIR__.'/ca_object_representations.php');
 	require_once(__CA_MODELS_DIR__.'/ca_locales.php');
 	require_once(__CA_APP_DIR__.'/plugins/statisticsViewer/lib/statisticsSQLHandler.php');
 	

 	class ContentDeliveryController extends ActionController {
 		# -------------------------------------------------------
  		protected $opo_config;		// plugin configuration file
 		protected $opa_dir_list;	// list of available import directories
 		protected $opa_regexes;		// list of available regular expression packages for extracting object idno's from filenames
 		protected $opa_regex_patterns;
 		protected $opa_locales;
 		protected $opa_statistics_xml_files;
 		protected $opa_statistics;
 		protected $opa_stat;
 		protected $opa_id;
 		protected $pa_parameters;
 		protected $allowed_universes;


 		# -------------------------------------------------------
 		# Constructor
 		# -------------------------------------------------------

 		public function __construct(&$po_request, &$po_response, $pa_view_paths=null) {
 			global $allowed_universes;
 			
 			parent::__construct($po_request, $po_response, $pa_view_paths);
 			
/*  			if (!$this->request->user->canDoAction('can_use_statistics_viewer_plugin')) {
 				$this->response->setRedirect($this->request->config->get('error_display_url').'/n/3000?r='.urlencode($this->request->getFullUrlPath()));
 				return;
 			} */
 			
 			// $this->opo_config = Configuration::load(__CA_APP_DIR__.'/plugins/statisticsViewer/conf/statisticsViewer.conf');
			
/* 			// Get directory list
			$va_file_list = caGetDirectoryContentsAsList(__CA_APP_DIR__."/plugins/statisticsViewer/".$this->opo_config->get('XmlStatisticsRootDirectory'), false, false);
			$va_dir_list_with_file_counts = caGetSubDirectoryList($this->opo_config->get('importRootDirectory'), true, false);
			$this->opa_statistics_xml_files = array();
			$this->opa_statistics = array();

			if (is_array($allowed_universes = $this->opo_config->getAssoc('AvailableUniversesForStats'))) {
				// if the conf variable AvailableUniversesFor stats is defined
				//echo "here.";
			} */
			
/* 			$this->get_statistics_listing($va_file_list,$allowed_universes);			
 */ 		}

 		# -------------------------------------------------------
 		# Local functions
 		# -------------------------------------------------------

 		 		
 		# -------------------------------------------------------
 		# Functions to render views
 		# -------------------------------------------------------
 		public function Index($type="") {
 			$universe=$this->request->getParameter('universe', pString);
 			if(!isset($universe)) {
 				_p("No corresponding table (or stat universe) declared.");
 			} else {
				$this->view->setVar('statistics_listing', $this->opa_statistics[$universe]);
				//$this->render('stats_home_html.php');			 				

				//Default view, the home page
				$view_to_rende = 'content_delivery_home_html.php';
				switch($universe) {
					case 'Content List':
						$view_to_rende = 'content_html.php';
						break;				
					case 'PID Generation':
						//View for Generating PIDs
						$view_to_rende = 'pid_html.php';
						break;							
					case 'ECK Core':
						//View for ECK Core Service
						$view_to_rende = 'eckcore_html.php';
						break;
                    case 'Set Manager':
                        //View for Set Manager Service
                        $view_to_rende = 'setmanager_html.php';
                        break;
                    case 'Validation':
                        //View for Validation  Service
                        $view_to_rende = 'validation_html.php';
                        break;
                    case 'Preview':
                        //View for Preview Service
                        $view_to_rende = 'preview_html.php';
                        break;
                    case 'Data Push':
                        //View for Data Push Service
                        $view_to_rende = 'datapush_html.php';
                        break;

                    case 'Datapush Result':
                        //View for Data Push Results
                        $view_to_rende = 'datapushresult_html.php';
                        break;

					default:
						$view_to_rende = 'content_delivery_home_html.php';
						break;
				}
				//Render the selected view
				$this->render($view_to_rende);
 			}
 		}

 		# ------------------------------------------------------- 				
 	}
 ?>