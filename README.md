[![Joomlashack](https://www.joomlashack.com/images/logo_circle_small.png)](https://www.joomlashack.com)

ShackHider
==========

## About

This content plugin allows you show or hide content based on user group, access level, user, login status.
The basic shortcodes are {jsshow} and {jshide} with parameters.

## Examples
```
{jsshow registered}show this content to logged in users{/jsshow}

{jsshow guest}show this content only to users who are not logged in{/jsshow}

{jshide userid="501,650"}Hide this from user IDs 501 and 650{\jshide}

{jshide email="help@joomlashack.com"}Hide this from logged in user with this email address{/jshide}

{jsshow username="john,paul,george,ringo"}Show this content only to logged in users with these usernames{/jsshow}

{jsshow group="manager,administrator"}Show to the selected named user groups{/jsshow}

{jsshow group="5,6,10"}Selected user group IDs. NOTE: you cannot mix IDs and names{/jsshow}

{jsshow access="Super Users"}Show this only to super users{/jsshow}

{jsshow access="3,6"}Show this only to users based on access level ID. DO NOT mix acl ids and names{/jsshow}
```

## Requirements

Joomla 3.x

## License

[GNU General Public License v2 or later](http://www.gnu.org/copyleft/gpl.html)

