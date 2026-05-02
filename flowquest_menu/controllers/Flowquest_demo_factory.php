<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Flowquest_demo_factory extends CI_Controller
{
    private const MASTER_DB = 'perfex_db';
    private const SOURCE_DEMO_DB = 'ps_demo';
    private const SOURCE_DEMO_PREFIX = 'demo_tbl';
    private const OWNER_CLIENT_ID = 3;
    private const DEMO_PASSWORD = 'Demo123!';

    private $schemaCache = [];

    public function __construct()
    {
        parent::__construct();
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    }

    public function ping()
    {
        echo "pong\n";
    }

    public function provision_all()
    {
        $this->guardCli();

        $results = [];
        foreach ($this->branchOrder() as $branch) {
            $results[$branch] = $this->provisionBranchInternal($branch);
        }

        echo json_encode(['status' => 'ok', 'branches' => $results], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    }

    public function provision_branch($branch = 'beauty')
    {
        $this->guardCli();

        echo json_encode($this->provisionBranchInternal($branch), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    }

    public function validate_branch($branch = 'beauty')
    {
        $this->guardCli();

        $spec = $this->getBranchSpec($branch);
        $validation = [
            'template' => $this->validateTenant($spec['template_slug'], $spec['template_db'], $spec['template_prefix'], $spec),
            'demo' => $this->validateTenant($spec['demo_slug'], $spec['demo_db'], $spec['demo_prefix'], $spec),
        ];

        echo json_encode(['status' => 'ok', 'branch' => $branch, 'validation' => $validation], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    }

    private function provisionBranchInternal(string $branch): array
    {
        $spec = $this->getBranchSpec($branch);
        $this->logFactoryMessage($branch, 'provision:start');
        $master = $this->connect(self::MASTER_DB);
        $backupDir = '/root/flowquest_backups/' . $branch . '_' . date('Ymd_His');
        $this->run("mkdir -p " . escapeshellarg($backupDir));
        $this->logFactoryMessage($branch, 'provision:backup_dir', ['backup_dir' => $backupDir]);

        $demoCompany = $this->fetchRow($master, "SELECT * FROM tblperfex_saas_companies WHERE slug = 'demo' LIMIT 1");
        if (!$demoCompany) {
            throw new RuntimeException('Nie znaleziono bazowego tenantu demo.');
        }
        $this->logFactoryMessage($branch, 'provision:demo_source_found');

        $this->backupAndRemoveTenant($master, $spec['template_slug'], $spec['template_db'], $backupDir);
        $this->backupAndRemoveTenant($master, $spec['demo_slug'], $spec['demo_db'], $backupDir);
        $this->logFactoryMessage($branch, 'provision:removed_previous_tenants');

        $this->cloneTenantDatabase(self::SOURCE_DEMO_DB, self::SOURCE_DEMO_PREFIX, $spec['template_db'], $spec['template_prefix']);
        $this->createModuleTablesForModules($spec['template_db'], $spec['template_prefix'], $spec['modules']);
        $this->repairPrefixedForeignKeys($spec['template_db'], $spec['template_prefix']);
        $this->prepareTemplateDb($spec);
        $this->logFactoryMessage($branch, 'provision:template_db_ready');
        $templateMeta = $this->buildBranchMetadata($demoCompany['metadata'], $spec, false);
        $templateId = $this->upsertTenant($master, $spec['template_slug'], $spec['template_name'], $spec['template_db'], $templateMeta);
        $this->logFactoryMessage($branch, 'provision:template_upserted', ['template_id' => $templateId]);

        $this->cloneTenantDatabase($spec['template_db'], $spec['template_prefix'], $spec['demo_db'], $spec['demo_prefix']);
        $this->logFactoryMessage($branch, 'provision:demo_db_cloned');
        $this->prepareDemoDb($spec);
        $this->logFactoryMessage($branch, 'provision:demo_db_seeded');
        $demoMeta = $this->buildBranchMetadata($demoCompany['metadata'], $spec, true);
        $demoId = $this->upsertTenant($master, $spec['demo_slug'], $spec['demo_name'], $spec['demo_db'], $demoMeta);
        $this->logFactoryMessage($branch, 'provision:demo_upserted', ['demo_id' => $demoId]);

        return [
            'branch' => $branch,
            'template' => [
                'id' => $templateId,
                'slug' => $spec['template_slug'],
                'db' => $spec['template_db'],
                'url' => 'https://' . $spec['template_slug'] . '.flowquest.pl/admin/',
            ],
            'demo' => [
                'id' => $demoId,
                'slug' => $spec['demo_slug'],
                'db' => $spec['demo_db'],
                'url' => 'https://' . $spec['demo_slug'] . '.flowquest.pl/admin/',
            ],
            'validation' => [
                'template' => $this->validateTenant($spec['template_slug'], $spec['template_db'], $spec['template_prefix'], $spec),
                'demo' => $this->validateTenant($spec['demo_slug'], $spec['demo_db'], $spec['demo_prefix'], $spec),
            ],
            'backup_dir' => $backupDir,
        ];
    }

    private function validateTenant(string $slug, string $dbName, string $prefix, array $spec): array
    {
        $db = $this->connect($dbName);
        $counts = [
            'staff' => $this->safeCount($db, $prefix . 'staff'),
            'clients' => $this->safeCount($db, $prefix . 'clients'),
            'contacts' => $this->safeCount($db, $prefix . 'contacts'),
            'leads' => $this->safeCount($db, $prefix . 'leads'),
            'projects' => $this->safeCount($db, $prefix . 'projects'),
            'tasks' => $this->safeCount($db, $prefix . 'tasks'),
            'comments' => $this->safeCount($db, $prefix . 'task_comments'),
            'chat' => $this->safeCount($db, $prefix . 'chatmessages') + $this->safeCount($db, $prefix . 'chatgroupmessages'),
        ];

        foreach ($spec['validation_tables'] as $key => $tableSuffix) {
            $counts[$key] = $this->safeCount($db, $prefix . $tableSuffix);
        }

        return [
            'slug' => $slug,
            'urls' => [
                'admin' => $this->curlStatus('https://' . $slug . '.flowquest.pl/admin/authentication'),
                'client' => $this->curlStatus('https://' . $slug . '.flowquest.pl/authentication/login'),
            ],
            'counts' => $counts,
        ];
    }

    private function prepareTemplateDb(array $spec): void
    {
        $db = $this->connect($spec['template_db']);
        $p = $spec['template_prefix'];

        $this->activateTenantModules($db, $p, $spec['modules']);
        $this->runSql($db, "UPDATE `{$p}options` SET value = '" . $db->real_escape_string($spec['template_name']) . "' WHERE name = 'companyname'");
        $this->runSql($db, "UPDATE `{$p}options` SET value = 'polish' WHERE name = 'active_language'");

        $this->cleanupCoreTables($db, $p);
        $this->cleanupBranchTables($db, $p, $spec);
        $this->seedTemplateIdentities($db, $p, $spec);
        $this->seedLeadSources($db, $p, $spec);
        $this->seedLeadStatuses($db, $p, $spec);
        $this->seedCustomerGroups($db, $p, $spec);
        $this->seedTemplateModuleRows($db, $p, $spec);
        $this->configureModuleOptions($db, $p, $spec, []);
    }

    private function prepareDemoDb(array $spec): void
    {
        $db = $this->connect($spec['demo_db']);
        $p = $spec['demo_prefix'];
        try {
            $this->activateTenantModules($db, $p, $spec['modules']);
            $this->runSql($db, "UPDATE `{$p}options` SET value = '" . $db->real_escape_string($spec['demo_name']) . "' WHERE name = 'companyname'");

            $this->seedDemoStaff($db, $p);
            $context = $this->seedDemoCoreData($db, $p, $spec);
            $this->seedChatAndComments($db, $p, $spec, $context);
            $this->seedNewsfeed($db, $p, $spec, $context);
            $this->seedBranchDemoData($db, $p, $spec, $context);
            $this->configureModuleOptions($db, $p, $spec, $context);
        } catch (Throwable $e) {
            $this->logFactoryError($spec['slug'], 'prepareDemoDb', $e);
            throw $e;
        }
    }

    private function activateTenantModules(mysqli $db, string $prefix, array $modules): void
    {
        if (!$this->tableExists($db, $prefix . 'modules')) {
            return;
        }

        $allModules = array_values(array_unique(array_merge($this->baseTenantModules(), $modules)));
        $escaped = array_map([$db, 'real_escape_string'], $allModules);

        $this->runSql($db, "UPDATE `{$prefix}modules` SET active = 0");
        $this->runSql($db, "UPDATE `{$prefix}modules` SET active = 1 WHERE module_name IN ('" . implode("','", $escaped) . "')");
    }

    private function cleanupCoreTables(mysqli $db, string $prefix): void
    {
        $tables = [
            'task_comments', 'task_assigned', 'task_followers', 'tasks',
            'project_members', 'project_activity', 'project_files', 'project_notes',
            'project_settings', 'projectdiscussioncomments', 'projectdiscussions', 'projects',
            'notes', 'newsfeed_post_comments', 'newsfeed_posts', 'leads', 'proposals',
            'invoices', 'invoicepaymentrecords', 'itemable', 'chatmessages',
            'chatclientmessages', 'chatgroupmessages', 'chatgroupmembers', 'chatgroups',
            'chatsharedfiles', 'chatgroupsharedfiles', 'taggables', 'tags',
            'customer_groups', 'customers_groups',
        ];

        foreach ($tables as $table) {
            $this->truncateIfExists($db, $prefix . $table);
        }

        if ($this->tableExists($db, $prefix . 'contacts')) {
            $this->runSql($db, "DELETE FROM `{$prefix}contacts` WHERE userid NOT IN (1)");
        }
        if ($this->tableExists($db, $prefix . 'clients')) {
            $this->runSql($db, "DELETE FROM `{$prefix}clients` WHERE userid NOT IN (1)");
        }
        $this->truncateIfExists($db, $prefix . 'contact_permissions');
    }

    private function cleanupBranchTables(mysqli $db, string $prefix, array $spec): void
    {
        foreach ($spec['cleanup_tables'] as $tableSuffix) {
            $this->truncateIfExists($db, $prefix . $tableSuffix);
        }
    }

    private function seedTemplateIdentities(mysqli $db, string $prefix, array $spec): void
    {
        $this->runSql($db, "UPDATE `{$prefix}staff` SET firstname='Wlasciciel', lastname='" . $db->real_escape_string($spec['label']) . "' WHERE staffid=1");
        $this->runSql($db, "UPDATE `{$prefix}staff` SET firstname='Pracownik', lastname='" . $db->real_escape_string($spec['label']) . "' WHERE staffid=2");
        $this->runSql($db, "UPDATE `{$prefix}clients` SET company='" . $db->real_escape_string($spec['company']) . "', city='" . $db->real_escape_string($spec['city']) . "', state='" . $db->real_escape_string($spec['state']) . "', address='" . $db->real_escape_string($spec['address']) . "', website='https://" . $db->real_escape_string($spec['demo_slug']) . ".flowquest.pl' WHERE userid=1");
        $this->runSql($db, "UPDATE `{$prefix}contacts` SET firstname='Kontakt', lastname='" . $db->real_escape_string($spec['label']) . "', email='" . $db->real_escape_string($spec['contact_email']) . "' WHERE id=1");
        $this->runSql($db, "UPDATE `{$prefix}contacts` SET firstname='Klient', lastname='" . $db->real_escape_string($spec['label']) . "', email='klient@demo.pl' WHERE id=4");
        $this->runSql($db, "INSERT INTO `{$prefix}contact_permissions` (permission_id, userid) VALUES (1,4),(2,4),(4,4)");
    }

    private function seedLeadSources(mysqli $db, string $prefix, array $spec): void
    {
        $this->truncateIfExists($db, $prefix . 'leads_sources');
        if (!$this->tableExists($db, $prefix . 'leads_sources')) {
            return;
        }

        $stmt = $db->prepare("INSERT INTO `{$prefix}leads_sources` (name) VALUES (?)");
        foreach ($spec['lead_sources'] as $source) {
            $stmt->bind_param('s', $source);
            $stmt->execute();
        }
        $stmt->close();
    }

    private function seedLeadStatuses(mysqli $db, string $prefix, array $spec): void
    {
        $this->truncateIfExists($db, $prefix . 'leads_status');
        if (!$this->tableExists($db, $prefix . 'leads_status')) {
            return;
        }

        $stmt = $db->prepare("INSERT INTO `{$prefix}leads_status` (name, color, statusorder, isdefault) VALUES (?, ?, ?, ?)");
        foreach ($spec['lead_statuses'] as [$name, $color, $order, $default]) {
            $stmt->bind_param('ssii', $name, $color, $order, $default);
            $stmt->execute();
        }
        $stmt->close();
    }

    private function seedCustomerGroups(mysqli $db, string $prefix, array $spec): void
    {
        if (!$this->tableExists($db, $prefix . 'customers_groups')) {
            return;
        }

        $stmt = $db->prepare("INSERT INTO `{$prefix}customers_groups` (name) VALUES (?)");
        foreach ($spec['customer_groups'] as $group) {
            $stmt->bind_param('s', $group);
            $stmt->execute();
        }
        $stmt->close();
    }

    private function seedTemplateModuleRows(mysqli $db, string $prefix, array $spec): void
    {
        if (!empty($spec['services'])) {
            foreach ($spec['services'] as $index => $service) {
                $this->insertSmartRow($db, $prefix . 'appointly_appointment_types', [
                    'type' => $service,
                    'color' => $spec['service_colors'][$index % count($spec['service_colors'])],
                ]);
            }
        }

        foreach ($spec['template_rows'] as $tableSuffix => $rows) {
            foreach ($rows as $row) {
                $this->insertSmartRow($db, $prefix . $tableSuffix, $row);
            }
        }
    }

    private function seedDemoStaff(mysqli $db, string $prefix): void
    {
        $employeeHash = $this->fetchValue($db, "SELECT password FROM `{$prefix}staff` WHERE staffid = 2");
        $staffRows = [
            ['karolina@demo.pl', 'Karolina', 'Nowak', '600100200'],
            ['natalia@demo.pl', 'Natalia', 'Mazur', '600100201'],
            ['recepcja@demo.pl', 'Recepcja', 'FlowQuest', '600100202'],
        ];
        $stmt = $db->prepare("INSERT INTO `{$prefix}staff` (email, firstname, lastname, phonenumber, password, datecreated, admin, role, active, hourly_rate) VALUES (?, ?, ?, ?, ?, NOW(), 0, 1, 1, 0.00)");
        foreach ($staffRows as [$email, $first, $last, $phone]) {
            $stmt->bind_param('sssss', $email, $first, $last, $phone, $employeeHash);
            $stmt->execute();
        }
        $stmt->close();
    }

    private function seedDemoCoreData(mysqli $db, string $prefix, array $spec): array
    {
        $clientHash = $this->fetchValue($db, "SELECT password FROM `{$prefix}contacts` WHERE id = 4");
        $clients = $this->buildClients($spec);

        [$company, $city, $state, $address, $phone, $email, $first, $last] = $clients[0];
        $stmt = $db->prepare("UPDATE `{$prefix}contacts` SET firstname=?, lastname=?, email=?, active=1 WHERE id=1");
        $stmt->bind_param('sss', $first, $last, $email);
        $stmt->execute();
        $stmt->close();

        $stmt = $db->prepare("UPDATE `{$prefix}contacts` SET firstname='Klient', lastname=?, email='klient@demo.pl', password=?, active=1 WHERE id=4");
        $stmt->bind_param('ss', $spec['label'], $clientHash);
        $stmt->execute();
        $stmt->close();

        $stmt = $db->prepare("UPDATE `{$prefix}clients` SET company=?, city=?, state=?, address=?, phonenumber=?, active=1 WHERE userid=1");
        $stmt->bind_param('sssss', $company, $city, $state, $address, $phone);
        $stmt->execute();
        $stmt->close();

        $insertClient = $db->prepare("INSERT INTO `{$prefix}clients` (company, country, city, state, address, phonenumber, datecreated, active, addedfrom, registration_confirmed) VALUES (?, 0, ?, ?, ?, ?, NOW(), 1, 1, 1)");
        $insertContact = $db->prepare("INSERT INTO `{$prefix}contacts` (userid, is_primary, firstname, lastname, email, phonenumber, datecreated, password, active, invoice_emails, estimate_emails, credit_note_emails, contract_emails, task_emails, project_emails, ticket_emails) VALUES (?, 1, ?, ?, ?, ?, NOW(), ?, 1, 1, 1, 1, 1, 1, 1, 1)");

        foreach (array_slice($clients, 1) as [$cCompany, $cCity, $cState, $cAddress, $cPhone, $cEmail, $cFirst, $cLast]) {
            $insertClient->bind_param('sssss', $cCompany, $cCity, $cState, $cAddress, $cPhone);
            $insertClient->execute();
            $clientId = (int)$db->insert_id;
            $insertContact->bind_param('isssss', $clientId, $cFirst, $cLast, $cEmail, $cPhone, $clientHash);
            $insertContact->execute();
        }
        $insertClient->close();
        $insertContact->close();

        $this->runSql($db, "INSERT INTO `{$prefix}contact_permissions` (permission_id, userid) VALUES (1,4),(2,4),(4,4)");

        $staffIds = $this->mapIdByKey($db, "SELECT staffid, email FROM `{$prefix}staff`");
        $groupIds = $this->mapIdByKey($db, "SELECT id, name FROM `{$prefix}customers_groups`");
        $leadSourceIds = $this->mapIdByKey($db, "SELECT id, name FROM `{$prefix}leads_sources`");
        $leadStatusIds = $this->mapIdByKey($db, "SELECT id, name FROM `{$prefix}leads_status`");

        $tagIds = [];
        if ($this->tableExists($db, $prefix . 'tags')) {
            $stmt = $db->prepare("INSERT INTO `{$prefix}tags` (name) VALUES (?)");
            foreach (['demo', 'vip', 'follow-up', $spec['slug'], 'pakiet'] as $tag) {
                $stmt->bind_param('s', $tag);
                $stmt->execute();
                $tagIds[$tag] = (int)$db->insert_id;
            }
            $stmt->close();
        }

        $stmt = $db->prepare("INSERT INTO `{$prefix}customer_groups` (groupid, customer_id) VALUES (?, ?)");
        foreach ([1 => 'VIP', 2 => 'Nowi klienci', 3 => 'Abonament', 4 => 'Nowi klienci', 5 => 'Polecenie'] as $customerId => $groupName) {
            if (!isset($groupIds[$groupName])) {
                continue;
            }
            $groupId = (int)$groupIds[$groupName];
            $stmt->bind_param('ii', $groupId, $customerId);
            $stmt->execute();
        }
        $stmt->close();

        $leadIds = $this->seedGenericLeads($db, $prefix, $spec, $leadSourceIds, $leadStatusIds, $staffIds, $tagIds);
        $projectIds = $this->seedGenericProjects($db, $prefix, $spec, $staffIds);
        $taskIds = $this->seedGenericTasks($db, $prefix, $spec, $staffIds, $projectIds);

        return [
            'staff_ids' => $staffIds,
            'client_ids' => array_map('intval', array_values($this->mapIdByKey($db, "SELECT userid, company FROM `{$prefix}clients` ORDER BY userid"))),
            'lead_ids' => $leadIds,
            'project_ids' => $projectIds,
            'task_ids' => $taskIds,
            'tag_ids' => $tagIds,
        ];
    }

    private function seedGenericLeads(mysqli $db, string $prefix, array $spec, array $leadSourceIds, array $leadStatusIds, array $staffIds, array $tagIds): array
    {
        $titles = $this->buildLeadTitles($spec['focus_terms']);
        $leadIds = [];
        $stmt = $db->prepare("INSERT INTO `{$prefix}leads` (hash, name, title, company, description, country, assigned, dateadded, status, source, addedfrom, email, phonenumber, lead_value) VALUES (?, ?, ?, ?, ?, 0, ?, ?, ?, ?, 1, ?, ?, ?)");
        foreach ($titles as $index => $title) {
            $name = $spec['label'] . ' Lead ' . ($index + 1);
            $hash = md5($name . $title);
            $company = $spec['company'];
            $description = $spec['focus_copy'];
            $assigned = (int)($staffIds['pracownik@demo.pl'] ?? 2);
            $dateAdded = (new DateTimeImmutable('2025-09-01 09:00:00'))->modify('+' . ($index * 19) . ' days')->format('Y-m-d H:i:s');
            $statusName = array_keys($leadStatusIds)[$index % max(1, count($leadStatusIds))];
            $sourceName = array_keys($leadSourceIds)[$index % max(1, count($leadSourceIds))];
            $status = (int)$leadStatusIds[$statusName];
            $source = (int)$leadSourceIds[$sourceName];
            $email = 'lead' . ($index + 1) . '@' . $spec['slug'] . '.pl';
            $phone = '50' . str_pad((string)($index + 1), 7, '0', STR_PAD_LEFT);
            $value = (float)(($index + 2) * $spec['price_factor']);
            $stmt->bind_param('sssssisiissd', $hash, $name, $title, $company, $description, $assigned, $dateAdded, $status, $source, $email, $phone, $value);
            $stmt->execute();
            $leadId = (int)$db->insert_id;
            $leadIds[] = $leadId;
            if (isset($tagIds['follow-up'])) {
                $this->attachTag($db, $prefix, $leadId, 'lead', $tagIds['follow-up']);
            }
        }
        $stmt->close();
        return $leadIds;
    }

    private function seedGenericProjects(mysqli $db, string $prefix, array $spec, array $staffIds): array
    {
        $titles = $this->buildProjectTitles($spec['focus_terms']);
        $projectIds = [];
        $stmt = $db->prepare("INSERT INTO `{$prefix}projects` (name, description, status, clientid, billing_type, start_date, deadline, project_created, date_finished, progress, project_cost, addedfrom) VALUES (?, ?, ?, ?, 1, ?, ?, ?, ?, ?, ?, ?)");
        $memberStmt = $db->prepare("INSERT INTO `{$prefix}project_members` (project_id, staff_id) VALUES (?, ?)");
        foreach ($titles as $index => $title) {
            $description = $spec['focus_copy'];
            $status = ($index % 4) + 1;
            $clientId = ($index % 5) + 1;
            $start = (new DateTimeImmutable('2025-08-15'))->modify('+' . ($index * 17) . ' days');
            $deadline = $start->modify('+21 days');
            $progress = min(100, 25 + ($index * 8));
            $cost = (float)(($index + 1) * $spec['price_factor']);
            $finished = $progress >= 100 ? $deadline->format('Y-m-d') : null;
            $startDate = $start->format('Y-m-d');
            $deadlineDate = $deadline->format('Y-m-d');
            $created = $startDate;
            $addedFrom = 1;
            $stmt->bind_param('ssiissssidi', $title, $description, $status, $clientId, $startDate, $deadlineDate, $created, $finished, $progress, $cost, $addedFrom);
            $stmt->execute();
            $projectId = (int)$db->insert_id;
            $projectIds[] = $projectId;
            foreach ([(int)($staffIds['admin@demo.pl'] ?? 1), (int)($staffIds['pracownik@demo.pl'] ?? 2)] as $staffId) {
                $memberStmt->bind_param('ii', $projectId, $staffId);
                $memberStmt->execute();
            }
        }
        $stmt->close();
        $memberStmt->close();
        return $projectIds;
    }

    private function seedGenericTasks(mysqli $db, string $prefix, array $spec, array $staffIds, array $projectIds): array
    {
        $titles = $this->buildTaskTitles($spec['focus_terms']);
        $taskIds = [];
        $stmt = $db->prepare("INSERT INTO `{$prefix}tasks` (name, description, priority, dateadded, startdate, duedate, status, addedfrom, is_public, billable, milestone, rel_id, rel_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, 0, 0, ?, ?)");
        $assignStmt = $db->prepare("INSERT INTO `{$prefix}task_assigned` (staffid, taskid, assigned_from, is_assigned_from_contact) VALUES (?, ?, ?, 0)");
        $staffKeys = array_keys($staffIds);
        foreach ($titles as $index => $title) {
            $description = 'Zadanie demo dla ' . strtolower($spec['label']) . ' pokazujace obieg pracy w CRM.';
            $priority = ($index % 3) + 1;
            $baseDate = (new DateTimeImmutable('2025-09-05 08:00:00'))->modify('+' . ($index * 9) . ' days');
            $dateAdded = $baseDate->format('Y-m-d H:i:s');
            $startDate = $baseDate->format('Y-m-d');
            $dueDate = $baseDate->modify('+5 days')->format('Y-m-d');
            $status = ($index % 5) + 1;
            $addedFrom = 1;
            $projectId = $projectIds[$index % max(1, count($projectIds))] ?? 0;
            $relId = $projectId;
            $relType = 'project';
            $stmt->bind_param('ssisssiiis', $title, $description, $priority, $dateAdded, $startDate, $dueDate, $status, $addedFrom, $relId, $relType);
            $stmt->execute();
            $taskId = (int)$db->insert_id;
            $taskIds[] = $taskId;
            $assignedStaff = (int)$staffIds[$staffKeys[$index % count($staffKeys)]];
            $assignStmt->bind_param('iii', $assignedStaff, $taskId, $addedFrom);
            $assignStmt->execute();
        }
        $stmt->close();
        $assignStmt->close();
        return $taskIds;
    }

    private function seedChatAndComments(mysqli $db, string $prefix, array $spec, array $context): void
    {
        $staffIds = $context['staff_ids'];
        $comments = $this->buildComments($spec['focus_terms']);
        $commentStmt = $db->prepare("INSERT INTO `{$prefix}task_comments` (content, taskid, staffid, dateadded) VALUES (?, ?, ?, ?)");
        foreach ($comments as $index => $content) {
            $taskId = $context['task_ids'][$index % max(1, count($context['task_ids']))];
            $staffId = (int)$staffIds[array_keys($staffIds)[$index % count($staffIds)]];
            $date = (new DateTimeImmutable('2025-09-10 09:00:00'))->modify('+' . ($index * 8) . ' days')->format('Y-m-d H:i:s');
            $commentStmt->bind_param('siis', $content, $taskId, $staffId, $date);
            $commentStmt->execute();
        }
        $commentStmt->close();

        $groupName = 'Zespol ' . $spec['label'];
        $creator = (int)($staffIds['admin@demo.pl'] ?? 1);
        $groupStmt = $db->prepare("INSERT INTO `{$prefix}chatgroups` (created_by_id, group_name) VALUES (?, ?)");
        $groupStmt->bind_param('is', $creator, $groupName);
        $groupStmt->execute();
        $groupId = (int)$db->insert_id;
        $groupStmt->close();

        $groupMemberStmt = $db->prepare("INSERT INTO `{$prefix}chatgroupmembers` (group_id, member_id, group_name) VALUES (?, ?, ?)");
        foreach ($staffIds as $staffId) {
            $groupMemberStmt->bind_param('iis', $groupId, $staffId, $groupName);
            $groupMemberStmt->execute();
        }
        $groupMemberStmt->close();

        $chatPairs = $this->buildChatMessages($spec['focus_terms']);
        $chatStmt = $db->prepare("INSERT INTO `{$prefix}chatmessages` (sender_id, reciever_id, message, viewed, time_sent, viewed_at) VALUES (?, ?, ?, 1, ?, ?)");
        $staffKeys = array_keys($staffIds);
        foreach ($chatPairs as $index => $message) {
            $sender = (int)$staffIds[$staffKeys[$index % count($staffKeys)]];
            $receiver = (int)$staffIds[$staffKeys[($index + 1) % count($staffKeys)]];
            $timeSent = (new DateTimeImmutable('2025-10-01 09:00:00'))->modify('+' . ($index * 6) . ' days')->format('Y-m-d H:i:s');
            $viewedAt = $timeSent;
            $chatStmt->bind_param('iisss', $sender, $receiver, $message, $timeSent, $viewedAt);
            $chatStmt->execute();
        }
        $chatStmt->close();

        $groupMessages = $this->buildGroupMessages($spec['focus_terms']);
        $groupMsgStmt = $db->prepare("INSERT INTO `{$prefix}chatgroupmessages` (group_id, message, sender_id, time_sent) VALUES (?, ?, ?, ?)");
        foreach ($groupMessages as $index => $message) {
            $sender = (int)$staffIds[$staffKeys[$index % count($staffKeys)]];
            $timeSent = (new DateTimeImmutable('2025-10-05 10:00:00'))->modify('+' . ($index * 11) . ' days')->format('Y-m-d H:i:s');
            $groupMsgStmt->bind_param('isis', $groupId, $message, $sender, $timeSent);
            $groupMsgStmt->execute();
        }
        $groupMsgStmt->close();

        $settingStmt = $db->prepare("INSERT INTO `{$prefix}chatsettings` (user_id, name, value) VALUES (?, ?, ?)");
        foreach ($staffIds as $staffId) {
            $name = 'chat_status';
            $value = 'online';
            $settingStmt->bind_param('iss', $staffId, $name, $value);
            $settingStmt->execute();
        }
        $settingStmt->close();
    }

    private function seedNewsfeed(mysqli $db, string $prefix, array $spec, array $context): void
    {
        if (!$this->tableExists($db, $prefix . 'newsfeed_posts')) {
            return;
        }

        $staffIds = $context['staff_ids'];
        $posts = $this->buildNewsfeedPosts($spec['focus_terms']);
        $postStmt = $db->prepare("INSERT INTO `{$prefix}newsfeed_posts` (creator, datecreated, visibility, content, pinned, datepinned) VALUES (?, ?, 'all', ?, ?, ?)");
        $commentStmt = $db->prepare("INSERT INTO `{$prefix}newsfeed_post_comments` (content, userid, postid, dateadded) VALUES (?, ?, ?, ?)");
        $staffKeys = array_keys($staffIds);
        foreach ($posts as $index => [$content, $comment]) {
            $creator = (int)$staffIds[$staffKeys[$index % count($staffKeys)]];
            $dateCreated = (new DateTimeImmutable('2025-11-01 08:00:00'))->modify('+' . ($index * 28) . ' days')->format('Y-m-d H:i:s');
            $pinned = $index === 0 ? 1 : 0;
            $datePinned = $pinned ? $dateCreated : null;
            $postStmt->bind_param('issis', $creator, $dateCreated, $content, $pinned, $datePinned);
            $postStmt->execute();
            $postId = (int)$db->insert_id;
            $commentUser = (int)$staffIds[$staffKeys[($index + 1) % count($staffKeys)]];
            $commentDate = (new DateTimeImmutable($dateCreated))->modify('+2 hours')->format('Y-m-d H:i:s');
            $commentStmt->bind_param('siis', $comment, $commentUser, $postId, $commentDate);
            $commentStmt->execute();
        }
        $postStmt->close();
        $commentStmt->close();
    }

    private function seedBranchDemoData(mysqli $db, string $prefix, array $spec, array $context): void
    {
        try {
            switch ($spec['family']) {
                case 'appointly':
                    $this->seedAppointlyFamily($db, $prefix, $spec, $context);
                    if ($spec['slug'] === 'gastronomia') {
                        $this->seedCateringFamily($db, $prefix, $spec, $context);
                    }
                    break;
                case 'hotel':
                    $this->seedHotelFamily($db, $prefix, $spec, $context);
                    break;
                case 'workshop':
                    $this->seedWorkshopFamily($db, $prefix, $spec, $context);
                    break;
                case 'realestate':
                    $this->seedRealestateFamily($db, $prefix, $spec, $context);
                    break;
                case 'logistic':
                    $this->seedLogisticFamily($db, $prefix, $context);
                    break;
                case 'academy':
                    $this->seedAcademyFamily($db, $prefix, $spec);
                    break;
                case 'catering':
                    $this->seedCateringFamily($db, $prefix, $spec, $context);
                    break;
                case 'website':
                    $this->seedWebsiteFamily($db, $prefix, $context);
                    break;
                case 'ecommerce':
                    $this->seedEcommerceFamily($db, $prefix);
                    break;
                case 'recruitment':
                    $this->seedRecruitmentFamily($db, $prefix);
                    break;
                case 'landing':
                    $this->seedLandingFamily($db, $prefix, $spec);
                    break;
            }
        } catch (Throwable $e) {
            $this->logFactoryError($spec['slug'], 'seedBranchDemoData:' . $spec['family'], $e);
            throw $e;
        }
    }

    private function seedAppointlyFamily(mysqli $db, string $prefix, array $spec, array $context): void
    {
        $typeIds = [];
        $clientIds = $this->demoClientIds($context);
        foreach ($spec['services'] as $index => $service) {
            $typeIds[$service] = $this->insertSmartRow($db, $prefix . 'appointly_appointment_types', [
                'type' => $service,
                'color' => $spec['service_colors'][$index % count($spec['service_colors'])],
            ]);
        }

        $staffIds = $context['staff_ids'];
        for ($i = 0; $i < 12; $i++) {
            $service = $spec['services'][$i % count($spec['services'])];
            $start = (new DateTimeImmutable('2025-10-01 10:00:00'))->modify('+' . ($i * 14) . ' days');
            $appointmentId = $this->insertSmartRow($db, $prefix . 'appointly_appointments', [
                'subject' => $service . ' - klient ' . ($i + 1),
                'description' => $spec['focus_copy'],
                'rel_type' => 'customer',
                'rel_id' => $clientIds[$i % count($clientIds)],
                'date' => $start->format('Y-m-d'),
                'start_hour' => $start->format('H:i:s'),
                'end_hour' => $start->modify('+60 minutes')->format('H:i:s'),
                'start_time' => $start->format('Y-m-d H:i:s'),
                'end_time' => $start->modify('+60 minutes')->format('Y-m-d H:i:s'),
                'approved' => 1,
                'type' => $typeIds[$service],
                'created_by' => (int)($staffIds['admin@demo.pl'] ?? 1),
                'staff_id' => (int)($staffIds['pracownik@demo.pl'] ?? 2),
            ]);
            $this->insertSmartRow($db, $prefix . 'appointly_attendees', [
                'appointment_id' => $appointmentId,
                'staff_id' => (int)($staffIds['pracownik@demo.pl'] ?? 2),
                'responsible' => 1,
            ]);
        }

        for ($i = 0; $i < 3; $i++) {
            $callbackId = $this->insertSmartRow($db, $prefix . 'appointly_callbacks', [
                'name' => ucfirst($spec['slug']) . ' callback ' . ($i + 1),
                'phonenumber' => '70' . str_pad((string)($i + 1), 7, '0', STR_PAD_LEFT),
                'description' => 'Kontakt po formularzu demo.',
                'date' => (new DateTimeImmutable('2025-11-01'))->modify('+' . ($i * 21) . ' days')->format('Y-m-d'),
                'time' => '12:00:00',
                'responsible' => (int)($staffIds['admin@demo.pl'] ?? 1),
            ]);
            $this->insertSmartRow($db, $prefix . 'appointly_callbacks_assignees', [
                'callback_id' => $callbackId,
                'staff_id' => (int)($staffIds['pracownik@demo.pl'] ?? 2),
            ]);
        }

        if ($this->tableExists($db, $prefix . 'feedback')) {
            foreach (array_slice($clientIds, 0, 3) as $idx => $clientId) {
                $projectId = $context['project_ids'][$idx] ?? 0;
                $this->insertSmartRow($db, $prefix . 'feedback', [
                    'customerid' => $clientId,
                    'projectid' => (string)$projectId,
                    'coding' => '10',
                    'communication' => '10',
                    'services' => '10',
                    'recommendation' => '10',
                    'message' => 'Bardzo dobra organizacja i szybki follow-up.',
                    'feedback_submitted' => 1,
                    'date' => (new DateTimeImmutable('2026-02-15'))->modify('+' . ($idx * 9) . ' days')->format('Y-m-d H:i:s'),
                ]);
            }
        }
    }

    private function seedHotelFamily(mysqli $db, string $prefix, array $spec, array $context): void
    {
        $clientIds = $this->demoClientIds($context);
        $landlordA = $this->insertSmartRow($db, $prefix . 'hms_landlords', ['name' => 'Alicja Maj', 'company' => 'Maj Estates', 'email' => 'alicja@hotel.pl', 'city' => 'Sopot', 'country' => 'Polska', 'datecreated' => '2025-09-01 09:00:00', 'created_by' => 1]);
        $landlordB = $this->insertSmartRow($db, $prefix . 'hms_landlords', ['name' => 'Tomasz Urban', 'company' => 'Urban Stay', 'email' => 'tomasz@hotel.pl', 'city' => 'Zakopane', 'country' => 'Polska', 'datecreated' => '2025-09-04 09:00:00', 'created_by' => 1]);
        $propertyA = $this->insertSmartRow($db, $prefix . 'hms_properties', ['landlord_id' => $landlordA, 'name' => 'FlowQuest Baltic Suites', 'address' => 'ul. Morska 5', 'city' => 'Sopot', 'country' => 'Polska', 'property_type' => 'hotel', 'description' => $spec['focus_copy'], 'status' => 'active', 'datecreated' => '2025-09-10 09:00:00', 'created_by' => 1]);
        $propertyB = $this->insertSmartRow($db, $prefix . 'hms_properties', ['landlord_id' => $landlordB, 'name' => 'FlowQuest Mountain House', 'address' => 'ul. Gorska 9', 'city' => 'Zakopane', 'country' => 'Polska', 'property_type' => 'aparthotel', 'description' => $spec['focus_copy'], 'status' => 'active', 'datecreated' => '2025-09-13 09:00:00', 'created_by' => 1]);
        $roomIds = [];
        foreach ([['Pokoj 101', $propertyA, 420.00], ['Pokoj 102', $propertyA, 650.00], ['Pokoj 201', $propertyB, 390.00], ['Apartament 301', $propertyB, 900.00]] as [$name, $propertyId, $price]) {
            $roomIds[] = $this->insertSmartRow($db, $prefix . 'hms_rooms', ['property_id' => $propertyId, 'name' => $name, 'room_type' => 'double', 'capacity' => 2, 'price_per_night' => $price, 'status' => 'available', 'datecreated' => '2025-09-20 09:00:00', 'created_by' => 1]);
        }
        $serviceIds = [];
        foreach ([['Sniadanie premium', 'food', 55.00], ['Transfer lotniskowy', 'transport', 180.00]] as [$name, $type, $price]) {
            $serviceIds[] = $this->insertSmartRow($db, $prefix . 'hms_services', ['name' => $name, 'service_type' => $type, 'price' => $price, 'status' => 'active', 'datecreated' => '2025-09-24 09:00:00', 'created_by' => 1]);
        }
        for ($i = 0; $i < 6; $i++) {
            $checkIn = (new DateTimeImmutable('2025-10-10'))->modify('+' . ($i * 26) . ' days')->format('Y-m-d');
            $checkOut = (new DateTimeImmutable($checkIn))->modify('+2 days')->format('Y-m-d');
            $bookingId = $this->insertSmartRow($db, $prefix . 'hms_bookings', [
                'room_id' => $roomIds[$i % count($roomIds)],
                'client_id' => $clientIds[$i % count($clientIds)],
                'booking_reference' => strtoupper(substr(md5('hotel' . $i), 0, 10)),
                'guest_name' => 'Gosc hotelowy ' . ($i + 1),
                'guest_email' => 'guest' . ($i + 1) . '@hotel.pl',
                'guest_phone' => '6015000' . $i,
                'check_in_date' => $checkIn,
                'check_out_date' => $checkOut,
                'adults' => 2,
                'children' => $i % 2,
                'total_nights' => 2,
                'room_price' => 500.00,
                'cleaning_fee' => 50.00,
                'additional_services' => 0.00,
                'taxes' => 0.00,
                'total_amount' => 1050.00,
                'payment_status' => 'paid',
                'booking_status' => 'confirmed',
                'datecreated' => $checkIn . ' 09:00:00',
                'created_by' => 1,
            ]);
            $this->insertSmartRow($db, $prefix . 'hms_booking_services', ['booking_id' => $bookingId, 'service_id' => $serviceIds[$i % count($serviceIds)], 'quantity' => 1, 'price' => 50.00]);
        }
        $this->seedCateringFamily($db, $prefix, $spec, $context);
    }

    private function seedWorkshopFamily(mysqli $db, string $prefix, array $spec, array $context): void
    {
        $clientIds = $this->demoClientIds($context);
        $branchId = $this->insertSmartRow($db, $prefix . 'wshop_branches', ['name' => 'Warsztat Glowne', 'email' => 'serwis@warsztat.pl', 'phonenumber' => '600800001', 'city' => 'Warszawa', 'datecreated' => '2025-09-01 09:00:00', 'staffid' => 1]);
        $deviceIds = [];
        foreach ([['BMW 320d 2018', 'BMW320', 'VINBMW001'], ['Audi A4 2019', 'AUDIA4', 'VINAUDI002'], ['Skoda Octavia 2020', 'SKODAOCT', 'VINSKODA003']] as [$name, $code, $serial]) {
            $deviceIds[] = $this->insertSmartRow($db, $prefix . 'wshop_devices', ['name' => $name, 'code' => $code, 'serial_no' => $serial, 'client_id' => $clientIds[(count($deviceIds)) % count($clientIds)], 'status' => 1, 'datecreated' => '2025-09-10 09:00:00', 'staffid' => 1]);
        }
        foreach ($this->buildProjectTitles($spec['focus_terms']) as $i => $title) {
            if ($i >= 5) {
                break;
            }
            $this->insertSmartRow($db, $prefix . 'wshop_repair_jobs', [
                'name' => $title,
                'number' => 1000 + $i,
                'prefix' => 'RJ',
                'client_id' => $clientIds[$i % count($clientIds)],
                'contact_name' => 'Klient warsztatu ' . ($i + 1),
                'contact_email' => 'warsztat' . ($i + 1) . '@demo.pl',
                'contact_phone' => '6021000' . $i,
                'appointment_date' => (new DateTimeImmutable('2025-10-10 08:00:00'))->modify('+' . ($i * 18) . ' days')->format('Y-m-d H:i:s'),
                'estimated_completion_date' => (new DateTimeImmutable('2025-10-12 17:00:00'))->modify('+' . ($i * 18) . ' days')->format('Y-m-d H:i:s'),
                'device_id' => $deviceIds[$i % count($deviceIds)],
                'branch_id' => $branchId,
                'status' => 'in progress',
                'issue_description' => 'Diagnoza i naprawa pojazdu.',
                'job_description' => $spec['focus_copy'],
                'estimated_labour_subtotal' => 650.00,
                'estimated_labour_total' => 650.00,
                'estimated_material_subtotal' => 450.00,
                'estimated_material_total' => 450.00,
                'currency' => 1,
                'subtotal' => 1100.00,
                'total' => 1100.00,
                'datecreated' => (new DateTimeImmutable('2025-10-01 08:00:00'))->modify('+' . ($i * 18) . ' days')->format('Y-m-d H:i:s'),
                'staffid' => 1,
            ]);
        }
        foreach ([['Moto Parts Polska', '602900001'], ['Auto Hurt Premium', '602900002']] as [$company, $phone]) {
            $this->insertSmartRow($db, $prefix . 'pur_vendor', ['company' => $company, 'phonenumber' => $phone, 'city' => 'Warszawa', 'address' => 'ul. Przemyslowa 4']);
        }
    }

    private function seedRealestateFamily(mysqli $db, string $prefix, array $spec, array $context): void
    {
        $clientIds = $this->demoClientIds($context);
        $companyA = $this->insertSmartRow($db, $prefix . 'real_companies', ['name' => 'Urban Estates', 'active' => 1, 'email' => 'office@urban.pl', 'city' => 'Warszawa', 'country' => 0, 'default_currency' => 0, 'created_date' => '2025-09-01 09:00:00', 'staff_id' => 1]);
        $companyB = $this->insertSmartRow($db, $prefix . 'real_companies', ['name' => 'Nova Broker House', 'active' => 1, 'email' => 'hello@nova.pl', 'city' => 'Poznan', 'country' => 0, 'default_currency' => 0, 'created_date' => '2025-09-05 09:00:00', 'staff_id' => 1]);
        for ($i = 0; $i < 5; $i++) {
            $this->insertSmartRow($db, $prefix . 'real_requests', [
                'clientid' => $clientIds[$i % count($clientIds)],
                'item_id' => 1,
                'datecreated' => (new DateTimeImmutable('2025-09-10 10:00:00'))->modify('+' . ($i * 22) . ' days')->format('Y-m-d H:i:s'),
                'date' => (new DateTimeImmutable('2025-09-10'))->modify('+' . ($i * 22) . ' days')->format('Y-m-d'),
                'currency' => 1,
                'property_price' => (float)(350000 + ($i * 180000)),
                'total' => (float)(350000 + ($i * 180000)),
                'contract_total' => (float)(350000 + ($i * 180000)),
                'hash' => md5('real' . $i),
                'discount_type' => 'before_tax',
                'company_id' => $i % 2 === 0 ? $companyA : $companyB,
                'request_type' => $i % 2 === 0 ? 'buy' : 'rent',
                'inspection_date' => (new DateTimeImmutable('2025-09-15'))->modify('+' . ($i * 22) . ' days')->format('Y-m-d'),
                'broker_related_type' => 'staff',
                'broker_related_id' => 1,
                'related_type' => 'staff',
                'related_id' => 1,
            ]);
        }
        if ($this->tableExists($db, $prefix . 'feedback')) {
            foreach (array_slice($clientIds, 0, 3) as $idx => $clientId) {
                $projectId = $context['project_ids'][$idx] ?? 0;
                $this->insertSmartRow($db, $prefix . 'feedback', ['customerid' => $clientId, 'projectid' => (string)$projectId, 'coding' => '10', 'communication' => '10', 'services' => '10', 'recommendation' => '10', 'message' => 'Proces oferty i follow-up wyglada bardzo profesjonalnie.', 'feedback_submitted' => 1, 'date' => (new DateTimeImmutable('2026-02-15'))->modify('+' . ($idx * 9) . ' days')->format('Y-m-d H:i:s')]);
            }
        }
    }

    private function seedLogisticFamily(mysqli $db, string $prefix, array $context): void
    {
        $clientIds = $this->demoClientIds($context);
        for ($i = 0; $i < 3; $i++) {
            $this->insertSmartRow($db, $prefix . 'lg_recipients', ['company' => 'Odbiorca ' . ($i + 1), 'phone_number' => '60410000' . $i, 'email' => 'log' . $i . '@demo.pl', 'address' => 'ul. Logistyczna ' . ($i + 3)]);
        }
        for ($i = 0; $i < 6; $i++) {
            $this->insertSmartRow($db, $prefix . 'lg_shippings', ['shipping_prefix' => 'FQ', 'number' => 100 + $i, 'number_type' => 'auto', 'customer_id' => $clientIds[$i % count($clientIds)], 'recipient_id' => ($i % 3) + 1, 'subtotal' => 450 + ($i * 80), 'total' => 450 + ($i * 80), 'currency' => 1, 'created_at' => (new DateTimeImmutable('2025-10-01 09:00:00'))->modify('+' . ($i * 12) . ' days')->format('Y-m-d H:i:s'), 'created_by' => 1, 'shipping_type' => 'demo', 'approve_status' => 'approved', 'created_from' => 'demo']);
        }
        $this->insertSmartRow($db, $prefix . 'pur_vendor', ['company' => 'Logi Parts Europe', 'phonenumber' => '604900001', 'city' => 'Gdynia', 'address' => 'ul. Portowa 2']);
    }

    private function seedAcademyFamily(mysqli $db, string $prefix, array $spec): void
    {
        $categoryId = $this->insertSmartRow($db, $prefix . 'flexacademy_categories', ['name' => $spec['label'] . ' Masterclass', 'slug' => $spec['slug'] . '-masterclass', 'description' => $spec['focus_copy'], 'created_at' => '2025-09-01 09:00:00']);
        foreach (['Podstawy', 'Growth', 'Premium'] as $index => $level) {
            $courseId = $this->insertSmartRow($db, $prefix . 'flexacademy_courses', ['title' => $spec['label'] . ' ' . $level, 'slug' => $spec['slug'] . '-' . strtolower($level), 'description' => $spec['focus_copy'], 'short_description' => $spec['focus_copy'], 'category_id' => $categoryId, 'creator_id' => 1, 'price' => 199 + ($index * 200), 'pricing_type' => 'paid', 'difficulty_level' => $index === 0 ? 'beginner' : ($index === 1 ? 'intermediate' : 'advanced'), 'status' => 'published', 'language' => 'polish', 'privacy' => 'public', 'access' => 'everyone', 'created_at' => (new DateTimeImmutable('2025-09-01 09:00:00'))->modify('+' . ($index * 31) . ' days')->format('Y-m-d H:i:s')]);
            $sectionId = $this->insertSmartRow($db, $prefix . 'flexacademy_sections', ['course_id' => $courseId, 'title' => 'Modul startowy', 'position' => 1, 'created_at' => '2025-09-02 09:00:00']);
            $this->insertSmartRow($db, $prefix . 'flexacademy_lessons', ['course_id' => $courseId, 'section_id' => $sectionId, 'title' => 'Wprowadzenie', 'slug' => 'wprowadzenie-' . $index, 'content' => $spec['focus_copy'], 'position' => 1, 'status' => 'published', 'created_at' => '2025-09-02 10:00:00']);
        }
        $this->seedLandingFamily($db, $prefix, $spec);
    }

    private function seedCateringFamily(mysqli $db, string $prefix, array $spec, array $context): void
    {
        try {
            $clientIds = $this->demoClientIds($context);
            $this->logFactoryMessage($spec['slug'], 'seedCateringFamily:start', ['prefix' => $prefix]);
            $categoryId = $this->insertSmartRow($db, $prefix . 'catering_menu_categories', [
                'name' => 'Menu glowne',
                'color' => '#5AA9E6',
                'display_order' => 1,
                'active' => 1,
                'created_by' => 1,
            ]);
            $this->logFactoryMessage($spec['slug'], 'seedCateringFamily:category', ['category_id' => $categoryId]);

            $eventTypes = [];
            foreach (['Konferencja', 'Wesele', 'Event firmowy'] as $name) {
                $eventTypes[] = $this->insertSmartRow($db, $prefix . 'catering_event_types', [
                    'name' => $name,
                    'created_by' => 1,
                ]);
            }
            $this->logFactoryMessage($spec['slug'], 'seedCateringFamily:event_types', ['event_type_ids' => $eventTypes]);

            $menuId = $this->insertSmartRow($db, $prefix . 'catering_menus', [
                'menu_name' => 'Menu premium',
                'description' => $spec['focus_copy'],
                'base_price_per_person' => 85.00,
                'active' => 1,
                'created_by' => 1,
            ]);
            $this->logFactoryMessage($spec['slug'], 'seedCateringFamily:menu', ['menu_id' => $menuId]);

            foreach ([['Finger food premium', 18.00, 45.00], ['Bufet eventowy', 22.00, 55.00], ['Kolacja bankietowa', 35.00, 95.00]] as [$name, $cost, $price]) {
                $this->insertSmartRow($db, $prefix . 'catering_menu_items', [
                    'item_name' => $name,
                    'category_id' => $categoryId,
                    'description' => 'Pozycja demo',
                    'unit_cost' => $cost,
                    'unit_price' => $price,
                    'active' => 1,
                    'created_by' => 1,
                ]);
            }
            $this->logFactoryMessage($spec['slug'], 'seedCateringFamily:items', ['count' => 3]);

            for ($i = 0; $i < 3; $i++) {
                $start = (new DateTimeImmutable('2025-11-01 16:00:00'))->modify('+' . ($i * 40) . ' days');
                $eventId = $this->insertSmartRow($db, $prefix . 'catering_events', [
                    'hash' => md5($spec['slug'] . '_event_' . $i),
                    'client_id' => $clientIds[$i % count($clientIds)],
                    'event_name' => ucfirst($spec['slug']) . ' event ' . ($i + 1),
                    'event_type_id' => $eventTypes[$i % count($eventTypes)],
                    'status' => 'confirmed',
                    'venue_name' => 'Sala demo ' . ($i + 1),
                    'venue_address' => 'ul. Eventowa ' . ($i + 5),
                    'event_start' => $start->format('Y-m-d H:i:s'),
                    'event_end' => $start->modify('+4 hours')->format('Y-m-d H:i:s'),
                    'guest_count_expected' => 40 + ($i * 25),
                    'created_by' => 1,
                ]);
                $this->insertSmartRow($db, $prefix . 'catering_event_menu', [
                    'event_id' => $eventId,
                    'menu_id' => $menuId,
                    'pricing_mode' => 'per_person',
                    'price_per_person' => 85.00,
                    'created_by' => 1,
                ]);
            }
            $this->logFactoryMessage($spec['slug'], 'seedCateringFamily:events', ['count' => 3]);
        } catch (Throwable $e) {
            $this->logFactoryError($spec['slug'], 'seedCateringFamily', $e);
            throw $e;
        }
    }

    private function seedWebsiteFamily(mysqli $db, string $prefix, array $context): void
    {
        $clientIds = $this->demoClientIds($context);
        $categoryId = $this->insertSmartRow($db, $prefix . 'wmm_categories', ['name' => 'Opieka premium', 'description' => 'Pakiet utrzymania i backupow.']);
        $this->insertSmartRow($db, $prefix . 'wmm_support_packages', ['name' => 'Pakiet 99', 'price' => 99.00, 'description' => 'Backupy, monitoring i support.', 'category_id' => $categoryId, 'client_id' => $clientIds[0], 'is_active' => 1]);
        for ($i = 0; $i < 4; $i++) {
            $websiteId = $this->insertSmartRow($db, $prefix . 'wmm_websites', ['project_id' => $context['project_ids'][$i % count($context['project_ids'])], 'client_id' => $clientIds[$i % count($clientIds)], 'website_url' => 'https://site' . ($i + 1) . '.demo.pl', 'is_active' => 1, 'added_by' => 1, 'date_added' => (new DateTimeImmutable('2025-10-01'))->modify('+' . ($i * 17) . ' days')->format('Y-m-d H:i:s')]);
            $this->insertSmartRow($db, $prefix . 'wmm_maintenance_tasks', ['website_id' => $websiteId, 'title' => 'Aktualizacja pluginow', 'description' => 'Cotygodniowa opieka.', 'priority' => 'medium', 'status' => 'completed', 'assigned_to' => 1, 'due_date' => date('Y-m-d')]);
        }
    }

    private function seedEcommerceFamily(mysqli $db, string $prefix): void
    {
        $this->insertSmartRow($db, $prefix . 'warehouse', ['warehouse_code' => 'FQ-MAIN', 'warehouse_name' => 'Magazyn glowny', 'warehouse_address' => 'ul. Przemyslowa 10, Warszawa', 'display' => 1]);
        $this->insertSmartRow($db, $prefix . 'acc_accounts', ['name' => 'Sprzedaz online', 'key_name' => 'sprzedaz_online', 'number' => '700-01', 'account_type_id' => 1, 'account_detail_type_id' => 1, 'balance' => 28500.00, 'balance_as_of' => date('Y-m-d'), 'default_account' => 1, 'active' => 1]);
        $this->insertSmartRow($db, $prefix . 'omni_master_channel_woocommere', ['name_channel' => 'Sklep Woo Demo', 'consumer_key' => 'ck_demo_key', 'consumer_secret' => 'cs_demo_secret', 'url' => 'https://shop.flowquest.pl']);
    }

    private function seedRecruitmentFamily(mysqli $db, string $prefix): void
    {
        foreach ([[1, 'HR-2025-001'], [2, 'HR-2025-002'], [3, 'HR-2025-003']] as [$staffId, $code]) {
            $this->insertSmartRow($db, $prefix . 'hr_staff_contract', ['contract_code' => $code, 'name_contract' => 1, 'staff' => $staffId, 'start_valid' => '2025-09-01', 'end_valid' => '2026-08-31', 'contract_status' => 'active', 'sign_day' => '2025-09-01', 'hourly_or_month' => 'month']);
        }
        foreach ([[1, '2026-03-02', '08:00'], [2, '2026-03-03', '07:45'], [3, '2026-03-04', '08:10'], [1, '2026-03-05', '08:00'], [2, '2026-03-06', '07:30'], [3, '2026-03-09', '08:15']] as [$staffId, $date, $value]) {
            $this->insertSmartRow($db, $prefix . 'timesheets_timesheet', ['staff_id' => $staffId, 'date_work' => $date, 'value' => $value, 'type' => 'work', 'add_from' => 1, 'latch' => 1]);
        }
    }

    private function seedLandingFamily(mysqli $db, string $prefix, array $spec): void
    {
        $this->insertSmartRow($db, $prefix . 'landing_pages', ['name' => $spec['label'] . ' landing demo', 'slug' => $spec['demo_slug'] . '-landing', 'title' => $spec['label'] . ' FlowQuest', 'description' => $spec['focus_copy'], 'status' => 1, 'created_at' => date('Y-m-d H:i:s')]);
    }

    private function configureModuleOptions(mysqli $db, string $prefix, array $spec, array $context): void
    {
        if (!in_array('appointly', $spec['modules'], true) || !$this->tableExists($db, $prefix . 'options')) {
            return;
        }

        $responsible = (int)($context['staff_ids']['admin@demo.pl'] ?? 1);
        $this->upsertOption($db, $prefix, 'appointly_responsible_person', (string)$responsible);
        $this->upsertOption($db, $prefix, 'callbacks_responsible_person', (string)$responsible);
        $this->upsertOption($db, $prefix, 'appointly_busy_times_enabled', '0');
        $this->upsertOption($db, $prefix, 'appointly_available_hours', json_encode(['09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00']));
    }

    private function buildClients(array $spec): array
    {
        return [
            [$spec['company'], $spec['city'], $spec['state'], $spec['address'], '790100001', 'kontakt@' . $spec['slug'] . '.pl', 'Anna', 'Dabrowska'],
            ['Nova ' . $spec['label'], 'Krakow', 'Malopolskie', 'ul. Szafirowa 7', '790100002', 'iza@' . $spec['slug'] . '.pl', 'Izabela', 'Wojcik'],
            ['Prime ' . $spec['label'], 'Wroclaw', 'Dolnoslaskie', 'ul. Perlista 4', '790100003', 'ola@' . $spec['slug'] . '.pl', 'Ola', 'Jankowska'],
            ['Studio ' . $spec['label'], 'Gdansk', 'Pomorskie', 'ul. Morska 18', '790100004', 'monika@' . $spec['slug'] . '.pl', 'Monika', 'Lis'],
            ['Flow ' . $spec['label'], 'Poznan', 'Wielkopolskie', 'ul. Rozana 22', '790100005', 'martyna@' . $spec['slug'] . '.pl', 'Martyna', 'Krol'],
            ['Client Hub', 'Lodz', 'Lodzkie', 'ul. Piotrkowska 9', '790100006', 'biuro@' . $spec['slug'] . '.pl', 'Marta', 'Bialek'],
            ['Premium House', 'Sopot', 'Pomorskie', 'ul. Bohaterow 5', '790100007', 'kontakt2@' . $spec['slug'] . '.pl', 'Katarzyna', 'Urban'],
            ['Growth Partner', 'Katowice', 'Slaskie', 'ul. Zielona 3', '790100008', 'hello@' . $spec['slug'] . '.pl', 'Pawel', 'Rataj'],
        ];
    }

    private function buildLeadTitles(array $terms): array
    {
        return [
            $terms[0] . ' premium',
            'Konsultacja ' . $terms[1],
            'Pakiet ' . $terms[2],
            'Follow-up po ' . $terms[3],
            'Nowy lead z landing page',
            'Rozszerzenie uslugi ' . $terms[0],
            'Pakiet zespolowy ' . $terms[1],
            'Abonament ' . $terms[2],
            'Ofertowanie ' . $terms[3],
            'Wdrozenie ' . $terms[0],
        ];
    }

    private function buildProjectTitles(array $terms): array
    {
        return [
            'Projekt ' . $terms[0],
            'Pakiet ' . $terms[1],
            'Onboarding ' . $terms[2],
            'Follow-up ' . $terms[3],
            'Nowy proces ' . $terms[0],
            'Oferta premium ' . $terms[1],
            'Rozwoj uslugi ' . $terms[2],
            'Audyt ' . $terms[3],
            'Akcja sezonowa ' . $terms[0],
            'Retainer ' . $terms[1],
        ];
    }

    private function buildTaskTitles(array $terms): array
    {
        return [
            'Potwierdz ' . $terms[0],
            'Wyslij follow-up po ' . $terms[1],
            'Dodaj notatke po ' . $terms[2],
            'Sprawdz status ' . $terms[3],
            'Przypisz odpowiedzialna osobe',
            'Przygotuj oferte ' . $terms[0],
            'Dopnij harmonogram ' . $terms[1],
            'Skonsultuj ' . $terms[2],
            'Zamknij etap ' . $terms[3],
            'Wyslij prosbe o opinie',
            'Aktualizacja danych klienta',
            'Kontrola platnosci',
            'Dopisac komentarz do projektu',
            'Przypomnienie po 48h',
            'Raport tygodniowy',
        ];
    }

    private function buildComments(array $terms): array
    {
        return [
            'Potwierdzono zakres prac dla ' . $terms[0] . '.',
            'Klient pozytywnie reaguje na follow-up i prosi o dalsze kroki.',
            'Zadanie przesuniete po konsultacji zespolu.',
            'Dopisano uwagi po rozmowie dotyczacej ' . $terms[1] . '.',
            'Wrzucilem notatke i przypisalem odpowiedzialnego za ' . $terms[2] . '.',
            'Klient chce przyspieszyc wdrozenie ' . $terms[3] . '.',
            'Status zaktualizowany po zamknieciu rozmowy handlowej.',
            'Wyslano kolejny follow-up i propozycje rozszerzenia pakietu.',
            'Projekt wyglada stabilnie, mozna ruszac dalej.',
            'Dopisano komentarz po spotkaniu z klientem VIP.',
            'Zadanie ma komplet danych wejsciowych.',
            'Potwierdzono harmonogram i zasoby na najblizszy tydzien.',
            'Komentarz dodany po analizie rentownosci.',
            'Wlasciciel poprosil o dodatkowe podsumowanie.',
            'Wrzucilem uwagi po kontakcie telefonicznym.',
            'Priorytet podniesiony po nowym feedbacku klienta.',
            'Dopisano checklisty i przypomnienia.',
            'Po rozmowie klient potrzebuje jeszcze jednego calla.',
            'Projekt zostaje jako referencyjny case demo.',
            'Domykamy etap i przechodzimy dalej.',
        ];
    }

    private function buildChatMessages(array $terms): array
    {
        return [
            'Sprawdz prosze leady wokol ' . $terms[0] . ' i ustaw follow-up na jutro.',
            'Jasne, dwa kontakty sa juz po rozmowie i czekaja na oferte.',
            'Wrzucilem aktualizacje do projektu zwiazanego z ' . $terms[1] . '.',
            'Klient pyta, czy mozemy przyspieszyc wdrozenie ' . $terms[2] . '.',
            'Dodaj prosze notatke po rozmowie i przypnij kolejne zadania.',
            'Nowy lead wszedl z landing page i wyglada obiecujaco.',
            'Zaktualizowalam dane klienta i przypisalam go do grupy VIP.',
            'W kalendarzu zwolnilo sie okno na dodatkowe spotkanie.',
            'Dodalem komentarze do taskow i rozpisalem kolejne kroki.',
            'Na koniec dnia zrobmy krotkie podsumowanie ' . $terms[3] . '.',
        ];
    }

    private function buildGroupMessages(array $terms): array
    {
        return [
            'Dzisiaj priorytetem jest szybki follow-up i porzadek wokol ' . $terms[0] . '.',
            'Pamietajcie o aktualizacji komentarzy po kazdym kontakcie z klientem.',
            'Jutro startujemy z nowa akcja sprzedazowa zwiazana z ' . $terms[1] . '.',
            'Dwa leady premium potrzebuja dzisiaj szybkiej odpowiedzi.',
            'Wrzucilem nowy status i plan na kolejny tydzien.',
            'Pilnujemy komunikacji i domykamy zadania zwiazane z ' . $terms[2] . '.',
            'Dzieki za szybkie domkniecie watku, klient jest zadowolony.',
            'Kolejny etap gotowy, mozna przejsc do wdrozenia ' . $terms[3] . '.',
            'Wszystkie taski na dzisiaj sa juz przypisane.',
            'Na wieczor potrzebuje jeszcze raportu z pipeline.',
        ];
    }

    private function buildNewsfeedPosts(array $terms): array
    {
        return [
            ['Startujemy tydzien z mocnym follow-upem i aktualizacja ofert wokol ' . $terms[0] . '.', 'Zespol ma juz rozpisane zadania i priorytety.'],
            ['Na jutro przygotowujemy nowe materialy i onboarding dla ' . $terms[1] . '.', 'Dodalem komentarze i checkliste dla zespolu.'],
        ];
    }

    private function branchOrder(): array
    {
        return ['beauty', 'hotel', 'warsztat', 'nieruchomosci', 'logistyka', 'kursy', 'gastronomia', 'serwiswww', 'ecommerce', 'oze', 'agencja', 'rekrutacja', 'medycyna', 'eventy'];
    }

    private function getBranchSpec(string $branch): array
    {
        $specs = $this->branchSpecs();
        if (!isset($specs[$branch])) {
            throw new RuntimeException('Nieznany branch: ' . $branch);
        }
        return $specs[$branch];
    }

    private function branchSpecs(): array
    {
        $base = function (string $slug, string $label, array $modules, string $family, array $terms, float $priceFactor, array $validation, array $cleanup, array $templateRows = [], array $services = []) {
            return [
                'slug' => $slug,
                'label' => $label,
                'family' => $family,
                'template_slug' => $slug . 'template',
                'demo_slug' => $slug,
                'template_db' => 'ps_' . $slug . 'template',
                'demo_db' => 'ps_' . $slug,
                'template_prefix' => $slug . 'template_tbl',
                'demo_prefix' => $slug . '_tbl',
                'template_name' => 'FlowQuest ' . $label . ' Template',
                'demo_name' => 'FlowQuest ' . $label . ' Demo',
                'company' => 'FlowQuest ' . $label,
                'city' => 'Warszawa',
                'state' => 'Mazowieckie',
                'address' => 'ul. FlowQuest 10',
                'contact_email' => strtolower($slug) . '@flowquest.pl',
                'modules' => array_values(array_unique(array_merge(['einvoice', 'form_sync', 'menu_setup', 'prchat', 'theme_style'], $modules))),
                'focus_terms' => $terms,
                'focus_copy' => 'To demo pokazuje, jak ' . strtolower($label) . ' pracuje na klientach, leadach, zadaniach i komunikacji w jednym CRM.',
                'price_factor' => $priceFactor,
                'lead_sources' => ['Instagram', 'Facebook Ads', 'Polecenie', 'Landing Page'],
                'lead_statuses' => [
                    ['Nowy kontakt', '#f59e0b', 1, 1],
                    ['Rozmowa w toku', '#3b82f6', 2, 0],
                    ['Do follow-upu', '#8b5cf6', 3, 0],
                    ['Wygrany', '#10b981', 4, 0],
                ],
                'customer_groups' => ['VIP', 'Nowi klienci', 'Abonament', 'Polecenie'],
                'validation_tables' => $validation,
                'cleanup_tables' => $cleanup,
                'template_rows' => $templateRows,
                'services' => $services,
                'service_colors' => ['#D977A8', '#7BC4C4', '#B58C5A', '#8F7AE5', '#F4A261', '#5AA9E6'],
            ];
        };

        return [
            'beauty' => $base('beauty', 'Beauty', ['appointly', 'feedback', 'zillapage'], 'appointly', ['zabiegi', 'wizyty', 'klientki', 'follow-up'], 350.0, ['appointments' => 'appointly_appointments', 'feedback' => 'feedback'], ['appointly_appointments', 'appointly_attendees', 'appointly_callbacks', 'appointly_callbacks_assignees', 'feedback', 'landing_pages', 'landing_page_settings', 'landing_page_form_data', 'landing_page_blocks'], [], ['Paznokcie hybrydowe', 'Oczyszczanie twarzy', 'Stylizacja brwi', 'Lifting rzes', 'Depilacja laserowa']),
            'hotel' => $base('hotel', 'Hotel', ['hotel_management_system', 'catering_management_module'], 'hotel', ['pokoje', 'rezerwacje', 'goscie', 'eventy'], 1200.0, ['bookings' => 'hms_bookings', 'rooms' => 'hms_rooms', 'events' => 'catering_events'], ['hms_bookings', 'hms_booking_services', 'hms_rooms', 'hms_properties', 'hms_landlords', 'hms_services', 'catering_events', 'catering_event_menu', 'catering_menu_items', 'catering_menus', 'catering_event_types']),
            'warsztat' => $base('warsztat', 'Warsztat', ['workshop', 'purchase'], 'workshop', ['zlecenia', 'pojazdy', 'czesci', 'serwis'], 900.0, ['repair_jobs' => 'wshop_repair_jobs', 'devices' => 'wshop_devices', 'vendors' => 'pur_vendor'], ['wshop_repair_jobs', 'wshop_devices', 'wshop_branches', 'pur_vendor']),
            'nieruchomosci' => $base('nieruchomosci', 'Nieruchomosci', ['realestate', 'feedback'], 'realestate', ['oferty', 'agenci', 'prezentacje', 'lead'], 180000.0, ['requests' => 'real_requests', 'companies' => 'real_companies', 'feedback' => 'feedback'], ['real_requests', 'real_companies', 'feedback']),
            'logistyka' => $base('logistyka', 'Logistyka', ['logistic', 'purchase'], 'logistic', ['paczki', 'wysylki', 'odbiorcy', 'alerty'], 750.0, ['shippings' => 'lg_shippings', 'recipients' => 'lg_recipients', 'vendors' => 'pur_vendor'], ['lg_shippings', 'lg_recipients', 'pur_vendor']),
            'kursy' => $base('kursy', 'Kursy', ['flexacademy', 'zillapage'], 'academy', ['kursy', 'uczestnicy', 'landing', 'sprzedaz'], 420.0, ['courses' => 'flexacademy_courses', 'sections' => 'flexacademy_sections', 'landing_pages' => 'landing_pages'], ['flexacademy_courses', 'flexacademy_categories', 'flexacademy_sections', 'flexacademy_lessons', 'landing_pages', 'landing_page_settings', 'landing_page_form_data', 'landing_page_blocks']),
            'gastronomia' => $base('gastronomia', 'Gastronomia', ['catering_management_module', 'appointly', 'feedback'], 'appointly', ['eventy', 'menu', 'degustacje', 'rezerwacje'], 950.0, ['events' => 'catering_events', 'appointments' => 'appointly_appointments', 'feedback' => 'feedback'], ['catering_events', 'catering_event_menu', 'catering_menu_items', 'catering_menus', 'catering_event_types', 'appointly_appointments', 'appointly_attendees', 'appointly_callbacks', 'appointly_callbacks_assignees', 'feedback'], [], ['Degustacja menu', 'Spotkanie eventowe']),
            'serwiswww' => $base('serwiswww', 'Serwis WWW', ['website_maintenance_management'], 'website', ['strony', 'taski sla', 'opieka', 'support'], 260.0, ['websites' => 'wmm_websites', 'tasks_wmm' => 'wmm_maintenance_tasks'], ['wmm_categories', 'wmm_support_packages', 'wmm_websites', 'wmm_maintenance_tasks', 'wmm_maintenance_logs']),
            'ecommerce' => $base('ecommerce', 'Ecommerce', ['omni_sales', 'warehouse', 'accounting'], 'ecommerce', ['zamowienia', 'magazyn', 'ksiegowosc', 'sync'], 1100.0, ['warehouse' => 'warehouse', 'acc_accounts' => 'acc_accounts', 'omni' => 'omni_master_channel_woocommere'], ['warehouse', 'acc_accounts', 'omni_master_channel_woocommere']),
            'oze' => $base('oze', 'OZE', ['zillapage'], 'landing', ['instalacje', 'wyceny', 'wdrozenia', 'lead'], 24000.0, ['landing_pages' => 'landing_pages'], ['landing_pages', 'landing_page_settings', 'landing_page_form_data', 'landing_page_blocks']),
            'agencja' => $base('agencja', 'Agencja', ['zillapage'], 'landing', ['kampanie', 'content', 'landing', 'retainer'], 1200.0, ['landing_pages' => 'landing_pages'], ['landing_pages', 'landing_page_settings', 'landing_page_form_data', 'landing_page_blocks']),
            'rekrutacja' => $base('rekrutacja', 'Rekrutacja', ['hr_profile', 'timesheets'], 'recruitment', ['kandydaci', 'onboarding', 'hr', 'czas pracy'], 800.0, ['contracts' => 'hr_staff_contract', 'timesheets' => 'timesheets_timesheet'], ['hr_staff_contract', 'timesheets_timesheet']),
            'medycyna' => $base('medycyna', 'Medycyna', ['appointly', 'feedback'], 'appointly', ['wizyty', 'pacjenci', 'kontrole', 'follow-up'], 260.0, ['appointments' => 'appointly_appointments', 'feedback' => 'feedback'], ['appointly_appointments', 'appointly_attendees', 'appointly_callbacks', 'appointly_callbacks_assignees', 'feedback'], [], ['Konsultacja', 'Kontrola', 'Badanie']),
            'eventy' => $base('eventy', 'Eventy', ['catering_management_module'], 'catering', ['wydarzenia', 'harmonogram', 'gala', 'obsluga'], 3200.0, ['events' => 'catering_events'], ['catering_events', 'catering_event_menu', 'catering_menu_items', 'catering_menus', 'catering_event_types']),
        ];
    }

    private function baseTenantModules(): array
    {
        return ['einvoice', 'form_sync', 'menu_setup', 'prchat', 'theme_style'];
    }

    private function moduleTablePatterns(): array
    {
        return [
            'prchat' => ['tblchat%'],
            'feedback' => ['tblfeedback'],
            'zillapage' => ['tbllanding_page_%', 'tbllanding_pages'],
            'appointly' => ['tblappointly_%'],
            'hotel_management_system' => ['tblhms_%'],
            'catering_management_module' => ['tblcatering_%'],
            'workshop' => ['tblwshop_%'],
            'purchase' => ['tblpur_%', 'tblpurchase_option'],
            'realestate' => ['tblreal_%'],
            'logistic' => ['tbllg_%'],
            'flexacademy' => ['tblflexacademy_%'],
            'website_maintenance_management' => ['tblwmm_%'],
            'omni_sales' => ['tblomni%'],
            'warehouse' => ['tblwarehouse', 'tblwh_%'],
            'accounting' => ['tblacc_%'],
            'hr_profile' => ['tblhr_%'],
            'timesheets' => ['tbltimesheets_%'],
        ];
    }

    private function buildBranchMetadata(string $baseMetadataJson, array $spec, bool $isDemo): string
    {
        $meta = json_decode($baseMetadataJson, true) ?: [];
        $meta['admin_approved_modules'] = array_values(array_unique(array_merge([''], $this->baseTenantModules(), $spec['modules'])));
        $meta['admin_disabled_modules'] = [''];
        $meta['admin_disabled_default_modules'] = ['estimate_request', 'expenses', 'knowledge_base', 'credit_notes', 'subscriptions'];
        $meta['login_panel'] = [
            'kicker' => $isDemo ? $spec['demo_name'] : $spec['template_name'],
            'title' => $isDemo ? 'Zobacz, jak pracuje ' . $spec['label'] : 'Szablon branzowy ' . $spec['label'],
            'copy' => $isDemo ? $spec['focus_copy'] : 'To czysty szablon do klonowania klientom bez danych operacyjnych.',
            'admin_note' => 'Wlasciciel i Pracownik otwieraja panel administracyjny.',
            'client_note' => 'Klient otwiera portal klienta i widzi dokumenty oraz komunikacje.',
            'accounts' => [
                'owner' => ['target' => 'admin', 'label' => 'Wlasciciel', 'email' => 'admin@demo.pl', 'password' => self::DEMO_PASSWORD, 'submit' => 'Zaloguj jako wlasciciel', 'hint' => 'Pelny dostep do firmy i marketplace', 'copy' => 'Wlasciciel widzi kalendarz, leady, finanse, projekty i dodatki.'],
                'employee' => ['target' => 'admin', 'label' => 'Pracownik', 'email' => 'pracownik@demo.pl', 'password' => self::DEMO_PASSWORD, 'submit' => 'Zaloguj jako pracownik', 'hint' => 'Obsluga codziennej pracy zespolu', 'copy' => 'Pracownik widzi leady, klientow, zadania, komentarze i operacje dnia codziennego.'],
                'client' => ['target' => 'client', 'label' => 'Klient', 'email' => 'klient@demo.pl', 'password' => self::DEMO_PASSWORD, 'submit' => 'Zaloguj jako klient', 'hint' => 'Portal klienta', 'copy' => 'Klient widzi swoje dokumenty, terminy, notatki i komunikacje z firma.'],
            ],
        ];
        return json_encode($meta, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    private function createModuleTablesForModules(string $targetDb, string $targetPrefix, array $modules): void
    {
        $patterns = $this->moduleTablePatterns();
        $tables = [];
        foreach (array_values(array_unique(array_merge($this->baseTenantModules(), $modules))) as $module) {
            if (!isset($patterns[$module])) {
                continue;
            }
            foreach ($patterns[$module] as $pattern) {
                foreach ($this->fetchTablesLike(self::MASTER_DB, $pattern) as $table) {
                    $tables[$table] = true;
                }
            }
        }

        foreach (array_keys($tables) as $table) {
            $replacement = $targetPrefix . '$1';
            $pipeline = sprintf(
                "mysqldump --skip-lock-tables --single-transaction --no-tablespaces --no-data %s %s | perl -0pe 's/`tbl([A-Za-z0-9_]+)`/`%s`/g' | mysql %s",
                self::MASTER_DB,
                $table,
                $replacement,
                $targetDb
            );
            $this->run('bash -lc ' . escapeshellarg($pipeline));
        }
    }

    private function fetchTablesLike(string $database, string $pattern): array
    {
        $db = $this->connect($database);
        $result = $db->query("SHOW TABLES LIKE '" . $db->real_escape_string($pattern) . "'");
        $tables = [];
        while ($row = $result->fetch_row()) {
            $tables[] = $row[0];
        }
        $result->close();
        $db->close();
        return $tables;
    }

    private function repairPrefixedForeignKeys(string $database, string $prefix): void
    {
        $db = $this->connect($database);
        $sql = "SELECT k.TABLE_NAME, k.COLUMN_NAME, k.CONSTRAINT_NAME, k.REFERENCED_TABLE_NAME, k.REFERENCED_COLUMN_NAME, rc.UPDATE_RULE, rc.DELETE_RULE
                FROM information_schema.KEY_COLUMN_USAGE k
                JOIN information_schema.REFERENTIAL_CONSTRAINTS rc
                  ON rc.CONSTRAINT_SCHEMA = k.TABLE_SCHEMA
                 AND rc.TABLE_NAME = k.TABLE_NAME
                 AND rc.CONSTRAINT_NAME = k.CONSTRAINT_NAME
               WHERE k.TABLE_SCHEMA = '" . $db->real_escape_string($database) . "'
                 AND k.REFERENCED_TABLE_NAME IS NOT NULL
                 AND k.TABLE_NAME LIKE '" . $db->real_escape_string($prefix) . "%'
                 AND k.REFERENCED_TABLE_NAME LIKE 'tbl%'";
        $result = $db->query($sql);
        while ($row = $result->fetch_assoc()) {
            $targetTable = $prefix . substr($row['REFERENCED_TABLE_NAME'], 3);
            if (!$this->tableExists($db, $targetTable)) {
                continue;
            }
            $constraintName = $row['CONSTRAINT_NAME'];
            $tableName = $row['TABLE_NAME'];
            $columnName = $row['COLUMN_NAME'];
            $referencedColumn = $row['REFERENCED_COLUMN_NAME'];
            $updateRule = $row['UPDATE_RULE'];
            $deleteRule = $row['DELETE_RULE'];
            $newConstraint = substr('fk_' . $tableName . '_' . $columnName, 0, 60);
            $db->query("ALTER TABLE `{$tableName}` DROP FOREIGN KEY `{$constraintName}`");
            $db->query("ALTER TABLE `{$tableName}` ADD CONSTRAINT `{$newConstraint}` FOREIGN KEY (`{$columnName}`) REFERENCES `{$targetTable}` (`{$referencedColumn}`) ON UPDATE {$updateRule} ON DELETE {$deleteRule}");
        }
        $result->close();
        $db->close();
    }

    private function mapIdByKey(mysqli $db, string $sql): array
    {
        $result = $db->query($sql);
        $mapped = [];
        while ($row = $result->fetch_row()) {
            $mapped[$row[1]] = (int)$row[0];
        }
        $result->close();
        return $mapped;
    }

    private function upsertOption(mysqli $db, string $prefix, string $name, string $value): void
    {
        $existing = $this->fetchRow($db, "SELECT name FROM `{$prefix}options` WHERE name = '" . $db->real_escape_string($name) . "' LIMIT 1");
        if ($existing) {
            $stmt = $db->prepare("UPDATE `{$prefix}options` SET value = ? WHERE name = ?");
            $stmt->bind_param('ss', $value, $name);
            $stmt->execute();
            $stmt->close();
            return;
        }
        $stmt = $db->prepare("INSERT INTO `{$prefix}options` (name, value, autoload) VALUES (?, ?, 1)");
        $stmt->bind_param('ss', $name, $value);
        $stmt->execute();
        $stmt->close();
    }

    private function safeCount(mysqli $db, string $table): int
    {
        return $this->tableExists($db, $table) ? (int)$this->fetchValue($db, "SELECT COUNT(*) FROM `{$table}`") : 0;
    }

    private function demoClientIds(array $context): array
    {
        $clientIds = array_values(array_filter(array_map('intval', $context['client_ids'] ?? [])));
        return !empty($clientIds) ? $clientIds : [1];
    }

    private function curlStatus(string $url): int
    {
        $output = [];
        $exitCode = 0;
        exec('curl -k -L -o /dev/null -s -w "%{http_code}" ' . escapeshellarg($url) . ' 2>&1', $output, $exitCode);
        return $exitCode === 0 ? (int)implode('', $output) : 0;
    }

    private function truncateIfExists(mysqli $db, string $table): void
    {
        if ($this->tableExists($db, $table)) {
            $this->runSql($db, "DELETE FROM `{$table}`");
        }
    }

    private function tableExists(mysqli $db, string $table): bool
    {
        $result = $db->query("SHOW TABLES LIKE '" . $db->real_escape_string($table) . "'");
        $exists = $result->num_rows > 0;
        $result->close();
        return $exists;
    }

    private function insertSmartRow(mysqli $db, string $table, array $data): int
    {
        if (!$this->tableExists($db, $table)) {
            return 0;
        }
        $schema = $this->describeTable($db, $table);
        $fields = [];
        $values = [];
        $types = '';
        foreach ($schema as $column) {
            $field = $column['Field'];
            if (strpos((string)$column['Extra'], 'auto_increment') !== false) {
                continue;
            }
            if (array_key_exists($field, $data)) {
                $value = $this->normalizeValueForColumn($data[$field], $column);
            } elseif ($column['Null'] === 'NO' && $column['Default'] === null) {
                $value = $this->normalizeValueForColumn($this->fallbackValueForColumn($column), $column);
            } else {
                continue;
            }
            $fields[] = "`{$field}`";
            $values[] = $value;
            $types .= $this->bindTypeForValue($value);
        }
        if (empty($fields)) {
            return 0;
        }
        $sql = "INSERT INTO `{$table}` (" . implode(',', $fields) . ") VALUES (" . implode(',', array_fill(0, count($fields), '?')) . ")";
        $stmt = $db->prepare($sql);
        $stmt->bind_param($types, ...$values);
        $stmt->execute();
        $insertId = (int)$db->insert_id;
        $stmt->close();
        return $insertId;
    }

    private function describeTable(mysqli $db, string $table): array
    {
        if (isset($this->schemaCache[$table])) {
            return $this->schemaCache[$table];
        }
        $result = $db->query("DESCRIBE `{$table}`");
        $schema = [];
        while ($row = $result->fetch_assoc()) {
            $schema[] = $row;
        }
        $result->close();
        $this->schemaCache[$table] = $schema;
        return $schema;
    }

    private function fallbackValueForColumn(array $column)
    {
        $type = strtolower((string)$column['Type']);
        if (strpos($type, 'int') !== false || strpos($type, 'decimal') !== false || strpos($type, 'float') !== false || strpos($type, 'double') !== false) {
            return 0;
        }
        if (strpos($type, 'date') !== false && strpos($type, 'datetime') === false) {
            return date('Y-m-d');
        }
        if (strpos($type, 'datetime') !== false || strpos($type, 'timestamp') !== false) {
            return date('Y-m-d H:i:s');
        }
        if (strpos($type, 'time') !== false) {
            return date('H:i:s');
        }
        if (strpos($type, 'enum') !== false && preg_match("/enum\\('([^']+)'/", $type, $m)) {
            return $m[1];
        }
        return '';
    }

    private function normalizeValueForColumn($value, array $column)
    {
        if ($value === null) {
            return $value;
        }

        $type = strtolower((string)$column['Type']);
        if (is_string($value) && preg_match('/^(var)?char\((\d+)\)$/', $type, $matches)) {
            $limit = (int)$matches[2];
            if ($limit > 0 && mb_strlen($value, 'UTF-8') > $limit) {
                return mb_substr($value, 0, $limit, 'UTF-8');
            }
        }

        return $value;
    }

    private function bindTypeForValue($value): string
    {
        if (is_int($value)) {
            return 'i';
        }
        if (is_float($value)) {
            return 'd';
        }
        return 's';
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
        $appUser = defined('APP_DB_USERNAME_DEFAULT') ? APP_DB_USERNAME_DEFAULT : null;
        if (!empty($appUser)) {
            $safeUser = str_replace("'", "\\'", $appUser);
            $safeDb = str_replace('`', '``', $targetDb);
            $this->run('mysql -e ' . escapeshellarg("GRANT ALL PRIVILEGES ON `{$safeDb}`.* TO '{$safeUser}'@'localhost'"));
            $this->run('mysql -e ' . escapeshellarg("FLUSH PRIVILEGES"));
        }
        $pipeline = sprintf("mysqldump --skip-lock-tables --single-transaction --no-tablespaces %s | perl -0pe 's/%s/%s/g' | mysql %s", $sourceDb, $sourcePrefix, $targetPrefix, $targetDb);
        $this->run('bash -lc ' . escapeshellarg($pipeline));
    }

    private function ensureTenantDbUser(string $slug, string $dbName): array
    {
        $username = substr('ps_' . preg_replace('/[^a-z0-9_]/', '', strtolower($slug)), 0, 24);
        $masterDb = $this->connect(self::MASTER_DB);
        $password = $this->fetchValue($masterDb, "SELECT JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.db_password')) FROM tblperfex_saas_companies WHERE slug = '" . $masterDb->real_escape_string($slug) . "' LIMIT 1");
        $masterDb->close();

        $userExistsCmd = sprintf(
            "mysql -Nse %s",
            escapeshellarg("SELECT COUNT(*) FROM mysql.user WHERE user = '{$username}' AND host = 'localhost'")
        );
        $output = [];
        $exitCode = 0;
        exec($userExistsCmd . ' 2>&1', $output, $exitCode);
        if ($exitCode !== 0) {
            throw new RuntimeException("Nie udalo sie sprawdzic uzytkownika MySQL {$username}: " . implode("\n", $output));
        }

        $exists = (int)trim(implode('', $output)) > 0;
        if (empty($password)) {
            $password = $this->randomPassword();
        }

        $safeUser = str_replace("'", "\\'", $username);
        $safePassword = str_replace("'", "\\'", $password);
        $safeDbName = str_replace('`', '``', $dbName);

        if ($exists) {
            $this->run('mysql -e ' . escapeshellarg("ALTER USER '{$safeUser}'@'localhost' IDENTIFIED BY '{$safePassword}'"));
        } else {
            $this->run('mysql -e ' . escapeshellarg("CREATE USER '{$safeUser}'@'localhost' IDENTIFIED BY '{$safePassword}'"));
        }

        $this->run('mysql -e ' . escapeshellarg("GRANT ALL PRIVILEGES ON `{$safeDbName}`.* TO '{$safeUser}'@'localhost'"));
        $this->run('mysql -e ' . escapeshellarg("FLUSH PRIVILEGES"));
        return ['user' => $username, 'password' => $password];
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

    private function logFactoryMessage(string $slug, string $stage, array $context = []): void
    {
        $payload = [
            'ts' => date('c'),
            'slug' => $slug,
            'stage' => $stage,
            'context' => $context,
        ];
        file_put_contents('/tmp/flowquest_demo_factory.log', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL, FILE_APPEND);
    }

    private function logFactoryError(string $slug, string $stage, Throwable $e): void
    {
        $payload = [
            'ts' => date('c'),
            'slug' => $slug,
            'stage' => $stage,
            'error' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ];
        file_put_contents('/tmp/flowquest_demo_factory.log', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL, FILE_APPEND);
    }

    private function attachTag(mysqli $db, string $prefix, int $relId, string $relType, int $tagId): void
    {
        if (!$this->tableExists($db, $prefix . 'taggables')) {
            return;
        }
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
        $user = defined('APP_DB_USERNAME_DEFAULT') ? APP_DB_USERNAME_DEFAULT : 'root';
        $password = defined('APP_DB_PASSWORD_DEFAULT') ? APP_DB_PASSWORD_DEFAULT : '';
        $host = defined('APP_DB_HOSTNAME_DEFAULT') ? APP_DB_HOSTNAME_DEFAULT : 'localhost';
        $db = new mysqli($host, $user, $password, $database);
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
