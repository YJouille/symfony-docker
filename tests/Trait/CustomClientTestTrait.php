<?php

namespace App\Tests\Trait;

use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;
use Symfony\Component\Panther\ServerExtension;

trait CustomClientTestTrait
{
    private static ?ObjectManager $entityManager = null;
    private static ?Client $client = null;
    private static ?string $labelBeneficiaire = null;
    private static ?string $beneficiaireLabel = null;
    private static ?Router $router = null;

    final protected function tearDown(): void
    {
        $title = $this->getName();
        self::takeScreenshot($title);
        self::saveBrowserLogs($title);

        self::$client = null;

        parent::tearDown();

        // doing this is recommended to avoid memory leaks
        self::$entityManager && self::$entityManager->close();
        self::$entityManager = null;
    }

    private static function getVarPath(): string
    {
        return self::bootKernel()->getContainer()->get("kernel")->getProjectDir() . "/var";
    }

    private static function takeScreenshot(string $title = ""): void
    {
        $date = date("Y-m-d_H-i-s");
        self::getCustomClient()->takeScreenshot(self::getVarPath() . "/screenshots/" . $date . "_" . $title . ".jpg");
    }

    private static function getCustomClient(): Client
    {
        is_null(self::$client) && (self::$client = self::createCustomClient());

        return self::$client;
    }

    private static function createCustomClient(): Client
    {
        $client = Client::createChromeClient(
            self::bootKernel()->getContainer()->get("kernel")->getProjectDir() . "/drivers/chromedriver",
            [
                "--headless",
                "--no-sandbox",
                "--disable-dev-shm-usage",
                "--window-size=1920,1080",
            ],
            [
                "connection_timeout_in_ms" => 120000,
                "request_timeout_in_ms" => 120000,
                "capabilities" => [
                    "goog:loggingPrefs" => ["browser" => "ALL"],
                ],
            ],
            "http://172.17.0.1:8811"
        );
        ServerExtension::registerClient($client);

        \Closure::bind(function (AbstractBrowser $client) {
            PantherTestCase::getClient($client);
        }, null, PantherTestCase::class)($client);

        return $client;
    }

    private static function saveBrowserLogs(string $title = ""): void
    {
        $logs = self::getCustomClient()->getWebDriver()->manage()->getLog("browser");
        $logPath = self::getVarPath() . "/log/browser.log";

        $result = array_map(fn ($log) => (
            date("d-m-Y H:i:s", $log["timestamp"])
            . " - $title : "
            . " [" . $log["level"] . "] "
            . $log["message"]
        ), $logs);

        file_put_contents($logPath, "\n" . implode("\n", $result), FILE_APPEND);
    }

    private static function getEntityManager(): ObjectManager
    {
        is_null(self::$entityManager)
        && (self::$entityManager = self::createEntityManager());

        return self::$entityManager;
    }

    private static function createEntityManager(): ObjectManager
    {
        self::$entityManager = self::bootKernel()->getContainer()->get("doctrine")->getManager();

        return self::$entityManager;
    }

    private static function getRouter(): Router
    {
        is_null(self::$router)
        && (self::$router = self::createRouter());

        return self::$router;
    }

    private static function createRouter(): Router
    {
        self::$router = self::bootKernel()->getContainer()->get("router");

        return self::$router;
    }

    private static function getUrlFromRoute(string $route, array $parameters = []): string
    {
        return self::getRouter()->generate($route, $parameters);
    }
}
