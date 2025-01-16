<?php
// Autoload includes
require_once get_template_directory() . '/includes/setup.php';
require_once get_template_directory() . '/includes/custom-post-types.php';
require_once get_template_directory() . '/includes/security.php';
require_once get_template_directory() . '/includes/acf-config.php';

// Register custom REST API endpoints
$endpoint_files = glob(get_template_directory() . '/includes/endpoints/*.php');
foreach ($endpoint_files as $file) {
    require_once $file;
}
