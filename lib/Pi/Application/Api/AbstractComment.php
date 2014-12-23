<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\Application\Api;

use Zend\Mvc\Router\RouteMatch;

/**
 * Abstract class for module comment callback
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
abstract class AbstractComment extends AbstractApi
{
    /** @var string Table name to fetch target meta data */
    protected $table;

    /** @var array Columns to fetch: table column => meta key */
    protected $meta = array(
        'id'        => 'id',
        'title'     => 'title',
        'time'      => 'time',
        'uid'       => 'uid',
    );

    /**
     * Get target data of item(s)
     *
     * - Fetch data of an item:
     *   - title
     *   - time
     *   - uid
     *   - url
     *
     * @param int|int[] $id
     *
     * @throws \Exception
     * @return array|bool
     */
    public function get($id)
    {
        $result = array();
        if (!$this->table) {
            return $result;
        }

        $items = (array) $id;
        $where = $this->canonizeConditions(array('id' => $items));
        $model = Pi::model($this->table, $this->module);
        $rowset = $model->select($where);
        foreach ($rowset as $row) {
            $item = $row->toArray();
            $item['url'] = $this->buildUrl($item);
            $result[] = $this->canonizeResult($item);
        }
        if (is_scalar($id)) {
            $result = array_pop($result);
        }

        return $result;
    }

    /**
     * Locate source id via route
     *
     * @param RouteMatch|array $params
     *
     * @throws \Exception
     * @return mixed|bool
     */
    public function locate($params = null)
    {
        throw new \Exception('Method is not defined.');
    }

    /**
     * Build URL of an item
     *
     * @param array $item
     *
     * @return string
     * @throws \Exception
     */
    protected function buildUrl(array $item)
    {
        throw new \Exception('Method is not defined.');
    }

    /**
     * Canonize result against meta
     *
     * @param array $data
     *
     * @return array
     */
    protected function canonizeResult(array $data)
    {
        $meta   = $this->meta;
        $result = array();
        foreach ($data as $var => $value) {
            if (isset($meta[$var])) {
                $result[$meta[$var]] = $value;
            }
        }

        return $result;
    }

    /**
     * Canonize conditions against meta
     *
     * @param array $conditions
     *
     * @return array
     */
    protected function canonizeConditions(array $conditions)
    {
        $meta   = array_flip($this->meta);
        $result = array();
        foreach ($conditions as $var => $condition) {
            if (isset($meta[$var])) {
                $result[$meta[$var]] = $condition;
            }
        }

        return $result;
    }
}
