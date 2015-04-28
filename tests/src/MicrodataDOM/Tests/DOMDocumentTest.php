<?php

namespace MicrodataDOM\Tests;

use MicrodataDOM\DOMDocument;

class DOMDocumentTest extends \PHPUnit_Framework_TestCase
{
    public function testGetItems()
    {
        $dom = new DOMDocument();
        @$dom->loadHTML(<<<EOS
<section itemscope itemtype="http://example.org/animals#cat http://example.org/animals#domestic">
  <span itemprop="name">American Shorthair</span>
</section>
<section itemscope itemtype="http://example.org/animals#dog http://example.org/animals#domestic">
  <span itemprop="name">German Shepherd</span>
</section>
<section itemscope />
EOS
        );

        $all = $dom->getItems();
        $this->assertEquals(3, $all->length);

        $cats = $dom->getItems('http://example.org/animals#cat');
        $this->assertEquals(1, $cats->length);

        $domestics = $dom->getItems('http://example.org/animals#domestic');
        $this->assertEquals(2, $domestics->length);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetItemsBadArg()
    {
        $dom = new DOMDocument();
        $dom->getItems(1);
    }

    public function testToArray()
    {
        $dom = new DOMDocument();
        @$dom->loadHTML(<<<EOS
<section itemscope itemtype="http://example.org/animals#dog http://example.org/animals#domestic">
  <span itemprop="name">German Shepherd</span>
</section>
EOS
        );

        $array = $dom->toArray();

        $this->assertInternalType('array', $array);
        $this->assertArrayHasKey('items', $array);
        $this->assertInternalType('array', $array['items']);

        $this->assertEquals(
            [
                [
                    'type' => [
                        'http://example.org/animals#dog',
                        'http://example.org/animals#domestic',
                    ],
                    'properties' => [
                        'name' => [
                            'German Shepherd',
                        ],
                    ],
                ],
            ],
            $array['items']
        );
    }

    public function testMakeAbsoluteUrlUnknown()
    {
        $dom = new DOMDocument();

        $this->assertEquals('lost.html', $dom->makeAbsoluteUrl('lost.html'));
    }

    public function testMakeAbsoluteUrlAbsolute()
    {
        $dom = new DOMDocument();
        $dom->documentURI = 'https://www.example.com/somewhere/nice.html?with=time';

        $this->assertEquals('http://192.0.2.1/elsewhere', $dom->makeAbsoluteUrl('http://192.0.2.1/elsewhere'));
        $this->assertEquals('ftp://192.0.2.1/elsewhere', $dom->makeAbsoluteUrl('ftp://192.0.2.1/elsewhere'));
    }

    public function testMakeAbsoluteUrlRelativeScheme()
    {
        $dom = new DOMDocument();
        $dom->documentURI = 'https://www.example.com/somewhere/nice.html?with=time';

        $this->assertEquals('https://192.0.2.1/elsewhere', $dom->makeAbsoluteUrl('//192.0.2.1/elsewhere'));
    }

    public function testMakeAbsoluteUrlRelativeEmpty()
    {
        $dom = new DOMDocument();
        $dom->documentURI = 'https://www.example.com/somewhere/nice.html?with=time';

        $this->assertEquals('https://www.example.com/somewhere/nice.html?with=time', $dom->makeAbsoluteUrl(''));
    }

    public function testMakeAbsoluteUrlRelativeRootPath()
    {
        $dom = new DOMDocument();
        $dom->documentURI = 'https://www.example.com/somewhere/nice.html?with=time';

        $this->assertEquals('https://www.example.com/', $dom->makeAbsoluteUrl('/'));
        $this->assertEquals('https://www.example.com/somewhere/warm.html', $dom->makeAbsoluteUrl('/somewhere/warm.html'));
    }

    public function testMakeAbsoluteUrlRelativeFragment()
    {
        $dom = new DOMDocument();
        $dom->documentURI = 'https://www.example.com/somewhere/nice.html?with=time';

        $this->assertEquals('https://www.example.com/somewhere/nice.html?with=time#snacking', $dom->makeAbsoluteUrl('#snacking'));
    }

    public function testMakeAbsoluteUrlRelativeQuery()
    {
        $dom = new DOMDocument();
        $dom->documentURI = 'https://www.example.com/somewhere/nice.html?with=time';

        $this->assertEquals('https://www.example.com/somewhere/nice.html?with=thunderstorms', $dom->makeAbsoluteUrl('?with=thunderstorms'));
    }

    public function testMakeAbsoluteUrlRelativePath()
    {
        $dom = new DOMDocument();
        $dom->documentURI = 'https://www.example.com/somewhere/nice.html?with=time';

        $this->assertEquals('https://www.example.com/somewhere/mountainous.html', $dom->makeAbsoluteUrl('mountainous.html'));
    }
}
