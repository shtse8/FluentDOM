<?php

namespace FluentDOM\Loader\Supports {

  use FluentDOM\Document;
  use FluentDOM\Exceptions\JsonError;
  use FluentDOM\Loader\Supports;

  trait Json {

    use Supports;

    /**
     * Load the json string into an DOMDocument
     *
     * @param mixed $source
     * @param string $contentType
     * @throws \UnexpectedValueException
     * @return Document|NULL
     */
    public function load($source, $contentType) {
      if (FALSE !== ($json = $this->getJson($source, $contentType))) {
        $dom = new Document('1.0', 'UTF-8');
        $this->transferTo($dom, $json);
        return $dom;
      }
      return NULL;
    }

    /**
     * @param mixed $source
     * @param string $contentType
     * @throws JsonError
     * @return mixed
     */
    private function getJson($source, $contentType)  {
      if ($this->supports($contentType)) {
        if (is_string($source)) {
          $json = FALSE;
          if (!$this->startsWith($source, '{[')) {
            $source = file_get_contents($source);
          }
          if ($this->startsWith($source, '{[')) {
            $json = json_decode($source);
            if (!($json || is_array($json))) {
              throw new JsonError(
                is_callable('json_last_error') ? json_last_error() : -1
              );
            }
          }
        } else {
          $json = $source;
        }
        return ($json || is_array($json)) ? $json : FALSE;
      }
      return FALSE;
    }

    /**
     * @param mixed $value
     * @return string
     */
    private function getValueAsString($value) {
      if (is_bool($value)) {
        return $value ? 'true' : 'false';
      } else {
        return (string)$value;
      }
    }
  }
}