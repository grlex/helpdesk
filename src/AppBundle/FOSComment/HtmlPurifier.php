<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 05.10.2017
 * Time: 17:49
 */

namespace AppBundle\FOSComment;

use FOS\CommentBundle\Markup\HtmlPurifier as BaseHtmlPurifier;

class HtmlPurifier extends BaseHtmlPurifier{

    public function __construct(\HTMLPurifier $purifier){
        $config = $purifier->config;
        $def = $config->getHTMLDefinition(true);
        $def->addAttribute('img', 'data-file-id', 'Text');
        $purifier = new \HTMLPurifier($config);
        parent::__construct($purifier);
    }
} 