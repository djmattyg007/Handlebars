<?php //-->
declare(strict_types=1);
/**
 * This file is part of the Eden PHP Library.
 * (c) 2014-2016 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Eden\Handlebars;

/**
 * Data needs to manage parent and children respectively
 *
 * @vendor   Eden
 * @package  handlebars
 * @author   Christian Blanquera <cblanquera@openovate.com>
 * @standard PSR-2
 */
class Data extends Base
{
    /**
     * @var array
     */
    protected $tree = array();

    /**
     * Construct - load data
     *
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        $this->tree[] = $data;
    }

    /**
     * This explains the importance of parent/child
     * Based on the given path we need to return the
     * correct results
     *
     * @param string $path Dot notated path
     * @param int $i Current context
     * @return mixed
     */
    public function find(string $path, int $i = 0)
    {
        if ($i >= count($this->tree)) {
            return null;
        }

        $current = $this->tree[$i];

        //if they are asking for the parent
        if (strpos($path, '../') === 0) {
            return $this->find(substr($path, 3), $i + 1);
        }

        if (strpos($path, './') === 0) {
            return $this->find(substr($path, 2), $i);
        }

        //separate by .
        $path = explode('.', $path);
        $last = count($path) - 1;

        foreach ($path as $i => $node) {
            //is it the last ?
            if ($i === $last) {
                //does it exist?
                if (isset($current[$node])) {
                    return $current[$node];
                }

                //is it length ?
                if ($node === 'length') {
                    //is it a string?
                    if (is_string($current)) {
                        return strlen($current);
                    }

                    //is it an array?
                    if (is_array($current) || $current instanceof \Countable) {
                        return count($current);
                    }

                    //we cant count it, so it's 0
                    return 0;
                }
            }

            //we are not at the last node...

            //does the node exist and is it an array ?
            if (isset($current[$node]) && is_array($current[$node])) {
                //great we can continue
                $current = $current[$node];
                continue;
            }

            //if it exists and we are just getting the length
            if (isset($current[$node]) && $path[$i + 1] === 'length' && ($i + 1) === $last) {
                //let it continue
                continue;
            }

            //if we are here, then there maybe a node in current,
            //but there's still more nodes to process
            //either way it cannot be what we are searching for
            break;
        }

        return null;
    }

    /**
     * Returns whatever the current context is
     *
     * @return array
     */
    public function get()
    {
        return $this->tree[0];
    }

    /**
     * Pushes a new context in the tree
     *
     * @param array $data
     * @return Data
     */
    public function push(array $data)
    {
        array_unshift($this->tree, $data);
        return $this;
    }

    /**
     * Pops the last context
     *
     * @return Data
     */
    public function pop()
    {
        array_shift($this->tree);
        return $this;
    }
}
