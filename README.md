[![Alledia](https://www.alledia.com/images/logo_circle_small.png)](https://www.alledia.com)

OSHider
===========

## About

This content plugin allows you show or hide content based on user group, access level, user, login status.
The basic shortcodes are {osshow} and {oshide} with parameters.

## Examples

{osshow registered}show this content to logged in users{/osshow}

{osshow guest}show this content only to users who are not logged in{/osshow}

{oshide userid="501,650"}Hide this from user IDs 501 and 650{\oshide}

{oshide email="support@ostraining.com"}Hide this from logged in user with this email address{/oshide}

{osshow username="john,paul,george,ringo"}Show this content only to logged in users with these usernames{/osshow}

{osshow group="manager,administrator"}Show to the selected named user groups{/osshow}

{osshow group="5,6,10"}Selected user group IDs. NOTE: you cannot mix IDs and names{/osshow}

{osshow access="Super Users"}Show this only to super users{/osshow}

{osshow access="3,6"}Show this only to users based on access level ID. DO NOT mix acl ids and names{/osshow}

## Legacy Support
OSHider optionally supports the shortcodes from the Dioscuri plugin that was the inspiration for OSHider.
These shortcodes will hide enclosed content as follows:

* Login status
  * {reg}{/reg}
  * {register}{/register} [Not originally from Hider]
  * {pub}{/pub}
  
* Named user groups
  * {author}{/author}
  * {editor}{/editor}
  * {publisher}{/publisher}
  * {manager}{/manager}
  * {admin}{/admin}
  * {super}{/super}
  
* Numbered User groups
  * {19}{/19}
  * {20}{/20}
  * {21}{/21}
  * {23}{/23}
  * {24}{/24}
  * {25}{/25}

* Access Level
  * {special}{/special}
  
## Requirements

Joomla 3.x

## License

[GNU General Public License v2 or later](http://www.gnu.org/copyleft/gpl.html)

