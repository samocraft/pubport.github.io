<?php

declare(strict_types=1);

enum ProjectCategory: string
{
    case WebMobileApp = 'web/mobile app';
    case Infographics = 'infographics';
    case VideoEditing = 'video editing';
    case MotionGraphics = 'motion graphics';
    case Art3d = '3d art';
    case AnimationsVfx = '3d animations/vfx';

  public function label(): string
    {
        return match ($this) {
            self::WebMobileApp => 'Web / Mobile App',
            self::Infographics => 'Infographics',
            self::VideoEditing => 'Video Editing',
            self::MotionGraphics => 'Motion Graphics',
            self::Art3d => '3D Art',
            self::AnimationsVfx => '3D Animations / VFX',
        };
    }

    /** @return list<ProjectCategory> */
    public static function all(): array
    {
        return self::cases();
    }

    public static function tryFromInput(string $value): ?self
    {
        $value = trim($value);

        foreach (self::cases() as $case) {
            if ($case->value === $value) {
                return $case;
            }
        }

        return null;
    }
}
