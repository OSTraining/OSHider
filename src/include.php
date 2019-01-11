<?php
/**
 * @package   ShackHider
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2019 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 *
 * This file is part of ShackHider.
 *
 * ShackHider is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * ShackHider is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with ShackHider.  If not, see <http://www.gnu.org/licenses/>.
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
