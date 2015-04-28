<?php

namespace MicrodataDOM\Tests;

use MicrodataDOM\DOMDocument;

class HTMLPropertiesCollectionTest extends \PHPUnit_Framework_TestCase
{
    protected $sut;

    public function setUp()
    {
        $dom = new DOMDocument();
        @$dom->loadHTML(<<<EOS
<section itemscope itemtype="
    http://example.org/animals#dog
    http://example.org/animals#domestic
" itemid="german-shepherd" itemref="missing ids">
  <span itemprop="name">German Shepherd</span>
  <span itemprop="temperament" itemscope itemtype="http://example.org/animals#temperament" itemid="temperament-intelligent">
    <span itemprop="name">Intelligent</span>
  </span>
  <span itemprop="temperament" itemscope itemtype="http://example.org/animals#temperament" itemid="temperament-loyal">
    <span itemprop="name">Loyal</span>
  </span>
</section>
EOS
        );

        $this->sut = $dom->getItems()->item(0)->properties;
    }

    public function tearDown()
    {
        unset($this->sut);
    }

    public function testMagicGetNames()
    {
        $this->assertEquals([ 'name', 'temperament' ], $this->sut->names);
    }

    public function testMagicGetLength()
    {
        $this->assertEquals(3, $this->sut->length);
    }
    
    /**
     * @expectedException LogicException
     */
    public function testMagicGetInvalid()
    {
        $this->sut->unknown;
    }

    public function testToArray()
    {
        $this->assertEquals(
            [
                'name' => [
                    'German Shepherd',
                ],
                'temperament' => [
                    [
                        'type' => [
                            'http://example.org/animals#temperament',
                        ],
                        'id' => 'temperament-intelligent',
                        'properties' => [
                            'name' => [
                                'Intelligent',
                            ],
                        ],
                    ],
                    [
                        'type' => [
                            'http://example.org/animals#temperament',
                        ],
                        'id' => 'temperament-loyal',
                        'properties' => [
                            'name' => [
                                'Loyal',
                            ],
                        ],
                    ],
                ],
            ],
            $this->sut->toArray()
        );
    }

    public function testItem()
    {
        $sut = $this->sut->item(1);
        $this->assertInstanceOf('MicrodataDOM\\DOMElement', $sut);
        $this->assertEquals('temperament-intelligent', $sut->itemId);

        $sut = $this->sut->getItem(1);
        $this->assertInstanceOf('MicrodataDOM\\DOMElement', $sut);
        $this->assertEquals('temperament-intelligent', $sut->itemId);
    }

    /**
     * @expectedException OutOfRangeException
     */
    public function testItemInvalid()
    {
        $this->sut->item(4)->itemId;
    }

    public function testNamedItem()
    {
        $sut = $this->sut->namedItem('name');
        $this->assertInstanceOf('MicrodataDOM\\PropertyNodeList', $sut);
        $this->assertEquals(1, $sut->length);

        $sut = $this->sut->getNamedItem('name');
        $this->assertInstanceOf('MicrodataDOM\\PropertyNodeList', $sut);
        $this->assertEquals(1, $sut->length);
    }

    public function testNamedItemInvalid()
    {
        $sut = $this->sut->namedItem('nonexistant');
        $this->assertInstanceOf('MicrodataDOM\\PropertyNodeList', $sut);
        $this->assertEquals(0, $sut->length);
    }


    /**
     * offsetGet
     */

    public function testOffsetGetName()
    {
        $sut = $this->sut['name'];
        $this->assertInstanceOf('MicrodataDOM\\PropertyNodeList', $sut);
        $this->assertEquals(1, $sut->length);
    }

    public function testOffsetGetNameInvalid()
    {
        $sut = $this->sut['nonexistant'];
        $this->assertInstanceOf('MicrodataDOM\\PropertyNodeList', $sut);
        $this->assertEquals(0, $sut->length);
    }

    public function testOffsetGetIndex()
    {
        $sut = $this->sut[1];
        $this->assertInstanceOf('MicrodataDOM\\DOMElement', $sut);
        $this->assertEquals('temperament-intelligent', $sut->itemId);
    }

    /**
     * @expectedException OutOfRangeException
     */
    public function testOffsetGetIndexInvalid()
    {
        $this->assertNull($this->sut[4]);
    }

    /**
     * @expectedException UnexpectedValueException
     */
    public function testOffsetGetInvalid()
    {
        $this->assertNull($this->sut[1.9]);
    }
    
    
    /**
     * offsetExists
     */

    public function testOffsetExistsName()
    {
        $this->assertTrue(isset($this->sut['name']));
    }

    public function testOffsetExistsNameInvalid()
    {
        $this->assertFalse(isset($this->sut['nonexistant']));
    }

    public function testOffsetExistsIndex()
    {
        $this->assertTrue(isset($this->sut[1]));
    }

    public function testOffsetExistsIndexInvalid()
    {
        $this->assertFalse(isset($this->sut[4]));
    }

    /**
     * @expectedException UnexpectedValueException
     */
    public function testOffsetExistsInvalid()
    {
        $this->assertNull(isset($this->sut[1.9]));
    }


    /**
     * offsetOthers
     */

    /**
     * @expectedException BadMethodCallException
     */
    public function testOffsetSet()
    {
        $this->assertNull($this->sut['name'] = null);
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testOffsetUnset()
    {
        unset($this->sut['name']);
    }
}
