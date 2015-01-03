<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\Filter;

use Pi;
use Zend\Filter\AbstractFilter;

/**
 * Link filter
 *
 * Transliterate a URL to clickable link:
 * From `http://url.tld` to `<a href="http://url.tld" title="Click to open">http://url.tld</a>`
 *
 * @see https://bitbucket.org/kwi/urllinker/src
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Linkify extends AbstractFilter
{
    /** @var array */
    protected $options = array(
        // attributes
        'attributes'    => array(),
        // open in new window
        'open_new'      => true,
    );

    /**
     * Filter text
     *
     * @param string $value
     * @return string
     */
    public function filter($value)
    {
        return $this->filterMisd($value);
    }

    /**
     * Get callback for link building
     *
     * @return callable
     */
    protected function linkCallback()
    {
        $attributes = array();
        if (!empty($this->options['attributes'])) {
            $attributes = $this->options['attributes'];
        }
        if (!isset($attributes['target']) && !empty($this->options['open_new'])) {
            $attributes['target'] = '_blank';
        }
        if (!isset($attributes['title'])) {
            $attributes['title'] = __('Click to open');
        }
        $helper = Pi::service('view')->getHelper('html_link');

        $callback = function ($href, $title) use ($helper, $attributes) {
            return $helper($href, $title, $attributes);
        };

        return $callback;
    }

    /**
     * Filter text via solution from Stack Overflow
     *
     * @param string $value
     *
     * @return string
     * @see http://stackoverflow.com/questions/5341168/best-way-to-make-links-clickable-in-block-of-text
     */
    protected function filterSo($value)
    {
        $callback = $this->linkCallback();

        //$pattern = '!((((f|ht)tp(s)?:)?//|www\.)[-a-zA-Zа-яА-Я()0-9@:%_+.~#?&;//=]+)!i';
        $pattern = '!(^|\s)((((f|ht)tps?:)?//|www\.)[-a-zA-Zа-яА-Я()0-9@:%_+.~#?&;//=]+)!i';
        $value = preg_replace_callback($pattern, function ($matches) use ($callback) {
            $url = $matches[2];
            if ('www.' == $matches[3]) {
                $href = 'http://' . $url;
            } else {
                $href = $url;
            }
            $link = $matches[1] . $callback($href, $url);

            return $link;
        }, $value);

        return $value;
    }

    /**
     * Filter text via solution from Chris Wilkinson
     *
     * @param string $value
     *
     * @return string
     * @see https://github.com/misd-service-development/php-linkify
     */
    protected function filterMisd($value)
    {
        $callback = $this->linkCallback();
        $pattern = '~(?xi)
              (?:
                ((ht|f)tps?://)                    # scheme://
                |                                  #   or
                www\d{0,3}\.                       # "www.", "www1.", "www2." ... "www999."
                |                                  #   or
                www\-                              # "www-"
                |                                  #   or
                [a-z0-9.\-]+\.[a-z]{2,4}(?=/)      # looks like domain name followed by a slash
              )
              (?:                                  # Zero or more:
                [^\s()<>]+                         # Run of non-space, non-()<>
                |                                  #   or
                \(([^\s()<>]+|(\([^\s()<>]+\)))*\) # balanced parens, up to 2 levels
              )*
              (?:                                  # End with:
                \(([^\s()<>]+|(\([^\s()<>]+\)))*\) # balanced parens, up to 2 levels
                |                                  #   or
                [^\s`!\-()\[\]{};:\'".,<>?«»“”‘’]  # not a space or one of these punct chars
              )
        ~';
        $func = function ($match) use ($callback) {
            $caption = $match[0];
            $pattern = "~^(ht|f)tps?://~";
            if (0 === preg_match($pattern, $match[0])) {
                $match[0] = 'http://' . $match[0];
            }
            return $callback($match[0], $caption);
        };

        return preg_replace_callback($pattern, $func, $value);
    }
}
