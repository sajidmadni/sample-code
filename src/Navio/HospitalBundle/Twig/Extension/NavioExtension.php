<?php
/**
 * User: robertfaulkner
 * Date: 24/02/15
 * Time: 20:13
 */

namespace Navio\HospitalBundle\Twig\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig_Extension;

class NavioExtension extends Twig_Extension{

    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getName()
    {
        return 'navio.extension';
    }

    /**
     * Define Twig filters
     * @example
     * {{ string|json_decode }}
     * {{ string|json_encode }}
     * @return array
     */
//    public function getFilters()
//    {
//        return array(
//            new \Twig_Filter_Method('json_decode', array($this, 'jsonDecode'))
//        );
//    }
    /**
     * Define Twig functions
     * @example
     * {{ json_decode(string) }}
     * {{ json_encode(string) }}
     * @return array
     */
    public function getFunctions()
    {
        return array(
            'json_decode'  => new \Twig_Function_Method($this, 'jsonDecode'),
            'json_encode' => new \Twig_Function_Method($this, 'jsonEncode'),
            'json_decodea'  => new \Twig_Function_Method($this, 'jsonDecodetoArray'),
        );
    }
    /**
     * Decode JSON string
     * @param  string $string
     * @return object
     */
    public function jsonDecode($string)
    {
        return json_decode($string);
    }
    
    /**
     * Decode JSON string
     * @param  string $string
     * @return array
     */
    public function jsonDecodetoArray($string,$bool)
    {
        return json_decode($string,true);
    }
    
    /**
     * Encode an object or array to JSON
     * @param  array $array
     * @return string
     */
    public function jsonEncode($array)
    {
        return json_encode($array);
    }
    
    
}
