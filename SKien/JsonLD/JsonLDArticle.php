<?php
declare(strict_types=1);

namespace SKien\JsonLD;

/**
 * Adding structured data to your news, blog or other info article page.
 *
 * #### Properties:
 *
 * | Name             | Description                                   | Necessity  |
 * |------------------|-----------------------------------------------|------------|
 * | author           | Person or Organization                        | required   |
 * | datePublished    | Date and time the article was first published | required   |
 * | headline         | The headline of the article                   | required   |
 * | image            | An image representing the page                | required   |
 * | publisher        | Publisher of the article (organization)       | required   |
 * | dateModified     | Date and time the article was last modified   | recomended |
 * | mainEntityOfPage | The canonical URL of the article page         | recomended |
 *
 *
 * #### Notes:
 * - Headlines MUST not be longer than 110 characters. The class truncates longer values
 *   with ellipsis (...)
 * - The datePublished may not be changed later (sett dateModified instead). It is recommended to
 *   include the hour in the time stamp in addition to the day.
 * - The dateModified value must be after the datePublished value.
 *
 * @link https://developers.google.com/search/docs/data-types/article
 * @link https://schema.org/Article
 *
 * @package JsonLD
 * @author Stefanius <s.kientzler@online.de>
 * @copyright MIT License - see the LICENSE file for details
 */
class JsonLDArticle extends JsonLD
{
    /**
     * Initializes a JsonLD object for article.
     * The main entity of the page is set to the webPage with
     * @id = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']
     * @param string $strType   possible values 'Article', 'NewsArticle' or 'BlogPosting'
     */
    public function __construct(string $strType = 'NewsArticle')
    {
        parent::__construct(self::ARTICLE, $strType);
        $strID = $_SERVER['HTTP_HOST'] ?? 'UNKNOWN_HOST';
        $strID .= $_SERVER['REQUEST_URI'] ?? '_UNKNOWN_REQUEST_URI';
        $this->aJsonLD["mainEntityOfPage"] = array(
            "@type" => "WebPage",
            "id"    => $strID,
        );
    }

    /**
     * Set the publisher of the article.
     * Name is mandatory.
     * @param string $strName
     * @param string $strEMail
     * @param string $strPhone
     */
    public function setPublisher(string $strName, string $strEMail = '', string $strPhone = '') : void
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
     * Set the logo of the publisher.
     * @param string $strLogoURL
     */
    public function setLogo(string $strLogoURL) : void
    {
        $aLogo = $this->buildImageObject($strLogoURL);
        if ($aLogo !== null) {
            if (!isset($this->aJsonLD["publisher"])) {
                $this->aJsonLD["publisher"] = array("@type" => "Organization");
            }
            $this->aJsonLD["publisher"]["logo"] = $aLogo;
        }
    }

    /**
     * Set infos to the article on this page.
     * Headlines cannot be longer than 110 characters. Longer text will be truncated with ellipsis (...)
     * @param string $strHeadline
     * @param string $strDescription
     * @param mixed $published          can be string (format YYYY-MM-DD HH:ii:ss), int (unixtimestamp) or DateTime - object
     * @param mixed $modified           can be string (format YYYY-MM-DD HH:ii:ss), int (unixtimestamp) or DateTime - object
     */
    public function setInfo(string $strHeadline, string $strDescription, $published, $modified = null) : void
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
    public function setAuthor(string $strAuthor) : void
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
