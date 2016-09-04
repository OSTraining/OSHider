<?php
/**
 * @package   OSHider
 * @contact   www.alledia.com, support@alledia.com
 * @copyright 2015 Alledia.com, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @fork      Most of the code is forked from http://dioscouri.com/joomla-extensions/non-commercial-extensions/hider
 */

use Alledia\Framework\Joomla\Extension\AbstractPlugin;

defined('_JEXEC') or die();

require_once 'include.php';

/**
 * OSHider Content Plugin
 *
 */
class PlgContentOSHider extends AbstractPlugin
{
    /**
     * @var string
     */
    protected $namespace = 'OSHider';

    protected $autoloadLanguage = true;

    /**
     * @var OstrainingShortcodes
     */
    protected $shortcodes = null;

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
     */
    public function onContentPrepare($context, $article)
    {
        if (JFactory::getApplication()->isAdmin()) {
            return;
        }

        if ($this->shortcodes === null) {
            $this->shortcodes = new OstrainingShortcodes();
        }

        $codes = $this->shortcodes->find($article->text, array('oshide', 'osshow'));
        foreach ($codes as $shortcode => $items) {
            switch ($shortcode) {
                case 'osshow':
                case 'oshide':
                    $show = $shortcode == 'osshow';
                    foreach ($items as $item) {
                        foreach ($item->params as $param => $value) {
                            $method = 'match' . ucfirst(strtolower($param));
                            if (method_exists($this, $method)) {
                                $match    = $show ? $item->content : '';
                                $mismatch = $show ? '' : $item->content;
                                if ($this->$method($value)) {
                                    $article->text = str_replace($item->source, $match, $article->text);
                                } else {
                                    $article->text = str_replace($item->source, $mismatch, $article->text);
                                }
                            }
                        }
                    }
                    break;
            }
        }
    }

    /**
     * @return bool
     */
    protected function matchRegistered()
    {
        return !$this->getUser()->guest;
    }

    /**
     * @return bool
     */
    protected function matchGuest()
    {
        return (bool)$this->getUser()->guest;
    }

    /**
     * @param string $paramValue
     *
     * @return bool
     */
    protected function matchUserid($paramValue)
    {
        if ($userIds = array_filter(array_map('intval', explode(',', $paramValue)))) {
            $user = JFactory::getUser();
            return in_array($user->id, $userIds);
        }

        return false;
    }

    /**
     * @param string $paramValue
     *
     * @return bool
     */
    protected function matchEmail($paramValue)
    {
        if ($emailAddresses = array_filter(array_map('trim', explode(',', $paramValue)))) {
            return in_array($this->getUser()->email, $emailAddresses);
        }

        return false;
    }

    /**
     * @param string $paramValue
     *
     * @return bool
     */
    protected function matchUsername($paramValue)
    {
        if ($usernames = array_filter(array_map('trim', explode(',', $paramValue)))) {
            return in_array($this->getUser()->username, $usernames);
        }

        return false;
    }

    /**
     * @param string $paramValue
     *
     * @return bool
     */
    protected function matchGroup($paramValue)
    {
        $groups = explode(',', strtolower($paramValue));
        $allGroups      = $this->getUsergroups();

        if (preg_match('/[a-z]/', $paramValue)) {
            $selectedGroups = array_keys(array_intersect($allGroups, array_filter(array_map('trim', $groups))));

        } else {
            $selectedGroups = array_filter(array_map('intval', $groups));
        }

        return (bool)array_intersect($selectedGroups, $this->getUser()->getAuthorisedGroups());
    }

    /**
     * @param string $paramValue
     *
     * @return bool
     */
    protected function matchAccess($paramValue)
    {
        $access = explode(',', strtolower($paramValue));
        $allAccess = $this->getAccessLevels();

        if (preg_match('/[a-z]/', $paramValue)) {
            $selectedAccess = array_keys(array_intersect($allAccess, array_filter(array_map('trim', $allAccess))));

        } else {
            $selectedAccess = array_filter(array_map('intval', $access));
        }

        return (bool)array_intersect($selectedAccess, $this->getUser()->getAuthorisedViewLevels());
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

            $db->setQuery('Select id,title From #__usergroups');
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

            $db->setQuery('Select id, title From #__viewlevels');
            $accessLevels = $db->loadObjectList();

            $this->accessLevels = array();
            foreach ($accessLevels as $accessLevel) {
                $this->accessLevels[$accessLevel->id] = strtolower($accessLevel->title);
            }
        }

        return $this->accessLevels;
    }
}
