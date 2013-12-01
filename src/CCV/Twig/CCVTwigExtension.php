<?php
namespace CCV\Twig;

use Twig_Extension;
use Twig_SimpleFilter;
use Twig_SimpleFunction;
use GeSHi;

class CCVTwigExtension extends Twig_Extension
{
	public function getFilters()
    {
        return array(
            new Twig_SimpleFilter('geshi', array($this, 'highlight'),array('is_safe' => array('html'))),
        );
    }

    public function getFunctions() {
    	return array(
    		new Twig_SimpleFunction('implode', 'implode'),
    	);
    }
	
	public function highlight($source, $language = 'lua') {
		$geshi = new GeSHi($source, $language);
		$geshi->enable_line_numbers(GESHI_NORMAL_LINE_NUMBERS);
		return $geshi->parse_code();
	}
	
    public function getName()
    {
        return 'ccv';
    }
}
