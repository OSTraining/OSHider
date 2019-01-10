<?php
/**
 * @package   ShackHider
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2019 Open Source Training, LLC. All rights reserved
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

use Alledia\Framework\Joomla\Extension\AbstractPlugin;

defined('_JEXEC') or die();

require_once 'include.php';

/**
 * ShackHider Content Plugin
 *
 */
class PlgContentShackhider extends AbstractPlugin
{
    /**
     * @var string
     */
    protected $namespace = 'ShackHider';

    protected $autoloadLanguage = true;

    /**
     * @var JoomlashackShortcodes
     */
    protected $finder = null;

    /**
     * @var JUser
     */
    protected $user = null;

    /**
     * @var string[]
     */
    protected $userGroups = null;

    /**
     * @var string[]
     */
    protected $accessLevels = null;

    /**
     * @param string $context
     * @param object $article
     *
     * @return void
     * @throws Exception
     */
    public function onContentPrepare($context, $article)
    {
        if (JFactory::getApplication()->isAdmin()) {
            return;
        }

        $codes = $this->find($article->text, array('jsshow', 'jshide'));
        foreach ($codes as $code => $items) {
            $show = ($code == 'jsshow');
            foreach ($items as $item) {
                foreach ($item->params as $param => $value) {
                    $method = 'replace' . ucfirst(strtolower($param));
                    if (method_exists($this, $method)) {
                        $match    = $show ? $item->content : '';
                        $mismatch = $show ? '' : $item->content;
                        $this->$method($item->source, $match, $mismatch, $article->text, $value);
                    }
                }
            }
        }
    }

    /**
     * Find the selected shortcode tags in the supplied text
     *
     * @param string   $text
     * @param string[] $codes
     *
     * @return object[]
     */
    protected function find($text, array $codes)
    {
        if ($this->finder === null) {
            $this->finder = new JoomlashackShortcodes();
        }

        return $this->finder->find($text, $codes);
    }

    /**
     * @param string $source
     * @param string $match
     * @param string $mismatch
     * @param string $text
     */
    protected function replaceRegistered($source, $match, $mismatch, &$text)
    {
        if ($this->getUser()->guest) {
            $text = str_replace($source, $mismatch, $text);
        } else {
            $text = str_replace($source, $match, $text);
        }
    }

    /**
     * @param string $source
     * @param string $match
     * @param string $mismatch
     * @param string $text
     */
    protected function replaceGuest($source, $match, $mismatch, &$text)
    {
        if ($this->getUser()->guest) {
            $text = str_replace($source, $match, $text);
        } else {
            $text = str_replace($source, $mismatch, $text);
        }
    }

    /**
     * @param string $source
     * @param string $match
     * @param string $mismatch
     * @param string $text
     * @param string $paramValue
     */
    protected function replaceUserid($source, $match, $mismatch, &$text, $paramValue)
    {
        $userIds = array_filter(array_map('intval', explode(',', $paramValue)));
        $user    = JFactory::getUser();
        if (in_array($user->id, $userIds)) {
            $text = str_replace($source, $match, $text);
        } else {
            $text = str_replace($source, $mismatch, $text);
        }
    }

    /**
     * @param string $source
     * @param string $match
     * @param string $mismatch
     * @param string $text
     * @param string $paramValue
     */
    protected function replaceEmail($source, $match, $mismatch, &$text, $paramValue)
    {
        $emailAddresses = array_filter(array_map('trim', explode(',', $paramValue)));
        if (in_array($this->getUser()->email, $emailAddresses)) {
            $text = str_replace($source, $match, $text);
        } else {
            $text = str_replace($source, $mismatch, $text);
        }
    }

    /**
     * @param string $source
     * @param string $match
     * @param string $mismatch
     * @param string $text
     * @param string $paramValue
     */
    protected function replaceUsername($source, $match, $mismatch, &$text, $paramValue)
    {
        $usernames = array_filter(array_map('trim', explode(',', $paramValue)));
        if (in_array($this->getUser()->username, $usernames)) {
            $text = str_replace($source, $match, $text);
        } else {
            $text = str_replace($source, $mismatch, $text);
        }
    }

    /**
     * @param string $source
     * @param string $match
     * @param string $mismatch
     * @param string $text
     * @param string $paramValue
     */
    protected function replaceGroup($source, $match, $mismatch, &$text, $paramValue)
    {
        $groups    = explode(',', strtolower($paramValue));
        $allGroups = $this->getUsergroups();

        if (preg_match('/[a-z]/', $paramValue)) {
            $selectedGroups = array_keys(array_intersect($allGroups, array_filter(array_map('trim', $groups))));

        } else {
            $selectedGroups = array_filter(array_map('intval', $groups));
        }

        if (array_intersect($selectedGroups, $this->getUser()->getAuthorisedGroups())) {
            $text = str_replace($source, $match, $text);
        } else {
            $text = str_replace($source, $mismatch, $text);
        }
    }

    /**
     * @param string $source
     * @param string $match
     * @param string $mismatch
     * @param string $text
     * @param string $paramValue
     */
    protected function replaceAccess($source, $match, $mismatch, &$text, $paramValue)
    {
        $access    = explode(',', strtolower($paramValue));
        $allAccess = $this->getAccessLevels();

        if (preg_match('/[a-z]/', $paramValue)) {
            $selectedAccess = array_keys(array_intersect($allAccess, array_filter(array_map('trim', $access))));

        } else {
            $selectedAccess = array_filter(array_map('intval', $access));
        }

        if (array_intersect($selectedAccess, $this->getUser()->getAuthorisedViewLevels())) {
            $text = str_replace($source, $match, $text);
        } else {
            $text = str_replace($source, $mismatch, $text);
        }
    }

    /**
     * @return JUser
     */
    protected function getUser()
    {
        if ($this->user === null) {
            $this->user = JFactory::getUser();
        }

        return $this->user;
    }

    /**
     * @return string[]
     */
    protected function getUsergroups()
    {
        if ($this->userGroups === null) {
            $db = JFactory::getDbo();

            $db->setQuery(
                $db->getQuery(true)
                    ->select('id,title')
                    ->from('#__usergroups')
            );
            $groups = $db->loadObjectList();

            $this->userGroups = array();
            foreach ($groups as $group) {
                $this->userGroups[$group->id] = strtolower($group->title);
            }
        }

        return $this->userGroups;
    }

    /**
     * @return string[]
     */
    protected function getAccessLevels()
    {
        if ($this->accessLevels === null) {
            $db = JFactory::getDbo();

            $db->setQuery(
                $db->getQuery(true)
                    ->select('id, title')
                    ->from('#__viewlevels')
            );
            $accessLevels = $db->loadObjectList();

            $this->accessLevels = array();
            foreach ($accessLevels as $accessLevel) {
                $this->accessLevels[$accessLevel->id] = strtolower($accessLevel->title);
            }
        }

        return $this->accessLevels;
    }
}
