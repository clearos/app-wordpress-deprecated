<?php

/**
 * WordPress controller.
 *
 * @category   Apps
 * @package    WordPress
 * @subpackage Controllers
 * @author     Your name <your@e-mail>
 * @copyright  2013 Your name / Company
 * @license    Your license
 */

///////////////////////////////////////////////////////////////////////////////
// Load dependencies
///////////////////////////////////////////////////////////////////////////////

$this->lang->load('wordpress');

///////////////////////////////////////////////////////////////////////////////
// Form
///////////////////////////////////////////////////////////////////////////////

echo infobox_highlight(lang('wordpress_app_name'), '...');
