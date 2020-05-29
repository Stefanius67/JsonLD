<?php
namespace SKien\JsonLD;

/**
 * With Local Business structured data, you can tell Google about your 
 * business hours, different departments within a business, reviews for 
 * your business, and more.
 *
 * {@link https://developers.google.com/search/docs/data-types/local-business}
 * 
 * {@link https://schema.org/Organization}
 * 
 * {@link https://schema.org/LocalBusiness}
 * 
 * ### required properties:
 *  - @id               ({@link JsonLDLocalBusiness::setURL()})
 *  - PostalAddress     ({@link JsonLDLocalBusiness::setAdress()})
 *  - name              ({@link JsonLDLocalBusiness::setInfo()})
 *  - telephone
 * 
 * ### recommended properties:
 *  - url 
 *  - geolocation      
 *  - priceRange
 *  - openingHours
 *  - servesCuisine (recommended for 'FoodEstablishment' and subtypes of it)
 * 
 * ### additional poroperties
 * 
 * #### aggregateRating
 * The average rating of the local company, based on multiple ratings and 
 * reviews. Review the guidelines for review snippets and the list of required 
 * and recommended attributes for overall ratings.
 *                  
 * #### department(s)
 * (Type: nested LocalBusiness)
 * Nested element(s) for department(s). In this table you can define any 
 * properties for a department.
 * Note: The id MUST differ from the id of the main business! 
 * Additional guidelines: Enter the business name with the department name 
 * in the following format: 
 *        {store name} {department name} 
 * Example: 
 *        gMart and gMart Pharmacy. 
 * 
 * #### menu-card URL
 * The fully qualified URL of the menu (for 'FoodEstablishment' and subtypes of it)
 * 
 * #### review
 * A review of the local company. Please refer to the guidelines for review 
 * snippets and the list of required and recommended properties for reviews.
 * 
 * 
 * ### History
 * ** 2020-05-25 **
 * - initial version.
 * 
 * @package SKien-JsonLD
 * @since 1.0.0
 * @version 1.0.0
 * @author Stefanius <s.kien@online.de>
 * @copyright MIT License - see the LICENSE file for details
 */
class JsonLDLocalBusiness extends JsonLD
{
    /**
     * Initializes a JsonLD object for local business.
     * Current host is set as URL and internal @id. In most cases this setting
     * should be correct. If different information is required, this setting 
     * can be changed using the setURL() method.
     * 
     * Valid values for 'strType' can be found in {@link DocJsonLDLocalBusinessType}
     * 
     * @param string $strType
     * 
     * @see DocJsonLDLocalBusinessType
     */
    public function __construct($strType='Organization', $bIsChild=false)
    {
        parent::__construct(self::LOCAL_BUSINESS, $strType, $bIsChild);
        if (!$bIsChild) {
            $this->aJsonLD["@id"] = $_SERVER['HTTP_HOST'];
        }
        $this->aJsonLD["url"] = $_SERVER['HTTP_HOST'];
    }

    /**
     * Set URL and id of the page.
     * Empty $strId will be set to $strURL. In the constructor the URL and Id is set 
     * to the current host. Deviating values must most case only be set for departments 
     * or if it is a special subpage of an Internet presence that is treated 
     * differently from the main page.
     *  
     * @param string $strURL
     * @param string $strId
     */
    public function setURL($strURL, $strId='')
    {
        $strURL = $this->validURL($strURL);
        $strId = $this->validString($strId);
        if (strlen($strURL) > 0) {
            if (!$this->bIsChild || strlen($strId) > 0) {
                $this->aJsonLD["@id"] = strlen($strId) == 0 ? $strURL : $strId;
            }
            $this->aJsonLD["url"] = $strURL;
        } else {
            trigger_error('invalid URL for Json-LD: ' . $strURL, E_USER_WARNING);
        }
    }
    
    /**
     * Set base informations about the organisation.
     * @param string $strName       mandatory property
     * @param string $strEMail      recommended property
     * @param string $strPhone      recommended property
     */
    public function setInfo($strName, $strEMail='', $strPhone='')
    {
        $strEMail = $this->validEMail($strEMail);
        $strName = $this->validString($strName);
        if (strlen($strName) > 0) {
            $this->aJsonLD["name"] = $this->validString($strName);
            if (strlen($strEMail) > 0) {
                $this->aJsonLD["email"] = $strEMail;
            }
            $strPhone = $this->validString($strPhone);
            if (strlen($strPhone) > 0) {
                $this->aJsonLD["telephone"] = $strPhone;
            }
        } else {
            trigger_error('invalid Name for Json-LD: ' . $strName, E_USER_WARNING);
        }
    }

    /**
     * Set postal adress of the business.
     * Here it makes sense to enter as many properties as possible. The more you 
     * specify, the more informative the result will be for users.
     * 
     * @param string $strStreet
     * @param string $strPostcode
     * @param string $strCity
     * @param string $strRegion     (default: '')
     * @param string $strCountry    (default: '')
     */
    public function setAdress($strStreet, $strPostcode, $strCity, $strRegion='', $strCountry='') 
    {
        $this->aJsonLD["address"] = $this->buildAdress($strStreet, $strPostcode, $strCity, $strRegion, $strCountry);
    }

    /**
     * Set the logo of the organization.
     * @param string $strLogoURL
     */
    public function setLogo($strLogoURL)
    {
        $aLogo = $this->buildImageObject($strLogoURL);
        if ($aLogo != null) {
            $this->aJsonLD["logo"] = $aLogo;
        }
    }
    
    /**
     * Used by 'LocalBusinesses' and all subtypes of it.
     * If you look closely, plain text is ambiguous in this context...
     * Not really found any good explanation, how to use this property - anyway, google
     * mark it as recomended for 'LocalBusinesses' - just set it to some value ('$', ...).
     * 
     * @param string $strPriceRange
     */
    public function setPriceRange($strPriceRange)
    {
        $this->setProperty("priceRange", $strPriceRange);
    }
    
    /**
     * Recommended for 'FoodEstablishment' and subtypes of it.
     * @param string $strServesCuisine
     */
    public function setServesCuisine($strServesCuisine)
    {
        $this->setProperty("servesCuisine", $strServesCuisine);
    }

    /**
     * Short method to set opening hours.
     * Only set string like i.e. 'Mo.-Fr. 08:00-12:00 13:00-17:30'.
     * Don't use together with extended version addOpeningHours()!
     * @param string $strOpeningHours
     */
    public function setOpeningHours($strOpeningHours)
    {
        //  "openingHours": "Mo 09:00-12:00 We 12:00-17:00",
        $this->setProperty("openingHours", $strOpeningHours);
    }
    
    /**
     * Add valid language. 
     * @param string $strLang    language in IETF BCP 47 format
     */
    public function addLanguage($strLang)
    {
        if (!isset($this->aJsonLD["knowsLanguage"])) {
            $this->aJsonLD["knowsLanguage"] = array();
        }
        $this->aJsonLD["knowsLanguage"][] = $strLang;
    }

    /**
     * Add contact to the Object.
     * @param string $strType
     * @param string $strEMail
     * @param string $strPhone
     * @return int  index of the added contact point
     */
    public function addContact($strType, $strEMail='', $strPhone='')
    {
        $iIndex = -1;
        $aCP = $this->buildContactPoint($strType, $strEMail, $strPhone);
        if ($aCP != null) {
            if (!isset($this->aJsonLD["contactPoint"])) {
                $this->aJsonLD["contactPoint"] = array();
            }
            $iIndex = count($this->aJsonLD["contactPoint"]);
            $this->aJsonLD["contactPoint"][] = $aCP;
        }
        return $iIndex;
    }

    /**
     * Add language to contact.
     * @param int $iContact
     * @param string $strLang   language in IETF BCP 47 format
     */
    public function addContactLanguage($iContact, $strLang)
    {
        if (isset($this->aJsonLD["contactPoint"]) && $iContact < count($this->aJsonLD["contactPoint"])) {
            if (!isset($this->aJsonLD["contactPoint"][$iContact]["availableLanguage"])) {
                $this->aJsonLD["contactPoint"][$iContact]["availableLanguage"] = array();
            }
            $this->aJsonLD["contactPoint"][$iContact]["availableLanguage"][] = $strLang;
        }
    }

    /**
     * Add URL of a reference Web page that unambiguously indicates the organizations
     * identity. E.g. the URL of the organizations social media page(s)
     * - facebook
     * - twitter
     * - instagramm
     * - wikipedia
     * - ...
     * @param string $strURL
     */
    public function addSameAs($strURL)
    {
        $strURL = $this->validURL($strURL);
        if (strlen($strURL) > 0) {
            if (!isset($this->aJsonLD["sameAs"])) {
                $this->aJsonLD["sameAs"] = array();
            }
            $this->aJsonLD["sameAs"][] = $strURL;
        }
    }
    
    /**
     * Extended method to set opening hours.
     * Multiple definitions may be set.
     * i.e.: 
     * Mo-Fr         8:00...12:00
     * Mo,Tu,Fr     13:00...17:30
     * 
     * addOpeningHours([1,1,1,1,1,0,0],  '8:00', '12:00');
     * addOpeningHours([1,1,0,0,1,0,0], '13:00', '17:30');
     * 
     * Don't use together with short version setOpeningHours()!
     * @todo openingHoursSpecification.validFrom / openingHoursSpecification.validThrough
     * 
     * @param array $aWeekdays      array containing 7 elements for each weekday (0-> Monday)
     * @param string $timeOpens     time opens
     * @param string $timeCloses    time closes
     * @return int  index of the added opening hours specification
     */
    public function addOpeningHours($aWeekdays, $timeOpens, $timeCloses)
    {
        $iIndex = -1;
        $timeOpens = $this->validTime($timeOpens);
        $timeCloses = $this->validTime($timeCloses);
        if (count($aWeekdays) == 7 && strlen($timeOpens) > 0 && strlen($timeCloses) > 0) {
            if (!isset($this->aJsonLD["location"])) {
                $this->aJsonLD["location"] = array("@type" => "Place");
            }
            if (!isset($this->aJsonLD["location"]["openingHoursSpecification"])) {
                $this->aJsonLD["location"]["openingHoursSpecification"] = array();
            }
            $iIndex = count($this->aJsonLD["location"]["openingHoursSpecification"]);
            $aOHS = array("@type" => "OpeningHoursSpecification");
            $aDayOfWeek = array("Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunnday");
            for ($i = 0; $i < 7; $i++) {
                if ($aWeekdays[$i] != 1) {
                    unset($aDayOfWeek[$i]);
                } 
            }
            $aDayOfWeek = array_values($aDayOfWeek);
            $aOHS["dayOfWeek"] = $aDayOfWeek;
            $aOHS["opens"] = $timeOpens;
            $aOHS["closes"] = $timeCloses;
            $this->aJsonLD["location"]["openingHoursSpecification"][] = $aOHS;
        }
        return $iIndex;
    }
    
    /**
     * Add department to the object.
     * Create for each department separate LocalBusiness object.
     * !!! The id for the department MUST be set manually and MUST differ 
     * from the id of the main object! 
     * @param JsonLDLocalBusiness $oDepartment
     */
    public function addDepartment($oDepartment)
    {
        $aDepartment = $oDepartment->getObject();
        if ($aDepartment != null) {
            if (!isset($this->aJsonLD["department"])) {
                $this->aJsonLD["department"] = array();
            }
            $this->aJsonLD["department"][] = $aDepartment;
        }
    }
}
