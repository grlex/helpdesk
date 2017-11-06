<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 13.09.2017
 * Time: 23:07
 */

namespace AppBundle\Service;


use Symfony\Component\Intl\DateFormatter\IntlDateFormatter;
use Symfony\Component\Translation\PluralizationRules;
use Symfony\Component\Translation\Translator;

class TwigExtension  extends \Twig_Extension{

    private $translator;
    protected $viewContextStorage = [];

    public function __construct(Translator $translator){

        $this->translator = $translator;
    }

    //
    // Tests
    //

    public function getTests()
    {
        return array(
            new \Twig_SimpleTest('typeof', array($this, 'typeOfTest')),
            new \Twig_SimpleTest('allowed_action', array($this, 'allowedActionTest'))
        );
    }
    public function typeOfTest($value, $type) {
        $func = 'is_'.strtolower($type);
        if(function_exists($func)) return $func($value);
        if($value instanceof $type) return true;
        return false;
    }
    public function allowedActionTest($action, $key, $position, $items=null, callable $canActCallback=null) {

        $action['position'] = is_array($action['position']) ? $action['position'] : [$action['position']];
        return $action!=false
        and ( !isset($action['position']) or  in_array($position,$action['position']))
        and ( !$canActCallback  or $canActCallback($key, $items) );
    }

    //
    // Filters
    //

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('onespace', array($this, 'oneSpaceFilter')),
            new \Twig_SimpleFilter('relative_date', array($this, 'relativeDateFilter')),
            new \Twig_SimpleFilter('date_trans', array($this, 'dateTransFilter')),
            new \Twig_SimpleFilter('merge_recursive', array($this, 'mergeRecursiveFilter')),
            new \Twig_SimpleFilter('to_string', array($this, 'toStringFilter' )),
            new \Twig_SimpleFilter('replace_uri_params', array($this, 'replaceUriParamsFilter')),
            new \Twig_SimpleFilter('call', array($this, 'callFilter')),
        );
    }

    public function oneSpaceFilter($value) {

        $value = preg_replace('/(?>\s+)/',' ',$value);
        //$value = preg_replace('/\n/',' ',$value);
        return $value;
    }
    public function relativeDateFilter($value) {
        $interval = $value->diff(new \DateTime());
        if($interval->days==0){
            if($interval->h==0){
                if($interval->i==0){
                    if($interval->s<10){
                        return $this->translator->trans('date-format.just-now');
                    }
                    return  sprintf('%s %s %s',
                        $interval->s,
                        $this->translator->transchoice('date-format.seconds', $interval->s),
                        $this->translator->trans('date-format.ago'));
                }
                return  sprintf('%s %s %s',
                    $interval->i,
                    $this->translator->transchoice('date-format.minutes', $interval->i),
                    $this->translator->trans('date-format.ago'));
            }
            return sprintf('%s %s %s',
                $interval->h,
                $this->translator->transchoice('date-format.hours', $interval->h),
                $this->translator->trans('date-format.ago'));
        }

        $value = $value instanceof \DateTime ? $value->getTimestamp() : intval($value);
        $month = \date('F', $value);
        $date = \date(' j, Y H:i', $value);
        $month = $this->translator->trans($month);
        return $month.$date;
    }
    public function dateTransFilter(\DateTime $date, $locale) {

        switch(substr($locale,0,2)){
            case 'en':
                $dateFormatter = IntlDateFormatter::create('en',IntlDateFormatter::LONG, IntlDateFormatter::SHORT);
                return $dateFormatter->format($date);
            case 'ru':
                return $date->format('d.m.Y, H:i');
            default:
                return $date->format('c');
        }
    }
    public function mergeRecursiveFilter($first, $second)  {
        return array_replace_recursive($first, $second);
    }
    public function toStringFilter($value){
        if(is_scalar($value)) return strval($value);
        if(is_object($value) and method_exists($value,'__toString')) return $value->__toString();
        if(is_array($value) or $value instanceof \Traversable) {
            $result = [];
            $filter = [$this, 'toStringFilter'];
            foreach ($value as $key => $val) $result[] = call_user_func($filter, $val);
            return join(', ', $result);
        }
        return '[no string view]';
    }
    public function replaceUriParamsFilter($uri, array $replacements = []){

        $params = parse_url($uri, PHP_URL_QUERY);
        //if(strlen($params)==0) return $uri;

        $uri = parse_url($uri, PHP_URL_PATH);
        $namedParams = [];
        $params = mb_split('&',$params);


        foreach($params as $param){
            $param = mb_split('=',$param);
            $paramName = urldecode($param[0]);
            $paramValue = isset($param[1]) ? urldecode($param[1]) : '';
            $namedParams[$paramName] = $paramValue;
        }


        $namedParams = array_replace($namedParams, $replacements);
        $namedParams = array_filter($namedParams, function($paramValue){return $paramValue!==false;});


        return $uri.'?'.http_build_query($namedParams);
    }
    public function callFilter(callable $callback)  {
        return call_user_func_array($callback, array_slice(func_get_args(),1));
    }

    //
    // Functions
    //

    public function getFunctions() {
        return [
            new \Twig_SimpleFunction('save_context', array($this, 'saveContext'), ['needs_context' => true]),
            new \Twig_SimpleFunction('restore_context', array($this, 'restoreContext'), ['needs_context' => true]),
        ];
    }

    public function saveContext($context) {
        $this->viewContextStorage = array_merge($this->viewContextStorage, $context);
    }
    public function restoreContext(&$context) {
        $context = array_merge($context, $this->viewContextStorage);
    }
}