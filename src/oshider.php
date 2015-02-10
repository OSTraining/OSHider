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

    /**
     * @param JEventDispatcher $subject
     * @param array            $config
     */
    public function __construct($subject, $config = array())
    {
        parent::__construct($subject, $config);

        $this->loadLanguage();

    }

    /**
     *
     * @param string  $context
     * @param object  $article
     * @param object  $params
     * @param integer $page
     *
     * @return boolean
     */
    public function onContentPrepare($context, $article, $params, $page = 0)
    {
        $success = true;

        // Added for registered vs public
        $regex1 = "#{reg}(.*?){/reg}#s";
        $regex2 = "#{pub}(.*?){/pub}#s";

        // added for user replacement
        $regex3 = "#{user:(.*?)}(.*?){/user}#s";

        // added to support 1/more groups, in CSV format of lowercase group names
        $regex4 = "#{groups:(.*?)}(.*?){/groups}#s";

        // perform the replacement for _reg
        $article->text = preg_replace_callback($regex1, array($this, 'reg'), $article->text);

        // perform the replacement for _pub
        $article->text = preg_replace_callback($regex2, array($this, 'pub'), $article->text);

        // perform the replacement for user
        $article->text = preg_replace_callback($regex3, array($this, 'user'), $article->text);

        // perform the replacement for groups
        $article->text = preg_replace_callback($regex4, array($this, 'groups'), $article->text);

        return $success;
    }

    /**
     *
     * @param array $matches
     *
     * @return string
     */
    protected function reg($matches)
    {
        $user   = JFactory::getUser();
        $return = '';

        if (!empty($user->id)) {
            $return = $matches[1];
        }

        return $return;
    }

    /**
     *
     * @param array $matches
     *
     * @return string
     */
    protected function pub($matches)
    {

        $user   = JFactory::getUser();
        $return = $matches[1];

        if (!empty($user->id)) {
            $return = '';
        }

        return $return;
    }

    /**
     *
     * @param array $matches
     *
     * @return string
     */
    protected function user($matches)
    {

        $user      = JFactory::getUser();
        $userid    = $user->get('id');
        $username  = $user->get('username');
        $useremail = $user->get('email');

        $match = $matches[1];

        $return = '';

        if (($match == $username) || ($match == $useremail) || ($match == strval($userid))) {
            $return = $matches[2];
        }

        return $return;
    }

    /**
     *
     * @param array $matches
     *
     * @return string
     */
    protected function groups($matches)
    {

        $match = $matches[1];
        // explode $match by ,
        $allowed_groups = explode(',', $match);
        foreach ($allowed_groups as $key => $allowed_group) {
            $allowed_groups[$key] = strtolower(trim($allowed_group));
            if (empty($allowed_groups[$key])) {
                unset($allowed_groups[$key]);
            }
        }

        $user_groups = $this->getUserGroups();

        $return = '';
        // if the user is in any of the groups in $allowed_groups, grant access to $match[2]
        foreach ($allowed_groups as $allowed_group) {
            if (in_array($allowed_group, $user_groups->group_ids) ||
                in_array($allowed_group, $user_groups->group_names)
            ) {
                $return = $matches[2];
                return $return;
            }
        }

        return $return;
    }

    /**
     * @return object
     */
    protected function getUserGroups()
    {
        // get all of the current user's groups
        $user        = JFactory::getUser();
        $user_groups = array();

        $authorized_groups = $user->getAuthorisedGroups();

        foreach ($authorized_groups as $authorized_group) {
            $table = JTable::getInstance('Usergroup', 'JTable');
            $table->load($authorized_group);
            $user_groups[$authorized_group] = strtolower($table->title);
        }

        $return = (object)array(
            'group_names' => $user_groups,
            'group_ids'   => $authorized_groups
        );

        return $return;
    }
}
