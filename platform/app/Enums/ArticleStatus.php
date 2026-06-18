<?php

namespace App\Enums;

enum ArticleStatus: string
{
    case Draft = 'draft';
    case Review = 'review';
    case Rejected = 'rejected';
    case Scheduled = 'scheduled';
    case Published = 'published';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => '草稿',
            self::Review => '待审',
            self::Rejected => '退回',
            self::Scheduled => '定时发布',
            self::Published => '已发布',
            self::Archived => '归档',
        };
    }
}
