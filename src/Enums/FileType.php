<?php

namespace Parvez\GalleryManager\Enums;

enum FileType: string
{
    case IMAGE = 'image';
    case VIDEO = 'video';
    case DOCUMENT = 'document';
    case AUDIO = 'audio';
    case OTHER = 'other';

    public static function getValues(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}
