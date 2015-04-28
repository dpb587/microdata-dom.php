<?php

namespace MicrodataDOM;

/**
 * This extends the builtin DOMElement and adds several read-only properties describing microdata items:
 *
 *  * `itemScope` (boolean) - true if the element has the `itemscope` attribute.
 *  * `itemType` (string[]|null) - a list of the vocabulary types from the `itemtype` attribute.
 *  * `itemId` (string|null) - the ID specified by the `itemid` attribute, when available.
 *  * `itemProp` (string[]|null) - a list of property names specified by the `itemprop` attribute, when available.
 *  * `itemRef` (string[]|null) - a list of external identifiers providing properties specified by the `itemprop` attribute, when available.
 *  * `itemValue` (string|DOMElement|null) - the value of the property, when available. The actual value depends on the element.
 *  * `properties` (HTMLPropertiesCollection) - a list of properties for the given element.
 */
class DOMElement extends \DOMElement
{
    /**
     * @var mixed[string]
     */
    protected $microdataCache;

    /**
     * Retrieve a read-only property about the microdata.
     *
     * @param string $name
     * @return string|string[]|\MicrodataDOM\DOMElement|\MicrodataDOM\HTMLPropertiesCollection
     * @throws \LogicException
     */
    public function __get($name)
    {
        if (isset($this->microdataCache[$name])) {
            return $this->microdataCache[$name];
        }

        switch ($name) {
            case 'itemScope':
                $attribute = $this->attributes->getNamedItem('itemscope');
                $value = (Boolean) $attribute;

                break;
            case 'itemType':
                if (!$this->itemScope) {
                    return null;
                }

                $attribute = $this->attributes->getNamedItem('itemtype');
                $value = $attribute ? preg_split('#\s+#', trim($attribute->nodeValue)) : null;

                break;
            case 'itemId':
                if (!$this->itemScope) {
                    return null;
                }

                $attribute = $this->attributes->getNamedItem('itemid');
                $value = $attribute ? $attribute->nodeValue : null;

                break;
            case 'itemProp':
                $attribute = $this->attributes->getNamedItem('itemprop');
                $value = $attribute ? preg_split('#\s+#', trim($attribute->nodeValue)) : null;

                break;
            case 'itemRef':
                if (!$this->itemScope) {
                    return null;
                }

                $attribute = $this->attributes->getNamedItem('itemref');
                $value = $attribute ? preg_split('#\s+#', trim($attribute->nodeValue)) : [];

                break;
            case 'itemValue':
                if (null === $this->itemProp) {
                    return null;
                } elseif ($this->itemScope) {
                    return $this;
                }

                switch (strtolower($this->nodeName)) {
                    case 'meta':
                        $attribute = $this->attributes->getNamedItem('content');
                        $value = $attribute ? $attribute->nodeValue : '';

                        break;
                    case 'audio':
                    case 'embed':
                    case 'iframe':
                    case 'img':
                    case 'source':
                    case 'track':
                    case 'video':
                        $attribute = $this->attributes->getNamedItem('src');
                        $value = $attribute ? $this->ownerDocument->makeAbsoluteUrl($attribute->nodeValue) : null;

                        break;
                    case 'a':
                    case 'area':
                    case 'link':
                        $attribute = $this->attributes->getNamedItem('href');
                        $value = $attribute ? $this->ownerDocument->makeAbsoluteUrl($attribute->nodeValue) : null;

                        break;
                    case 'object':
                        $attribute = $this->attributes->getNamedItem('data');
                        $value = $attribute ? $this->ownerDocument->makeAbsoluteUrl($attribute->nodeValue) : null;

                        break;
                    case 'data':
                        $attribute = $this->attributes->getNamedItem('value');
                        $value = $attribute ? $attribute->nodeValue : null;

                        break;
                    case 'meter':
                        $attribute = $this->attributes->getNamedItem('value');
                        $value = $attribute ? $attribute->nodeValue : null;

                        break;
                    case 'time':
                        $attribute = $this->attributes->getNamedItem('datetime');
                        $value = $attribute ? $attribute->nodeValue : null;

                        break;
                    default:
                        $attribute = $this->attributes->getNamedItem('content');
                        $value = $attribute ? $attribute->nodeValue : $this->textContent;
                }

                break;
            case 'properties':
                if ($this->itemScope) {
                    return new HTMLPropertiesCollection($this);
                }

                $value = null;

                break;
            default:
                throw new \LogicException('Invalid property: ' . $name);
        }

        return $this->microdataCache[$name] = $value;
    }

    public function toArray()
    {
        $result = [];

        if ($this->itemId) {
            $result['id'] = $this->itemId;
        }

        if ($this->itemType) {
            $result['type'] = $this->itemType;
        }

        $result['properties'] = $this->properties->toArray();

        return $result;
    }

    public function xpathQuery($query)
    {
        return $this->ownerDocument->xpathQuery('.' . $query, $this);
    }
}
