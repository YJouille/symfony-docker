<?php

namespace App\Tests\Trait;

use App\Helper\ApiMessages;

trait FlashMessageTrait
{
    private function waitForFlashMessage(string $type = null, string $expectedMessage = null): void
    {
        ($client = self::getCustomClient())
            ->executeScript("document.querySelector('#flash_message_container').scrollIntoView();");

        ($type && $client->waitForVisibility(".flash_message[data-type=$type]", 10))
            || $client->waitForVisibility(".flash_message", 10);

        !empty($expectedMessage)
        && self::assertSelectorTextContains('#flash_message_container .flash_message .toast-body', $expectedMessage);

        $client->refreshCrawler()
            ->filter("#flash_message_container .flash_message .toast-header i[aria-label='Close']")
            ->click();
    }

    private function waitForFlashMessageSuccess(string $expectedMessage = null): void
    {
        $this->waitForFlashMessage(ApiMessages::STATUS_SUCCESS, $expectedMessage);
    }

    private function waitForFlashMessageWarning(string $expectedMessage = null): void
    {
        $this->waitForFlashMessage(ApiMessages::STATUS_WARNING, $expectedMessage);
    }

    private function waitForFlashMessageDanger(string $expectedMessage = null): void
    {
        $this->waitForFlashMessage(ApiMessages::STATUS_DANGER, $expectedMessage);
    }

    private function waitForFlashMessageInfo(string $expectedMessage = null): void
    {
        $this->waitForFlashMessage(ApiMessages::STATUS_INFO, $expectedMessage);
    }
}
