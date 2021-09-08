<?php
declare(strict_types=1);

namespace SKien\JsonLD;

/**
 * Base class of the package.
 * The class provides all basic functions for generating valid JsonLD objects of
 * different types.
 *
 * @link https://search.google.com/structured-data/testing-tool
 * @link https://developers.google.com/search/docs/guides/sd-policies
 * @link https://www.w3.org/TR/json-ld11/
 *
 * @package JsonLD
 * @author Stefanius <s.kientzler@online.de>
 * @copyright MIT License - see the LICENSE file for details
 */
class JsonLD
{
    /** constants for JsonLD type */
    public const __TYPE = '';
    /** type: LocalBusiness or subtype  */
    public const LOCAL_BUSINESS = 0;
    /** type: Article, NewsArticle,   */
    public const ARTICLE = 1;
    /** type: Event   */
    public const EVENT = 2;

    /** constants for validation */
    public const __VALIDATION = '';
    /** validation for string   */
    public const STRING = 0;
    /** validation for date   */
    public const DATE = 1;
    /** validation for time   */
    public const TIME = 2;
    /** validation for e-mail   */
    public const EMAIL = 3;
    /** validation for url   */
    public const URL = 4;

    /** @var int    internal object type     */
    protected $iType = -1;
    /** @var array<mixed>  the linked data as array     */
    protected $aJsonLD = null;
    /** @var bool   object is nested into another object     */
    protected $bIsChild = false;

    /**
     * Instanciation of JsonLD object.
     * @param int $iType        internal type
     * @param string $strType   type for JsonLD
     * @param bool $bIsChild
     */
    public function __construct(int $iType, string $strType, bool $bIsChild = false)
    {
        $this->iType = $iType;
        $this->bIsChild = $bIsChild;
        $this->aJsonLD = [
            "@context" => "https://schema.org",
            "@type" => $strType,
        ];
    }

    /**
     * Set description text.
     * Usable for all JsonLD types.
     * @param string $strDescription
     */
    public function setDescription(string $strDescription) : void
    {
        $this->setProperty("description", $strDescription);
    }

    /**
     * Set location for the object.
     * @param string $strName           Name for the location
     * @param string|float $latitude    latidude
     * @param string|float $longitude   longitude
     * @param string $strMap            URL to a map that shows the location
     */
    public function setLocation(string $strName, $latitude, $longitude, string $strMap = '') : void
    {
        $aLocation = $this->buildLocation($strName, $latitude, $longitude, $strMap);
        if ($aLocation != null) {
            if (isset($this->aJsonLD["location"]) && is_array($this->aJsonLD["location"])) {
                $this->aJsonLD["location"] = array_merge($this->aJsonLD["location"], $aLocation);
            } else {
                $this->aJsonLD["location"] = $aLocation;
            }
        }
    }

    /**
     * Add an image.
     * Multiple images are supported. Only existing images can be set.
     * Pixel size (width/height) is detected from image file.
     * @param string $strImageURL
     */
    public function addImage(string $strImageURL) : void
    {
        $aImg = $this->buildImageObject($strImageURL);
        if ($aImg != null) {
            if (isset($this->aJsonLD["image"])) {
                if (isset($this->aJsonLD["image"]["@type"])) {
                    // only one image set so far... change to array for multiple images
                    $aFirstImg = $this->aJsonLD["image"];
                    $this->aJsonLD["image"] = array();
                    $this->aJsonLD["image"][] = $aFirstImg;
                }
                $this->aJsonLD["image"][] = $aImg;
            } else {
                // first image - set property direct
                $this->aJsonLD["image"] = $aImg;
            }
        }
    }

    /**
     * Build ImageObject property.
     * Logos and images are defiend as ImageObject.
     * @param string $strURL    URL to a valid image (PNG, GIF, JPG)
     * @return array<string>    array containing the property or null if invalid URL
     */
    protected function buildImageObject(string $strURL) : ?array
    {
        $aLogo = null;
        if (file_exists($strURL)) {
            $aSize = getimagesize($strURL);
            if ($aSize) {
                $aLogo = array(
                                "@type" => "ImageObject",
                                "url" =>  $strURL,
                                "width" => $aSize[0],
                                "height" => $aSize[1]
                );
            }
        }
        return $aLogo;
    }

    /**
     * Build a postal adress object.
     * @param string $strStreet
     * @param string $strPostcode
     * @param string $strCity
     * @param string $strRegion
     * @param string $strCountry
     * @return array<string>    array containing the property
     */
    protected function buildAdress(string $strStreet, string $strPostcode, string $strCity, string $strRegion = '', string $strCountry = '') : array
    {
        $aAdress = array("@type" => "PostalAddress");
        if (strlen($strStreet) > 0) {
            $aAdress["streetAddress"] = $this->validString($strStreet);
        }
        if (strlen($strPostcode) > 0) {
            $aAdress["postalCode"] = $this->validString($strPostcode);
        }
        if (strlen($strCity) > 0) {
            $aAdress["addressLocality"] = $this->validString($strCity);
        }
        if (strlen($strCountry) > 0) {
            $aAdress["addressCountry"] = $this->validString($strCountry);
        }
        if (strlen($strCountry) > 0) {
            $aAdress["addressRegion"] = $this->validString($strRegion);
        }
        return $aAdress;
    }

    /**
     * Build a location object.
     * @param string $strName       Name for the location
     * @param string|float $latitude
     * @param string|float $longitude
     * @param string $strMap        URL to map show the location
     * @return array<mixed>    array containing the property or null if invalid URL
     */
    protected function buildLocation(string $strName, $latitude, $longitude, string $strMap) : ?array
    {
        $aLocation = null;
        $latitude = $this->validLongLat($latitude);
        $longitude = $this->validLongLat($longitude);
        $strMap = $this->validURL($strMap);

        if ((strlen($latitude) > 0 && strlen($longitude) > 0) || strlen($strMap) > 0) {
            $aLocation = array("@type" => "Place");
            $strName = $this->validString($strName);
            if (strlen($strName) > 0) {
                $aLocation["name"] = $strName;
            }
            if (strlen($latitude) > 0 && strlen($longitude) > 0) {
                $aLocation['geo'] = array(
                                "@type" => "GeoCoordinates",
                                "latitude" => $latitude,
                                "longitude" => $longitude
                );
            }
            if (strlen($strMap) > 0) {
                $aLocation["hasMap"] = $strMap;
            }
        }
        return $aLocation;
    }

    /**
     * Build a contact point object.
     * The type must not contain any predefiend value, it is to describe the contact.
     * (i.e. 'Information', 'Hotline', 'Customer Service', 'Administration'...)
     * @param string $strType
     * @param string $strEMail
     * @param string $strPhone
     * @return array<string>    array containing the property or null if invalid URL
     */
    protected function buildContactPoint(string $strType, string $strEMail, string $strPhone) : ?array
    {
        $aCP = null;
        $strType = $this->validString($strType);
        $strEMail = $this->validString($strEMail);
        $strPhone = $this->validString($strPhone);
        if (strlen($strType) > 0) {
            $aCP = array("@type" => "ContactPoint");
            $aCP["contactType"] = $strType;
            if (strlen($strEMail) > 0) {
                $aCP["email"] = $strEMail;
            }
            if (strlen($strPhone) > 0) {
                $aCP["telephone"] = $strPhone;
            }
        }
        return $aCP;
    }

    /**
     * Set the property to value of given type.
     * @param string $strName
     * @param string $strValue
     * @param int $iType
     */
    public function setProperty(string $strName, string $strValue, int $iType = self::STRING) : void
    {
        switch ($iType) {
            case self::DATE:
                $strValue = $this->validDate($strValue);
                break;
            case self::TIME:
                $strValue = $this->validTime($strValue);
                break;
            case self::EMAIL:
                $strValue = $this->validEMail($strValue);
                break;
            case self::URL:
                $strValue = $this->validURL($strValue);
                break;
            case self::STRING:
            default:
                $strValue = $this->validString($strValue);
                break;
        }
        if (strlen($strValue) > 0) {
            $this->aJsonLD[$strName] = $strValue;
        }
    }

    /**
     * Get complete tag for the HTML head.
     * (including <script></script>)
     * @param bool $bPrettyPrint
     * @return string
     */
    public function getHTMLHeadTag(bool $bPrettyPrint = false) : string
    {
        $strTag = '';
        if (!$this->bIsChild) {
            $strTag  = '<script type="application/ld+json">' . PHP_EOL;
            $strTag .= json_encode($this->aJsonLD, $bPrettyPrint ? JSON_PRETTY_PRINT : 0) . PHP_EOL;
            $strTag .= '</script>' . PHP_EOL;
        }
        return $strTag;
    }

    /**
     * Get the resulting json object.
     * @param bool $bPrettyPrint
     * @return string
     */
    public function getJson(bool $bPrettyPrint = false) : string
    {
        $strJson = json_encode($this->aJsonLD, $bPrettyPrint ? JSON_PRETTY_PRINT : 0);
        if ($strJson === false) {
            $strJson = '';
        }
        return $strJson;
    }

    /**
     * Get the array object.
     * @return array<mixed>
     */
    public function getObject() : array
    {
        return $this->aJsonLD;
    }

    /**
     * Build valid string value.
     * @param string $str
     * @return string
     */
    protected function validString(string $str) : string
    {
        // replace " and all '\r' from string
        $str = str_replace('"', "'", $str);
        $str = str_replace("\r\n", "\n", $str);
        $str = str_replace("\r", "\n", $str);
        return $str;
    }

    /**
     * Build valid longitude/latidute value.
     * @param float|string $longlat
     * @return string
     */
    protected function validLongLat($longlat) : string
    {
        // TODO: long/lat validation
        if (is_numeric($longlat)) {
            $longlat = (string)$longlat;
        }
        return $longlat;
    }

    /**
     * Build valid date value.
     * If no time set (H,i and s == 0), only 'Y-m-d' format is used, otherwise
     * the full ISO8601 format is used.
     * @param string|int|\DateTime $date       can be string (format YYYY-MM-DD {HH:ii:ss.}), int (unixtimestamp) or DateTime - object
     * @return string
     */
    protected function validDate($date) : string
    {
        $strDate = '';
        if ($date !== null) {
            $uxts = 0;
            if (is_object($date) && get_class($date) == 'DateTime') {
                // DateTime -object
                $uxts = $date->getTimestamp();
            } elseif (is_numeric($date)) {
                // unix timestamp
                $uxts = intval($date);
            } else {
                $uxts = strtotime($date);
                if ($uxts === false) {
                    $uxts = 0;
                }
            }
            if ($uxts > 0) {
                $strTime = date('H:i:s', $uxts);
                if ($strTime == '00:00:00') {
                    $strDate = date('Y-m-d', $uxts);
                } else {
                    $strDate = date(DATE_ISO8601, $uxts);
                }
            }
        }
        return $strDate;
    }

    /**
     * Build valid time value.
     * @param string $strTime
     * @return string
     */
    protected function validTime(string $strTime) : string
    {
        $aTime = explode(':', $strTime);
        $strTime = '';
        if (count($aTime) == 2) {
            $iHour = intval($aTime[0]);
            $iMin = intval($aTime[1]);
            if ($iHour >= 0 && $iHour < 24 && $iMin >= 0 && $iMin < 60) {
                $strTime = sprintf('%02d:%02d', $iHour, $iMin);
            }
        }
        return $strTime;
    }

    /**
     * Check for valid URL value.
     * @param string $strURL
     * @return string
     */
    protected function validURL(string $strURL) : string
    {
        if (!($strURL = filter_var($strURL, FILTER_VALIDATE_URL))) {
            $strURL = '';
        }
        return $strURL;
    }

    /**
     * Check for valid e-Mail adress.
     * @param string $strEMail
     * @return string
     */
    protected function validEMail(string $strEMail) : string
    {
        if (!($strEMail = filter_var($strEMail, FILTER_VALIDATE_EMAIL))) {
            $strEMail = '';
        }
        return $strEMail;
    }

    /**
     * Truncate string to max length and add 'ellipsis' (...) at the end.
     * Truncation can be made 'hard' or 'soft' (default). Hard break menas, the text is
     * cut off at $iMaxLen-3 characters and '...' appended. In the case of
     * a soft break, the text is cut off after the last word that fits within
     * the maximum length and the '...' is added.
     * @param string    $strText
     * @param int       $iMaxLen
     * @param bool      $bHardBreak
     * @return string
     */
    protected function strTruncateEllipsis(string $strText, int $iMaxLen, bool $bHardBreak = false) : string
    {
        if (strlen($strText) > $iMaxLen - 3 && $iMaxLen > 4) {
            $strText = substr($strText, 0, $iMaxLen - 3);
            if (strrpos($strText, ' ') !== false && !$bHardBreak) {
                $strText = substr($strText, 0, strrpos($strText, ' '));
            }
            $strText .= '...';
        }
        return $strText;
    }
}
