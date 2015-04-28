<?php

namespace MicrodataDOM;

class DOMDocument extends \DOMDocument
{
    /**
     * @var DOMXPath
     */
    protected $xpath;

    /**
     * {@inheritDoc}
     */
    public function __construct($version = null, $encoding = null)
    {
        parent::__construct($version, $encoding);

        $this->registerNodeClass('DOMDocument', __CLASS__);
        $this->registerNodeClass('DOMElement', 'MicrodataDOM\\DOMElement');
    }

    /**
     * Return all, top-level microdata items whose types include all the types specified, or, if no types are specified,
     * all, top-level microdata items.
     *
     * @param array|string $typeNames
     * @return \DOMNodeList
     * @throws \InvalidArgumentException
     */
    public function getItems($typeNames = [])
    {
        if (is_array($typeNames)) {
            $types = $typeNames;
        } elseif (is_string($typeNames)) {
            $types = preg_split('/\s+/', trim($typeNames));
        } else {
            throw new \InvalidArgumentException('Expected array or string for argument 1');
        }

        $xpathQuery = implode(
            ' and ',
            array_map(
                function ($type) {
                    return 'contains(concat(" ", normalize-space(@itemtype), " "), " ' . htmlspecialchars(trim($type)) . ' ")';
                },
                $types
            )
        );

        return $this->xpathQuery('//*[@itemscope and not(@itemprop)' . ($xpathQuery ? ' and (' . $xpathQuery . ')' : '') . ']');
    }

    /**
     * Return an array of all microdata items in a Microdata JSON structure.
     *
     * @see http://www.w3.org/TR/microdata/#json
     * @return mixed[string]
     */
    public function toArray()
    {
        $results = [];

        foreach ($this->getItems() as $item) {
            $results['items'][] = $item->toArray();
        }

        return $results;
    }

    /**
     * Execute an XPath query against the document and, optionally, within a specific node.
     *
     * @internal
     * @param string $expression
     * @param \DOMNode $contextnode
     * @return type
     */
    public function xpathQuery($expression, \DOMNode $contextnode = null)
    {
        if (null === $this->xpath) {
            $this->xpath = new \DOMXpath($this);
        }

        return $this->xpath->query($expression, $contextnode);
    }

    /**
     * Attempt to convert possibly-relative URLs into an absolute URL based on the document context.
     *
     * @internal
     * @param string $url
     * @return string
     */
    public function makeAbsoluteUrl($url)
    {
        if (null !== parse_url($url, PHP_URL_SCHEME)) {
            return $url;
        } elseif (null === $this->documentURI) {
            return $url;
        }

        $base = parse_url($this->documentURI);

        if ('//' == substr($url, 0, 2)) {
            return $base['scheme'] . ':' . $url;
        }

        $basepartial = $base['scheme'] . '://' . $base['host'] . (!empty($base['port']) ? (':' . $base['port']) : '');

        if ('' == $url) {
            return $this->documentURI;
        } elseif ('/' == $url[0]) {
            return $basepartial . $url;
        } elseif ('#' == $url[0]) {
            return $basepartial . $base['path'] . (!empty($base['query']) ? ('?' . $base['query']) : '') . $url;
        } elseif ('?' == $url[0]) {
            return $basepartial . $base['path'] . $url;
        } elseif ('/' == substr($base['path'], -1)) {
            return $basepartial . $base['path'] . $url;
        }

        return $basepartial . dirname($base['path']) . '/' . $url;
    }
}
