<?php
/**
 * FluentDOM\Element extends PHPs DOMDocument class. It adds some generic namespace handling on
 * the document level and registers extended Node classes for convenience.
 *
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @copyright Copyright (c) 2009-2014 Bastian Feder, Thomas Weinert
 */

namespace FluentDOM {

  /**
   * FluentDOM\Element extends PHPs DOMDocument class. It adds some generic namespace handling on
   * the document level and registers extended Node classes for convenience.
   */
  class Element extends \DOMElement {

    /**
     * Append an child element
     *
     * @param string $name
     * @param string $content
     * @param array $attributes
     * @return \DOMElement
     */
    public function appendElement($name, $content = '', array $attributes = NULL) {
      $this->appendChild(
        $node = $this->ownerDocument->createElement($name, $content, $attributes)
      );
      return $node;
    }

    /**
     * Set an attribute on an element
     *
     * @param string $name
     * @param string $content
     * @param array $attributes
     * @return \DOMElement
     */
    public function setAttribute($name, $value) {
      if ($this->ownerDocument instanceOf Document &&
          FALSE !== ($position = strpos($name, ':'))) {
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        return parent::setAttributeNS(
          $this->ownerDocument->getNamespace(substr($name, 0, $position)),
          $name,
          $value
        );
      } else {
        return parent::setAttribute(
          $name,
          $value
        );
      }
    }
  }
}