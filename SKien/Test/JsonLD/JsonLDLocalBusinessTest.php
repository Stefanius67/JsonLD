<?php
declare(strict_types=1);

namespace SKien\Test\JsonLD;

use SKien\JsonLD\JsonLDLocalBusiness;

class JsonLDLocalBusinessTest extends JsonLDTestCase
{
    public function setUp() : void
    {
        $this->oJsonLD = new JsonLDLocalBusiness();
    }

    public function test__construct() : void
    {
        $this->assertIsObject($this->oJsonLD);
    }

    public function test_setUrl() : void
    {
        $this->oJsonLD->setURL('https://www.My-URL.de', 'My Id');

        $aJsonLD = $this->oJsonLD->getObject();
        $this->assertIsArray($aJsonLD);
        $this->assertEquals('My Id', $aJsonLD['@id']);
        $this->assertEquals('https://www.My-URL.de', $aJsonLD['url']);
    }

    public function test_setInfo() : void
    {
        $this->oJsonLD->setInfo('My name', 'My@Mail.de', 'My telephone');

        $aJsonLD = $this->oJsonLD->getObject();
        $this->assertIsArray($aJsonLD);
        $this->assertEquals('My name', $aJsonLD['name']);
        $this->assertEquals('My@Mail.de', $aJsonLD['email']);
        $this->assertEquals('My telephone', $aJsonLD['telephone']);
    }

    public function test_setAddress() : void
    {
        $this->oJsonLD->setAddress('My street', 'My postcode', 'My city', 'My region', 'My country');

        $aAddress = $this->oJsonLD->getObject()['address'];
        $this->assertIsArray($aAddress);

        $this->assertEquals('PostalAddress', $aAddress['@type']);
        $this->assertEquals('My street', $aAddress['streetAddress']);
        $this->assertEquals('My postcode', $aAddress['postalCode']);
        $this->assertEquals('My city', $aAddress['addressLocality']);
        $this->assertEquals('My region', $aAddress['addressRegion']);
        $this->assertEquals('My country', $aAddress['addressCountry']);
    }

    public function test_setLogo() : void
    {
        $strURL = 'http://localhost/packages/JsonLD/elephpant.png';
        $this->oJsonLD->setLogo($strURL);

        $aLogo = $this->oJsonLD->getObject()['logo'];
        $this->assertIsArray($aLogo);
        $this->assertArrayHasKey('url', $aLogo);
        $this->assertEquals($strURL, $aLogo['url']);
    }

    public function test_setPriceRange() : void
    {
        $this->oJsonLD->setPriceRange('My pricerange');
        $this->assertEquals('My pricerange', $this->oJsonLD->getObject()['priceRange']);
    }

    public function test_setServesCuisine() : void
    {
        $this->oJsonLD->setServesCuisine('My cuisine');
        $this->assertEquals('My cuisine', $this->oJsonLD->getObject()['servesCuisine']);
    }

    public function test_setOpeningHours() : void
    {
        $this->oJsonLD->setOpeningHours('My opening hours');
        $this->assertEquals('My opening hours', $this->oJsonLD->getObject()['openingHours']);
    }

    public function test_addLanguages() : void
    {
        $this->oJsonLD->addLanguage('de-DE');
        $this->oJsonLD->addLanguage('en-UK');
        $aLang = $this->oJsonLD->getObject()['knowsLanguage'];
        $this->assertIsArray($aLang);
        $this->assertEquals(2, count($aLang));
        $this->assertArrayContains('de-DE', $aLang);
        $this->assertArrayContains('en-UK', $aLang);
    }

    public function test_addContact() : void
    {
        $this->oJsonLD->addContact('Info', 'Info e-Mail', 'Info phone');
        $this->oJsonLD->addContact('Support', 'Info e-Mail', 'Info phone');
        $aContacts = $this->oJsonLD->getObject()['contactPoint'];
        $this->assertIsArray($aContacts);
        $this->assertEquals(2, count($aContacts));
    }

    public function test_addContactLanguage() : void
    {
        $this->oJsonLD->addContact('Info', 'Info e-Mail', 'Info phone');
        $this->oJsonLD->addContactLanguage(0, 'de-DE');
        $this->oJsonLD->addContactLanguage(0, 'en-UK');
        $aContact = $this->oJsonLD->getObject()['contactPoint'][0];
        $aLang = $aContact['availableLanguage'];
        $this->assertEquals(2, count($aLang));
        $this->assertArrayContains('de-DE', $aLang);
        $this->assertArrayContains('en-UK', $aLang);
    }

    public function test_addSameAs() : void
    {
        $this->oJsonLD->addSameAs('https://www.my-twitter.de');
        $this->oJsonLD->addSameAs('https://www.my-facebook.de');
        $aSameAs = $this->oJsonLD->getObject()['sameAs'];
        $this->assertIsArray($aSameAs);
        $this->assertEquals(2, count($aSameAs));
        $this->assertArrayContains('https://www.my-twitter.de', $aSameAs);
        $this->assertArrayContains('https://www.my-facebook.de', $aSameAs);
    }

    public function test_addOpeningHours() : void
    {
        $this->oJsonLD->addOpeningHours([1,1,1,1,1,0,0],  '8:00', '12:00');
        $this->oJsonLD->addOpeningHours([1,1,0,0,1,0,0], '13:00', '17:30');

        $aOpeningHours = $this->oJsonLD->getObject()["location"]["openingHoursSpecification"];
        $this->assertIsArray($aOpeningHours);
        $this->assertEquals(2, count($aOpeningHours));

        $this->assertEquals('08:00', $aOpeningHours[0]['opens']);
        $this->assertEquals('12:00', $aOpeningHours[0]['closes']);
        $aWeekDays = $aOpeningHours[0]['dayOfWeek'];
        $this->assertIsArray($aWeekDays);
        $this->assertEquals(5, count($aWeekDays));
        $this->assertArrayContains('Monday', $aWeekDays);
        $this->assertArrayContains('Tuesday', $aWeekDays);
        $this->assertArrayContains('Wednesday', $aWeekDays);
        $this->assertArrayContains('Thursday', $aWeekDays);
        $this->assertArrayContains('Friday', $aWeekDays);
        $this->assertArrayNotContains('Saturday', $aWeekDays);
        $this->assertArrayNotContains('Sunday', $aWeekDays);

        $this->assertEquals('13:00', $aOpeningHours[1]['opens']);
        $this->assertEquals('17:30', $aOpeningHours[1]['closes']);
        $aWeekDays = $aOpeningHours[1]['dayOfWeek'];
        $this->assertIsArray($aWeekDays);
        $this->assertEquals(3, count($aWeekDays));
        $this->assertArrayContains('Monday', $aWeekDays);
        $this->assertArrayContains('Tuesday', $aWeekDays);
        $this->assertArrayNotContains('Wednesday', $aWeekDays);
        $this->assertArrayNotContains('Thursday', $aWeekDays);
        $this->assertArrayContains('Friday', $aWeekDays);
        $this->assertArrayNotContains('Saturday', $aWeekDays);
        $this->assertArrayNotContains('Sunday', $aWeekDays);
    }

    public function test_addDepartment() : void
    {
        $oDepartment = new JsonLDLocalBusiness('Organization', true);
        $oDepartment->setURL('https://www.mydomain.de/outdoor');

        $this->oJsonLD->addDepartment($oDepartment);
        $aDepartments = $this->oJsonLD->getObject()["department"];
        $this->assertIsArray($aDepartments);
        $this->assertEquals(1, count($aDepartments));
        $this->assertEquals('Organization', $aDepartments[0]['@type']);
        $this->assertEquals('https://www.mydomain.de/outdoor', $aDepartments[0]['url']);
    }
}
