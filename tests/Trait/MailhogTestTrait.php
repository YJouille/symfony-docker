<?php

namespace App\Tests\Trait;

use Facebook\WebDriver\WebDriverBy;
use Symfony\Component\Panther\DomCrawler\Crawler;

trait MailhogTestTrait
{
    private function resetMailList(): Crawler
    {
        $crawler = ($client = self::getCustomClient())->request("GET", "http://172.17.0.1:8814");
        $client->click(
            $crawler->selectLink("Delete all messages")->link()
        );

        $client->waitForVisibility("#confirm-delete-all");
        $client->executeScript("angular.element('#confirm-delete-all button.btn-danger').click();");
        self::assertPageTitleContains("MailHog");

        return $client->refreshCrawler();
    }

    private static function assertEmailReceived(string $toVerify): Crawler
    {
        ($client = self::getCustomClient())->request("GET", "http://172.17.0.1:8814");

        $client->waitForVisibility(".msglist-message .subject");
        self::assertSelectorTextContains(".msglist-message .subject", $toVerify);
        self::takeMailhogScreenshot("mail_list");

        return $client->refreshCrawler();
    }

    private function openFirstEmail(): Crawler
    {
        ($client = self::getCustomClient())->request("GET", "http://172.17.0.1:8814");
        $client->waitFor(".msglist-message .subject", 5)
            ->filter(".msglist-message .subject")
            ->click();

        $client->waitForVisibility("iframe#preview-html", 5);
        $frame = $client->findElement(WebDriverBy::cssSelector("iframe#preview-html"));
        $client->switchTo()->frame($frame);

        $crawler = $client->waitForVisibility("body", 5);
        $client->switchTo()->defaultContent();
        self::takeMailhogScreenshot("mail_first_opened");
        self::assertPageTitleContains("MailHog");

        return $crawler;
    }

    private function getActionUrlInMail(): string
    {
        $frame = ($client = self::getCustomClient())->findElement(
            WebDriverBy::cssSelector("iframe#preview-html")
        );
        $client->switchTo()->frame($frame);

        $url = $client->refreshCrawler()
            ->filter("a.btn-primary")
            ->getAttribute("href");

        $client->switchTo()->defaultContent();

        return $url;
    }

    private static function takeMailhogScreenshot(string $title = "mailhog"): void
    {
        $date = date("Y-m-d_H-i-s");
        self::getCustomClient()->takeScreenshot(self::getVarPath() . "/screenshots/". $date . "_" . $title . ".jpg");
    }
}
