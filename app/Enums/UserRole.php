<?php

namespace App\Enums;

enum UserRole: string
{
    case SUPERADMIN = 'superadmin';
    case ADMIN = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::SUPERADMIN => 'Супер-администратор',
            self::ADMIN => 'Администратор',
        };
    }

    public static function values(): array
    {
        return array_map(
            static fn (self $role) => $role->value,
            self::cases(),
        );
    }
}