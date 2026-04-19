<?php

namespace Techy4m\XmlExports\Enums;

enum Scheme: string
{
    case PEPPOL = 'peppol';
    case Romania = 'ro_cuis';
    case Spain = 'es';
    case Italy = 'it';
    case Germany = 'de';

    public static function AsSelectArray(): array
    {
        return collect(self::cases())
            ->map(fn(self $case) => ['id' => $case->value, 'name' => $case->getName()])
            ->toArray();
    }

    public function getName(): string
    {
        return match ($this) {
            self::PEPPOL => _l('xml_exports_peppol'),
            self::Romania => _l('xml_exports_romanian'),
            self::Spain => _l('xml_exports_spain'),
            self::Italy => _l('xml_exports_italy'),
            self::Germany => _l('xml_exports_germany'),
            default => $this->name
        };
    }
}
