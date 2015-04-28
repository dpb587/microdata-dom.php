<?php

namespace MicrodataDOM\Tests;

use MicrodataDOM\DOMDocument;

class DOMElementTest extends \PHPUnit_Framework_TestCase
{
    public function testMagicGet()
    {
        $dom = new DOMDocument();
        @$dom->loadHTML(<<<EOS
<section itemscope itemtype="
    http://example.org/animals#dog
    http://example.org/animals#domestic
" itemid="german-shepherd" itemref="missing ids">
  <span itemprop="name">German Shepherd</span>
  <span itemprop="temperament" itemscope itemtype="http://example.org/animals#temperament">
    <span itemprop="name">Intelligent</span>
  </span>
</section>
EOS
        );

        $sut = $dom->getItems()->item(0);

        $this->assertTrue($sut->itemScope);
        $this->assertEquals([ 'http://example.org/animals#dog', 'http://example.org/animals#domestic' ], $sut->itemType);
        $this->assertEquals('german-shepherd', $sut->itemId);
        $this->assertNull($sut->itemProp);
        $this->assertEquals([ 'missing', 'ids' ], $sut->itemRef);
        $this->assertNull($sut->itemValue);
        $this->assertInstanceOf('MicrodataDOM\\HTMLPropertiesCollection', $sut->properties);

        $sut = $sut->firstChild;

        $this->assertFalse($sut->itemScope);
        $this->assertNull($sut->itemType);
        $this->assertNull($sut->itemId);
        $this->assertEquals([ 'name' ], $sut->itemProp);
        $this->assertNull($sut->itemRef);
        $this->assertEquals('German Shepherd', $sut->itemValue);
        $this->assertNull($sut->properties);

        $sut = $sut->nextSibling->nextSibling;

        $this->assertTrue($sut->itemScope);
        $this->assertEquals([ 'http://example.org/animals#temperament' ], $sut->itemType);
        $this->assertNull($sut->itemId);
        $this->assertEquals([ 'temperament' ], $sut->itemProp);
        $this->assertEquals([], $sut->itemRef);
        $this->assertEquals($sut, $sut->itemValue);
        $this->assertInstanceOf('MicrodataDOM\\HTMLPropertiesCollection', $sut->properties);
    }

    public function testToArray()
    {
        $dom = new DOMDocument();
        @$dom->loadHTML(<<<EOS
<section itemscope itemtype="http://example.org/animals#dog http://example.org/animals#domestic" itemid="german-shepherd">
  <span itemprop="name">German Shepherd</span>
  <span itemprop="temperament" itemscope itemtype="http://example.org/animals#temperament">
    <span itemprop="name">Intelligent</span>
  </span>
</section>
EOS
        );

        $sut = $dom->getItems()->item(0);

        $this->assertEquals(
            [
                'type' => [
                    'http://example.org/animals#dog',
                    'http://example.org/animals#domestic',
                ],
                'id' => 'german-shepherd',
                'properties' => [
                    'name' => [
                        'German Shepherd',
                    ],
                    'temperament' => [
                        [
                            'type' => [
                                'http://example.org/animals#temperament',
                            ],
                            'properties' => [
                                'name' => [
                                    'Intelligent',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            $sut->toArray()
        );
    }

    /**
     * @expectedException LogicException
     */
    public function testMagicGetInvalid()
    {
        $dom = new DOMDocument();
        @$dom->loadHTML('<span>');

        $dom->firstChild->nextSibling->firstChild->firstChild->itemNonexistant;
    }

    /**
     * @dataProvider dataMagicGetItemValueNode
     */
    public function testMagicGetItemValueNode($html, $value)
    {
        $dom = new DOMDocument();
        @$dom->loadHTML('<html><body>' . $html . '</body></html>');
        $dom->documentURI = 'https://www.example.com/';

        $this->assertEquals($value, $dom->firstChild->nextSibling->firstChild->firstChild->itemValue);
    }

    public function dataMagicGetItemValueNode()
    {
        return [
            /**
             * meta
             */
            [
                '<meta itemprop="test" content="meta value">',
                'meta value',
            ],
            [
                '<meta itemprop="test">',
                '',
            ],

            /**
             * audio
             */
            [
                '<audio itemprop="test" src="audio.mp4">',
                'https://www.example.com/audio.mp4',
            ],
            [
                '<audio itemprop="test">',
                null,
            ],

            /**
             * embed
             */
            [
                '<embed itemprop="test" src="embed.flv">',
                'https://www.example.com/embed.flv',
            ],
            [
                '<embed itemprop="test">',
                null,
            ],

            /**
             * iframe
             */
            [
                '<iframe itemprop="test" src="iframe.html">',
                'https://www.example.com/iframe.html',
            ],
            [
                '<iframe itemprop="test">',
                null,
            ],

            /**
             * img
             */
            [
                '<img itemprop="test" src="img.jpg">',
                'https://www.example.com/img.jpg',
            ],
            [
                '<img itemprop="test">',
                null,
            ],

            /**
             * source
             */
            [
                '<source itemprop="test" src="source.raw">',
                'https://www.example.com/source.raw',
            ],
            [
                '<source itemprop="test">',
                null,
            ],

            /**
             * track
             */
            [
                '<track itemprop="test" src="track.rpm">',
                'https://www.example.com/track.rpm',
            ],
            [
                '<track itemprop="test">',
                null,
            ],

            /**
             * video
             */
            [
                '<video itemprop="test" src="video.ogv">',
                'https://www.example.com/video.ogv',
            ],
            [
                '<video itemprop="test">',
                null,
            ],

            /**
             * a
             */
            [
                '<a itemprop="test" href="a.html">',
                'https://www.example.com/a.html',
            ],
            [
                '<a itemprop="test">',
                null,
            ],

            /**
             * area
             */
            [
                '<area itemprop="test" href="area.html">',
                'https://www.example.com/area.html',
            ],
            [
                '<area itemprop="test">',
                null,
            ],

            /**
             * link
             */
            [
                '<link itemprop="test" href="link.html">',
                'https://www.example.com/link.html',
            ],
            [
                '<link itemprop="test">',
                null,
            ],

            /**
             * object
             */
            [
                '<object itemprop="test" data="object.data">',
                'https://www.example.com/object.data',
            ],
            [
                '<object itemprop="test">',
                null,
            ],

            /**
             * data
             */
            [
                '<data itemprop="test" value="data:value">',
                'data:value',
            ],
            [
                '<data itemprop="test">',
                null,
            ],

            /**
             * meter
             */
            [
                '<meter itemprop="test" value="27.5">',
                '27.5',
            ],
            [
                '<meter itemprop="test">',
                null,
            ],

            /**
             * time
             */
            [
                '<time itemprop="test" datetime="2015-01-09">',
                '2015-01-09',
            ],
            [
                '<time itemprop="test">',
                null,
            ],

            /**
             * @content
             */
            [
                '<span itemprop="test" content="hidden text">visible text</span>',
                'hidden text',
            ],
            [
                '<span itemprop="test" content="">visible text</span>',
                '',
            ],

            /**
             * plain
             */
            [
                '<span itemprop="test">
                    multiline
                    text
                </span>',
                '
                    multiline
                    text
                ',
            ],
            [
                '<span itemprop="test"><strong>emphatic</strong> text</span>',
                'emphatic text',
            ],
            [
                '<span itemprop="test"></span>',
                '',
            ],
        ];
    }
}
