<?php
/**
 * @package   Shortcode utility
 * @contact   www.ostraining.com, support@ostraining.com
 * @copyright 2016 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

/**
 * Class JoomlashackShortcodes
 *
 * Generic processor for custom short codes.
 *
 * Short codes can be in any combination of the following forms.
 * How they are replaced is up to individual processing methods.
 *
 * {code}
 * {code param}
 * {code param="value"}
 * {code}content{/code}
 *
 * Usage:
 *
 * $shortcodes = new JoomlashackShortcodes()
 * $items = $shortcodes->find($string, array('yourshortcode'));
 *
 * $items is an associative array keyed on the shortcode matched with each item object returned as:
 *
 * @var string   $source  The source text that matches the entire shortcode
 * @var string[] $params  An associative array of parameters and their values
 * @var string   $content The text between opening and closing tag or null for unclosed tag
 *
 */
class JoomlashackShortcodes
{
    /**
     * @param string   $text       The text to search
     * @param string[] $shortCodes Array of shortcode names to look for
     *
     * @return object[]
     */
    public function find($text, array $shortCodes)
    {
        $items = array();

        $regexList = array();
        foreach ($shortCodes as $shortCode) {
            $regexList[$shortCode] = "#{{$shortCode}\s*([^}]*)}#ms";
        }

        foreach ($regexList as $shortCode => $regex) {
            if (preg_match_all($regex, $text, $matches)) {
                $closeTag = "{/{$shortCode}}";
                $nextTag  = "{{$shortCode}";

                $segment = $text;
                foreach ($matches[0] as $i => $source) {
                    $params = $this->parseParams($matches[1][$i]);

                    // Look for closed and open versions of the shortcode
                    $segment = substr($segment, strpos($segment, $source) + strlen($source));
                    $next    = strpos($segment, $nextTag) ?: strlen($segment);
                    $close   = strpos($segment, $closeTag) ?: strlen($segment);

                    if ($close < $next) {
                        // Closed tag with content
                        $content = substr($segment, 0, $close);
                        $source .= $content . $closeTag;

                    } else {
                        // Open tag has no content
                        $content = '';
                    }

                    if (empty($items[$shortCode])) {
                        $items[$shortCode] = array();
                    }
                    $items[$shortCode][] = (object)array(
                        'source'  => $source,
                        'params'  => $params,
                        'content' => $content
                    );
                }
            }
        }

        return $items;
    }

    /**
     * Parse and translate recognized inline parameters from a shortcode.
     *
     * Return array is in the form:
     * array(name => value[, ...])
     *
     * @param $params string
     *
     * @return array
     */
    protected function parseParams($params)
    {
        $regex = '/(?:(?:(\S*)\s?=\s?["\']([^\'"]*)[\'"])|(\S+))/';

        $parsed = array();
        if (preg_match_all($regex, $params, $vars)) {
            foreach ($vars[0] as $j => $var) {
                if ($var) {
                    if ($vars[3][$j]) {
                        $param          = $vars[3][$j];
                        $parsed[$param] = true;
                    } else {
                        $param          = $vars[1][$j];
                        $parsed[$param] = $vars[2][$j];
                    }
                }
            }
        }

        return $parsed;
    }
}
