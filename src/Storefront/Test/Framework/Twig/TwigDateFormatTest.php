<?php declare(strict_types=1);

namespace Shopware\Storefront\Test\Framework\Twig;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use Shopware\Storefront\Framework\Twig\TwigDateRequestListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Twig\Loader\ArrayLoader;

class TwigDateFormatTest extends TestCase
{
    use KernelTestBehaviour;

    public function testFallbackBehavior()
    {
        static::markTestSkipped();
        try {
            $this->getKernel()->handle(new Request([], [], [], [TwigDateRequestListener::TIMEZONE_COOKIE => 'Invalid']), HttpKernelInterface::MASTER_REQUEST, false);
        } catch (\Error $e) {
            // nth
        }

        $date = new \DateTime();
        $output = $this->renderTestTemplate($date);
        static::assertSame($date->format('d/m/Y, H:i'), $output);
    }

    public function testDifferentTimeZoneBehavior()
    {
        static::markTestSkipped();
        $timezone = 'Europe/Berlin';
        try {
            $this->getKernel()->handle(new Request([], [], [], [TwigDateRequestListener::TIMEZONE_COOKIE => $timezone]), HttpKernelInterface::MASTER_REQUEST, false);
        } catch (\Error $e) {
            // nth
        }

        $date = new \DateTime();
        $output = $this->renderTestTemplate($date);
        static::assertSame($date->setTimezone(new \DateTimeZone($timezone))->format('d/m/Y, H:i'), $output);
    }

    private function renderTestTemplate(\DateTimeInterface $dateTime)
    {
        $twig = $this->getContainer()->get('twig');
        $twig->

        $originalLoader = $twig->getLoader();
        $twig->setLoader(new ArrayLoader([
            'test.html.twig' => "{{ date|localizeddate('short', 'short')}}",
        ]));
        $output = $twig->render('test.html.twig', ['date' => $dateTime]);
        $twig->setLoader($originalLoader);

        return $output;
    }
}
