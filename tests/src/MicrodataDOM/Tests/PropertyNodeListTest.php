<?php

namespace MicrodataDOM\Tests;

use MicrodataDOM\DOMDocument;

class PropertyNodeListTest extends \PHPUnit_Framework_TestCase
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
  <span itemprop="temperament">Loyal</span>
</section>
EOS
        );

        $this->sut = $dom->getItems()->item(0)->properties->getNamedItem('temperament');
    }

    public function tearDown()
    {
        unset($this->sut);
    }

    public function testGetIterator()
    {
        $array = iterator_to_array($this->sut);

        $this->assertCount(2, $array);
        $this->assertEquals('temperament-intelligent', $array[0]->itemId);
        $this->assertEquals('Loyal', $array[1]->itemValue);
    }

    public function testGetValues()
    {
        $values = $this->sut->getValues();

        $this->assertCount(2, $values);
        $this->assertInstanceOf('MicrodataDOM\\DOMElement', $values[0]);
        $this->assertEquals('Loyal', $values[1]);
    }

    public function testMagicGetLength()
    {
        $this->assertEquals(2, $this->sut->length);
    }

    /**
     * @expectedException LogicException
     */
    public function testMagicGetInvalid()
    {
        $this->sut->unknown;
    }


    /**
     * offsetGet
     */

    public function testOffsetGetValid()
    {
        $this->assertInstanceOf('MicrodataDOM\\DOMElement', $this->sut[0]);
        $this->assertInstanceOf('MicrodataDOM\\DOMElement', $this->sut[1]);
    }

    /**
     * @expectedException OutOfRangeException
     */
    public function testOffsetGetIndexInvalid()
    {
        $this->assertNull($this->sut[2]);
    }


    /**
     * offsetExists
     */

    public function testOffsetExistsTrue()
    {
        $this->assertTrue(isset($this->sut[1]));
    }

    public function testOffsetExistsFalse()
    {
        $this->assertFalse(isset($this->sut[2]));
    }


    /**
     * offsetOthers
     */

    /**
     * @expectedException BadMethodCallException
     */
    public function testOffsetSet()
    {
        $this->assertNull($this->sut[3] = null);
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testOffsetUnset()
    {
        unset($this->sut[3]);
    }
}
