<?php
declare(strict_types=1);

namespace SKien\Test\JsonLD;

use SKien\JsonLD\JsonLDEvent;


class JsonLDEventTest extends JsonLDTestCase
{
    public function setUp() : void
    {
        $this->oJsonLD = new JsonLDEvent();
    }

    public function test__construct() : void
    {
        $this->assertIsObject($this->oJsonLD);
    }

    public function test_setInfo() : void
    {
        $this->oJsonLD->setInfo('My name', new \DateTime('2020-06-12 18:30'), new \DateTime('2020-06-12 22:00'));

        $aJsonLD = $this->oJsonLD->getObject();
        $this->assertIsArray($aJsonLD);
        $this->assertEquals('My name', $aJsonLD['name']);
        $this->assertEquals('2020-06-12T18:30:00+0200', $aJsonLD['startDate']);
        $this->assertEquals('2020-06-12T22:00:00+0200', $aJsonLD['endDate']);
    }

    public function test_setAddress() : void
    {
        $this->oJsonLD->setAddress('My street', 'My postcode', 'My city', 'My region', 'My country');

        $aAddress = $this->oJsonLD->getObject()['location']['address'];
        $this->assertIsArray($aAddress);

        $this->assertEquals('PostalAddress', $aAddress['@type']);
        $this->assertEquals('My street', $aAddress['streetAddress']);
        $this->assertEquals('My postcode', $aAddress['postalCode']);
        $this->assertEquals('My city', $aAddress['addressLocality']);
        $this->assertEquals('My region', $aAddress['addressRegion']);
        $this->assertEquals('My country', $aAddress['addressCountry']);
    }

    public function test_setVirtualLocation() : void
    {
        $this->oJsonLD->setVirtualLocation('http://www.my-channel.de/event');
        $this->assertEquals('https://schema.org/OnlineEventAttendanceMode', $this->oJsonLD->getObject()['eventAttendanceMode']);
        $aLocation = $this->oJsonLD->getObject()['location'];
        $this->assertIsArray($aLocation);
        $this->assertEquals('http://www.my-channel.de/event', $aLocation['url']);
    }

    public function test_setMixedLocation() : void
    {
        $this->oJsonLD->setLocation('The location', 1.23456, 34.56789);
        $this->oJsonLD->setVirtualLocation('http://www.my-channel.de/event');
        $this->assertEquals('https://schema.org/MixedEventAttendanceMode', $this->oJsonLD->getObject()['eventAttendanceMode']);
        $aLocation = $this->oJsonLD->getObject()['location'];
        $this->assertIsArray($aLocation);
        $this->assertEquals(2, count($aLocation));
        $this->assertEquals('http://www.my-channel.de/event', $aLocation[0]['url']);
        $this->assertEquals('The location', $aLocation[1]['name']);
    }

    public function statusProvider() : array
    {
        return [
            'valid1' => [JsonLDEvent::EVENT_SCHEDULED],
            'valid2' => [JsonLDEvent::EVENT_CANCELLED],
            'valid3' => [JsonLDEvent::EVENT_MOVED_ONLINE],
            'valid4' => [JsonLDEvent::EVENT_POSTPONED],
        ];
    }

    /**
     * @dataProvider statusProvider
     */
    public function test_setStatus(string $strStatus) : void
    {
        $this->oJsonLD->setStatus($strStatus, '2021-09-07');
        $this->assertEquals('https://schema.org/' . $strStatus, $this->oJsonLD->getObject()['eventStatus']);
        $this->assertArrayNotHasKey('previousStartDate', $this->oJsonLD->getObject());
    }

    public function test_setStatusInvalid() : void
    {
        $strStatus = $this->oJsonLD->getObject()['eventStatus'];
        $this->oJsonLD->setStatus('invalid');
        $this->assertEquals($strStatus, $this->oJsonLD->getObject()['eventStatus']);
    }

    public function test_setStatusRescheduled() : void
    {
        $this->oJsonLD->setStatus(JsonLDEvent::EVENT_RESCHEDULED, '2021-09-07');
        $this->assertEquals('2021-09-07', $this->oJsonLD->getObject()['previousStartDate']);
    }

    public function test_addOffer() : void
    {
        $this->oJsonLD->addOffer('Seat', 55, 'EUR', JsonLDEvent::AVAILABLE_IN_STOCK, '2021-09-12', 'https://www.my-page.de/order.php?type=seat');
        $this->oJsonLD->addOffer('Loge', 92, 'EUR', JsonLDEvent::AVAILABLE_PRE_ORDER, '2021-09-12', 'https://www.my-page.de/order.php?type=loge');
        $this->oJsonLD->addOffer('Loge', 92, 'EUR', 'invalid', '2021-09-12', 'https://www.my-page.de/order.php?type=loge');

        $aOffers = $this->oJsonLD->getObject()['offers'];
        $this->assertIsArray($aOffers);
        $this->assertEquals(2, count($aOffers));

        $this->assertEquals('Offer', $aOffers[0]['@type']);
        $this->assertEquals('Seat', $aOffers[0]['name']);
        $this->assertEquals(55, $aOffers[0]['price']);
        $this->assertEquals('EUR', $aOffers[0]['priceCurrency']);
        $this->assertEquals('https://schema.org/' . JsonLDEvent::AVAILABLE_IN_STOCK, $aOffers[0]['availability']);
        $this->assertEquals('2021-09-12', $aOffers[0]['validFrom']);
        $this->assertEquals('https://www.my-page.de/order.php?type=seat', $aOffers[0]['url']);

        $this->assertEquals('Offer', $aOffers[1]['@type']);
        $this->assertEquals('Loge', $aOffers[1]['name']);
        $this->assertEquals(92, $aOffers[1]['price']);
        $this->assertEquals('EUR', $aOffers[1]['priceCurrency']);
        $this->assertEquals('https://schema.org/' . JsonLDEvent::AVAILABLE_PRE_ORDER, $aOffers[1]['availability']);
        $this->assertEquals('2021-09-12', $aOffers[1]['validFrom']);
        $this->assertEquals('https://www.my-page.de/order.php?type=loge', $aOffers[1]['url']);
    }

    public function test_setOrganizer() : void
    {
        $this->oJsonLD->setOrganizer('The organizer', 'https://www.my-page.de/about', JsonLDEvent::ORGANIZATION);
        $aOrg = $this->oJsonLD->getObject()['organizer'];
        $this->assertEquals(JsonLDEvent::ORGANIZATION, $aOrg['@type']);
        $this->assertEquals('The organizer', $aOrg['name']);
        $this->assertEquals('https://www.my-page.de/about', $aOrg['url']);
    }

    public function test_setOrganizerInvalid() : void
    {
        $this->oJsonLD->setOrganizer('The organizer', 'https://www.my-page.de/about', 'invalid');
        $this->assertArrayNotHasKey('organizer', $this->oJsonLD->getObject());
    }

    public function test_addPerformer() : void
    {
        $this->oJsonLD->addPerformer('The group', 'https://www.my-page.de/event/1', JsonLDEvent::GROUP);
        $this->oJsonLD->addPerformer('Solist', 'https://www.my-page.de/event/2', JsonLDEvent::PERSON);
        $this->oJsonLD->addPerformer('Seat', 'https://www.my-page.de/event/1', 'invalid');

        $aPerf = $this->oJsonLD->getObject()['performer'];
        $this->assertIsArray($aPerf);
        $this->assertEquals(2, count($aPerf));

        $this->assertEquals(JsonLDEvent::GROUP, $aPerf[0]['@type']);
        $this->assertEquals('The group', $aPerf[0]['name']);
        $this->assertEquals('https://www.my-page.de/event/1', $aPerf[0]['url']);

        $this->assertEquals(JsonLDEvent::PERSON, $aPerf[1]['@type']);
        $this->assertEquals('Solist', $aPerf[1]['name']);
        $this->assertEquals('https://www.my-page.de/event/2', $aPerf[1]['url']);
    }
}
