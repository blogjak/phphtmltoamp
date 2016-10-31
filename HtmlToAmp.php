<?php

/**
 * Created by PhpStorm.
 * User: HenrikKarapetyan
 * Date: 31/10/2016
 * Time: 22:41
 */
class HtmlToAmp
{


    private $html;
    private $document;
    private $xpath;

    /**
     * HtmlToAmp constructor.
     */
    public function __construct($htmlContent)
    {
        $this->document = new DOMDocument;
        libxml_use_internal_errors(true);
        $this->html = $this->ampify($htmlContent);
        $this->document->loadHTML($this->html);
        $this->xpath = new DOMXpath($this->document);

    }

    public function getConvertedHtml()
    {

        $this->html = preg_replace('~<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>\s*~i', '', $this->document->saveHTML());
        //var_dump($this->html);

        echo $this->html;
    }

    protected function ampify($htmlContent)
    {
        # Replacing all img, audio, iframe, video elements to amp custom elements
        $htmlContent = str_ireplace(
            ['<img', '<video', '/video>', '<audio', '/audio>'],
            ['<amp-img', '<amp-video', '/amp-video>', '<amp-audio', '/amp-audio>'],
            $htmlContent
        );
        /**
         * adding amp-img closing tag
         */
        $htmlContent = preg_replace('/<amp-img(.*?)\/?>/', '<amp-img$1></amp-img>', $htmlContent);
        $htmlContent = strip_tags($htmlContent, '<h1><h2><h3><h4><h5><h6><a><p><ul><ol><li><blockquote><q><cite><ins><del><strong><em><code><pre><svg><table><thead><tbody><tfoot><th><tr><td><dl><dt><dd><article><section><header><footer><aside><figure><time><abbr><div><span><hr><small><br><amp-img><amp-audio><amp-video><amp-ad><amp-anim><amp-carousel><amp-fit-rext><amp-image-lightbox><amp-instagram><amp-lightbox><amp-twitter><amp-youtube>');
        return $htmlContent;
    }


    /**
     * @param $tagname string
     * @param $layout string
     * @param $special_properties array
     */
    private function setAmptagProperties($tagname, $layout, $special_properties = [])
    {
        foreach ($this->xpath->query('//' . $tagname) as $node) {

            $width = 'width:';
            $height = 'height:';

            $str = $node->getAttribute('style');

            /**
             *Getting tag  width
             */
            $width_str = strstr($str, $width);
            $w = trim(substr($width_str, strlen($width), stripos($width_str, 'px') - strlen($width)));

            /**
             *Getting tag  height
             */
            $height_str = strstr($str, $height);
            $h = trim(substr($height_str, strlen($height), stripos($height_str, 'px') - strlen($height)));


            /**
             *Setting amp attr  layout with width and  height
             */
            if ($layout != "") {
                $node->setAttribute("layout", $layout);
            }
            if ($w != "") {
                $node->setAttribute("width", $w);
                $str = str_replace($width . $w . "px;", ' ', trim($str));
                $str = str_replace($width . $w . "px", ' ', trim($str));
            }
            if ($h != "") {
                $node->setAttribute("height", $h);
                $str = str_replace($height . $h . "px;", ' ', trim($str));
                $str = str_replace($height . $h . "px", ' ', trim($str));
            }
            if (empty($special_properties)) {
                foreach ($special_properties as $key => $value) {
                    $node->setAttribute($key, $value);
                }
            }
            /**
             *removing at style  tag  width and height
             */
            $node->removeAttribute('style');
            $node->setAttribute('style', $str);
        }
    }

    /**
     * @param $layout
     */
    public function iframeToAmpIframe($layout)
    {
        $this->setAmptagProperties("amp-iframe", $layout);
    }

    /**
     * @param $layout string
     * @param string $poster string
     * @param string $type string
     */
    public function videoToAmpVideo($layout, $poster = "", $type = "")
    {
        $special_properties = [];
        if ($poster != "" || $type != "") {
            $special_properties = [
                'poster' => $poster,
                'type' => $type
            ];
        }
        $this->setAmptagProperties("amp-video", $layout, $special_properties);
    }

    public function audioToAmpAudio()
    {
        $this->setAmptagProperties("amp-audio", "");
    }

    /**
     * @param string $layout
     * @param string $srcset
     */
    public function imgToAmpImg($layout, $srcset = "")
    {
        $special_properties = [];
        if ($srcset != "") {
            $special_properties = [
                'srcset' => $srcset,
            ];
        }
        $this->setAmptagProperties("amp-img", $layout, $special_properties);

    }
}