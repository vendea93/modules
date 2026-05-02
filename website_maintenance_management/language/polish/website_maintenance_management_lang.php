<?php

defined('BASEPATH') or exit('No direct script access allowed');

// License
$lang['wmm_zegaware_license']              = 'Licencja WMM';
$lang['wmm_zegaware_license_title']        = 'Licencja modułu Website Management';
$lang['invalid_license']                   = 'Nieprawidłowa licencja';
$lang['invalid_license_module']            = 'Nieprawidłowa licencja';
$lang['license_activated']                 = 'Licencja została aktywowana';
$lang['license_expired']                   = 'Licencja wygasła';
$lang['invalid_license_domain']            = 'Nieprawidłowa domena licencji';
$lang['invalid_license_email']             = 'Nieprawidłowy e-mail licencji';
$lang['activation_error']                  = 'Błąd aktywacji licencji';
$lang['require_license']                   = 'Wymagana licencja';
$lang['zegaware_license_key']              = 'Klucz licencyjny';
$lang['zegaware_license_activate']         = 'Aktywuj';
$lang['zegaware_your_license']             = 'Twój klucz licencyjny';
$lang['zegaware_your_name']                = 'Twoje imię';
$lang['zegaware_your_email']               = 'Twój e-mail';
$lang['zegaware_activated_at']             = 'Aktywowano';
$lang['zegaware_remove_license']           = 'Usuń licencję';
$lang['zegaware_activated_success']        = 'Licencja aktywowana';
$lang['zegaware_removed_success']          = 'Licencja usunięta';
$lang['zegaware_require_license']          = 'Wymagana licencja';
$lang['zegaware_customer_name']            = 'Imię';
$lang['zegaware_customer_email']           = 'E-mail';
$lang['zegaware_customer_envato_username'] = 'Nazwa użytkownika Envato';

// General
$lang['wmm_website_maintenance'] = 'Website Management';
$lang['wmm_dashboard']           = 'Pulpit';
$lang['wmm_maintenance_tasks']   = 'Zadania utrzymaniowe';
$lang['wmm_manage_websites']     = 'Zarządzanie stronami';
$lang['wmm_perform_maintenance'] = 'Wykonaj prace utrzymaniowe';
$lang['wmm_maintenance_logs']    = 'Logi utrzymaniowe';
$lang['wmm_log_maintenance']     = 'Dodaj log utrzymania';
$lang['wmm_problem_updating']    = 'Problem z aktualizacją: %s';
$lang['wmm_categories']          = 'Kategorie';
$lang['wmm_websites']            = 'Strony';
$lang['wmm_calendar']            = 'Kalendarz';
$lang['wmm_support_packages']    = 'Pakiety wsparcia';
$lang['wmm_package_usage_history'] = 'Historia wykorzystania pakietów';
$lang['wmm_reports_analytics']   = 'Raporty i analityka';

// Tasks
$lang['wmm_maintenance_task'] = 'Zadanie utrzymaniowe';
$lang['wmm_add_new_task']     = 'Dodaj nowe zadanie';
$lang['wmm_task_name']        = 'Nazwa zadania';
$lang['wmm_category']         = 'Kategoria';
$lang['wmm_description']      = 'Opis';
$lang['wmm_status']           = 'Status';
$lang['wmm_is_active']        = 'Aktywne';
$lang['wmm_active']           = 'Aktywne';
$lang['wmm_inactive']         = 'Nieaktywne';
$lang['wmm_created_at']       = 'Utworzono';
$lang['wmm_task_has_logs']    = 'Nie można usunąć tego zadania, ponieważ zostało użyte w logach utrzymaniowych.';

// Categories
$lang['wmm_category_plugin'] = 'Aktualizacja wtyczek';
$lang['wmm_category_theme']  = 'Aktualizacja motywu';
$lang['wmm_category_core']   = 'Aktualizacja systemu';
$lang['wmm_category_other']  = 'Inne';

// Websites
$lang['wmm_website']                     = 'Strona';
$lang['wmm_add_website_to_maintenance']  = 'Dodaj stronę do utrzymania';
$lang['wmm_websites_under_maintenance']  = 'Strony w utrzymaniu';
$lang['wmm_select_customer']             = 'Wybierz klienta';
$lang['wmm_select_project']              = 'Wybierz projekt';
$lang['wmm_select_project_first']        = 'Najpierw wybierz klienta';
$lang['wmm_website_url']                 = 'Adres strony';
$lang['wmm_add_to_maintenance']          = 'Dodaj do utrzymania';
$lang['wmm_website_added_successfully']  = 'Strona została dodana do utrzymania';
$lang['wmm_website_add_failed']          = 'Nie udało się dodać strony do utrzymania';
$lang['wmm_project_already_added']       = 'Ten projekt został już dodany do utrzymania';
$lang['wmm_website_has_logs']            = 'Nie można usunąć tej strony, ponieważ ma logi utrzymaniowe.';
$lang['wmm_customer']                    = 'Klient';
$lang['wmm_project']                     = 'Projekt';
$lang['wmm_date_added']                  = 'Data dodania';

// Perform Maintenance
$lang['wmm_no_websites_available']           = 'Brak stron dostępnych do utrzymania. Najpierw dodaj strony.';
$lang['wmm_add_websites']                    = 'Dodaj strony';
$lang['wmm_select_website']                  = 'Wybierz stronę';
$lang['wmm_select_completed_tasks']          = 'Wybierz wykonane zadania';
$lang['wmm_select_tasks_description']        = 'Zaznacz wszystkie zadania wykonane w tej sesji utrzymaniowej.';
$lang['wmm_loading_tasks']                   = 'Ładowanie zadań...';
$lang['wmm_no_tasks_available']              = 'Brak aktywnych zadań. Najpierw dodaj zadania.';
$lang['wmm_notes']                           = 'Notatki';
$lang['wmm_notes_placeholder']               = 'Dodaj dodatkowe notatki do tej sesji utrzymaniowej (opcjonalnie)';
$lang['wmm_maintenance_completed']           = 'Utrzymanie zakończone';
$lang['wmm_please_select_tasks']             = 'Wybierz co najmniej jedno wykonane zadanie.';
$lang['wmm_confirm_maintenance_complete']    = 'Czy na pewno chcesz oznaczyć to utrzymanie jako zakończone? E-mail zostanie wysłany do klienta.';
$lang['wmm_maintenance_logged_successfully'] = 'Log utrzymania został zapisany i wysłano powiadomienie do klienta.';
$lang['wmm_maintenance_log_failed']          = 'Nie udało się zapisać logu utrzymania. Spróbuj ponownie.';
$lang['wmm_select_tasks']                    = 'Wybierz zadania';

// Logs
$lang['wmm_maintenance_log']         = 'Log utrzymania';
$lang['wmm_maintenance_log_details'] = 'Szczegóły logu utrzymania';
$lang['wmm_performed_by']            = 'Wykonane przez';
$lang['wmm_performed_at']            = 'Wykonano';
$lang['wmm_maintenance_date']        = 'Data utrzymania';
$lang['wmm_email_status']            = 'Status e-maila';
$lang['wmm_email_sent']              = 'E-mail wysłany';
$lang['wmm_email_not_sent']          = 'E-mail nie został wysłany';
$lang['wmm_resend_email']            = 'Wyślij ponownie e-mail';
$lang['wmm_send_email']              = 'Wyślij e-mail';
$lang['wmm_confirm_resend_email']    = 'Czy na pewno chcesz ponownie wysłać e-mail z powiadomieniem?';
$lang['wmm_confirm_send_email']      = 'Czy na pewno chcesz wysłać e-mail z powiadomieniem?';
$lang['wmm_email_sent_successfully'] = 'E-mail został wysłany';
$lang['wmm_email_send_failed']       = 'Nie udało się wysłać e-maila. Sprawdź konfigurację poczty.';
$lang['wmm_tasks_completed']         = 'Wykonane zadania';
$lang['wmm_no_tasks_completed']      = 'W tej sesji nie wykonano żadnych zadań.';
$lang['wmm_log_not_found']           = 'Nie znaleziono logu utrzymania.';

// Email Template
$lang['wmm_email_subject']  = 'Zakończono prace utrzymaniowe na stronie';
$lang['wmm_email_greeting'] = 'Dzień dobry {client_name},';
$lang['wmm_email_body']     = 'Informujemy, że wykonaliśmy prace utrzymaniowe na Twojej stronie internetowej.';
$lang['wmm_email_closing']  = 'Jeśli masz pytania lub uwagi, skontaktuj się z nami.';

// Attachments
$lang['wmm_attachments']                 = 'Załączniki';
$lang['wmm_attachment']                  = 'Załącznik';
$lang['wmm_no_attachments']              = 'Brak załączników';
$lang['wmm_download_all']                = 'Pobierz wszystko';
$lang['wmm_drop_files_here']             = 'Upuść pliki tutaj, aby je przesłać';
$lang['wmm_or_click_to_browse']          = 'lub kliknij, aby wybrać';
$lang['wmm_upload_files']                = 'Prześlij pliki';
$lang['wmm_files_uploaded_successfully'] = 'Pliki zostały przesłane';
$lang['wmm_upload_failed']               = 'Przesyłanie nie powiodło się. Spróbuj ponownie.';

// Quick Actions
$lang['wmm_copy_link']   = 'Kopiuj link';
$lang['wmm_link_copied'] = 'Link skopiowany do schowka';

// Additional
$lang['id']       = 'ID';
$lang['view']     = 'Podgląd';
$lang['back']     = 'Wstecz';
$lang['download'] = 'Pobierz';

// Priorities
$lang['wmm_priority']        = 'Priorytet';
$lang['wmm_priority_low']    = 'Niski';
$lang['wmm_priority_medium'] = 'Średni';
$lang['wmm_priority_high']   = 'Wysoki';
$lang['wmm_priority_urgent'] = 'Pilny';

// Task Statuses
$lang['wmm_status_not_started']       = 'Nie rozpoczęto';
$lang['wmm_status_in_progress']       = 'W trakcie';
$lang['wmm_status_testing']           = 'Testowanie';
$lang['wmm_status_awaiting_feedback'] = 'Oczekuje na feedback';
$lang['wmm_status_complete']          = 'Zakończone';

// Timer & Time Tracking
$lang['wmm_start_timer']           = 'Uruchom timer';
$lang['wmm_stop_timer']            = 'Zatrzymaj timer';
$lang['wmm_timer_already_running'] = 'Timer już działa dla tego zadania';
$lang['wmm_no_active_timer']       = 'Nie znaleziono aktywnego timera';
$lang['wmm_time_logged']           = 'Zalogowany czas';
$lang['wmm_total_time']            = 'Łączny czas';
$lang['wmm_billable']              = 'Rozliczalne';
$lang['wmm_hourly_rate']           = 'Stawka godzinowa';
$lang['wmm_time_h']                = '%s godzin';
$lang['wmm_time_m']                = '%s minut';
$lang['wmm_no_time_logged']        = 'Brak zalogowanego czasu';
$lang['wmm_add_time_entry']        = 'Dodaj wpis czasu';
$lang['wmm_edit_time_entry']       = 'Edytuj wpis czasu';
$lang['wmm_delete_time_entry']     = 'Usuń wpis czasu';
$lang['wmm_confirm_delete_time']   = 'Czy na pewno chcesz usunąć ten wpis czasu?';

// Assignees
$lang['wmm_assignees']       = 'Przypisani';
$lang['wmm_assign_to']       = 'Przypisz do';
$lang['wmm_assigned_to']     = 'Przypisano do %s';
$lang['wmm_no_assignees']    = 'Brak przypisanych osób';
$lang['wmm_add_assignees']   = 'Dodaj przypisanych';
$lang['wmm_remove_assignee'] = 'Usuń przypisaną osobę';

// Checklist
$lang['wmm_checklist']          = 'Checklist';
$lang['wmm_add_checklist_item'] = 'Dodaj element checklisty';
$lang['wmm_checklist_item']     = 'Element checklisty';
$lang['wmm_checklist_items']    = 'Elementy checklisty';
$lang['wmm_no_checklist_items'] = 'Brak elementów checklisty';
$lang['wmm_checklist_finished'] = '%s z %s ukończono';

// Comments & Activity
$lang['wmm_comments']               = 'Komentarze';
$lang['wmm_add_comment']            = 'Dodaj komentarz';
$lang['wmm_write_comment']          = 'Napisz komentarz...';
$lang['wmm_edit_comment']           = 'Edytuj komentarz';
$lang['wmm_delete_comment']         = 'Usuń komentarz';
$lang['wmm_confirm_delete_comment'] = 'Czy na pewno chcesz usunąć ten komentarz?';
$lang['wmm_no_comments']            = 'Brak komentarzy';
$lang['wmm_activity']               = 'Aktywność';
$lang['wmm_task_activity']          = 'Aktywność zadania';

// Recurring Tasks
$lang['wmm_recurring']             = 'Cykliczne';
$lang['wmm_is_recurring_task']     = 'To jest zadanie cykliczne';
$lang['wmm_recurring_every']       = 'Powtarzaj co';
$lang['wmm_recurring_interval']    = 'Interwał';
$lang['wmm_recurring_type']        = 'Typ powtarzania';
$lang['wmm_recurring_type_day']    = 'Dzień/dni';
$lang['wmm_recurring_type_week']   = 'Tydzień/tygodnie';
$lang['wmm_recurring_type_month']  = 'Miesiąc/miesiące';
$lang['wmm_recurring_type_year']   = 'Rok/lata';
$lang['wmm_recurring_type_custom'] = 'Własne';
$lang['wmm_last_recurring_date']   = 'Data ostatniego powtórzenia';

// Dates
$lang['wmm_due_date']   = 'Termin';
$lang['wmm_start_date'] = 'Data startu';
$lang['wmm_overdue']    = 'Po terminie';
$lang['wmm_due_today']  = 'Na dziś';
$lang['wmm_due_in']     = 'Termin za %s dni';

// Tags
$lang['wmm_tags']       = 'Tagi';
$lang['wmm_add_tag']    = 'Dodaj tag';
$lang['wmm_enter_tags'] = 'Wpisz tagi...';

// Task Details
$lang['wmm_task_details']        = 'Szczegóły zadania';
$lang['wmm_task_overview']       = 'Przegląd';
$lang['wmm_task_information']    = 'Informacje o zadaniu';
$lang['wmm_view_task']           = 'Podgląd zadania';
$lang['wmm_edit_task']           = 'Edytuj zadanie';
$lang['wmm_created_by']          = 'Utworzone przez';
$lang['wmm_last_updated']        = 'Ostatnia aktualizacja';
$lang['wmm_updated_by']          = 'Zaktualizowane przez';
$lang['wmm_completed_on']        = 'Ukończono';
$lang['wmm_visible_to_customer'] = 'Widoczne dla klienta';

// Maintenance History
$lang['wmm_maintenance_history'] = 'Historia utrzymania';
$lang['wmm_performed_in_logs']   = 'Wykonane w logach';
$lang['wmm_times_performed']     = 'Liczba wykonań';
$lang['wmm_last_performed']      = 'Ostatnio wykonano';
$lang['wmm_never_performed']     = 'Nigdy nie wykonano';
$lang['wmm_view_log']            = 'Zobacz log';

// Notifications
$lang['wmm_notify_assignees']           = 'Powiadom przypisanych';
$lang['wmm_task_assigned_notification'] = 'Zostałeś przypisany do zadania utrzymaniowego: %s';
$lang['wmm_task_due_notification']      = 'Zadanie utrzymaniowe "%s" ma termin %s';
$lang['wmm_task_overdue_notification']  = 'Zadanie utrzymaniowe "%s" jest po terminie!';

// Stats & Summary
$lang['wmm_total_tasks']      = 'Wszystkie zadania';
$lang['wmm_active_tasks']     = 'Aktywne zadania';
$lang['wmm_completed_tasks']  = 'Zakończone zadania';
$lang['wmm_overdue_tasks']    = 'Zadania po terminie';
$lang['wmm_my_tasks']         = 'Moje zadania';
$lang['wmm_unassigned_tasks'] = 'Zadania bez przypisania';

// Actions
$lang['wmm_mark_complete']   = 'Oznacz jako zakończone';
$lang['wmm_mark_incomplete'] = 'Oznacz jako nieukończone';
$lang['wmm_change_status']   = 'Zmień status';
$lang['wmm_change_priority'] = 'Zmień priorytet';
