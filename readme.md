# PHP JsonLD Generator: Generate *Linked Data* for embedding in a Website
![Latest Stable Version](https://img.shields.io/badge/release-v1.0.0-brightgreen.svg) ![License](https://img.shields.io/packagist/l/gomoob/php-pushwoosh.svg) [![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%205.6-8892BF.svg)](https://php.net/)
----------
## History
##### 2020-05-25
  * initial Version

## Overview
JSON-LD is a syntax recommended by the W3C that can be used to embed structured data and generally applicable schemas for data structuring in the compact JSON format.

Structured data helps search engines understand web pages better. Semantic annotation enables meaningful connections to be established, information to be automatically read out and transferred to other forms of representation. The Google search engine relies on structured data to provide users with rich search results and other SERP elements. The advantage for the website operator is that the search results highlighted in this way stand out much more and thus increase the visibility of a website.

With the help of *Linked Data*, a website provides not only the presentation, but also its content in machine-readable and classifiable form for further processing and categorization.

In addition to **JsonLD**, **Microdata** and **RDFa** are often used to integrate linked data into a website. The most important difference between **JsonLD** and **Microdata** or **RDFa** is that the structured data is stored in the ***&lt;head&gt;*** area of a page (and no longer within the ***&lt;body&gt;***, as with the other methods). This means that existing HTML elements do not have to be expanded or optimized. In this way, existing pages can be expanded much more quickly with *Linked Data*, and the generation of the *Linked Data* and the generation of the HTML code for displaying the page are clearly separated from each other.

Structured data helps search engines put the information on your website in the right context. This enables search engines to better determine what the website is about.

Website owners have the advantage that this additional information is sometimes displayed in the search results. For example an organization, an author, recipes, reviews or locations. This makes your entry stand out from the others and is rather clicked on.

### Important: 
Google does not guarantee that your structured data will show up in search results, even if your page is marked up correctly according to the Structured Data Testing Tool. Here are some common reasons why:
> - Using structured data enables a feature to be present, it does not guarantee that it will be present. The Google algorithm tailors search results to create what it thinks is the best search experience for a user, depending on many variables, including search history, location, and device type. In some cases it may determine that one feature is more appropriate than another, or even that a plain blue link is best.
> - The structured data is not representative of the main content of the page, or is potentially misleading.
> - The structured data is incorrect in a way that the testing tool was not able to catch.
> - The content referred to by the structured data is hidden from the user.
> - The page does not meet the guidelines for structured data described here, the type-specific guidelines, or the general webmaster guidelines.



## Installation
You can download the  Latest [release version ](https://www.phpclasses.org/package/xxxx.html) from PHPClasses.org

## Usage
*EventTest.php* shows simple code to generate a valid JsonLD Tag for an event:

```php
<?php
//...
$oJsonLD = new JsonLDEvent();

$oJsonLD->setInfo('A great Concert for all...', new DateTime('2020-06-12 18:30'), new DateTime('2020-06-12 22:00'));
$oJsonLD->setDescription('... would it not be amazing, if these two musicians could perform together!);
$oJsonLD->setAdress('Kensington Gore', 'SW7 2AP', 'London', '', 'United Kingdom');
$oJsonLD->setLocation('Royal Albert Hall', 51.5009088, -0.177366, 'https://www.google.com/maps/...');
$oJsonLD->addOffer('Seat', 80, 'GBP', $oJsonLD::AVAILABLE_PRE_ORDER, 'https://www.tickets.com/rah');
$oJsonLD->addOffer('VIP', 250, 'GBP', $oJsonLD::AVAILABLE_PRE_ORDER, 'https://www.tickets.com/rah');
$oJsonLD->setOrganizer('Queen Mum');
$oJsonLD->addPerformer('U2');
$oJsonLD->addPerformer('Elton John');
?>
<!DOCTYPE html>
<html>
<head>
<title>Json LD Generator</title>
<!-- insert the tag in the head section of the document -->
<?php echo $oJsonLD->getHTMLHeadTag(false);?>
</head>
```
Examples of LocalBusiness and Article can be found in the *BusinessTest.php* and *ArticleTest.php*.

A detailed description of the required and recommended properties for the objects and the valid values to be used can be found in the class and function headings of the various objects.
