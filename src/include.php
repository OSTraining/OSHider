<?php
/**
 * @package   ShackHider
 * @contact   www.alledia.com, support@alledia.com
 * @copyright 2015 Alledia.com, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

if (!defined('ALLEDIA_FRAMEWORK_LOADED')) {
    $allediaFrameworkPath = JPATH_SITE . '/libraries/allediaframework/include.php';

    if (!file_exists($allediaFrameworkPath)) {
        throw new Exception('Alledia framework not found');
    }

    require_once $allediaFrameworkPath;
}

if (!defined('SHACKHIDER_PLUGIN_PATH')) {
    define('SHACKHIDER_PLUGIN_PATH', __DIR__);

    JLoader::register('JoomlashackShortcodes', __DIR__ . '/assets/shortcodes.php');
}
