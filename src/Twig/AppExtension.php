<?php


namespace App\Twig;


use App\Entity\LikeNotification;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFilter;
use Twig\TwigTest;

class AppExtension extends AbstractExtension implements GlobalsInterface
{

    /**
     * @var string
     */
    private $locale;

    public function __construct(string $locale)
    {
        $this->locale = $locale;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('price', [$this, 'priceFilter']),
        ];
    }

    public function priceFilter($number)
    {
        return '$' . number_format($number, 2, '.', ',');
    }

    /**
     * Returns a list of global variables to add to the existing list.
     *
     * @return array An array of global variables
     */
    public function getGlobals()
    {
        return [
            'locale' => $this->locale,
        ];
    }

    public function getTests()
    {
        return [
            new TwigTest('like', function ($object) {
                return $object instanceof LikeNotification;
            })
        ];
    }
}
