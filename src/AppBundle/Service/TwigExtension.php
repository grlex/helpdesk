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
    public function __construct(Translator $translator){

        $this->translator = $translator;
    }
    public function getTests()
    {
        return array(
            new \Twig_SimpleTest('typeof', function ($value, $type) {
                $func = 'is_'.strtolower($type);
                if(function_exists($func)) return $func($value);
                if($value instanceof $type) return true;
                return false;
            })
        );
    }
    public function getFilters()
    {
        $translator = $this->translator;
        return array(
            new \Twig_SimpleFilter('type', function ($value) {

                return get_class($value);
            }),
            new \Twig_SimpleFilter('onespace', function ($value) {

                $value = preg_replace('/(?>\s+)/',' ',$value);
                //$value = preg_replace('/\n/',' ',$value);
                return $value;
            }),
            new \Twig_SimpleFilter('comment_date', function ($value) use ($translator)  {
                $interval = $value->diff(new \DateTime());
                if($interval->days==0){
                    if($interval->h==0){
                        if($interval->i==0){
                            if($interval->s<10){
                                return $translator->trans('date-format.just-now');
                            }
                            return  sprintf('%s %s %s',
                                $interval->s,
                                $translator->transchoice('date-format.seconds', $interval->s),
                                $translator->trans('date-format.ago'));
                        }
                        return  sprintf('%s %s %s',
                            $interval->i,
                            $translator->transchoice('date-format.minutes', $interval->i),
                            $translator->trans('date-format.ago'));
                    }
                    return sprintf('%s %s %s',
                        $interval->h,
                        $translator->transchoice('date-format.hours', $interval->h),
                        $translator->trans('date-format.ago'));
                }

                $value = $value instanceof \DateTime ? $value->getTimestamp() : intval($value);
                $month = \date('F', $value);
                $date = \date(' j, Y H:i', $value);
                $month = $translator->trans($month);
                return $month.$date;
            })
        );
    }
}