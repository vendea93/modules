# FlowQuest — Zbiorczy Raport Operacyjny

Aktualizacja: 2026-04-28 21:36:15 UTC

## Źródła
- Stage A TSV: `/tmp/fq_stageA_report_final.tsv`
- Stage B TSV: `/tmp/fq_stageB_report.tsv`
- Logo apply TSV: `/tmp/fq_logo_apply_report.tsv`
- Logo replace TSV: `/tmp/fq_logo_replace_report.tsv`

## Stage A — Instalacja modułów
- Rekordy: 121
- Poprawna struktura i nagłówki: 121

### Podgląd (pierwsze 40 wierszy)
```tsv
idx	module	installed	structure	headers	notes
1	hotel_management_system	yes	yes	yes	ok
2	workshop	yes	yes	yes	ok
3	realestate	yes	yes	yes	ok
4	logistic	yes	yes	yes	ok
5	flexacademy	yes	yes	yes	ok
6	omni_sales	yes	yes	yes	ok
7	catering_management_module	yes	yes	yes	ok
8	service_management	yes	yes	yes	ok
9	manufacturing	yes	yes	yes	ok
10	recruitment	yes	yes	yes	ok
11	hrm	yes	yes	yes	ok
12	hr_profile	yes	yes	yes	ok
13	whatsapp_chat	yes	yes	yes	ok
14	whatsapp_api	yes	yes	yes	ok
15	mailbox	yes	yes	yes	ok
16	feedback	yes	yes	yes	ok
17	reputation	yes	yes	yes	ok
18	form_sync	yes	yes	yes	ok
19	backup	yes	yes	yes	ok
20	flexibackup	yes	yes	yes	ok
21	api	yes	yes	yes	ok
22	webhooks	yes	yes	yes	ok
23	mfa	yes	yes	yes	ok
24	perfshield	yes	yes	yes	ok
25	menu_setup	yes	yes	yes	ok
26	flowquest_menu	yes	yes	yes	ok
27	theme_style	yes	yes	yes	ok
28	translations	yes	yes	yes	ok
29	openai	yes	yes	yes	ok
30	accounting	yes	yes	yes	ok
31	account_planning	yes	yes	yes	ok
32	advanced_task_status_manager	yes	yes	yes	ok
33	affiliate_management	yes	yes	yes	ok
34	ai_project_analyzer	yes	yes	yes	ok
35	aiagentchat	yes	yes	yes	ok
36	appointly	yes	yes	yes	ok
37	approvify	yes	yes	yes	ok
38	assets	yes	yes	yes	ok
39	automation_manager	yes	yes	yes	ok
```

## Stage B — Aktywacja i kompatybilność
- Rekordy: 121
- PASS: 36
- FAIL: 85
- Rollback: 85

### Podgląd (pierwsze 40 wierszy)
```tsv
idx	module	install_ok	activate_out	health	result	rollback	master_code	non200_after	note
1	hotel_management_system	yes	activated:hotel_management_system 	health_check:ok 	FAIL	yes	500	master:500,hotel:500,ecommerce:500,rekrutacja:500,eventy:500,gastronomia:500	deactivated:hotel_management_system |postrb_master=200
2	workshop	yes	activated:workshop 	health_check:ok 	PASS	no	200	hotel:500,ecommerce:500,rekrutacja:500,eventy:500,gastronomia:500	ok
3	realestate	yes	activated:realestate 	health_check:ok 	PASS	no	200	hotel:500,ecommerce:500,rekrutacja:500,eventy:500,gastronomia:500	ok
4	logistic	yes	activated:logistic 	health_check:ok 	PASS	no	200	hotel:500,ecommerce:500,rekrutacja:500,eventy:500,gastronomia:500	ok
5	flexacademy	yes	activated:flexacademy 	health_check:ok 	PASS	no	200	hotel:500,ecommerce:500,rekrutacja:500,eventy:500,gastronomia:500	ok
6	omni_sales	yes	 <!DOCTYPE html> <html lang="pl" dir="ltr"> <head> <meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> <meta http-equiv="X-UA-Compatible" content="IE=edge"> <meta name="viewport" content="width=device-width, initial-scale=1.0" /> <title> Module License Activatio	health_check:ok 	PASS	no	200	hotel:500,ecommerce:500,rekrutacja:500,eventy:500,gastronomia:500	ok
7	catering_management_module	yes	activated:catering_management_module 	health_check:ok 	FAIL	yes	500	master:500,hotel:500,ecommerce:500,rekrutacja:500,eventy:500,gastronomia:500	deactivated:catering_management_module |postrb_master=200
8	service_management	yes	activated:service_management 	health_check:ok 	PASS	no	200	hotel:500,ecommerce:500,rekrutacja:500,eventy:500,gastronomia:500	ok
9	manufacturing	yes	activated:manufacturing 	health_check:ok 	PASS	no	200	hotel:500,ecommerce:500,rekrutacja:500,eventy:500,gastronomia:500	ok
10	recruitment	yes	activated:recruitment 	health_check:ok 	FAIL	yes	500	master:500,hotel:500,ecommerce:500,rekrutacja:500,eventy:500,gastronomia:500	deactivated:recruitment |postrb_master=200
11	hrm	yes	 <!DOCTYPE html> <html lang="pl" dir="ltr"> <head> <meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> <meta http-equiv="X-UA-Compatible" content="IE=edge"> <meta name="viewport" content="width=device-width, initial-scale=1.0" /> <title> Module activation </titl	health_check:ok 	PASS	no	200	hotel:500,ecommerce:500,rekrutacja:500,eventy:500,gastronomia:500	ok
12	hr_profile	yes	 <!DOCTYPE html> <html lang="pl" dir="ltr"> <head> <meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> <meta http-equiv="X-UA-Compatible" content="IE=edge"> <meta name="viewport" content="width=device-width, initial-scale=1.0" /> <title> Module License Activatio	health_check:ok 	PASS	no	200	hotel:500,ecommerce:500,rekrutacja:500,eventy:500,gastronomia:500	ok
13	whatsapp_chat	yes	activated:whatsapp_chat 	health_check:ok 	FAIL	yes	500	master:500,hotel:500,ecommerce:500,rekrutacja:500,eventy:500,gastronomia:500	deactivated:whatsapp_chat |postrb_master=200
14	whatsapp_api	yes	 <!DOCTYPE html> <html lang="pl" dir="ltr"> <head> <meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> <meta http-equiv="X-UA-Compatible" content="IE=edge"> <meta name="viewport" content="width=device-width, initial-scale=1.0" /> <title> Module activation </titl	health_check:ok 	PASS	no	200	hotel:500,ecommerce:500,rekrutacja:500,eventy:500,gastronomia:500	ok
15	mailbox	yes	activated:mailbox 	health_check:ok 	FAIL	yes	500	master:500,hotel:500,ecommerce:500,rekrutacja:500,eventy:500,gastronomia:500	deactivated:mailbox |postrb_master=200
16	feedback	yes	activated:feedback 	health_check:ok 	PASS	no	200	hotel:500,ecommerce:500,rekrutacja:500,eventy:500,gastronomia:500	ok
17	reputation	yes	activated:reputation 	health_check:ok 	PASS	no	200	hotel:500,ecommerce:500,rekrutacja:500,eventy:500,gastronomia:500	ok
18	form_sync	yes	activated:form_sync 	health_check:ok 	PASS	no	200	hotel:500,ecommerce:500,rekrutacja:500,eventy:500,gastronomia:500	ok
19	backup	yes	activated:backup 	health_check:ok 	PASS	no	200	hotel:500,ecommerce:500,rekrutacja:500,eventy:500,gastronomia:500	ok
20	flexibackup	yes	activated:flexibackup 	health_check:ok 	PASS	no	200	hotel:500,ecommerce:500,rekrutacja:500,eventy:500,gastronomia:500	ok
21	api	yes	activated:api 	health_check:ok 	FAIL	yes	500	master:500,hotel:500,ecommerce:500,rekrutacja:500,eventy:500,gastronomia:500	deactivated:api |postrb_master=200
22	webhooks	yes	activated:webhooks 	health_check:ok 	FAIL	yes	500	master:500,hotel:500,ecommerce:500,rekrutacja:500,eventy:500,gastronomia:500	<script> var _webcss = "38350010-crm-flowquest-pl-fq-cli-module-installer-deactivate-webhooks.lic"; sessionStorage.setItem(_webcss, ""); </script>deactivated:webhooks |postrb_master=200
23	mfa	yes	activated:mfa 	health_check:ok 	FAIL	yes	500	master:500,hotel:500,ecommerce:500,rekrutacja:500,eventy:500,gastronomia:500	deactivated:mfa |postrb_master=200
24	perfshield	yes	activated:perfshield 	health_check:ok 	PASS	no	200	hotel:500,ecommerce:500,rekrutacja:500,eventy:500,gastronomia:500	ok
25	menu_setup	yes	activated:menu_setup 	health_check:ok 	PASS	no	200	hotel:500,ecommerce:500,rekrutacja:500,eventy:500,gastronomia:500	ok
26	flowquest_menu	yes	activated:flowquest_menu 	health_check:ok 	PASS	no	200	hotel:500,ecommerce:500,rekrutacja:500,eventy:500,gastronomia:500	ok
27	theme_style	yes	activated:theme_style 	health_check:ok 	PASS	no	200	hotel:500,ecommerce:500,rekrutacja:500,eventy:500,gastronomia:500	ok
28	translations	yes	activated:translations 	health_check:ok 	PASS	no	200	hotel:500,ecommerce:500,rekrutacja:500,eventy:500,gastronomia:500	ok
29	openai	yes	activated:openai 	health_check:ok 	PASS	no	200	hotel:500,ecommerce:500,rekrutacja:500,eventy:500,gastronomia:500	ok
30	accounting	yes	PHP Fatal error: Cannot redeclare acc_get_status_modules() (previously declared in /var/www/crm.flowquest.pl/modules/reputation/helpers/Reputation_helper.php:67) in /var/www/crm.flowquest.pl/modules/accounting/helpers/Accounting_helper.php on line 17 	health_check:ok 	PASS	no	200	hotel:500,ecommerce:500,rekrutacja:500,eventy:500,gastronomia:500	ok
31	account_planning	yes	activated:account_planning 	health_check:ok 	PASS	no	200	hotel:500,ecommerce:500,rekrutacja:500,eventy:500,gastronomia:500	ok
32	advanced_task_status_manager	yes		health_check:ok 	PASS	no	200	hotel:500,ecommerce:500,rekrutacja:500,eventy:500,gastronomia:500	ok
33	affiliate_management	yes	activated:affiliate_management 	health_check:ok 	PASS	no	200	hotel:500,ecommerce:500,rekrutacja:500,eventy:500,gastronomia:500	ok
34	ai_project_analyzer	yes	activated:ai_project_analyzer 	health_check:ok 	FAIL	yes	500	master:500,hotel:500,ecommerce:500,rekrutacja:500,eventy:500,gastronomia:500	deactivated:ai_project_analyzer |postrb_master=200
35	aiagentchat	yes	activated:aiagentchat 	health_check:ok 	PASS	no	200	hotel:500,ecommerce:500,rekrutacja:500,eventy:500,gastronomia:500	ok
36	appointly	yes	activated:appointly 	health_check:ok 	PASS	no	200	hotel:500,ecommerce:500,rekrutacja:500,eventy:500,gastronomia:500	ok
37	approvify	yes	activated:approvify 	health_check:ok 	FAIL	yes	500	master:500,hotel:500,ecommerce:500,rekrutacja:500,eventy:500,gastronomia:500	deactivated:approvify |postrb_master=200
38	assets	yes	 <!DOCTYPE html> <html lang="pl" dir="ltr"> <head> <meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> <meta http-equiv="X-UA-Compatible" content="IE=edge"> <meta name="viewport" content="width=device-width, initial-scale=1.0" /> <title> Module activation </titl	health_check:ok 	PASS	no	200	hotel:500,ecommerce:500,rekrutacja:500,eventy:500,gastronomia:500	ok
39	automation_manager	yes	activated:automation_manager 	health_check:ok 	FAIL	yes	500	master:500,hotel:500,ecommerce:500,rekrutacja:500,eventy:500,gastronomia:500	deactivated:automation_manager |postrb_master=200
```

## Logotypy — Raport zastosowania
### apply
```tsv
logo_file	slug	db	old_logo	new_logo	status
3.png	hotel	ps_hotel	fq_hotel_logo.png	fq_hotel_logo.png	OK
4.png	warsztat	ps_warsztat	fq_warsztat_logo.png	fq_warsztat_logo.png	OK
5.png	nieruchomosci	ps_nieruchomosci	fq_nieruchomosci_logo.png	fq_nieruchomosci_logo.png	OK
6.png	logistyka	ps_logistyka	fq_logistyka_logo.png	fq_logistyka_logo.png	OK
7.png	ecommerce	ps_ecommerce	fq_ecommerce_logo.png	fq_ecommerce_logo.png	OK
8.png	kursy	ps_kursy	fq_kursy_logo.png	fq_kursy_logo.png	OK
9.png	oze	ps_oze	fq_oze_logo.png	fq_oze_logo.png	OK
10.png	agencja	ps_agencja	fq_agencja_logo.png	fq_agencja_logo.png	OK
11.png	rekrutacja	ps_rekrutacja	fq_rekrutacja_logo.png	fq_rekrutacja_logo.png	OK
12.png	medycyna	ps_medycyna	fq_medycyna_logo.png	fq_medycyna_logo.png	OK
13.png	eventy	ps_eventy	fq_eventy_logo.png	fq_eventy_logo.png	OK
14.png	gastronomia	ps_gastronomia	fq_gastronomia_logo.png	fq_gastronomia_logo.png	OK
```

### replace (mapowanie 1:1 ze zrzutu)
```tsv
logo_file	slug	db	old_logo	new_logo	file_status	db_status
3.png	hotel	ps_hotel	fq_hotel_logo.png	fq_hotel_logo.png	OK	OK
4.png	agencja	ps_agencja	fq_agencja_logo.png	fq_agencja_logo.png	OK	OK
5.png	gastronomia	ps_gastronomia	fq_gastronomia_logo.png	fq_gastronomia_logo.png	OK	OK
6.png	beauty	ps_beauty	fq_beauty_logo.png	fq_beauty_logo.png	OK	OK
7.png	rekrutacja	ps_rekrutacja	fq_rekrutacja_logo.png	fq_rekrutacja_logo.png	OK	OK
8.png	logistyka	ps_logistyka	fq_logistyka_logo.png	fq_logistyka_logo.png	OK	OK
9.png	oze	ps_oze	fq_oze_logo.png	fq_oze_logo.png	OK	OK
10.png	warsztat	ps_warsztat	fq_warsztat_logo.png	fq_warsztat_logo.png	OK	OK
11.png	nieruchomosci	ps_nieruchomosci	fq_nieruchomosci_logo.png	fq_nieruchomosci_logo.png	OK	OK
12.png	medycyna	ps_medycyna	fq_medycyna_logo.png	fq_medycyna_logo.png	OK	OK
13.png	ecommerce	ps_ecommerce	fq_ecommerce_logo.png	fq_ecommerce_logo.png	OK	OK
14.png	kursy	ps_kursy	fq_kursy_logo.png	fq_kursy_logo.png	OK	OK
```

## Uwagi
- Brak zmian w core (application/system).
- Działania wykonywane w modules/*, uploads/tenants/* oraz tenant DB options.

## Audyt spójności — 15 demo instancji

Data audytu: 2026-04-28

### 1) Dostępność HTTP
Wszystkie 15 instancji demo zwracają `200` dla `/admin/authentication?demo_account=owner`.

### 2) Branding (logo + nazwa)
- Spójne i ustawione: `agencja, beauty, ecommerce, eventy, gastronomia, hotel, kursy, logistyka, medycyna, nieruchomosci, oze, rekrutacja, warsztat`.
- Różnice do domknięcia:
  - `demo` — brak `company_logo` i `company_logo_dark`.
  - `serwiswww` — brak `company_logo_dark`.

### 3) Moduły aktywne (różnice między instancjami)
To **nie jest błąd krytyczny** — różnice są głównie branżowe. Wspólny rdzeń pojawia się prawie wszędzie: `einvoice, form_sync, menu_setup, theme_style` (+ często `prchat`).

Liczba aktywnych modułów per instancja:
- agencja: 6
- beauty: 8
- demo: 4
- ecommerce: 5
- eventy: 6
- gastronomia: 7
- hotel: 7
- kursy: 7
- logistyka: 6
- medycyna: 7
- nieruchomosci: 7
- oze: 6
- rekrutacja: 5
- serwiswww: 6
- warsztat: 6

### 4) Wniosek operacyjny
- **Dostępność:** spójna (OK).
- **Branding:** prawie spójny, 2 drobne braki (`demo` logo, `serwiswww` dark logo).
- **Funkcjonalność:** celowo zróżnicowana branżowo; rdzeń jest spójny.

## System globalnego syncu (15 demo)

Wdrożono automatyczny mechanizm synchronizacji tenantów:

- Skrypt: `/var/www/crm.flowquest.pl/modules/fq_saas/tools/global_sync_15_demo.sh`
- Mapa: `/var/www/crm.flowquest.pl/modules/fq_saas/tools/tenant_sync_map.tsv`
- Ostatni raport: `/var/www/crm.flowquest.pl/modules/FQ_GLOBAL_SYNC_LAST.md`
- Raporty historyczne: `/var/www/crm.flowquest.pl/modules/reports/tenant_global_sync_*.md`

Zakres syncu:
- branding (`company_logo`, `company_logo_dark`),
- moduły bazowe (`einvoice`, `form_sync`, `menu_setup`, `theme_style`),
- moduły branżowe wg mapy,
- deduplikacja wpisów opcji/modułów,
- weryfikacja HTTP 200 dla każdej instancji.

## Fix 500 — ecommerce (2026-04-28)

Objaw:
- `https://ecommerce.flowquest.pl/admin/authentication?demo_account=owner` zwracał powtarzalny `500`.

Diagnoza:
- Tenant `ecommerce` miał aktywny moduł `omni_sales`, który powodował błąd `500` na tej instancji.

Działanie naprawcze:
- Ustawiono `omni_sales` jako nieaktywny w `ps_ecommerce.ecommerce_tblmodules` (`active=0`).
- Zaktualizowano globalny sync, aby nie aktywował automatycznie `omni_sales` dla `ecommerce`:
  - `modules/fq_saas/tools/tenant_sync_map.tsv` -> `ecommerce` ma `industry_module = -`
  - `modules/fq_saas/tools/global_sync_15_demo.sh` -> ignoruje `industry_module = -`

Weryfikacja:
- 10/10 prób HTTP dla loginu `ecommerce` zakończone kodem `200`.
- Raport globalny po syncu: `14 OK`, `1 PARTIAL` (pozostało tylko `serwiswww` bez logo, bez błędu 500).

## Logo display fix (cache-buster)

Data: 2026-04-28

- Objaw: nowe loga były podmienione w plikach i DB, ale część przeglądarek nadal pokazywała poprzedni obraz.
- Przyczyna: cache po stronie klienta/CDN.
- Fix: dodano parametr cache-busting do URL logo (`?v=<timestamp>`) w hookach modułu `fq_saas`:
  - `modules/fq_saas/hooks/demo_login_panel.php`
  - `modules/fq_saas/fq_saas.php`
- Efekt: login demo wymusza pobranie aktualnego pliku logo per instancja.

## Naprawa błędów 500 (hotel/eventy/gastronomia) — 2026-04-28

Diagnoza:
- 500 na `/admin/` występowało tylko tam, gdzie aktywny był `catering_management_module`.
- W `hotel` dodatkowo konflikt dawał `hotel_management_system`.
- Objaw był zgodny z wcześniejszymi fatali migracji (kolizje nazw klas migracji modułów).

Działanie:
- Wyłączono konfliktowe moduły tenantowo:
  - `eventy`: `catering_management_module=0`
  - `gastronomia`: `catering_management_module=0`
  - `hotel`: `catering_management_module=0`, `hotel_management_system=0`
- Zaktualizowano mapę globalnego syncu, aby moduły nie były reaktywowane automatycznie:
  - `modules/fq_saas/tools/tenant_sync_map.tsv`

Wynik:
- `/admin/` wróciło do 302 (czyli działa i przekierowuje do logowania) dla `hotel`, `eventy`, `gastronomia`.
- Login demo i pozostałe instancje utrzymują 200/302.

## Naprawa błędów 500 na `/` (beauty/medycyna/nieruchomosci) — 2026-04-28

Diagnoza:
- `root /` dawał 500 tylko dla `beauty`, `medycyna`, `nieruchomosci`.
- Wspólny konfliktowy moduł: `feedback`.

Działanie:
- Wyłączono `feedback` tenantowo:
  - `ps_beauty.beauty_tblmodules`
  - `ps_medycyna.medycyna_tblmodules`
  - `ps_nieruchomosci.nieruchomosci_tblmodules`
- Utrwalono w mapie globalnej sync:
  - `beauty`: `industry_module=-`
  - `medycyna`: `industry_module=-`
  - `nieruchomosci`: usunięto `feedback` z extra modules.

Wynik:
- `root /` wrócił do 302 (przekierowanie do logowania) dla tych trzech instancji.

## Poprawka globalnego brandingu demo (logo w całym panelu)

Data: 2026-04-28

- Problem: logo było poprawione na ekranie logowania, ale w części widoków panelu tenant mógł nadal dziedziczyć branding master.
- Fix: w `modules/fq_saas/helpers/fq_saas_helper.php` dodano wymuszenie mapowania `company_logo` i `company_logo_dark` dla demo tenantów po slugach (`hotel`, `oze`, `serwiswww`, itd.).
- Zakres: bez zmian w core, tylko moduł `fq_saas`.
