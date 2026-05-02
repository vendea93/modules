# Global Sync 15 Demo

Data: 2026-04-29 18:15:52 UTC

## Założenia
- Brak zmian w core.
- Synchronizacja ustawień tenantów: branding + moduły bazowe.
- Źródło mapowania: `modules/fq_saas/tools/tenant_sync_map.tsv`.

## Wyniki

| Slug | HTTP | Logo | company_logo_dark | Tabela options | Tabela modules | Core modules | Branżowy | Dodatkowe | Status |
|---|---:|---|---|---|---|---|---|---|---|
| agencja | 200 | fq_agencja_logo.png | fq_agencja_logo.png | agencja_tbloptions | agencja_tblmodules | einvoice,form_sync,menu_setup,theme_style | zillapage | prchat | OK |
| beauty | 200 | fq_beauty_logo.png | fq_beauty_logo.png | beauty_tbloptions | beauty_tblmodules | einvoice,form_sync,menu_setup,theme_style | - | appointly,prchat,zillapage | OK |
| demo | 200 | fq_demo_logo.png | fq_demo_logo.png | demo_tbloptions | demo_tblmodules | einvoice,form_sync,menu_setup,theme_style | core | - | OK |
| ecommerce | 200 | fq_ecommerce_logo.png | fq_ecommerce_logo.png | ecommerce_tbloptions | ecommerce_tblmodules | einvoice,form_sync,menu_setup,theme_style | - | prchat | OK |
| eventy | 200 | fq_eventy_logo.png | fq_eventy_logo.png | eventy_tbloptions | eventy_tblmodules | einvoice,form_sync,menu_setup,theme_style | - | prchat | OK |
| gastronomia | 200 | fq_gastronomia_logo.png | fq_gastronomia_logo.png | gastronomia_tbloptions | gastronomia_tblmodules | einvoice,form_sync,menu_setup,theme_style | - | appointly,prchat | OK |
| hotel | 200 | fq_hotel_logo.png | fq_hotel_logo.png | hotel_tbloptions | hotel_tblmodules | einvoice,form_sync,menu_setup,theme_style | - | prchat | OK |
| kursy | 200 | fq_kursy_logo.png | fq_kursy_logo.png | kursy_tbloptions | kursy_tblmodules | einvoice,form_sync,menu_setup,theme_style | flexacademy | prchat,zillapage | OK |
| logistyka | 200 | fq_logistyka_logo.png | fq_logistyka_logo.png | logistyka_tbloptions | logistyka_tblmodules | einvoice,form_sync,menu_setup,theme_style | logistic | prchat | OK |
| medycyna | 200 | fq_medycyna_logo.png | fq_medycyna_logo.png | medycyna_tbloptions | medycyna_tblmodules | einvoice,form_sync,menu_setup,theme_style | - | appointly,prchat | OK |
| nieruchomosci | 200 | fq_nieruchomosci_logo.png | fq_nieruchomosci_logo.png | nieruchomosci_tbloptions | nieruchomosci_tblmodules | einvoice,form_sync,menu_setup,theme_style | realestate | prchat | OK |
| oze | 200 | fq_oze_logo.png | fq_oze_logo.png | oze_tbloptions | oze_tblmodules | einvoice,form_sync,menu_setup,theme_style | projects | prchat,zillapage | OK |
| rekrutacja | 200 | fq_rekrutacja_logo.png | fq_rekrutacja_logo.png | rekrutacja_tbloptions | rekrutacja_tblmodules | einvoice,form_sync,menu_setup,theme_style | recruitment | prchat | OK |
| serwiswww | 200 | fq_serwiswww_logo.png | fq_serwiswww_logo.png | serwiswww_tbloptions | serwiswww_tblmodules | einvoice,form_sync,menu_setup,theme_style | website_maintenance_management | prchat | OK |
| warsztat | 200 | fq_warsztat_logo.png | fq_warsztat_logo.png | warsztat_tbloptions | warsztat_tblmodules | einvoice,form_sync,menu_setup,theme_style | workshop | prchat | OK |

## Podsumowanie
- OK: 15
- PARTIAL: 0
- MISSING_TABLES: 0
