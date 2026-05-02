<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Flowquest_provision extends CI_Controller
{
    private const MASTER_DB = 'perfex_db';
    private const SOURCE_DEMO_DB = 'ps_demo';
    private const SOURCE_DEMO_PREFIX = 'demo_tbl';
    private const TEMPLATE_DB = 'ps_beautytemplate';
    private const TEMPLATE_SLUG = 'beautytemplate';
    private const TEMPLATE_PREFIX = 'beautytemplate_tbl';
    private const DEMO_DB = 'ps_beauty';
    private const DEMO_SLUG = 'beauty';
    private const DEMO_PREFIX = 'beauty_tbl';
    private const OWNER_CLIENT_ID = 3;

    public function __construct()
    {
        parent::__construct();
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    }

    public function ping()
    {
        echo "pong\n";
    }

    public function repair_beauty_db_access()
    {
        $this->guardCli();

        $master = $this->connect(self::MASTER_DB);
        $repairs = [
            [
                'slug' => self::TEMPLATE_SLUG,
                'name' => 'FlowQuest Beauty Template',
                'db' => self::TEMPLATE_DB,
                'metadata' => $this->fetchValue($master, "SELECT metadata FROM tblperfex_saas_companies WHERE slug = '" . self::TEMPLATE_SLUG . "' LIMIT 1"),
            ],
            [
                'slug' => self::DEMO_SLUG,
                'name' => 'FlowQuest Beauty Demo',
                'db' => self::DEMO_DB,
                'metadata' => $this->fetchValue($master, "SELECT metadata FROM tblperfex_saas_companies WHERE slug = '" . self::DEMO_SLUG . "' LIMIT 1"),
            ],
        ];

        $result = [];
        foreach ($repairs as $repair) {
            if (empty($repair['metadata'])) {
                throw new RuntimeException('Brak metadata dla tenantu: ' . $repair['slug']);
            }
            $creds = $this->ensureTenantDbUser($repair['slug'], $repair['db']);
            $this->upsertTenant($master, $repair['slug'], $repair['name'], $repair['db'], $repair['metadata'], $creds['user'], $creds['password']);
            $result[] = [
                'slug' => $repair['slug'],
                'db' => $repair['db'],
                'user' => $creds['user'],
            ];
        }

        echo json_encode(['status' => 'ok', 'repairs' => $result], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
    }

    public function refresh_beauty_metadata()
    {
        $this->guardCli();

        $master = $this->connect(self::MASTER_DB);
        $demoCompany = $this->fetchRow($master, "SELECT metadata FROM tblperfex_saas_companies WHERE slug = 'demo' LIMIT 1");
        if (!$demoCompany) {
            throw new RuntimeException('Nie znaleziono metadata demo.');
        }

        $templateMeta = $this->buildBeautyMetadata($demoCompany['metadata'], false);
        $demoMeta = $this->buildBeautyMetadata($demoCompany['metadata'], true);

        $templateName = $this->fetchValue($master, "SELECT name FROM tblperfex_saas_companies WHERE slug = '" . self::TEMPLATE_SLUG . "' LIMIT 1") ?: 'FlowQuest Beauty Template';
        $demoName = $this->fetchValue($master, "SELECT name FROM tblperfex_saas_companies WHERE slug = '" . self::DEMO_SLUG . "' LIMIT 1") ?: 'FlowQuest Beauty Demo';

        $this->upsertTenant($master, self::TEMPLATE_SLUG, $templateName, self::TEMPLATE_DB, $templateMeta);
        $this->upsertTenant($master, self::DEMO_SLUG, $demoName, self::DEMO_DB, $demoMeta);

        echo json_encode(['status' => 'ok', 'slugs' => [self::TEMPLATE_SLUG, self::DEMO_SLUG]], JSON_UNESCAPED_SLASHES) . PHP_EOL;
    }

    public function inspect_tenant($slug = self::DEMO_SLUG)
    {
        $this->guardCli();

        $row = $this->db->where('slug', $slug)->get('tblperfex_saas_companies')->row_array();
        if (!$row) {
            throw new RuntimeException('Nie znaleziono tenantu: ' . $slug);
        }

        $decryptedDsn = $this->encryption->decrypt($row['dsn']);
        $parsed = function_exists('perfex_saas_parse_dsn') ? perfex_saas_parse_dsn($decryptedDsn) : [];

        $result = [
            'id' => (int)$row['id'],
            'slug' => $row['slug'],
            'name' => $row['name'],
            'status' => $row['status'],
            'clientid' => (int)$row['clientid'],
            'dsn_decrypted' => $decryptedDsn,
            'dsn_parsed' => $parsed,
            'tenant_prefix' => function_exists('perfex_saas_tenant_db_prefix') ? perfex_saas_tenant_db_prefix($slug) : null,
        ];

        if (!empty($parsed['dbname'])) {
            $db = $this->connect($parsed['dbname'], $parsed['user'] ?? 'root', $parsed['password'] ?? '');
            $prefix = $result['tenant_prefix'];
            $tables = [];
            $tableResult = $db->query("SHOW TABLES LIKE '{$prefix}%'");
            while ($tableRow = $tableResult->fetch_row()) {
                $tables[] = $tableRow[0];
            }
            $tableResult->close();

            $result['table_count'] = count($tables);
            $result['sample_tables'] = array_slice($tables, 0, 20);

            $critical = ['options', 'staff', 'modules', 'clients', 'contacts'];
            $checks = [];
            foreach ($critical as $table) {
                $full = $prefix . $table;
                $exists = in_array($full, $tables, true);
                $checks[$full] = $exists;
                if ($exists) {
                    $checks[$full . '_count'] = (int)$this->fetchValue($db, "SELECT COUNT(*) FROM `{$full}`");
                }
            }
            $result['critical_checks'] = $checks;
        }

        echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    }

    public function provision_beauty()
    {
        $this->guardCli();

        $master = $this->connect(self::MASTER_DB);
        $backupDir = '/root/flowquest_backups/beauty_' . date('Ymd_His');
        $this->run("mkdir -p " . escapeshellarg($backupDir));

        $demoCompany = $this->fetchRow($master, "SELECT * FROM tblperfex_saas_companies WHERE slug = 'demo' LIMIT 1");
        if (!$demoCompany) {
            throw new RuntimeException('Nie znaleziono tenantu demo.');
        }

        $templateMeta = $this->buildBeautyMetadata($demoCompany['metadata'], false);
        $demoMeta = $this->buildBeautyMetadata($demoCompany['metadata'], true);

        $this->backupAndRemoveTenant($master, self::TEMPLATE_SLUG, self::TEMPLATE_DB, $backupDir);
        $this->backupAndRemoveTenant($master, self::DEMO_SLUG, self::DEMO_DB, $backupDir);

        $this->cloneTenantDatabase(self::SOURCE_DEMO_DB, self::SOURCE_DEMO_PREFIX, self::TEMPLATE_DB, self::TEMPLATE_PREFIX);
        $this->createModuleTables(self::TEMPLATE_DB, self::TEMPLATE_PREFIX);
        $this->prepareBeautyTemplateDb();
        $templateId = $this->upsertTenant($master, self::TEMPLATE_SLUG, 'FlowQuest Beauty Template', self::TEMPLATE_DB, $templateMeta);

        $this->cloneTenantDatabase(self::TEMPLATE_DB, self::TEMPLATE_PREFIX, self::DEMO_DB, self::DEMO_PREFIX);
        $this->prepareBeautyDemoDb();
        $demoId = $this->upsertTenant($master, self::DEMO_SLUG, 'FlowQuest Beauty Demo', self::DEMO_DB, $demoMeta);

        echo json_encode([
            'status' => 'ok',
            'template' => [
                'id' => $templateId,
                'slug' => self::TEMPLATE_SLUG,
                'db' => self::TEMPLATE_DB,
                'url' => 'https://' . self::TEMPLATE_SLUG . '.flowquest.pl/admin/',
            ],
            'demo' => [
                'id' => $demoId,
                'slug' => self::DEMO_SLUG,
                'db' => self::DEMO_DB,
                'url' => 'https://' . self::DEMO_SLUG . '.flowquest.pl/admin/',
            ],
            'backup_dir' => $backupDir,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
    }

    public function finish_beauty_demo()
    {
        $this->guardCli();

        $db = $this->connect(self::DEMO_DB);
        $p = self::DEMO_PREFIX;
        $master = $this->connect(self::MASTER_DB);
        $demoCompany = $this->fetchRow($master, "SELECT metadata FROM tblperfex_saas_companies WHERE slug = 'demo' LIMIT 1");
        if (!$demoCompany) {
            throw new RuntimeException('Brak metadata demo do finalizacji Beauty.');
        }

        $staffIds = [];
        $result = $db->query("SELECT staffid, email FROM `{$p}staff` ORDER BY staffid");
        while ($row = $result->fetch_assoc()) {
            $staffIds[$row['email']] = (int)$row['staffid'];
        }
        $result->close();

        $projectIds = [];
        $result = $db->query("SELECT id FROM `{$p}projects` ORDER BY id");
        while ($row = $result->fetch_assoc()) {
            $projectIds[] = (int)$row['id'];
        }
        $result->close();

        if ((int)$this->fetchValue($db, "SELECT COUNT(*) FROM `{$p}chatgroups`") === 0) {
            $groupCreator = $staffIds['admin@demo.pl'];
            $groupName = 'Zespol salonu Beauty';
            $groupStmt = $db->prepare("INSERT INTO `{$p}chatgroups` (created_by_id, group_name) VALUES (?, ?)");
            $groupStmt->bind_param('is', $groupCreator, $groupName);
            $groupStmt->execute();
            $groupId = (int)$db->insert_id;
            $groupStmt->close();

            $groupMemberStmt = $db->prepare("INSERT INTO `{$p}chatgroupmembers` (group_id, member_id, group_name) VALUES (?, ?, ?)");
            foreach ($staffIds as $staffId) {
                $groupMemberStmt->bind_param('iis', $groupId, $staffId, $groupName);
                $groupMemberStmt->execute();
            }
            $groupMemberStmt->close();

            $chatMsgStmt = $db->prepare("INSERT INTO `{$p}chatmessages` (sender_id, reciever_id, message, viewed, time_sent, viewed_at) VALUES (?, ?, ?, 1, ?, ?)");
            $chatPairs = [
                [$staffIds['admin@demo.pl'], $staffIds['pracownik@demo.pl'], 'Sprawdz prosze leady z depilacji i oddzwon do nowych klientek.', '2026-03-23 09:05:00'],
                [$staffIds['pracownik@demo.pl'], $staffIds['admin@demo.pl'], 'Jasne, dwie osoby sa juz ustawione na konsultacje jutro.', '2026-03-23 09:12:00'],
                [$staffIds['karolina@beauty.pl'], $staffIds['recepcja@beauty.pl'], 'Mam wolne okno o 14:00, mozesz dosunac stylizacje brwi.', '2026-03-23 10:21:00'],
                [$staffIds['recepcja@beauty.pl'], $staffIds['karolina@beauty.pl'], 'Super, wpisalam Klaudie i wyslalam potwierdzenie.', '2026-03-23 10:26:00'],
            ];
            foreach ($chatPairs as [$sender, $receiver, $message, $timeSent]) {
                $viewedAt = $timeSent;
                $chatMsgStmt->bind_param('iisss', $sender, $receiver, $message, $timeSent, $viewedAt);
                $chatMsgStmt->execute();
            }
            $chatMsgStmt->close();

            $groupMsgStmt = $db->prepare("INSERT INTO `{$p}chatgroupmessages` (group_id, message, sender_id, time_sent) VALUES (?, ?, ?, ?)");
            $groupMessages = [
                [$groupId, 'Dzisiaj promujemy pakiet lifting plus brwi. Prosze ogarnac follow-up po 12:00.', $staffIds['admin@demo.pl'], '2026-03-23 08:45:00'],
                [$groupId, 'Recepcja ma juz wypelniony grafik do piatku, zostalo jedno okno na sobote.', $staffIds['recepcja@beauty.pl'], '2026-03-23 11:05:00'],
                [$groupId, 'Klientki po mezoterapii dostaly dzisiaj instrukcje pozabiegowe.', $staffIds['natalia@beauty.pl'], '2026-03-23 12:15:00'],
            ];
            foreach ($groupMessages as [$gId, $message, $sender, $timeSent]) {
                $groupMsgStmt->bind_param('isis', $gId, $message, $sender, $timeSent);
                $groupMsgStmt->execute();
            }
            $groupMsgStmt->close();

            $chatSettingsStmt = $db->prepare("INSERT INTO `{$p}chatsettings` (user_id, name, value) VALUES (?, ?, ?)");
            foreach ($staffIds as $staffId) {
                $settingName = 'chat_status';
                $value = 'online';
                $chatSettingsStmt->bind_param('iss', $staffId, $settingName, $value);
                $chatSettingsStmt->execute();
            }
            $chatSettingsStmt->close();
        }

        if ((int)$this->fetchValue($db, "SELECT COUNT(*) FROM `{$p}feedback`") === 0 && !empty($projectIds)) {
            $feedbackStmt = $db->prepare("INSERT INTO `{$p}feedback` (customerid, projectid, coding, communication, services, recommendation, message, feedback_submitted, date) VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?)");
            $feedbackRows = [
                [1, (string)$projectIds[0], '10', '10', '10', '10', 'Makijaz i kontakt byly perfekcyjne.', '2026-03-22 16:00:00'],
                [2, (string)$projectIds[1], '9', '10', '10', '9', 'Bardzo dobra organizacja i piekny efekt.', '2026-03-22 17:20:00'],
                [5, (string)$projectIds[4], '10', '9', '10', '10', 'Wracam na kolejne zabiegi, swietny standard.', '2026-03-23 10:10:00'],
            ];
            foreach ($feedbackRows as [$customerId, $projectId, $coding, $communication, $services, $recommendation, $message, $date]) {
                $feedbackStmt->bind_param('isssssss', $customerId, $projectId, $coding, $communication, $services, $recommendation, $message, $date);
                $feedbackStmt->execute();
            }
            $feedbackStmt->close();
        }

        if ((int)$this->fetchValue($db, "SELECT COUNT(*) FROM `{$p}newsfeed_posts`") === 0) {
            $newsStmt = $db->prepare("INSERT INTO `{$p}newsfeed_posts` (creator, datecreated, visibility, content, pinned, datepinned) VALUES (?, ?, 'all', ?, ?, ?)");
            $newsCommentStmt = $db->prepare("INSERT INTO `{$p}newsfeed_post_comments` (content, userid, postid, dateadded) VALUES (?, ?, ?, ?)");
            $posts = [
                [$staffIds['admin@demo.pl'], '2026-03-23 08:30:00', 'Startujemy tydzien z promocja pakietu wiosennego i follow-upem do klientek VIP.', 1, '2026-03-23 08:31:00', 'Brzmi super, mam juz przygotowane szablony wiadomosci.', $staffIds['pracownik@demo.pl'], '2026-03-23 08:42:00'],
                [$staffIds['recepcja@beauty.pl'], '2026-03-23 11:20:00', 'Sobota prawie pelna. Zostalo jedno okienko na 14:00.', 0, null, 'Wrzuce to jeszcze na stories i przypne w kalendarzu.', $staffIds['karolina@beauty.pl'], '2026-03-23 11:35:00'],
            ];
            foreach ($posts as [$creator, $dateCreated, $content, $pinned, $datePinned, $comment, $commentUser, $commentDate]) {
                $newsStmt->bind_param('issis', $creator, $dateCreated, $content, $pinned, $datePinned);
                $newsStmt->execute();
                $postId = (int)$db->insert_id;
                $newsCommentStmt->bind_param('siis', $comment, $commentUser, $postId, $commentDate);
                $newsCommentStmt->execute();
            }
            $newsStmt->close();
            $newsCommentStmt->close();
        }

        $demoMeta = $this->buildBeautyMetadata($demoCompany['metadata'], true);
        $demoId = $this->upsertTenant($master, self::DEMO_SLUG, 'FlowQuest Beauty Demo', self::DEMO_DB, $demoMeta);
        echo json_encode(['status' => 'ok', 'demo_id' => $demoId, 'url' => 'https://beauty.flowquest.pl/admin/'], JSON_UNESCAPED_SLASHES) . PHP_EOL;
    }

    private function buildBeautyMetadata(string $baseMetadataJson, bool $isDemo): string
    {
        $meta = json_decode($baseMetadataJson, true) ?: [];
        $meta['admin_approved_modules'] = ['', '', 'appointly', 'einvoice', 'feedback', 'form_sync', 'menu_setup', 'prchat', 'theme_style', 'zillapage'];
        $meta['admin_disabled_modules'] = [''];
        $meta['admin_disabled_default_modules'] = ['estimate_request', 'expenses', 'knowledge_base', 'credit_notes', 'subscriptions'];
        $meta['login_panel'] = [
            'kicker' => $isDemo ? 'FlowQuest Beauty Demo' : 'FlowQuest Beauty Template',
            'title' => $isDemo ? 'Zobacz, jak pracuje salon beauty' : 'Szablon branzy beauty',
            'copy' => $isDemo
                ? 'To zywe demo pokazuje wizyty, leady, zespol, zadania i komunikacje w salonie beauty. Konta logowania uzupelniaja sie po kliknieciu.'
                : 'To czysty szablon beauty do dalszego klonowania. Zostawiamy te same loginy techniczne, ale bez wypelnionych danych operacyjnych.',
            'admin_note' => 'Wlasciciel i Pracownik otwieraja panel administracyjny.',
            'client_note' => 'Klient otwiera portal klienta i widzi dokumenty oraz komunikacje.',
            'accounts' => [
                'owner' => [
                    'target' => 'admin',
                    'label' => 'Wlasciciel',
                    'email' => 'admin@demo.pl',
                    'password' => 'Demo123!',
                    'submit' => 'Zaloguj jako wlasciciel',
                    'hint' => 'Pelny dostep do salonu i marketplace',
                    'copy' => 'Wlasciciel widzi kalendarz, leady, finanse, wizyty, projekty i dodatki.',
                ],
                'employee' => [
                    'target' => 'admin',
                    'label' => 'Pracownik',
                    'email' => 'pracownik@demo.pl',
                    'password' => 'Demo123!',
                    'submit' => 'Zaloguj jako pracownik',
                    'hint' => 'Obsluga wizyt, klientek i zadan',
                    'copy' => 'Pracownik widzi leady, klientki, zadania, komentarze i codzienna operacje salonu.',
                ],
                'client' => [
                    'target' => 'client',
                    'label' => 'Klient',
                    'email' => 'klient@demo.pl',
                    'password' => 'Demo123!',
                    'submit' => 'Zaloguj jako klient',
                    'hint' => 'Portal klienta beauty',
                    'copy' => 'Klient widzi swoje dokumenty, terminy, notatki i komunikacje z salonem.',
                ],
            ],
        ];

        return json_encode($meta, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    private function prepareBeautyTemplateDb(): void
    {
        $db = $this->connect(self::TEMPLATE_DB);
        $p = self::TEMPLATE_PREFIX;

        $this->runSql($db, "UPDATE `{$p}options` SET value = 'FlowQuest Beauty Template' WHERE name = 'companyname'");
        $this->runSql($db, "UPDATE `{$p}options` SET value = 'polish' WHERE name = 'active_language'");
        $this->runSql($db, "UPDATE `{$p}modules` SET active = 1 WHERE module_name IN ('appointly','feedback','form_sync','einvoice','menu_setup','prchat','theme_style','zillapage')");

        $cleanupTables = [
            'task_comments',
            'task_assigned',
            'task_followers',
            'tasks',
            'project_members',
            'project_activity',
            'project_files',
            'project_notes',
            'project_settings',
            'projectdiscussioncomments',
            'projectdiscussions',
            'projects',
            'appointly_appointments',
            'appointly_attendees',
            'appointly_callbacks',
            'appointly_callbacks_assignees',
            'notes',
            'newsfeed_post_comments',
            'newsfeed_posts',
            'leads',
            'proposals',
            'invoices',
            'invoicepaymentrecords',
            'itemable',
            'feedback',
            'chatmessages',
            'chatclientmessages',
            'chatgroupmessages',
            'chatgroupmembers',
            'chatgroups',
            'chatsharedfiles',
            'chatgroupsharedfiles',
            'taggables',
            'tags',
            'customer_groups',
            'customers_groups',
        ];
        foreach ($cleanupTables as $table) {
            $this->runSql($db, "DELETE FROM `{$p}{$table}`");
        }

        $this->runSql($db, "DELETE FROM `{$p}contacts` WHERE userid NOT IN (1)");
        $this->runSql($db, "DELETE FROM `{$p}clients` WHERE userid NOT IN (1)");
        $this->runSql($db, "DELETE FROM `{$p}contact_permissions`");

        $this->runSql($db, "UPDATE `{$p}staff` SET firstname='Wlasciciel', lastname='Beauty' WHERE staffid=1");
        $this->runSql($db, "UPDATE `{$p}staff` SET firstname='Pracownik', lastname='Beauty' WHERE staffid=2");
        $this->runSql($db, "UPDATE `{$p}clients` SET company='Studio Bella Aura', city='Warszawa', state='Mazowieckie', address='ul. Lawendowa 12', website='https://beauty.flowquest.pl' WHERE userid=1");
        $this->runSql($db, "UPDATE `{$p}contacts` SET firstname='Anna', lastname='Dabrowska', email='kontakt@bellaaura.pl' WHERE id=1");
        $this->runSql($db, "UPDATE `{$p}contacts` SET firstname='Klient', lastname='Beauty', email='klient@demo.pl' WHERE id=4");
        $this->runSql($db, "INSERT INTO `{$p}contact_permissions` (permission_id, userid) VALUES (1,4),(2,4),(4,4)");

        $appointmentTypes = [
            ['Paznokcie hybrydowe', '#D977A8'],
            ['Oczyszczanie twarzy', '#7BC4C4'],
            ['Stylizacja brwi', '#B58C5A'],
            ['Lifting rzes', '#8F7AE5'],
            ['Depilacja laserowa', '#F4A261'],
            ['Mezoterapia', '#5AA9E6'],
            ['Makijaz okazjonalny', '#E76F51'],
        ];
        $stmt = $db->prepare("INSERT INTO `{$p}appointly_appointment_types` (type, color) VALUES (?, ?)");
        foreach ($appointmentTypes as [$type, $color]) {
            $stmt->bind_param('ss', $type, $color);
            $stmt->execute();
        }
        $stmt->close();

        $customerGroups = ['VIP', 'Nowe klientki', 'Abonament', 'Panny mlode'];
        $stmt = $db->prepare("INSERT INTO `{$p}customers_groups` (name) VALUES (?)");
        foreach ($customerGroups as $name) {
            $stmt->bind_param('s', $name);
            $stmt->execute();
        }
        $stmt->close();

        $leadSources = ['Instagram', 'Facebook Ads', 'Polecenie', 'Landing Page'];
        $stmt = $db->prepare("INSERT INTO `{$p}leads_sources` (name) VALUES (?)");
        foreach ($leadSources as $name) {
            $stmt->bind_param('s', $name);
            $stmt->execute();
        }
        $stmt->close();

        $leadStatuses = [
            ['Nowa konsultacja', '#f59e0b', 1, 1],
            ['Umowiona wizyta', '#10b981', 2, 0],
            ['Do follow-upu', '#3b82f6', 3, 0],
            ['Sprzedany pakiet', '#8b5cf6', 4, 0],
        ];
        $stmt = $db->prepare("INSERT INTO `{$p}leads_status` (name, color, statusorder, isdefault) VALUES (?, ?, ?, ?)");
        foreach ($leadStatuses as [$name, $color, $order, $default]) {
            $stmt->bind_param('ssii', $name, $color, $order, $default);
            $stmt->execute();
        }
        $stmt->close();
    }

    private function prepareBeautyDemoDb(): void
    {
        $db = $this->connect(self::DEMO_DB);
        $p = self::DEMO_PREFIX;

        $this->runSql($db, "UPDATE `{$p}options` SET value = 'FlowQuest Beauty Demo' WHERE name = 'companyname'");
        $this->runSql($db, "UPDATE `{$p}modules` SET active = 1 WHERE module_name IN ('appointly','feedback','form_sync','einvoice','menu_setup','prchat','theme_style','zillapage')");

        $employeeHash = $this->fetchValue($db, "SELECT password FROM `{$p}staff` WHERE staffid = 2");
        $clientHash = $this->fetchValue($db, "SELECT password FROM `{$p}contacts` WHERE id = 4");

        $staff = [
            ['karolina@beauty.pl', 'Karolina', 'Nowak', '600100200'],
            ['natalia@beauty.pl', 'Natalia', 'Mazur', '600100201'],
            ['recepcja@beauty.pl', 'Recepcja', 'Glow', '600100202'],
        ];
        $stmt = $db->prepare("INSERT INTO `{$p}staff` (email, firstname, lastname, phonenumber, password, datecreated, admin, role, active, hourly_rate) VALUES (?, ?, ?, ?, ?, NOW(), 0, 1, 1, 0.00)");
        foreach ($staff as [$email, $first, $last, $phone]) {
            $stmt->bind_param('sssss', $email, $first, $last, $phone, $employeeHash);
            $stmt->execute();
        }
        $stmt->close();

        $staffIds = [];
        $result = $db->query("SELECT staffid, email FROM `{$p}staff` ORDER BY staffid");
        while ($row = $result->fetch_assoc()) {
            $staffIds[$row['email']] = (int)$row['staffid'];
        }
        $result->close();

        $clients = [
            ['Studio Bella Aura', 'Warszawa', 'Mazowieckie', 'ul. Lawendowa 12', '790100001', 'anna@bellaaura.pl', 'Anna', 'Dabrowska'],
            ['Glow Atelier', 'Krakow', 'Malopolskie', 'ul. Szafirowa 7', '790100002', 'iza@glowatelier.pl', 'Izabela', 'Wojcik'],
            ['SkinLab Premium', 'Wroclaw', 'Dolnoslaskie', 'ul. Perlista 4', '790100003', 'Aleksandra', 'Jankowska', 'ola@skinlab.pl'],
            ['Lash Room Nova', 'Gdansk', 'Pomorskie', 'ul. Morska 18', '790100004', 'Monika', 'Lis', 'monika@lashroom.pl'],
            ['Pure Face Clinic', 'Poznan', 'Wielkopolskie', 'ul. Rozana 22', '790100005', 'Martyna', 'Krol', 'martyna@pureface.pl'],
        ];

        $this->runSql($db, "DELETE FROM `{$p}contacts` WHERE userid NOT IN (1)");
        $this->runSql($db, "DELETE FROM `{$p}clients` WHERE userid NOT IN (1)");
        $this->runSql($db, "DELETE FROM `{$p}contact_permissions`");

        $primaryContactStmt = $db->prepare("UPDATE `{$p}contacts` SET firstname=?, lastname=?, email=?, active=1 WHERE id=1");
        $clientDemoStmt = $db->prepare("UPDATE `{$p}contacts` SET firstname='Klient', lastname='Beauty', email='klient@demo.pl', password=?, active=1 WHERE id=4");
        $clientUpdateStmt = $db->prepare("UPDATE `{$p}clients` SET company=?, city=?, state=?, address=?, phonenumber=?, active=1 WHERE userid=1");
        [$company, $city, $state, $address, $phone, $email, $first, $last] = $clients[0];
        $primaryContactStmt->bind_param('sss', $first, $last, $email);
        $primaryContactStmt->execute();
        $clientUpdateStmt->bind_param('sssss', $company, $city, $state, $address, $phone);
        $clientUpdateStmt->execute();
        $clientDemoStmt->bind_param('s', $clientHash);
        $clientDemoStmt->execute();
        $primaryContactStmt->close();
        $clientUpdateStmt->close();
        $clientDemoStmt->close();

        $insertClient = $db->prepare("INSERT INTO `{$p}clients` (company, country, city, state, address, phonenumber, datecreated, active, addedfrom, registration_confirmed) VALUES (?, 0, ?, ?, ?, ?, NOW(), 1, 1, 1)");
        $insertContact = $db->prepare("INSERT INTO `{$p}contacts` (userid, is_primary, firstname, lastname, email, phonenumber, datecreated, password, active, invoice_emails, estimate_emails, credit_note_emails, contract_emails, task_emails, project_emails, ticket_emails) VALUES (?, 1, ?, ?, ?, ?, NOW(), ?, 1, 1, 1, 1, 1, 1, 1, 1)");

        foreach (array_slice($clients, 1) as [$cCompany, $cCity, $cState, $cAddress, $cPhone, $cFirst, $cLast, $cEmail]) {
            $insertClient->bind_param('sssss', $cCompany, $cCity, $cState, $cAddress, $cPhone);
            $insertClient->execute();
            $clientId = (int)$db->insert_id;
            $insertContact->bind_param('isssss', $clientId, $cFirst, $cLast, $cEmail, $cPhone, $clientHash);
            $insertContact->execute();
        }
        $insertClient->close();
        $insertContact->close();

        $this->runSql($db, "INSERT INTO `{$p}contact_permissions` (permission_id, userid) VALUES (1,4),(2,4),(4,4)");

        $groupIds = [];
        $result = $db->query("SELECT id, name FROM `{$p}customers_groups` ORDER BY id");
        while ($row = $result->fetch_assoc()) {
            $groupIds[$row['name']] = (int)$row['id'];
        }
        $result->close();

        $stmt = $db->prepare("INSERT INTO `{$p}customer_groups` (groupid, customer_id) VALUES (?, ?)");
        foreach ([1 => 'VIP', 2 => 'Nowe klientki', 3 => 'Abonament', 4 => 'Nowe klientki', 5 => 'Panny mlode'] as $customerId => $groupName) {
            if (!isset($groupIds[$groupName])) {
                continue;
            }
            $groupId = $groupIds[$groupName];
            $stmt->bind_param('ii', $groupId, $customerId);
            $stmt->execute();
        }
        $stmt->close();

        $tagIds = [];
        $stmt = $db->prepare("INSERT INTO `{$p}tags` (name) VALUES (?)");
        foreach (['beauty', 'vip', 'stala-klientka', 'abonament', 'follow-up', 'pakiet-wiosna', 'instagram'] as $tag) {
            $stmt->bind_param('s', $tag);
            $stmt->execute();
            $tagIds[$tag] = (int)$db->insert_id;
        }
        $stmt->close();

        $leadSourceIds = [];
        $result = $db->query("SELECT id, name FROM `{$p}leads_sources` ORDER BY id");
        while ($row = $result->fetch_assoc()) {
            $leadSourceIds[$row['name']] = (int)$row['id'];
        }
        $result->close();

        $leadStatusIds = [];
        $result = $db->query("SELECT id, name FROM `{$p}leads_status` ORDER BY id");
        while ($row = $result->fetch_assoc()) {
            $leadStatusIds[$row['name']] = (int)$row['id'];
        }
        $result->close();

        $leads = [
            ['Joanna Kaczmarek', 'Konsultacja slubna', 'Instagram', 'Nowa konsultacja', 'joanna@gmail.com', '501600001', 450.00],
            ['Magdalena Sroka', 'Pakiet depilacji', 'Landing Page', 'Umowiona wizyta', 'magda@gmail.com', '501600002', 800.00],
            ['Paulina Dudek', 'Lifting rzes + brwi', 'Polecenie', 'Do follow-upu', 'paulina@gmail.com', '501600003', 350.00],
            ['Klaudia Lis', 'Paznokcie premium', 'Instagram', 'Sprzedany pakiet', 'klaudia@gmail.com', '501600004', 290.00],
            ['Weronika Banas', 'Mezoterapia twarzy', 'Facebook Ads', 'Nowa konsultacja', 'weronika@gmail.com', '501600005', 950.00],
            ['Agata Michalska', 'Oczyszczanie + abonament', 'Landing Page', 'Umowiona wizyta', 'agata@gmail.com', '501600006', 600.00],
        ];
        $stmt = $db->prepare("INSERT INTO `{$p}leads` (hash, name, title, company, description, country, assigned, dateadded, status, source, addedfrom, email, phonenumber, lead_value) VALUES (?, ?, ?, ?, ?, 0, ?, NOW(), ?, ?, 1, ?, ?, ?)");
        foreach ($leads as $index => [$name, $title, $source, $status, $email, $phone, $value]) {
            $hash = md5($email . microtime(true) . $index);
            $companyName = $name;
            $description = "Lead beauty: {$title}";
            $assigned = $staffIds['pracownik@demo.pl'];
            $statusId = $leadStatusIds[$status] ?? 1;
            $sourceId = $leadSourceIds[$source] ?? 1;
            $stmt->bind_param('sssssiiissd', $hash, $name, $title, $companyName, $description, $assigned, $statusId, $sourceId, $email, $phone, $value);
            $stmt->execute();
            $leadId = (int)$db->insert_id;
            $this->attachTag($db, $p, $leadId, 'lead', $tagIds['beauty']);
            if (in_array($status, ['Sprzedany pakiet', 'Umowiona wizyta'], true)) {
                $this->attachTag($db, $p, $leadId, 'lead', $tagIds['follow-up']);
            }
        }
        $stmt->close();

        $projects = [
            ['Pakiet slubny Anna 2026', 1, '2026-03-20', '2026-04-18', 'Kompleksowa obsluga panny mlodej i probny makijaz.', [1, 2, 5]],
            ['Abonament pielegnacji Glow Atelier', 2, '2026-03-18', '2026-04-28', 'Cykl zabiegow twarz + pielegnacja domowa.', [2, 3]],
            ['Kampania social media brwi premium', 3, '2026-03-21', '2026-04-10', 'Sesja contentowa i leady z Instagrama.', [1, 4]],
            ['Pakiet lifting rzes Lash Room', 4, '2026-03-15', '2026-04-05', 'Wdrozenie oferty sezonowej i follow-up klientek.', [2, 4]],
            ['Metamorfoza wiosenna Pure Face', 5, '2026-03-12', '2026-04-15', 'Pakiet premium dla stalej klientki.', [1, 3]],
            ['Program VIP Studio Bella Aura', 1, '2026-03-10', '2026-05-01', 'Nowy abonament dla klientek wracajacych.', [1, 2, 3]],
            ['Landing depilacja laserowa', 1, '2026-03-22', '2026-03-30', 'Landing page i formularz leadowy pod depilacje.', [1, 4]],
            ['Onboarding recepcji i kalendarza', 2, '2026-03-11', '2026-03-27', 'Uklad pracy zespolu i standard odpowiedzi.', [2, 5]],
            ['Pakiet zabiegow anti-aging', 3, '2026-03-16', '2026-04-20', 'Seria wizyt i monitorowanie efektow.', [1, 3]],
            ['Follow-up klientek po mezoterapii', 5, '2026-03-19', '2026-04-08', 'Kontrola satysfakcji i sprzedaz kolejnych terminow.', [2, 4]],
        ];

        $insertProject = $db->prepare("INSERT INTO `{$p}projects` (name, description, status, clientid, billing_type, start_date, deadline, project_created, progress, progress_from_tasks, addedfrom, contact_notification) VALUES (?, ?, 2, ?, 1, ?, ?, CURDATE(), ?, 1, 1, 1)");
        $memberStmt = $db->prepare("INSERT INTO `{$p}project_members` (project_id, staff_id) VALUES (?, ?)");
        $projectIds = [];
        foreach ($projects as $idx => [$name, $clientId, $startDate, $deadline, $description, $members]) {
            $progress = 25 + (($idx * 7) % 60);
            $insertProject->bind_param('ssissi', $name, $description, $clientId, $startDate, $deadline, $progress);
            $insertProject->execute();
            $projectId = (int)$db->insert_id;
            $projectIds[] = $projectId;
            foreach ($members as $staffId) {
                $memberStmt->bind_param('ii', $projectId, $staffId);
                $memberStmt->execute();
            }
            $this->attachTag($db, $p, $projectId, 'project', $tagIds['beauty']);
            if ($idx % 2 === 0) {
                $this->attachTag($db, $p, $projectId, 'project', $tagIds['pakiet-wiosna']);
            }
        }
        $insertProject->close();
        $memberStmt->close();

        $tasks = [
            ['Potwierdzic wizyte probna makijazu', 1, '2026-03-23', '2026-03-24', 1, 'Skontaktuj sie z klientka i potwierdz godzine.'],
            ['Przygotowac plan postow na Instagram', 3, '2026-03-23', '2026-03-26', 4, 'Content pod stylizacje brwi i lifting.'],
            ['Oddzwonic do leadow z landing page', 7, '2026-03-23', '2026-03-25', 2, 'Lead follow-up po depilacji.'],
            ['Ustalic harmonogram wizyt VIP', 6, '2026-03-24', '2026-03-28', 3, 'Rozpisz dwa tygodnie z wyprzedzeniem.'],
            ['Wyslac przypomnienie po mezoterapii', 10, '2026-03-24', '2026-03-24', 1, 'Krotka wiadomosc i check-in po zabiegu.'],
            ['Uzupelnic portfolio przed-po', 5, '2026-03-24', '2026-03-29', 4, 'Zdjecia i zgody klientek.'],
            ['Przygotowac cennik pakietow slubnych', 1, '2026-03-25', '2026-03-27', 1, 'Aktualizacja oferty na sezon.'],
            ['Dopiac grafik recepcji', 8, '2026-03-25', '2026-03-25', 2, 'Sprawdz oblozenie i przerwy.'],
            ['Przeslac klientce zalecenia domowe', 2, '2026-03-25', '2026-03-26', 4, 'Dolacz personalizowane wskazowki.'],
            ['Przygotowac raport powracajacych klientek', 6, '2026-03-26', '2026-03-30', 3, 'Lista VIP i wartosc wizyt.'],
            ['Potwierdzic konsultacje anti-aging', 9, '2026-03-26', '2026-03-27', 1, 'Potwierdz termin i zakres.'],
            ['Zamknac follow-up po liftingu rzes', 4, '2026-03-27', '2026-03-29', 5, 'Zbierz ocene i zaoferuj kolejna wizyte.'],
            ['Zrobic checkliste startowa dla recepcji', 8, '2026-03-27', '2026-03-30', 2, 'Standard odbioru telefonu i CRM.'],
            ['Przygotowac harmonogram stories', 3, '2026-03-28', '2026-03-31', 4, 'Tematy na 7 dni.'],
            ['Wyslac probe makijazu do akceptacji', 1, '2026-03-29', '2026-03-31', 1, 'Zdjecia i notatki po probie.'],
        ];
        $taskStmt = $db->prepare("INSERT INTO `{$p}tasks` (name, description, priority, dateadded, startdate, duedate, addedfrom, status, rel_id, rel_type, is_public, billable, billed, invoice_id, hourly_rate, milestone, kanban_order, milestone_order, visible_to_client, deadline_notified) VALUES (?, ?, 2, NOW(), ?, ?, 1, ?, ?, 'project', 0, 0, 0, 0, 0.00, 0, 1, 0, 0, 0)");
        $assignStmt = $db->prepare("INSERT INTO `{$p}task_assigned` (staffid, taskid, assigned_from, is_assigned_from_contact) VALUES (?, ?, 1, 0)");
        $followStmt = $db->prepare("INSERT INTO `{$p}task_followers` (staffid, taskid) VALUES (?, ?)");
        $taskCommentStmt = $db->prepare("INSERT INTO `{$p}task_comments` (content, taskid, staffid, contact_id, file_id, dateadded) VALUES (?, ?, ?, 0, 0, NOW())");
        $staffList = array_values($staffIds);
        foreach ($tasks as $index => [$name, $projectIndex, $startDate, $dueDate, $status, $description]) {
            $projectId = $projectIds[$projectIndex - 1];
            $taskStmt->bind_param('ssssii', $name, $description, $startDate, $dueDate, $status, $projectId);
            $taskStmt->execute();
            $taskId = (int)$db->insert_id;
            $assignedStaffId = $staffList[$index % count($staffList)];
            $followStaffId = $staffList[($index + 1) % count($staffList)];
            $assignStmt->bind_param('ii', $assignedStaffId, $taskId);
            $assignStmt->execute();
            $followStmt->bind_param('ii', $followStaffId, $taskId);
            $followStmt->execute();
            $comment = $index % 2 === 0 ? 'Klientka potwierdzila zakres. Przechodzimy dalej.' : 'Do ogarniecia dzisiaj do 16:00, zalezy nam na szybkim follow-upie.';
            $taskCommentStmt->bind_param('sii', $comment, $taskId, $assignedStaffId);
            $taskCommentStmt->execute();
            if ($index % 3 === 0) {
                $this->attachTag($db, $p, $taskId, 'task', $tagIds['follow-up']);
            }
        }
        $taskStmt->close();
        $assignStmt->close();
        $followStmt->close();
        $taskCommentStmt->close();

        $appointmentTypeIds = [];
        $result = $db->query("SELECT id, type FROM `{$p}appointly_appointment_types` ORDER BY id");
        while ($row = $result->fetch_assoc()) {
            $appointmentTypeIds[$row['type']] = (int)$row['id'];
        }
        $result->close();

        $appointments = [
            ['Paznokcie hybrydowe', '2026-03-24', '09:00', 'Anna Krol', 'anna@bellaaura.pl', '790100001', 1],
            ['Stylizacja brwi', '2026-03-24', '10:30', 'Izabela Wojcik', 'iza@glowatelier.pl', '790100002', 2],
            ['Oczyszczanie twarzy', '2026-03-24', '12:00', 'Aleksandra Jankowska', 'ola@skinlab.pl', '790100003', 3],
            ['Lifting rzes', '2026-03-24', '14:00', 'Monika Lis', 'monika@lashroom.pl', '790100004', 4],
            ['Mezoterapia', '2026-03-25', '09:30', 'Martyna Krol', 'martyna@pureface.pl', '790100005', 1],
            ['Makijaz okazjonalny', '2026-03-25', '11:00', 'Paulina Dudek', 'paulina@gmail.com', '501600003', 1],
            ['Depilacja laserowa', '2026-03-25', '13:00', 'Magdalena Sroka', 'magda@gmail.com', '501600002', 2],
            ['Paznokcie hybrydowe', '2026-03-26', '10:00', 'Klaudia Lis', 'klaudia@gmail.com', '501600004', 3],
            ['Stylizacja brwi', '2026-03-26', '12:30', 'Joanna Kaczmarek', 'joanna@gmail.com', '501600001', 4],
            ['Oczyszczanie twarzy', '2026-03-27', '09:00', 'Agata Michalska', 'agata@gmail.com', '501600006', 1],
            ['Mezoterapia', '2026-03-27', '11:30', 'Weronika Banas', 'weronika@gmail.com', '501600005', 1],
            ['Lifting rzes', '2026-03-28', '13:30', 'Natalia Glow', 'kontakt@bellaaura.pl', '790100001', 2],
        ];
        $apptStmt = $db->prepare("INSERT INTO `{$p}appointly_appointments` (subject, description, email, name, phone, address, notes, contact_id, by_sms, by_email, hash, notification_date, external_notification_date, date, start_hour, approved, created_by, reminder_before, reminder_before_type, finished, cancelled, cancel_notes, source, type_id, feedback, feedback_comment, recurring, recurring_type, repeat_every, custom_recurring, cycles, total_cycles, last_recurring_date) VALUES (?, ?, ?, ?, ?, ?, ?, NULL, 0, 1, ?, NOW(), NOW(), ?, ?, 1, ?, 60, 'minutes', 0, 0, NULL, 'crm', ?, NULL, NULL, 0, NULL, NULL, 0, 0, 0, NULL)");
        foreach ($appointments as [$type, $date, $hour, $name, $email, $phone, $staffSlot]) {
            $subject = $type;
            $description = "Wizyta beauty: {$type}";
            $address = 'Salon FlowQuest Beauty';
            $notes = 'Potwierdzona wizyta demo.';
            $hash = md5($email . $date . $hour);
            $createdBy = $staffList[($staffSlot - 1) % count($staffList)];
            $typeId = $appointmentTypeIds[$type] ?? 1;
            $apptStmt->bind_param('ssssssssssii', $subject, $description, $email, $name, $phone, $address, $notes, $hash, $date, $hour, $createdBy, $typeId);
            $apptStmt->execute();
        }
        $apptStmt->close();

        $groupCreator = $staffIds['admin@demo.pl'];
        $groupName = 'Zespol salonu Beauty';
        $groupStmt = $db->prepare("INSERT INTO `{$p}chatgroups` (created_by_id, group_name) VALUES (?, ?)");
        $groupStmt->bind_param('is', $groupCreator, $groupName);
        $groupStmt->execute();
        $groupId = (int)$db->insert_id;
        $groupStmt->close();

        $groupMemberStmt = $db->prepare("INSERT INTO `{$p}chatgroupmembers` (group_id, member_id, group_name) VALUES (?, ?, ?)");
        foreach ($staffIds as $staffId) {
            $groupMemberStmt->bind_param('iis', $groupId, $staffId, $groupName);
            $groupMemberStmt->execute();
        }
        $groupMemberStmt->close();

        $chatMsgStmt = $db->prepare("INSERT INTO `{$p}chatmessages` (sender_id, reciever_id, message, viewed, time_sent, viewed_at) VALUES (?, ?, ?, 1, ?, ?)");
        $chatPairs = [
            [$staffIds['admin@demo.pl'], $staffIds['pracownik@demo.pl'], 'Sprawdz prosze leady z depilacji i oddzwon do nowych klientek.', '2026-03-23 09:05:00'],
            [$staffIds['pracownik@demo.pl'], $staffIds['admin@demo.pl'], 'Jasne, dwie osoby sa juz ustawione na konsultacje jutro.', '2026-03-23 09:12:00'],
            [$staffIds['karolina@beauty.pl'], $staffIds['recepcja@beauty.pl'], 'Mam wolne okno o 14:00, mozesz dosunac stylizacje brwi.', '2026-03-23 10:21:00'],
            [$staffIds['recepcja@beauty.pl'], $staffIds['karolina@beauty.pl'], 'Super, wpisalam Klaudie i wyslalam potwierdzenie.', '2026-03-23 10:26:00'],
        ];
        foreach ($chatPairs as [$sender, $receiver, $message, $timeSent]) {
            $viewedAt = $timeSent;
            $chatMsgStmt->bind_param('iisss', $sender, $receiver, $message, $timeSent, $viewedAt);
            $chatMsgStmt->execute();
        }
        $chatMsgStmt->close();

        $groupMsgStmt = $db->prepare("INSERT INTO `{$p}chatgroupmessages` (group_id, message, sender_id, time_sent) VALUES (?, ?, ?, ?)");
        $groupMessages = [
            [$groupId, 'Dzisiaj promujemy pakiet lifting plus brwi. Prosze ogarnac follow-up po 12:00.', $staffIds['admin@demo.pl'], '2026-03-23 08:45:00'],
            [$groupId, 'Recepcja ma juz wypelniony grafik do piatku, zostalo jedno okno na sobote.', $staffIds['recepcja@beauty.pl'], '2026-03-23 11:05:00'],
            [$groupId, 'Klientki po mezoterapii dostaly dzisiaj instrukcje pozabiegowe.', $staffIds['natalia@beauty.pl'], '2026-03-23 12:15:00'],
        ];
        foreach ($groupMessages as [$gId, $message, $sender, $timeSent]) {
            $groupMsgStmt->bind_param('isis', $gId, $message, $sender, $timeSent);
            $groupMsgStmt->execute();
        }
        $groupMsgStmt->close();

        $chatSettingsStmt = $db->prepare("INSERT INTO `{$p}chatsettings` (user_id, name, value) VALUES (?, ?, ?)");
        foreach ($staffIds as $staffId) {
            $settingName = 'chat_status';
            $value = 'online';
            $chatSettingsStmt->bind_param('iss', $staffId, $settingName, $value);
            $chatSettingsStmt->execute();
        }
        $chatSettingsStmt->close();

        $feedbackStmt = $db->prepare("INSERT INTO `{$p}feedback` (customerid, projectid, coding, communication, services, recommendation, message, feedback_submitted, date) VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?)");
        $feedbackRows = [
            [1, (string)$projectIds[0], '10', '10', '10', '10', 'Makijaz i kontakt byly perfekcyjne.', '2026-03-22 16:00:00'],
            [2, (string)$projectIds[1], '9', '10', '10', '9', 'Bardzo dobra organizacja i piekny efekt.', '2026-03-22 17:20:00'],
            [5, (string)$projectIds[4], '10', '9', '10', '10', 'Wracam na kolejne zabiegi, swietny standard.', '2026-03-23 10:10:00'],
        ];
        foreach ($feedbackRows as [$customerId, $projectId, $coding, $communication, $services, $recommendation, $message, $date]) {
            $feedbackStmt->bind_param('isssssss', $customerId, $projectId, $coding, $communication, $services, $recommendation, $message, $date);
            $feedbackStmt->execute();
        }
        $feedbackStmt->close();

        $newsStmt = $db->prepare("INSERT INTO `{$p}newsfeed_posts` (creator, datecreated, visibility, content, pinned, datepinned) VALUES (?, ?, 'all', ?, ?, ?)");
        $newsCommentStmt = $db->prepare("INSERT INTO `{$p}newsfeed_post_comments` (content, userid, postid, dateadded) VALUES (?, ?, ?, ?)");
        $posts = [
            [$staffIds['admin@demo.pl'], '2026-03-23 08:30:00', 'Startujemy tydzien z promocja pakietu wiosennego i follow-upem do klientek VIP.', 1, '2026-03-23 08:31:00', 'Brzmi super, mam juz przygotowane szablony wiadomosci.', $staffIds['pracownik@demo.pl'], '2026-03-23 08:42:00'],
            [$staffIds['recepcja@beauty.pl'], '2026-03-23 11:20:00', 'Sobota prawie pelna. Zostalo jedno okienko na 14:00.', 0, null, 'Wrzuce to jeszcze na stories i przypne w kalendarzu.', $staffIds['karolina@beauty.pl'], '2026-03-23 11:35:00'],
        ];
        foreach ($posts as [$creator, $dateCreated, $content, $pinned, $datePinned, $comment, $commentUser, $commentDate]) {
            $newsStmt->bind_param('issis', $creator, $dateCreated, $content, $pinned, $datePinned);
            $newsStmt->execute();
            $postId = (int)$db->insert_id;
            $newsCommentStmt->bind_param('siis', $comment, $commentUser, $postId, $commentDate);
            $newsCommentStmt->execute();
        }
        $newsStmt->close();
        $newsCommentStmt->close();
    }

    private function upsertTenant(mysqli $master, string $slug, string $name, string $dbName, string $metadataJson, ?string $dbUser = null, ?string $dbPassword = null): int
    {
        if ($dbUser === null || $dbPassword === null) {
            $creds = $this->ensureTenantDbUser($slug, $dbName);
            $dbUser = $creds['user'];
            $dbPassword = $creds['password'];
        }

        $dsn = sprintf('mysqli:host=localhost;dbname=%s;user=%s;password=%s;', $dbName, $dbUser, $dbPassword);
        $encryptedDsn = $this->encryption->encrypt($dsn);
        $existing = $this->fetchRow($master, "SELECT id FROM tblperfex_saas_companies WHERE slug = '" . $master->real_escape_string($slug) . "' LIMIT 1");

        if ($existing) {
            $stmt = $master->prepare("UPDATE tblperfex_saas_companies SET clientid=?, name=?, status='active', status_note=NULL, dsn=?, custom_domain=NULL, metadata=?, created_at=COALESCE(created_at, NOW()) WHERE id=?");
            $clientId = self::OWNER_CLIENT_ID;
            $id = (int)$existing['id'];
            $stmt->bind_param('isssi', $clientId, $name, $encryptedDsn, $metadataJson, $id);
            $stmt->execute();
            $stmt->close();
            return $id;
        }

        $stmt = $master->prepare("INSERT INTO tblperfex_saas_companies (clientid, slug, name, status, status_note, dsn, custom_domain, metadata, created_at) VALUES (?, ?, ?, 'active', NULL, ?, NULL, ?, NOW())");
        $clientId = self::OWNER_CLIENT_ID;
        $stmt->bind_param('issss', $clientId, $slug, $name, $encryptedDsn, $metadataJson);
        $stmt->execute();
        $id = (int)$master->insert_id;
        $stmt->close();
        return $id;
    }

    private function backupAndRemoveTenant(mysqli $master, string $slug, string $dbName, string $backupDir): void
    {
        $existing = $this->fetchRow($master, "SELECT * FROM tblperfex_saas_companies WHERE slug = '" . $master->real_escape_string($slug) . "' LIMIT 1");
        if ($existing) {
            $backupFile = $backupDir . '/' . $slug . '.sql';
            $this->run('bash -lc ' . escapeshellarg("mysqldump --skip-lock-tables --single-transaction --no-tablespaces {$dbName} > {$backupFile}"));
            $master->query("DELETE FROM tblperfex_saas_companies WHERE id = " . (int)$existing['id']);
        }
        $this->run('mysql -e ' . escapeshellarg("DROP DATABASE IF EXISTS `{$dbName}`"));
    }

    private function cloneTenantDatabase(string $sourceDb, string $sourcePrefix, string $targetDb, string $targetPrefix): void
    {
        $this->run('mysql -e ' . escapeshellarg("CREATE DATABASE `{$targetDb}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"));
        $pipeline = sprintf(
            "mysqldump --skip-lock-tables --single-transaction --no-tablespaces %s | perl -0pe 's/%s/%s/g' | mysql %s",
            $sourceDb,
            $sourcePrefix,
            $targetPrefix,
            $targetDb
        );
        $this->run('bash -lc ' . escapeshellarg($pipeline));
    }

    private function createModuleTables(string $targetDb, string $targetPrefix): void
    {
        $moduleTables = [
            'tblchatmessages',
            'tblchatclientmessages',
            'tblchatsettings',
            'tblchatgroups',
            'tblchatgroupmembers',
            'tblchatgroupmessages',
            'tblchatsharedfiles',
            'tblchatgroupsharedfiles',
            'tblfeedback',
            'tbllanding_page_blocks',
            'tbllanding_page_form_data',
            'tbllanding_page_settings',
            'tbllanding_page_templates',
            'tbllanding_pages',
        ];
        foreach ($moduleTables as $table) {
            $targetTable = $targetPrefix . substr($table, 3);
            $pipeline = sprintf(
                "mysqldump --skip-lock-tables --single-transaction --no-tablespaces --no-data %s %s | perl -0pe 's/`%s`/`%s`/g' | mysql %s",
                self::MASTER_DB,
                $table,
                $table,
                $targetTable,
                $targetDb
            );
            $this->run('bash -lc ' . escapeshellarg($pipeline));
        }
    }

    private function ensureTenantDbUser(string $slug, string $dbName): array
    {
        $username = 'ps_' . preg_replace('/[^a-z0-9_]/', '', strtolower($slug));
        $username = substr($username, 0, 24);

        $master = $this->connect('mysql');
        $existing = $this->fetchRow($master, "SELECT user FROM user WHERE user = '" . $master->real_escape_string($username) . "' LIMIT 1");

        if ($existing) {
            $password = $this->fetchValue($this->connect(self::MASTER_DB), "SELECT JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.db_password')) FROM tblperfex_saas_companies WHERE slug = '" . addslashes($slug) . "' LIMIT 1");
            if (empty($password)) {
                $password = $this->randomPassword();
                $master->query("ALTER USER '" . $master->real_escape_string($username) . "'@'localhost' IDENTIFIED BY '" . $master->real_escape_string($password) . "'");
            }
        } else {
            $password = $this->randomPassword();
            $master->query("CREATE USER '" . $master->real_escape_string($username) . "'@'localhost' IDENTIFIED BY '" . $master->real_escape_string($password) . "'");
        }

        $master->query("GRANT ALL PRIVILEGES ON `" . $master->real_escape_string($dbName) . "`.* TO '" . $master->real_escape_string($username) . "'@'localhost'");
        $master->query("FLUSH PRIVILEGES");
        $master->close();

        return [
            'user' => $username,
            'password' => $password,
        ];
    }

    private function randomPassword(int $length = 24): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $max = strlen($chars) - 1;
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, $max)];
        }
        return $password;
    }

    private function attachTag(mysqli $db, string $prefix, int $relId, string $relType, int $tagId): void
    {
        $stmt = $db->prepare("INSERT INTO `{$prefix}taggables` (rel_id, rel_type, tag_id, tag_order) VALUES (?, ?, ?, 0)");
        $stmt->bind_param('isi', $relId, $relType, $tagId);
        $stmt->execute();
        $stmt->close();
    }

    private function guardCli(): void
    {
        if (!$this->input->is_cli_request()) {
            show_404();
        }
    }

    private function connect(string $database): mysqli
    {
        $db = new mysqli('localhost', 'root', '', $database);
        $db->set_charset('utf8mb4');
        return $db;
    }

    private function fetchRow(mysqli $db, string $sql): ?array
    {
        $result = $db->query($sql);
        $row = $result->fetch_assoc();
        $result->close();
        return $row ?: null;
    }

    private function fetchValue(mysqli $db, string $sql): ?string
    {
        $row = $this->fetchRow($db, $sql);
        return $row ? (string)array_values($row)[0] : null;
    }

    private function runSql(mysqli $db, string $sql): void
    {
        $db->query($sql);
    }

    private function run(string $command): void
    {
        $output = [];
        $exitCode = 0;
        exec($command . ' 2>&1', $output, $exitCode);
        if ($exitCode !== 0) {
            throw new RuntimeException("Command failed ({$exitCode}): {$command}\n" . implode("\n", $output));
        }
    }
}
