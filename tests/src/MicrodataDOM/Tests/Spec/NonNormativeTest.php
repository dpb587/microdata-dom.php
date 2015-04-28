<?php

namespace MicrodataDOM\Tests\Spec;

use MicrodataDOM\DOMDocument;

class NonNormativeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DOMDocument
     */
    protected $dom;

    protected $libxmlErrors;

    public function setUp()
    {
        $this->libxmlErrors = libxml_use_internal_errors(true);

        $this->dom = new DOMDocument();
    }

    public function tearDown()
    {
        unset($this->dom);

        libxml_clear_errors();
        libxml_use_internal_errors($this->libxmlErrors);
    }

    public function testFourDotTwoDotOneExampleOne()
    {
        $example = <<<EOS
<div itemscope>
  <p>My name is <span itemprop="name">Elizabeth</span>.</p>
</div>
<div itemscope>
  <p>My name is <span itemprop="name">Daniel</span>.</p>
</div>
EOS;

        $this->dom->loadHTML($example);
        $items = $this->dom->getItems();

        $this->assertEquals(2, $items->length);

        $properties = $items->item(0)->properties;
        $this->assertEquals('Elizabeth', $properties[0]->itemValue);
        $this->assertEquals('Elizabeth', $properties['name'][0]->itemValue);
        $this->assertEquals([ 'Elizabeth' ], $properties['name']->getValues());
        $this->assertEquals([ 'name' ], $properties->names);

        $properties = $items->item(1)->properties;
        $this->assertEquals('Daniel', $properties[0]->itemValue);
        $this->assertEquals([ 'name' ], $properties->names);
    }

    public function testFourDotTwoDotOneExampleTwo()
    {
        $example = <<<EOS
<div itemscope>
  <p>My <em>name</em> is <span itemprop="name">E<strong>liz</strong>abeth</span>.</p>
</div>

<section>
  <div itemscope>
    <aside>
      <p>My name is <span itemprop="name"><a href="/?user=daniel">Daniel</a></span>.</p>
    </aside>
  </div>
</section>
EOS;

        $this->dom->loadHTML($example);
        $items = $this->dom->getItems();

        $this->assertEquals(2, $items->length);

        $properties = $items->item(0)->properties;
        $this->assertEquals([ 'Elizabeth' ], $properties['name']->getValues());
        $this->assertEquals([ 'name' ], $properties->names);

        $properties = $items->item(1)->properties;
        $this->assertEquals([ 'Daniel' ], $properties['name']->getValues());
        $this->assertEquals([ 'name' ], $properties->names);
    }

    public function testFourDotTwoDotOneExampleThree()
    {
        $example = <<<EOS
<div itemscope>
  <p>My name is <span itemprop="name">Neil</span>.</p>
  <p>My band is called <span itemprop="band">Four Parts Water</span>.</p>
  <p>I am <span itemprop="nationality">British</span>.</p>
</div>
EOS;

        $this->dom->loadHTML($example);
        $items = $this->dom->getItems();

        $this->assertEquals(1, $items->length);

        $properties = $items->item(0)->properties;
        $this->assertEquals([ 'Neil' ], $properties['name']->getValues());
        $this->assertEquals([ 'Four Parts Water' ], $properties['band']->getValues());
        $this->assertEquals([ 'British' ], $properties['nationality']->getValues());
        $this->assertEquals([ 'name', 'band', 'nationality' ], $properties->names);
    }

    public function testFourDotTwoDotOneExampleFour()
    {
        $example = <<<EOS
<div itemscope>
  <img itemprop="image" src="google-logo.png" alt="Google">
</div>
EOS;

        $this->dom->loadHTML($example);
        $items = $this->dom->getItems();

        $this->assertEquals(1, $items->length);

        $properties = $items->item(0)->properties;
        $this->assertEquals([ 'google-logo.png' ], $properties['image']->getValues());
        $this->assertEquals([ 'image' ], $properties->names);
    }

    public function testFourDotTwoDotOneExampleFive()
    {
        $example = <<<EOS
<h1 itemscope>
  <data itemprop="product-id" value="9678AOU879">The Instigator 2000</data>
</h1>
EOS;

        $this->dom->loadHTML($example);
        $items = $this->dom->getItems();

        $this->assertEquals(1, $items->length);

        $properties = $items->item(0)->properties;
        $this->assertEquals([ '9678AOU879' ], $properties['product-id']->getValues());
        $this->assertEquals([ 'product-id' ], $properties->names);
    }

    public function testFourDotTwoDotOneExampleSix()
    {
        $example = <<<EOS
<div itemscope itemtype="http://schema.org/Product">
  <span itemprop="name">Panasonic White 60L Refrigerator</span>
  <img src="panasonic-fridge-60l-white.jpg" alt="">
  <div itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">
    <meter itemprop="ratingValue" min=0 value=3.5 max=5>Rated 3.5/5</meter>
    (based on <span itemprop="reviewCount">11</span> customer reviews)
  </div>
</div>
EOS;

        $this->dom->loadHTML($example);
        $items = $this->dom->getItems();

        $this->assertEquals(1, $items->length);

        $properties = $items->item(0)->properties;
        $this->assertEquals([ 'http://schema.org/Product' ], $items->item(0)->itemType);
        $this->assertEquals([ 'Panasonic White 60L Refrigerator' ], $properties['name']->getValues());
        $this->assertEquals([ 'name', 'aggregateRating' ], $properties->names);

        $item0 = $properties['aggregateRating'];
        $this->assertEquals(1, $item0->length);

        $this->assertEquals([ 'http://schema.org/AggregateRating' ], $item0[0]->itemType);
        $this->assertEquals([ 3.5 ], $item0[0]->properties['ratingValue']->getValues());
        $this->assertEquals([ '11' ], $item0[0]->properties['reviewCount']->getValues());
        $this->assertEquals([ 'ratingValue', 'reviewCount' ], $item0[0]->properties->names);
    }

    public function testFourDotTwoDotOneExampleSeven()
    {
        $example = <<<EOS
<div itemscope>
  I was born on <time itemprop="birthday" datetime="2009-05-10">May 10th 2009</time>.
</div>
EOS;

        $this->dom->loadHTML($example);
        $items = $this->dom->getItems();

        $this->assertEquals(1, $items->length);

        $properties = $items->item(0)->properties;
        $this->assertEquals([ '2009-05-10' ], $properties['birthday']->getValues());
        $this->assertEquals([ 'birthday' ], $properties->names);
    }

    public function testFourDotTwoDotOneExampleEight()
    {
        $example = <<<EOS
<div itemscope>
  <p>Name: <span itemprop="name">Amanda</span></p>
  <p>Band: <span itemprop="band" itemscope> <span itemprop="name">Jazz Band</span> (<span itemprop="size">12</span> players)</span></p>
</div>
EOS;

        $this->dom->loadHTML($example);
        $items = $this->dom->getItems();

        $this->assertEquals(1, $items->length);

        $properties = $items->item(0)->properties;
        $this->assertEquals([ 'Amanda' ], $properties['name']->getValues());
        $this->assertEquals([ 'name', 'band' ], $properties->names);

        $item0 = $properties['band'];
        $this->assertEquals(1, $item0->length);

        $this->assertEquals([ 'Jazz Band' ], $item0[0]->properties['name']->getValues());
        $this->assertEquals([ '12' ], $item0[0]->properties['size']->getValues());
        $this->assertEquals([ 'name', 'size' ], $item0[0]->properties->names);
    }

    public function testFourDotTwoDotOneExampleNine()
    {
        $example = <<<EOS
<div itemscope id="amanda" itemref="a b"></div>
<p id="a">Name: <span itemprop="name">Amanda</span></p>
<div id="b" itemprop="band" itemscope itemref="c"></div>
<div id="c">
  <p>Band: <span itemprop="name">Jazz Band</span></p>
  <p>Size: <span itemprop="size">12</span> players</p>
</div>
EOS;

        $this->dom->loadHTML($example);
        $items = $this->dom->getItems();

        $this->assertEquals(1, $items->length);

        $properties = $items->item(0)->properties;
        $this->assertEquals([ 'Amanda' ], $properties['name']->getValues());
        $this->assertEquals([ 'name', 'band' ], $properties->names);

        $item0 = $properties['band'];
        $this->assertEquals(1, $item0->length);

        $this->assertEquals([ 'Jazz Band' ], $item0[0]->properties['name']->getValues());
        $this->assertEquals([ '12' ], $item0[0]->properties['size']->getValues());
        $this->assertEquals([ 'name', 'size' ], $item0[0]->properties->names);
    }

    public function testFourDotTwoDotOneExampleTen()
    {
        $example = <<<EOS
<div itemscope>
  <p>Flavors in my favorite ice cream:</p>
  <ul>
    <li itemprop="flavor">Lemon sorbet</li>
    <li itemprop="flavor">Apricot sorbet</li>
  </ul>
</div>
EOS;

        $this->dom->loadHTML($example);
        $items = $this->dom->getItems();

        $this->assertEquals(1, $items->length);

        $properties = $items->item(0)->properties;
        $this->assertEquals([ 'Lemon sorbet', 'Apricot sorbet' ], $properties['flavor']->getValues());
        $this->assertEquals([ 'flavor' ], $properties->names);
    }

    public function testFourDotTwoDotOneExampleEleven()
    {
        $example = <<<EOS
<div itemscope>
  <span itemprop="favorite-color favorite-fruit">orange</span>
</div>
EOS;

        $this->dom->loadHTML($example);
        $items = $this->dom->getItems();

        $this->assertEquals(1, $items->length);

        $properties = $items->item(0)->properties;
        $this->assertEquals([ 'orange' ], $properties['favorite-color']->getValues());
        $this->assertEquals([ 'orange' ], $properties['favorite-fruit']->getValues());
        $this->assertEquals([ 'favorite-color', 'favorite-fruit' ], $properties->names);
    }

    public function testFourDotTwoDotOneExampleTwelveA()
    {
        $example = <<<EOS
<figure>
  <img src="castle.jpeg">
  <figcaption><span itemscope><span itemprop="name">The Castle</span></span> (1986)</figcaption>
</figure>
EOS;

        $this->dom->loadHTML($example);
        $items = $this->dom->getItems();

        $this->assertEquals(1, $items->length);

        $properties = $items->item(0)->properties;
        $this->assertEquals([ 'The Castle' ], $properties['name']->getValues());
        $this->assertEquals([ 'name' ], $properties->names);
    }

    public function testFourDotTwoDotOneExampleTwelveB()
    {
        $example = <<<EOS
<span itemscope><meta itemprop="name" content="The Castle"></span>
<figure>
  <img src="castle.jpeg">
  <figcaption>The Castle (1986)</figcaption>
</figure>
EOS;

        $this->dom->loadHTML($example);
        $items = $this->dom->getItems();

        $this->assertEquals(1, $items->length);

        $properties = $items->item(0)->properties;
        $this->assertEquals([ 'The Castle' ], $properties['name']->getValues());
        $this->assertEquals([ 'name' ], $properties->names);
    }

    public function testFourDotThreeExampleOne()
    {
        $example = <<<EOS
<section itemscope itemtype="http://example.org/animals#cat">
  <h1 itemprop="name">Hedral</h1>
  <p itemprop="desc">Hedral is a male american domestic shorthair, with a fluffy black fur with white paws and belly.</p>
  <img itemprop="img" src="hedral.jpeg" alt="" title="Hedral, age 18 months">
</section>
EOS;

        $this->dom->loadHTML($example);
        $items = $this->dom->getItems();

        $this->assertEquals(1, $items->length);

        $properties = $items->item(0)->properties;
        $this->assertEquals([ 'http://example.org/animals#cat' ], $items->item(0)->itemType);
        $this->assertEquals([ 'Hedral' ], $properties['name']->getValues());
        $this->assertEquals([ 'Hedral is a male american domestic shorthair, with a fluffy black fur with white paws and belly.' ], $properties['desc']->getValues());
        $this->assertEquals([ 'hedral.jpeg' ], $properties['img']->getValues());
        $this->assertEquals([ 'name', 'desc', 'img' ], $properties->names);
    }

    public function testFourDotFourExampleOne()
    {
        $example = <<<EOS
<dl itemscope itemtype="http://vocab.example.net/book" itemid="urn:isbn:0-330-34032-8">
  <dt>Title
  <dd itemprop="title">The Reality Dysfunction
  <dt>Author
  <dd itemprop="author">Peter F. Hamilton
  <dt>Publication date
  <dd><time itemprop="pubdate" datetime="1996-01-26">26 January 1996</time>
</dl>
EOS;

        $this->dom->loadHTML($example);
        $items = $this->dom->getItems();

        $this->assertEquals(1, $items->length);

        $properties = $items->item(0)->properties;
        $this->assertEquals([ 'http://vocab.example.net/book' ], $items->item(0)->itemType);
        $this->assertEquals('urn:isbn:0-330-34032-8', $items->item(0)->itemId);
        $this->assertEquals([ 'The Reality Dysfunction
  ' ], $properties['title']->getValues());
        $this->assertEquals([ 'Peter F. Hamilton
  ' ], $properties['author']->getValues());
        $this->assertEquals([ '1996-01-26' ], $properties['pubdate']->getValues());
        $this->assertEquals([ 'title', 'author', 'pubdate' ], $properties->names);
    }

    public function testFourDotFiveExampleOne()
    {
        $example = <<<EOS
<section itemscope itemtype="http://example.org/animals#cat">
  <h1 itemprop="name http://example.com/fn">Hedral</h1>
  <p itemprop="desc">
    Hedral is a male american domestic shorthair, with a fluffy
    <span itemprop="http://example.com/color">black</span> fur with
    <span itemprop="http://example.com/color">white</span> paws and belly.
  </p>
  <img itemprop="img" src="hedral.jpeg" alt="" title="Hedral, age 18 months">
</section>
EOS;

        $this->dom->loadHTML($example);
        $items = $this->dom->getItems();

        $this->assertEquals(1, $items->length);

        $properties = $items->item(0)->properties;
        $this->assertEquals([ 'http://example.org/animals#cat' ], $items->item(0)->itemType);
        $this->assertEquals([ 'Hedral' ], $properties['name']->getValues());
        $this->assertEquals([ 'Hedral' ], $properties['http://example.com/fn']->getValues());
        $this->assertEquals([ '
    Hedral is a male american domestic shorthair, with a fluffy
    black fur with
    white paws and belly.
  ' ], $properties['desc']->getValues());
        $this->assertEquals([ 'black', 'white' ], $properties['http://example.com/color']->getValues());
        $this->assertEquals([ 'hedral.jpeg' ], $properties['img']->getValues());
        $this->assertEquals([ 'name', 'http://example.com/fn', 'desc', 'http://example.com/color', 'img' ], $properties->names);
    }
}
