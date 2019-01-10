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
     */
    public function onContentPrepare($context, $article)
    {
        if (JFactory::getApplication()->isAdmin()) {
            return;
        }

        $codes = $this->find($article->text, array('osshow', 'oshide'));
        foreach ($codes as $code => $items) {
            $show = ($code == 'osshow');
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

        if ($this->params->get('legacy', 1)) {
            $this->processLegacyTags($article->text);
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
            $this->finder = new OstrainingShortcodes();
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

    /**
     * Process tags that were supported by the Dioscury Hider plugin
     *
     * @param $text
     */
    protected function processLegacyTags(&$text)
    {
        // Let's look for the oddballs first
        // flexible user match.
        // NOTE: email matches will not work if email cloaking is enabled
        if (preg_match_all('#{user:(.*?)}(.*?){/user}#s', $text, $matches)) {
            $user = $this->getUser();
            foreach ($matches[0] as $i => $source) {
                $param = $matches[1][$i];
                if ($user->id && ($param == $user->id || $param == $user->email || $param == $user->username)) {
                    $text = str_replace($source, $matches[2][$i], $text);
                } else {
                    $text = str_replace($source, '', $text);
                }
            }
        }

        // csv Group name matches
        if (preg_match_all('#{groups:(.*?)}(.*?){/groups}#s', $text, $matches)) {
            foreach ($matches[0] as $i => $source) {
                $this->replaceGroup($source, $matches[2][$i], '', $text, $matches[1][$i]);
            }
        }

        // Tags in accepted format
        $tags = array(
            'author'    => 'author',
            'editor'    => 'editor',
            'publisher' => 'publisher',
            'manager'   => 'manager',
            'admin'     => 'administrator',
            'super'     => 'super users',
            'reg'       => null,
            'register'  => null,
            'pub'       => null,
            'special'   => null,
            '19'        => null,
            '20'        => null,
            '21'        => null,
            '23'        => null,
            '24'        => null,
            '25'        => null,
        );

        $codes = $this->find($text, array_keys($tags));
        foreach ($codes as $code => $items) {
            foreach ($items as $item) {
                if ((int)$code) {
                    // Show only to users in group ID
                    $this->replaceGroup($item->source, $item->content, '', $text, $code);

                } else {
                    switch ($code) {
                        case 'reg':
                        case 'register':
                            // Show only to registered users
                            $this->replaceRegistered($item->source, $item->content, '', $text);
                            break;

                        case 'pub':
                            // Show only to public/guest users
                            $this->replaceGuest($item->source, $item->content, '', $text);
                            break;

                        case 'special':
                            // Show only to user in 'special' access level
                            $this->replaceAccess($item->source, $item->content, '', $text, $code);
                            break;

                        default:
                            // Show only to named user group
                            $this->replaceGroup($item->source, $item->content, '', $text, $tags[$code]);
                            break;
                    }
                }
            }
        }
    }
}
