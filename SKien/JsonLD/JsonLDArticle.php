<?php
namespace SKien\JsonLD;

/**
 * Adding structured data to your news, blog or other info article page. 
 * 
 * https://developers.google.com/search/docs/data-types/article
 * 
 * https://schema.org/Article
 * 
 * ### required properties: 
 * #### author
 * Person or Organization
 * 
 * #### datePublished
 * Date and time the article was first published. The information is given in 
 * ISO 8601 format. The date may not change later. It is recommended to 
 * include the hour in the time stamp in addition to the day. The dateModified 
 * value must be after the datePublished value.
 *                  
 * #### headline
 * The headline of the article. Headlines cannot be longer than 110 characters. 
 *                  
 * #### image
 * #### publisher
 * (Type: Organization) Publisher of the article.
 *  - publisher.name
 *  - publisher.logo
 *  - publisher.logo.url
 * 
 * ### recomended properties:
 * #### dateModified
 * Date and time the article was last modified. The information is given in 
 * ISO 8601 format. The dateModified must be after the datePublished value.
 *
 * #### mainEntityOfPage
 * The canonical URL of the article page. Specify this property if the 
 * article is the main topic of the article page.
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
class JsonLDArticle extends JsonLD
{
    /**
     * Initializes a JsonLD object for article.
     * Set the main entity of the page to
     * WebPage with @id = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']
     * @param string $strType
     */ 
    public function __construct($strType='NewsArticle')
    {
        parent::__construct(self::ARTICLE, $strType);
        $this->aJsonLD["mainEntityOfPage"] = array(
                "@type" => "WebPage",
                "id"    => $id = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] 
            );
    }
    
    /**
     * Set the publisher of the article.
     * Name is mandatory.
     * @param string $strName 
     * @param string $strEMail
     * @param string $strPhone
     */
    public function setPublisher($strName, $strEMail='', $strPhone='')
    {
        $strName = $this->validString($strName);
        $strEMail = $this->validEMail($strEMail);
        if (strlen($strName) > 0) {
            if (!isset($this->aJsonLD["publisher"])) {
                $this->aJsonLD["publisher"] = array("@type" => "Organization");
            }
            $this->aJsonLD["publisher"]["name"] = $strName;
            if (strlen($strEMail) > 0) {
                $this->aJsonLD["publisher"]["email"] = $strEMail;
            }
            $strPhone = $this->validString($strPhone);
            if (strlen($strPhone) > 0) {
                $this->aJsonLD["publisher"]["telephone"] = $strPhone;
            }
        }
    }

    /**
     * Set the logo of the organization.
     * @param string $strLogoURL
     */
    public function setLogo($strLogoURL)
    {
        $aLogo = $this->buildImageObject($strLogoURL);
        if ($aLogo != null) {
            if (!isset($this->aJsonLD["publisher"])) {
                $this->aJsonLD["publisher"] = array("@type" => "Organization");
            }
            $this->aJsonLD["publisher"]["logo"] = $aLogo;
        }
    }
    
    /**
     * Set infos to the article obn this page.
     * Headlines cannot be longer than 110 characters. Longer text will be truncated with ellipsis (...)
     * @param string $strHeadline       
     * @param string $strDescription
     * @param mixed $published          can be string (format YYYY-MM-DD HH:ii:ss), int (unixtimestamp) or DateTime - object
     * @param mixed $modified           can be string (format YYYY-MM-DD HH:ii:ss), int (unixtimestamp) or DateTime - object
     */
    public function setInfo($strHeadline, $strDescription, $published, $modified=null)
    {
        $strHeadline = $this->validString($strHeadline);
        $strHeadline = $this->strTruncateEllipsis($strHeadline, 110);
        if (strlen($strHeadline) > 0) {
            $this->aJsonLD["headline"] = $strHeadline;
            $this->aJsonLD["description"] = $this->validString($strDescription);
            if ($published != null) {
                $this->aJsonLD["datePublished"] = $this->validDate($published);
            }
            if ($modified != null) {
                $this->aJsonLD["dateModified"] = $this->validDate($modified);
            }
        }
    }

    /**
     * Set the authors name
     * @param string $strAuthor
     */
    public function setAuthor($strAuthor)
    {
        $strAuthor = $this->validString($strAuthor);
        if (strlen($strAuthor) > 0) {
            $this->aJsonLD["author"] = array(
                    "@type" => "Person",
                    "name" =>  $this->validString($strAuthor)
                );
        }
    }
}
