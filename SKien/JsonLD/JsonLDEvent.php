<?php
declare(strict_types=1);

namespace SKien\JsonLD;

/**
 * Adding structured data to any of your event pages.
 *
 * ### Avoid marking non-events as events:
 *  - Don’t promote non-event products or services such as
 *      "Trip package: San Diego/LA, 7 nights" as events.
 *  - Don’t add short-term discounts or purchase opportunities, such as:
 *      "Concert — buy your tickets now," or "Concert - 50% off until Saturday."
 *  - Don’t mark business hours as events, such as:
 *      "Adventure park open 8 AM to 5PM."
 *  - Don't mark coupons or vouchers as events, such as:
 *      "5% off your first order."
 *
 * ### Mark up multi-day events correctly:
 *  - If your event or ticket info is for an event that runs over several
 *    days, specify both the start and end dates of the event.
 *  - If there are several different performances across different days, each
 *    with individual tickets, add a separate Event element for each performance.
 *
 * ### required properties:
 *  - name
 *  - startdate
 *  - location
 *
 * ### recomended properties:
 *  - enddate
 *  - eventAttendanceMode (will be set automaticaly)
 *  - eventStatus
 *  - image
 *  - description
 *  - offers
 *  - organizer
 *  - performer
 *
 * @link https://developers.google.com/search/docs/data-types/event
 * @link https://schema.org/Event
 *
 * @package JsonLD
 * @author Stefanius <s.kientzler@online.de>
 * @copyright MIT License - see the LICENSE file for details
 */
class JsonLDEvent extends JsonLD
{
    /** The event is scheduled (default value for the status) */
    public const EVENT_SCHEDULED     = 'EventScheduled';
    /** The event is cancelled */
    public const EVENT_CANCELLED     = 'EventCancelled';
    /** The event moved to online event */
    public const EVENT_MOVED_ONLINE  = 'EventMovedOnline';
    /** The event is postponed */
    public const EVENT_POSTPONED     = 'EventPostponed';
    /** The event is re-scheduled */
    public const EVENT_RESCHEDULED   = 'EventRescheduled';

    /** Tickets available in stock  */
    public const AVAILABLE_IN_STOCK  = 'InStock';
    /** No more tickets of this categorie available  */
    public const AVAILABLE_SOLD_OUT  = 'SoldOut';
    /** Tickets can be pre ordered  */
    public const AVAILABLE_PRE_ORDER = 'PreOrder';

    /** Event is performed/organized by person(s) */
    public const ORGANIZATION    = 'Organization';
    /** Event is performed/organized by group */
    public const GROUP           = 'PerformingGroup';
    /** Event is performed/organized by person(s) */
    public const PERSON          = 'Person';

    /**
     * Initializes a JsonLD object for article.
     */
    public function __construct()
    {
        parent::__construct(self::EVENT, 'Event');
        $this->aJsonLD["eventAttendanceMode"] = 'https://schema.org/OfflineEventAttendanceMode';
        $this->aJsonLD["eventStatus"] = 'https://schema.org/EventScheduled';
    }

    /**
     * Set the main infos for the event.
     * @param string $strName
     * @param mixed $start      can be string (format YYYY-MM-DD HH:ii:ss), int (unixtimestamp) or DateTime - object
     * @param mixed $end        can be string (format YYYY-MM-DD HH:ii:ss), int (unixtimestamp) or DateTime - object
     */
    public function setInfo(string $strName, $start, $end = null) : void
    {
        $strName = $this->validString($strName);
        if (strlen($strName) > 0) {
            $this->aJsonLD["name"] = $strName;
            if ($start != null) {
                $this->aJsonLD["startDate"] = $this->validDate($start);
            }
            if ($end != null) {
                $this->aJsonLD["endDate"] = $this->validDate($end);
            }
        }
    }

    /**     * Set postal adress of the business.
     * Here it makes sense to enter as many properties as possible. The more you
     * specify, the more informative the result will be for users.
     *
     * @param string $strStreet
     * @param string $strPostcode
     * @param string $strCity
     * @param string $strRegion     (default: '')
     * @param string $strCountry    (default: '')
     */
    public function setAdress(string $strStreet, string $strPostcode, string $strCity, string $strRegion = '', string $strCountry = '') : void
    {
        $aAddress = $this->buildAdress($strStreet, $strPostcode, $strCity, $strRegion, $strCountry);
        if ($aAddress != null) {
            if (!isset($this->aJsonLD["location"]) || !is_array($this->aJsonLD["location"])) {
                $this->aJsonLD["location"] = array("@type" => "Place");
            }
            $this->aJsonLD["location"]["address"] = $aAddress;
        }
    }

    /**
     * Set the virlual location of the event.
     * If the event is happening online, use this method instead of setLocation() / setAdress().
     * If an event has a mix of online and physical location components, FIRST call
     * setLocation() / setAdress() for the physical component and THEN call setVirtualLocation().
     * @param string $strURL
     */
    public function setVirtualLocation(string $strURL) : void
    {
        $strURL = $this->validURL($strURL);
        if (strlen($strURL) > 0) {
            $aVirtualLocation = array('@type' => 'VirtualLocation', 'url' => $strURL);
            if (isset($this->aJsonLD["location"]) && is_array($this->aJsonLD["location"])) {
                $aLocation = $this->aJsonLD["location"];
                $this->aJsonLD["location"] = array();
                $this->aJsonLD["location"][] = $aVirtualLocation;
                $this->aJsonLD["location"][] = $aLocation;
                $this->aJsonLD["eventAttendanceMode"] = 'https://schema.org/MixedEventAttendanceMode';
            } else {
                $this->aJsonLD["location"] = $aVirtualLocation;
                $this->aJsonLD["eventAttendanceMode"] = 'https://schema.org/OnlineEventAttendanceMode';
            }
        }
    }

    /**
     * Set the staus of the event.
     * Valid values are one of
     * - self::EVENT_SCHEDULED
     * - self::EVENT_CANCELLED
     * - self::EVENT_MOVED_ONLINE
     * - self::EVENT_POSTPONED
     * - self::EVENT_RESCHEDULED
     * If the event has been canceled or postponed, don't remove or change other
     * properties (for example, don't remove startDate or location) instead, keep all
     * values as the same as they were before the cancelation, and only update the
     * eventStatus.
     * If the event has been postponed to a later date, but the date isn't known yet,
     * Keep the original date in the startDate of the event until you know when the
     * event will take place. Once you know the new date information, change the
     * eventStatus to EventRescheduled and update the startDate and endDate with the
     * new date information. Optionally, you can also mark the eventStatus field as
     * rescheduled and add the previousStartDate.
     * @param string $strStatus
     * @param mixed $prevstart      can be string (format YYYY-MM-DD HH:ii:ss), int (unixtimestamp) or DateTime - object
     */
    public function setStatus(string $strStatus, $prevstart = null) : void
    {
        $aValid = array('EventScheduled', 'EventCancelled', 'EventMovedOnline', 'EventPostponed', 'EventRescheduled');
        if (in_array($strStatus, $aValid)) {
            $this->aJsonLD["eventStatus"] = 'https://schema.org/' . $strStatus;
        }
        if ($prevstart != null && $strStatus == 'EventRescheduled') {
            $this->aJsonLD["previousStartDate"] = $this->validDate($prevstart);
        }
    }

    /**
     * Add offer for tickets to the event.
     * Valid values for $strAvailable are one of
     * - self::AVAILABLE_IN_STOCK
     * - self::AVAILABLE_SOLD_OUT
     * - self::AVAILABLE_PRE_ORDER
     * @param string    $strName          Description or categorie of the ticket
     * @param float     $dftPrice         Price (should include service charges and fees)
     * @param string    $strCur           The 3-letter currency code.
     * @param string    $strAvailable     Availability
     * @param mixed     $validFrom        Offer valid from, may be string (format YYYY-MM-DD HH:ii:ss), int (unixtimestamp) or DateTime - object
     * @param string    $strURL           landing page that clearly and predominantly provides the opportunity to buy a ticket
     */
    public function addOffer(string $strName, float $dftPrice, string $strCur, $strAvailable = self::AVAILABLE_IN_STOCK, $validFrom = null, string $strURL = '') : void
    {
        $aValid = array('InStock', 'SoldOut', 'PreOrder');
        if (in_array($strAvailable, $aValid)) {
            $strName = $this->validString($strName);
            $strCur =  strtoupper($this->validString($strCur));
            $strURL = $this->validURL($strURL);
            if (!isset($this->aJsonLD['offers'])) {
                $this->aJsonLD['offers'] = array();
            }
            $aOffer = array('@type' => 'Offer');
            if (strlen($strName) > 0) {
                $aOffer['name'] = $strName;
            }
            if ($dftPrice > 0.0) {
                $aOffer['price'] = $dftPrice;
            }
            if (strlen($strCur) > 0) {
                $aOffer['priceCurrency'] = $strCur;
            }
            if ($validFrom != null) {
                $aOffer['validFrom'] = $this->validDate($validFrom);
            }
            if (strlen($strAvailable) > 0) {
                $aOffer['availability'] = 'https://schema.org/' . $strAvailable;
            }
            if (strlen($strURL) > 0) {
                $aOffer['url'] = $strURL;
            }
            $this->aJsonLD['offers'][] = $aOffer;
        }
    }

    /**
     * Set organizer of the event.
     * Valid values for $strType are one of
     * - self::GROUP
     * - self::PERSON
     * - self::ORGANIZATION
     * @param string $strName
     * @param string $strURL
     * @param string $strType
     */
    public function setOrganizer(string $strName, string $strURL = '', string $strType=self::ORGANIZATION) : void
    {
        $aValid = array('PerformingGroup', 'Person', 'Organization');
        $strName = $this->validString($strName);
        $strURL = $this->validURL($strURL);
        if (strlen($strName) > 0 && in_array($strType, $aValid)) {
            if (!isset($this->aJsonLD["organizer"])) {
                $this->aJsonLD["organizer"] = array("@type" => $strType);
            }
            $this->aJsonLD["organizer"]["name"] = $strName;
            if (strlen($strURL) > 0) {
                $this->aJsonLD["organizer"]["url"] = $strURL;
            }
        }
    }

    /**
     * Add performer of the event.
     * Valid values for $strType are one of
     * - self::GROUP
     * - self::PERSON
     * - self::ORGANIZATION
     * @param string $strName
     * @param string $strURL
     * @param string $strType
     */
    public function addPerformer(string $strName, string $strURL = '', string $strType = self::GROUP) : void
    {
        $aValid = array('PerformingGroup', 'Person', 'Organization');
        $strName = $this->validString($strName);
        $strURL = $this->validURL($strURL);
        if (strlen($strName) > 0 && in_array($strType, $aValid)) {
            if (!isset($this->aJsonLD["performer"])) {
                $this->aJsonLD["performer"] = array();
            }
            $aPerformer = array("@type" => $strType);
            $aPerformer["name"] = $strName;
            if (strlen($strURL) > 0) {
                $aPerformer["url"] = $strURL;
            }
            $this->aJsonLD["performer"][] = $aPerformer;
        }
    }
}
