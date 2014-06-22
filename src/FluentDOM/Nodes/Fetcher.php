<?php
/**
 * Fetches dom nodes for the current context.
 *
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @copyright Copyright (c) 2009-2014 Bastian Feder, Thomas Weinert
 */

namespace FluentDOM\Nodes {

  use FluentDOM\Nodes;

  /*
   * Fetches dom nodes for the current context.
   */
  class Fetcher {

    /** reverse the order of the fetched nodes */
    const REVERSE = 1;
    /** include the node at the stop */
    const INCLUDE_STOP = 2;
    /** unique and sort nodes */
    const UNIQUE = 4;
    /** ignore the current context (use the document context) */
    const IGNORE_CONTEXT = 8;

    /**
     * @var Nodes
     */
    private $_nodes = NULL;

    /**
     * @param Nodes $nodes
     */
    public function __construct(Nodes $nodes) {
      $this->_nodes = $nodes;
    }

    /**
     * @param string $expression
     * @param callable $filter
     * @param callable $stopAt
     * @param int $options
     * @throws \InvalidArgumentException
     * @return array
     */
    public function fetch($expression, $filter = NULL, $stopAt = NULL, $options = 0) {
      if (!is_string($expression) || empty($expression)) {
        throw new \InvalidArgumentException(
          'Invalid selector/expression.'
        );
      }
      $nodes = array();
      if (($options & self::IGNORE_CONTEXT) == self::IGNORE_CONTEXT) {
        $nodes = $this->fetchFor(
          $expression, NULL, $filter, $stopAt, $options
        );
      } else {
        foreach ($this->_nodes->toArray() as $context) {
          $nodes = array_merge(
            $nodes,
            $this->fetchFor(
              $expression, $context, $filter, $stopAt, $options
            )
          );
        }
      }
      if (($options & self::UNIQUE) == self::UNIQUE) {
        $nodes = $this->_nodes->unique($nodes);
      }
      return $nodes;
    }

    /**
     * Fetch the nodes for the provided context node. If $context
     * ist NULL the document context is used. Use $filter and
     * $stopAt to reduce the returned nodes.
     *
     * @throws \InvalidArgumentException
     * @param string $expression
     * @param \DOMNode $context
     * @param callable $filter
     * @param callable $stopAt
     * @param int $options
     * @return array|bool|\DOMNodeList|float|string
     */
    private function fetchFor(
      $expression,
      \DOMNode $context = NULL,
      callable $filter = NULL,
      callable $stopAt = NULL,
      $options = 0
    ) {
      $nodes = $this->fetchNodes($expression, $context, $options);
      if ($filter || $stopAt) {
        $result = array();
        foreach ($nodes as $index => $node) {
          if (isset($stopAt) && $stopAt($node, $index)) {
            if (
              ($options & self::INCLUDE_STOP) == self::INCLUDE_STOP &&
              (empty($filter) || $filter($node, $index))
            ) {
              $result[] = $node;
            }
            break;
          }
          if (empty($filter) || $filter($node, $index)) {
            $result[] = $node;
          }
        }
        return $result;
      } else {
        return $nodes;
      }
    }

    /**
     * Fetch the nodes for the provided context node. If $context
     * ist NULL the document context is used.
     *
     * @throws \InvalidArgumentException
     * @param string $expression
     * @param \DOMNode $context
     * @param int $options
     * @return array|bool|\DOMNodeList|float|string
     */
    private function fetchNodes($expression, $context, $options) {
      $nodes = $this->_nodes->xpath()->evaluate($expression, $context);
      if (!$nodes instanceof \DOMNodeList) {
        throw new \InvalidArgumentException(
          'Given selector/expression did not return a node list.'
        );
      }
      $nodes = iterator_to_array($nodes);
      if (($options & self::REVERSE) == self::REVERSE) {
        return array_reverse($nodes, FALSE);
      }
      return $nodes;
    }
  }
}