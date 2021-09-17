<?php
declare(strict_types=1);

namespace SKien\Test\JsonLD;

use SKien\JsonLD\JsonLD;

class JsonLDTest extends JsonLDTestCase
{
    public function setUp() : void
    {
        $this->oJsonLD = new JsonLD(JsonLD::ARTICLE, 'Article');
    }
    public function test__construct() : void
    {
        $this->assertIsObject($this->oJsonLD);
    }

    public function test_setDescription() : void
    {
        $this->oJsonLD->setDescription('My description');
        $this->assertEquals('My description', $this->oJsonLD->getObject()['description']);
    }

    public function test_addImage() : void
    {
        $strURL = 'http://localhost/packages/JsonLD/elephpant.png';
        $this->oJsonLD->addImage($strURL);

        $aImage = $this->oJsonLD->getObject()['image'];
        $this->assertIsArray($aImage);
        $this->assertEquals('ImageObject', $aImage['@type']);
        $this->assertEquals($strURL, $aImage['url']);
        $this->assertEquals(133, $aImage['width']);
        $this->assertEquals(117, $aImage['height']);

        $this->oJsonLD->addImage($strURL);

        $aImages = $this->oJsonLD->getObject()['image'];

        $this->assertEquals(2, count($aImages));
        $aImage = $aImages[1];

        $this->assertIsArray($aImage);
        $this->assertEquals('ImageObject', $aImage['@type']);
        $this->assertEquals($strURL, $aImage['url']);
        $this->assertEquals(133, $aImage['width']);
        $this->assertEquals(117, $aImage['height']);
    }

    public function test_setLocation() : void
    {
        $this->oJsonLD->setLocation('Location 1', 1.23456, 34.56789);
        $aLocation = $this->oJsonLD->getObject()['location'];
        $this->assertIsArray($aLocation);

        $this->assertArrayHasKey('@type', $aLocation);
        $this->assertEquals('Place', $aLocation['@type']);
        $this->assertArrayHasKey('geo', $aLocation);
        $this->assertIsArray($aLocation['geo']);
        $this->assertEquals('GeoCoordinates', $aLocation['geo']['@type']);
        $this->assertEquals('1.23456', $aLocation['geo']['latitude']);
        $this->assertEquals('34.56789', $aLocation['geo']['longitude']);

        $this->oJsonLD->setLocation('Location 1', '', '', 'https://www.mymap.de');
        $aLocation = $this->oJsonLD->getObject()['location'];
        $this->assertArrayHasKey('hasMap', $aLocation);
        $this->assertEquals('https://www.mymap.de', $aLocation['hasMap']);
    }

    public function test_getObject() : void
    {
        $this->assertIsArray($this->oJsonLD->getObject());
    }

    public function test_getHTMLHeadTag() : void
    {
        $this->assertNotEmpty($this->oJsonLD->getHTMLHeadTag());
    }

    public function test_getJson() : void
    {
        $this->assertNotEmpty($this->oJsonLD->getJson());
    }

    public function test_getJsonFail() : void
    {
        $this->oJsonLD->setProperty('test', utf8_decode('Umlauts (ä, ö, ü) are not allowed in JSON'));
        $this->assertEmpty($this->oJsonLD->getJson());
    }

    public function propertyProvider() : array
    {
        return [
            'JsonLD::STRING' => ['string', JsonLD::STRING, 'string'],
            'JsonLD::DATE' => [new \DateTime('2021-09-11'), JsonLD::DATE, '2021-09-11'],
            'JsonLD::TIME' => ['20:00', JsonLD::TIME, '20:00'],
            'JsonLD::EMAIL' => ['s.kientzler@online.de', JsonLD::EMAIL, 's.kientzler@online.de'],
            'JsonLD::URL' => ['http://www.s-kien.de', JsonLD::URL, 'http://www.s-kien.de'],
            'JsonLD::LONG_LAT' => [1.234567, JsonLD::LONG_LAT, '1.234567'],
        ];
    }

    /**
     * @dataProvider propertyProvider
     */
    public function test_setProperty($value, int $iType, string $strExpected) : void
    {
        $this->oJsonLD->setProperty('test', $value, $iType);
        $this->assertEquals($strExpected, $this->oJsonLD->getObject()['test']);
    }

    public function test_validString() : void
    {
        $rflMethod = $this->getProtectedMethod('validString');
        $strTest = $rflMethod->invokeArgs($this->oJsonLD, ["\"test1\rtest2\r\n"]);
        $this->assertEquals("'test1\ntest2\n", $strTest);
    }

    public function longlatProvider() : array
    {
        return [
            'number' => [1.23456, '1.23456'],
            'string' => ['1.23456', '1.23456'],
        ];
    }

    /**
     * @dataProvider longlatProvider
     */
    public function test_validLongLat($longlat, string $strExpected) : void
    {
        $rflMethod = $this->getProtectedMethod('validLongLat');
        $strTest = $rflMethod->invokeArgs($this->oJsonLD, [$longlat]);
        $this->assertEquals($strExpected, $strTest);
    }

    public function dateProvider() : array
    {
        return [
            'string' => ['2021-09-11', '2021-09-11'],
            'DateTime' => [new \DateTime('2021-09-11'), '2021-09-11'],
            'timestamp' => [1631277724, '2021-09-10T14:42:04+0200'],
            'string 2' => ['12.08.2021', '2021-08-12'],
            'invalid string' => ['invalid', ''],
        ];
    }

    /**
     * @dataProvider dateProvider
     */
    public function test_validDate($date, string $strExpected) : void
    {
        $rflMethod = $this->getProtectedMethod('validDate');
        $strTest = $rflMethod->invokeArgs($this->oJsonLD, [$date]);
        $this->assertEquals($strExpected, $strTest);
    }

    public function timeProvider() : array
    {
        return [
            'string' => ['9:00', '09:00'],
            'invalid string 1' => ['9:00:00', ''],
            'invalid string 2' => ['9 Uhr', ''],
        ];
    }

    /**
     * @dataProvider timeProvider
     */
    public function test_validTime($time, string $strExpected) : void
    {
        $rflMethod = $this->getProtectedMethod('validTime');
        $strTest = $rflMethod->invokeArgs($this->oJsonLD, [$time]);
        $this->assertEquals($strExpected, $strTest);
    }

    public function urlProvider() : array
    {
        return [
            'valid' => ['http://www.s-kien.de', 'http://www.s-kien.de'],
            'empty' => ['', ''],
            'invalid 1' => ['s-kien', ''],
            'invalid 2' => ['s-kien.de', ''],
            'invalid 3' => ['www.s-kien.de', ''],
        ];
    }

    /**
     * @dataProvider urlProvider
     */
    public function test_validUrl($url, string $strExpected) : void
    {
        $rflMethod = $this->getProtectedMethod('validUrl');
        if (strlen($strExpected) == 0 && strlen($url) > 0) {
            $this->expectError();
            $rflMethod->invokeArgs($this->oJsonLD, [$url]);
        } else {
            $strTest = $rflMethod->invokeArgs($this->oJsonLD, [$url]);
            $this->assertEquals($strExpected, $strTest);
        }
    }

    public function emailProvider() : array
    {
        return [
            'valid' => ['s.kientzler@online.de', 's.kientzler@online.de'],
            'empty' => ['', ''],
            'invalid 1' => ['s.kientzler@online', ''],
            'invalid 2' => ['s.kientzler@online@xy', ''],
            'invalid 3' => ['s.kientzler-at-online.de', ''],
        ];
    }

    /**
     * @dataProvider emailProvider
     */
    public function test_validEMail($email, string $strExpected) : void
    {
        $rflMethod = $this->getProtectedMethod('validEMail');
        if (strlen($strExpected) == 0 && strlen($email) > 0) {
            $this->expectError();
            $rflMethod->invokeArgs($this->oJsonLD, [$email]);
        } else {
            $strTest = $rflMethod->invokeArgs($this->oJsonLD, [$email]);
            $this->assertEquals($strExpected, $strTest);
        }
    }

    public function truncateProvider() : array
    {
        return [
            'test 1' => ['text with nothing to truncate', 50, false, 'text with nothing to truncate'],
            'test 2' => ['text to be truncated here', 24, false, 'text to be truncated...'],
            'test 3' => ['text to be truncated here', 26, false, 'text to be truncated...'],
            'test 4' => ['text to be truncated hard', 26, true, 'text to be truncated ha...'],
        ];
    }

    /**
     * @dataProvider truncateProvider
     */
    public function test_strTruncateEllipsis(string $strTest, int $iLen, bool $bHardbreak, string $strExpected) : void
    {
        $rflMethod = $this->getProtectedMethod('strTruncateEllipsis');
        $strTest = $rflMethod->invokeArgs($this->oJsonLD, [$strTest, $iLen, $bHardbreak]);
        $this->assertEquals($strExpected, $strTest);
    }

    public function test_buildContactPoint() : void
    {
        $rflMethod = $this->getProtectedMethod('buildContactPoint');
        $aContact = $rflMethod->invokeArgs($this->oJsonLD, ['Info', 'Info e-Mail', 'Info phone']);

        $this->assertIsArray($aContact);
        $this->assertEquals('ContactPoint', $aContact['@type']);
        $this->assertEquals('Info', $aContact['contactType']);
        $this->assertEquals('Info e-Mail', $aContact['email']);
        $this->assertEquals('Info phone', $aContact['telephone']);
    }
}
