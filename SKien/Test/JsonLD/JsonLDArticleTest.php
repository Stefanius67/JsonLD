<?php
declare(strict_types=1);

namespace SKien\Test\JsonLD;

use SKien\JsonLD\JsonLDArticle;

class JsonLDArticleTest extends JsonLDTestCase
{
    public function setUp() : void
    {
        $this->oJsonLD = new JsonLDArticle();
    }

    public function test__construct() : void
    {
        $this->assertIsObject($this->oJsonLD);
    }

    public function test_setPublisher() : void
    {
        $this->oJsonLD->setPublisher('My Name', 'My@e-Mail.de', 'My Phone');
        $aPublisher = $this->oJsonLD->getObject()['publisher'];
        $this->assertIsArray($aPublisher);

        $this->assertArrayHasKey('@type', $aPublisher);
        $this->assertEquals('Organization', $aPublisher['@type']);
        $this->assertEquals('My Name', $aPublisher['name']);
        $this->assertEquals('My@e-Mail.de', $aPublisher['email']);
        $this->assertEquals('My Phone', $aPublisher['telephone']);
    }

    public function test_setLogo() : void
    {
        $strURL = 'http://localhost/packages/JsonLD/elephpant.png';
        $this->oJsonLD->setLogo($strURL);

        $aPublisher = $this->oJsonLD->getObject()['publisher'];
        $this->assertIsArray($aPublisher);
        $this->assertArrayHasKey('@type', $aPublisher);
        $this->assertEquals('Organization', $aPublisher['@type']);

        $aLogo = $aPublisher['logo'];
        $this->assertIsArray($aLogo);
        $this->assertArrayHasKey('url', $aLogo);
        $this->assertEquals($strURL, $aLogo['url']);
    }

    public function test_setInfo() : void
    {
        $this->oJsonLD->setInfo('Headline', 'Description', '2021-09-15', '2021-09-17');

        $aJsonLD = $this->oJsonLD->getObject();
        $this->assertIsArray($aJsonLD);
        $this->assertArrayHasKey('headline', $aJsonLD);
        $this->assertEquals('Headline', $aJsonLD['headline']);
        $this->assertArrayHasKey('description', $aJsonLD);
        $this->assertEquals('Description', $aJsonLD['description']);
        $this->assertArrayHasKey('datePublished', $aJsonLD);
        $this->assertEquals('2021-09-15', $aJsonLD['datePublished']);
        $this->assertArrayHasKey('dateModified', $aJsonLD);
        $this->assertEquals('2021-09-17', $aJsonLD['dateModified']);
    }

    public function test_setAuthor() : void
    {
        $this->oJsonLD->setAuthor('The author');
        $aAuthor = $this->oJsonLD->getObject()['author'];
        $this->assertIsArray($aAuthor);
        $this->assertArrayHasKey('@type', $aAuthor);
        $this->assertEquals('Person', $aAuthor['@type']);
        $this->assertArrayHasKey('name', $aAuthor);
        $this->assertEquals('The author', $aAuthor['name']);
    }
}
