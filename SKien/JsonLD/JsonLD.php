<?php
namespace SKien\JsonLD;
/**
 * Base class of the package, which provides all basic functions 
 * for generating the valid JsonLD objects of different types.
 * 
 * https://search.google.com/structured-data/testing-tool
 * 
 * https://developers.google.com/search/docs/guides/sd-policies
 * 
 * https://www.w3.org/TR/json-ld11/
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
class JsonLD
{
    /** type: LocalBusiness or subtype  */
    const   LOCAL_BUSINESS  = 0;
    /** type: Article, NewsArticle,   */
    const   ARTICLE         = 1;
    /** type: Event   */
    const   EVENT           = 2;
    
    /** internal validation for string   */
    const   STRING  = 0;
    /** internal validation for date   */
    const   DATE    = 1;
    /** internal validation for time   */
    const   TIME    = 2;
    /** internal validation for e-mail   */
    const   EMAIL   = 3;
    /** internal validation for url   */
    const   URL     = 4;

    /** @var int    internal object type     */
    protected $iType = '';
    /** @var string type according the JsonLD spec     */
    protected $aJsonLD = null;
    /** @var bool   object is nested into another object     */
    protected $bIsChild = false;
    
    /**
     * Instantciation of JsonLD object
     * @param int $iType        internal type
     * @param string $strType   type for JsonLD
     */
    public function __construct($iType, $strType, $bIsChild=false)
    {
        $this->iType = $iType;
        $this->bIsChild = $bIsChild;
        $this->aJsonLD = array(
                "@context"  => "https://schema.org",
                "@type"     => $strType
            );
    }

    /**
     * Set description text.
     * Usable for all JsonLD types.
     * @param string $strDescription
     */
    public function setDescription($strDescription)
    {
        $this->setProperty("description", $strDescription);
    }

    /**
     * Set location for the object
     * @param string $strName       Name for the location 
     * @param mixed $latitude       string/float
     * @param mixed $longitude      string/float
     * @param string $strMap        URL to map show the location
     */
    public function setLocation($strName, $latitude, $longitude, $strMap='')
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
     * Add image.
     * Multiple images are supported. Only existing images can be set.
     * Pixel size (width/height) is detected from image file.
     * @param string $strImageURL
     */
    public function addImage($strImageURL)
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
     * @param string $strURL
     * @return array    array containing the property or null if invalid URL
     */
    public function buildImageObject($strURL)
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
     * build postal adress object.
     *
     * @param string $strStreet
     * @param string $strPostcode
     * @param string $strCity
     * @param string $strRegion
     * @param string $strCountry
     * @return array    array containing the property
     */
    public function buildAdress($strStreet, $strPostcode, $strCity, $strRegion='', $strCountry='')
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
     * build location object.
     *
     * @param string $strName       Name for the location 
     * @param mixed $latitude       string/float
     * @param mixed $longitude      string/float
     * @param string $strMap        URL to map show the location
     * @return array    array containing the property or null if invalid URL
     */
    public function buildLocation($strName, $latitude, $longitude, $strMap)
    {
        $aLocation = null;
        $latitude = $this->validString($latitude);
        $longitude = $this->validString($longitude);
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
     * build contact point object.
     * The type must not contain any predefiend value, it is to describe the contact.
     * (i.e. 'Information', 'Hotline', 'Customer Service', 'Administration'...)
     *
     * @param string $strType   
     * @param string $strEMail
     * @param string $strPhone
     * @return array    array containing the property or null if invalid URL
     */
    public function buildContactPoint($strType, $strEMail, $strPhone)
    {
        $aCP = null;
        $strType = $this->validString($strType);
        $strEMail = $this->validString($strEMail);
        $strPhone = $this->validString($strPhone);
        if (strlen($strType) > 0) {
            $aCP = array("@type" => "ContactPoint");
            $aCP["contactType"] = $strType;
            if (strlen($strEMail) > 0 ) {
                $aCP["email"] = $strEMail;
            }
            if (strlen($strPhone) > 0 ) {
                $aCP["telephone"] = $strPhone;
            }
        }
        return $aCP;
    }
    
    /*
     "sameAs" : [ "http://www.facebook.com/your-profile",
     "http://www.twitter.com/yourProfile",
     null    "http://plus.google.com/your_profile"]
     */

    /**
     * Set the property to vsalue of given type 
     * @param string $strName
     * @param string $strValue
     * @param int $iType
     * @param string $strFormat
     */
    public function setProperty($strName, $strValue, $iType=self::STRING, $strFormat='')
    {
        switch ($iType) {
            case self::DATE:
                $strValue = $this->validDate($strValue, $strFormat);
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
     * Get complete tag for the HTML head (including <script></script>)  
     * @param bool $bPrettyPrint
     * @return string
     */
    public function getHTMLHeadTag($bPrettyPrint=false)
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
    public function getJson($bPrettyPrint=false)
    {
        $strJson = json_encode($this->aJsonLD, $bPrettyPrint ? JSON_PRETTY_PRINT : 0);
        return $strJson;
    }

    /**
     * Get the builded array object.
     * @return array
     */
    public function getObject()
    {
        return $this->aJsonLD;
    }
    
    /**
     * Build valid string value.
     * @param unknown $str
     * @return mixed
     */
    protected function validString($str)
    {
        // replace " and all linebreaks from string
        $str = str_replace('"', "'", $str);
        $str = str_replace("\r\n", "\n", $str);
        $str = str_replace("\r", "\n", $str);
        return $str;
    }
    
    /**
     * Build valid date value.
     * If not time set (H,i and s == 0), only 'Y-m-d' format is used, otherwise
     * the full ISO8601 format is used. 
     * @param mixed $date       can be string (format YYYY-MM-DD {HH:ii:ss.}), int (unixtimestamp) or DateTime - object
     * @return string
     */
    protected function validDate($date)
    {
        $strDate = '';
        if ($date != null) {
            $uxts = 0;
            if (is_object($date) && get_class($date) == 'DateTime') {
                // DateTime -object
                $uxts = $date->getTimestamp();
            } else if (is_numeric($date)) {
                // unix timestamp
                $uxts = $date;
            } else {
                $uxts = strtotime($date);
            }
            $strTime = date('H:i:s', $uxts);
            if ($strTime == '00:00:00') {
                $strDate = date('Y-m-d', $uxts);
            } else {
                $strDate = date(DATE_ISO8601, $uxts);
            }
        }
        return $strDate;
    }

    /**
     * Build vaslid time value.
     * @param string $strTime
     * @return string
     */
    protected function validTime($strTime)
    {
        $aTime = explode(':', $strTime);
        $strTime = '';
        if (count($aTime) == 2) {
            $iHour = intval($aTime[0]);
            $iMin = intval($aTime[1]);
            if ($iHour >= 0 && $iHour < 24 && $iMin >= 0 && $iMin <60) {
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
    protected function validURL($strURL)
    {
        if (!($strURL = filter_var($strURL, FILTER_VALIDATE_URL))) {
            $strURL = '';
        }
        return $strURL;
    }

    /**
     * Check for valid e-Mail adress
     * @param string $strEMail
     * @return string
     */
    protected function validEMail($strEMail)
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
     * 
     * @param string    $strText
     * @param int       $iMaxLen
     * @param bool      $bHardBreak
     */
    static public function strTruncateEllipsis($strText, $iMaxLen, $bHardBreak=false)
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
