[Microdata][3] [DOM API][4] for [PHP][1].

 > This library extends the native PHP [`DOMDocument`][2] providing methods described by the Microdata specifications
 > to support finding microdata items and describing their properties. It is well covered by tests and tries to be
 > efficient as it parses the DOM.


## Usage

For a document with microdata, use `MicrodataDOM\DOMDocument`. It works just like a regular `DOMDocument`, but has the
extra microdata accessors. For example, the following example...

    $dom = new MicrodataDOM\DOMDocument();
    $dom->loadHTMLFile('http://dpb587.me/about.html');

    // find Person types and get the first item
    $dpb587 = $dom->getItems('http://schema.org/Person')->item(0);
    echo $dpb587->itemId;

    // items are still regular DOMElement objects
    printf(" (from %s on line %s)\n", $dpb587->getNodePath(), $dpb587->getLineNo());

    // there are a couple ways to access the first value of a named property
    printf("givenName: %s\n", $dpb587->properties['givenName'][0]->itemValue);
    printf("familyName: %s\n", $dpb587->properties['familyName']->getValues()[0]);

    // or directly get the third, property-defining DOM element
    $property = $dpb587->properties[3];
    printf("%s: %s\n", $property->itemProp[0], $property->itemValue);

    // use the toArray method to get a Microdata JSON structure
    echo json_encode($dpb587->toArray(), JSON_UNESCAPED_SLASHES) . "\n";

Will output something like...

    http://dpb587.me/ (from /html/body/article/section on line 97)
    givenName: Danny
    familyName: Berger
    jobTitle: Software Engineer
    {"id":"http://dpb587.me/","type":["http://schema.org/Person"],"properties":{"givenName":["Danny"],...snip...}


## Installation

You can install this library via [`composer`][5]...

    $ composer install dpb587/microdata-dom


## Development

You can find runtime code in [`src`](./src) and test code in [`tests/src`](./tests/src). If you are making changes, you
will need to have [PHPUnit][6] installed before you can run the tests...

    $ phpunit

The [Travis CI job][7] takes care of running tests on proposed and published changes.


## References

You may find these specifications useful...

 * [W3C DOM4](http://www.w3.org/TR/dom/)
 * [HTML5](http://www.w3.org/TR/html5/)
 * [HTML Microdata](http://www.w3.org/TR/microdata/)


## License

[MIT License](./LICENSE)


 [1]: http://php.net/
 [2]: http://php.net/manual/en/class.domdocument.php
 [3]: http://www.w3.org/TR/microdata/
 [4]: http://www.w3.org/TR/microdata/#microdata-dom-api
 [5]: https://getcomposer.org/
 [6]: https://phpunit.de/
 [7]: https://travis-ci.org/dpb587/microdata-dom.php
