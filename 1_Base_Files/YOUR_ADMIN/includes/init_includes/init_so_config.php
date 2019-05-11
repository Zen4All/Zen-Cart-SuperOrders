<?php

/**
 * Super Orders
 * @version 5.0.0
 * @author Zen4All
 * @copyright (c) 2014-2019, Zen4All
 * @license http://www.gnu.org/licenses/gpl.txt GNU General Public License V2.0
 */
 if (!defined('IS_ADMIN_FLAG')) {
     die('Illegal access');
 }

 $module_constant = 'SO_VERSION';
 $module_installer_directory = DIR_FS_ADMIN . 'includes/installers/super_orders';
 $module_name = 'Super Orders';
 $module_file_for_version_check = '';
 $zencart_com_plugin_id = 155;

 $configuration_group_id = '';
 if (defined($module_constant)) {
     $current_version = constant($module_constant);
 } else {
     $current_version = '0.0.0';
  $db->Execute("INSERT INTO " . TABLE_CONFIGURATION_GROUP . " (configuration_group_title, configuration_group_description, sort_order, visible)
                VALUES ('" . $module_name . "', '" . $module_name . " Settings', '1', '1');");
  $configuration_group_id = $db->Insert_ID();

  $db->Execute("UPDATE " . TABLE_CONFIGURATION_GROUP . "
                SET sort_order = " . $configuration_group_id . "
                WHERE configuration_group_id = " . $configuration_group_id . ";");

  $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added)
                VALUES ('Version', '" . $module_constant . "', '" . $current_version . "', 'Version installed:', " . $configuration_group_id . ", 0, NOW(), NOW());");
}

if ($configuration_group_id == '') {
  $config = $db->Execute("SELECT configuration_group_id FROM " . TABLE_CONFIGURATION . " WHERE configuration_key= '" . $module_constant . "'");
  $configuration_group_id = $config->fields['configuration_group_id'];
}

$installers = scandir($module_installer_directory, 1);

$newest_version = $installers[0];
$newest_version = substr($newest_version, 0 - 4);

sort($installers);
if (version_compare($newest_version, $current_version) > 0) {
  foreach ($installers as $installer) {
    if (version_compare($newest_version, substr($installer, 0, - 4)) >= 0 && version_compare($current_version, substr($installer, 0, - 4)) < 0) {
      include($module_installer_directory . '/' . $installer);
      $current_version = str_replace('_', '.', substr($installer, 0, - 4));
      $db->Execute("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '" . $current_version . "' WHERE configuration_key = '" . $module_constant . "' LIMIT 1;");
      $messageStack->add("Installed " . $module_name . " v" . $current_version, 'success');
    }
  }
}

// Version Checking
$module_file_for_version_check = ($module_file_for_version_check != '') ? DIR_FS_ADMIN . $module_file_for_version_check : '';
if ($zencart_com_plugin_id != 0 && $module_file_for_version_check != '' && $_SERVER["PHP_SELF"] == $module_file_for_version_check) {
  $new_version_details = plugin_version_check_for_updates($zencart_com_plugin_id, $current_version);
  if ($_GET['gID'] == $configuration_group_id && $new_version_details != false) {
    $messageStack->add("Version " . $new_version_details['latest_plugin_version'] . " of " . $new_version_details['title'] . ' is available at <a href="' . $new_version_details['link'] . '" target="_blank">[Details]</a>', 'caution');
  }
}

if (!function_exists('plugin_version_check_for_updates')) {

  function plugin_version_check_for_updates($plugin_file_id = 0, $version_string_to_compare = '', $strict_zc_version_compare = false)
  {

    if ($plugin_file_id === 0) {
      return false;
    }

    if (false === ENABLE_PLUGIN_VERSION_CHECKING) {
      return false;
    }

    $new_version_available = false;
    $versionServer = new VersionServer();
    $data = json_decode($versionServer->getPluginVersion($plugin_file_id), true);

    if (null === $data || isset($data['error'])) {
      if (!empty(LOG_PLUGIN_VERSIONCHECK_FAILURES)) {
        error_log('CURL error checking plugin versions: ' . print_r($data['error'], true));
      }
      return false;
    }

    if (!is_array($data)) {
      $data = json_decode($data, true);
    }

    if (strcmp($data[0]['latest_plugin_version'], $version_string_to_compare) > 0) {
      $new_version_available = true;
    }

    // check whether present ZC version is compatible with the latest available plugin version
    if (!defined('PLUGIN_VERSION_CHECK_MATCHING_OVERRIDE') || empty(PLUGIN_VERSION_CHECK_MATCHING_OVERRIDE)) {
      $zc_version = PROJECT_VERSION_MAJOR . '.' . preg_replace('/[^0-9.]/', '', PROJECT_VERSION_MINOR);
      if ($strict_zc_version_compare) {
        $zc_version = PROJECT_VERSION_MAJOR . '.' . PROJECT_VERSION_MINOR;
      }
      if (!in_array('v' . $zc_version, $data[0]['zcversions'], false)) {
        $new_version_available = false;
      }
    }

    return $new_version_available ? $data[0] : false;
  }

}
