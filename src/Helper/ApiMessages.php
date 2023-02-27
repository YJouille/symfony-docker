<?php

namespace App\Helper;

class ApiMessages
{
    const INDEX_STATUS = "status";
    const INDEX_MESSAGE = "message";
    const STATUS_SUCCESS = "success";
    const STATUS_WARNING = "warning";
    const STATUS_DANGER = "danger";
    const STATUS_INFO = "info";
    const STATUS_PRIMARY = "primary";
    const STATUS_SECONDARY = "secondary";
    const DEFAULT_ERROR_MESSAGE = "Oops, une erreur est survenue...";
    const FRONT_MESSAGES = [
        self::STATUS_SUCCESS => "Action réalisée",
        self::STATUS_WARNING => "Attention",
        self::STATUS_DANGER => "Erreur",
        self::STATUS_INFO => "Note informative",
        self::STATUS_PRIMARY => "Note",
        self::STATUS_SECONDARY => "Note",
    ];

    public static function makeContent(string $status, string $message): array
    {
        return [
            ApiMessages::INDEX_STATUS => $status,
            ApiMessages::INDEX_MESSAGE => $message,
        ];
    }

    public static function makeDefaultErrorContent(): array
    {
        return ApiMessages::makeContent(
            ApiMessages::STATUS_DANGER,
            ApiMessages::DEFAULT_ERROR_MESSAGE,
        );
    }
}
