<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\Markup\Renderer;

use Traversable;
use Pi\Markup\Parser\AbstractParser;
use Zend\Stdlib\ArrayUtils;

/**
 * Abstract render for markup
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
abstract class AbstractRenderer
{
    /**
     * Parser
     *
     * @var AbstractParser
     */
    protected $parser;

    /**
     * Encoding
     *
     * @var string
     */
    //protected $encoding = 'UTF-8';

    /** @var array Options */
    protected $options = array();

    /**
     * Constructor
     *
     * @param  array|Traversable $options
     */
    public function __construct($options = array())
    {
        $this->setOptions($options);
    }

    /**
     * Set options
     *
     * @param array|\Traversable $options
     * @return $this
     */
    public function setOptions($options)
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }

        if (isset($options['parser'])) {
            $this->setParser($options['parser']);
            unset($options['parser']);
        }

        foreach ($options as $key => $val) {
            $this->options[$key] = $val;
        }

        return $this;
    }

    /**
     * Set the parser
     *
     * @param AbstractParser|string $parser
     * @return $this
     */
    public function setParser(AbstractParser $parser)
    {
        $this->parser = $parser;

        return $this;
    }

    /**
     * Get the parser
     *
     * @return AbstractParser|string
     */
    public function getParser()
    {
        return $this->parser;
    }

    /**
     * Render function
     *
     * @param string $content
     * @return string
     */
    public function render($content)
    {
        $content = $this->getParser()->parse($content);
        $content = $this->renderContent($content);

        return $content;
    }

    /**
     * Parse content
     *
     * @param string $content
     * @return string
     */
    abstract protected function renderContent($content);
}
