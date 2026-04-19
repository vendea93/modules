<?php

namespace Techy4m\XmlExports\Enums;

enum SPainTaxType: string
{
    case VAT = '01';          // Impuesto sobre el Valor Añadido (IVA)
    case IRPF = '02';         // Retención IRPF
    case OTHER_TAXES = '03';  // Other Taxes
    case EQUIVALENCE_SURCHARGE = '04'; // Recargo de Equivalencia
    case IGIC = '05';         // Impuesto General Indirecto Canario
    case IPSI = '06';         // Impuesto sobre la Producción, los Servicios y la Importación (Ceuta/Melilla)

    /**
     * Get the tax type code by name.
     *
     * @param string $name
     * @return string
     */
    public static function fromName(string $name): string
    {
        return match (strtoupper($name)) {
            'VAT', 'IVF', 'IVA', 'REDUCED VAT', 'IVA REDUCIDO', 'I.V.A.' => self::VAT->value,
            'IRPF', 'RETENCION', strtoupper('retención') => self::IRPF->value,
            'EQUIVALENCE_SURCHARGE' => self::EQUIVALENCE_SURCHARGE->value,
            'IGIC' => self::IGIC->value,
            'IPSI' => self::IPSI->value,
            default => self::OTHER_TAXES->value,
        };
    }
}
