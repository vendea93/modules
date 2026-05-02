<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FlowQuest Office Theme Test</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- FlowQuest Theme CSS -->
    <link rel="stylesheet" href="<?= module_dir_url('flowquest_office_theme', 'assets/css/flowquest-integration.css') ?>">
    <link rel="stylesheet" href="<?= module_dir_url('flowquest_office_theme', 'assets/css/theme_styles.css') ?>">
    
    <!-- Google Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,400;0,500;0,700;1,400;1,500;1,700&display=swap">
    
    <style>
        body {
            padding: 2rem;
            background-color: var(--fq-bg);
        }
        
        .test-section {
            margin-bottom: 2rem;
        }
        
        .test-section h2 {
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--fq-border);
        }
    </style>
</head>
<body class="flowquest-theme">
    <div class="container">
        <header class="main-header mb-4 p-4 rounded">
            <h1 class="text-white">FlowQuest Office Theme Test</h1>
            <p class="text-white">Preview of the new FlowQuest Office Theme components</p>
        </header>
        
        <div class="row">
            <div class="col-md-12">
                <div class="test-section">
                    <h2>Colors</h2>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card mb-3">
                                <div class="card-body bg-primary text-white">
                                    <h5>Primary (#2563eb)</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card mb-3">
                                <div class="card-body bg-success text-white">
                                    <h5>Success (#10b981)</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card mb-3">
                                <div class="card-body bg-danger text-white">
                                    <h5>Danger (#ef4444)</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card mb-3">
                                <div class="card-body bg-warning text-white">
                                    <h5>Warning (#f59e0b)</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="test-section">
                    <h2>Buttons</h2>
                    <div class="row">
                        <div class="col-md-12">
                            <button class="btn btn-primary me-2">Primary</button>
                            <button class="btn btn-secondary me-2">Secondary</button>
                            <button class="btn btn-success me-2">Success</button>
                            <button class="btn btn-danger me-2">Danger</button>
                            <button class="btn btn-warning me-2">Warning</button>
                            <button class="btn btn-info me-2">Info</button>
                        </div>
                        <div class="col-md-12 mt-3">
                            <button class="btn btn-outline-primary me-2">Outline Primary</button>
                            <button class="btn btn-outline-secondary me-2">Outline Secondary</button>
                            <button class="btn btn-outline-success me-2">Outline Success</button>
                        </div>
                    </div>
                </div>
                
                <div class="test-section">
                    <h2>Cards</h2>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card dashboard-stat-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5 class="card-title">Total Revenue</h5>
                                            <p class="card-text">$24,569</p>
                                        </div>
                                        <div class="card-icon">
                                            <i class="fas fa-dollar-sign"></i>
                                        </div>
                                    </div>
                                    <div class="progress mt-3">
                                        <div class="progress-bar" role="progressbar" style="width: 75%" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card fq-card-gradient">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5 class="card-title">New Clients</h5>
                                            <p class="card-text">142</p>
                                        </div>
                                        <div class="card-icon">
                                            <i class="fas fa-users"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card fq-stat-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5 class="card-title">Pending Tasks</h5>
                                            <p class="card-text">24</p>
                                        </div>
                                        <div class="card-icon text-primary">
                                            <i class="fas fa-tasks"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="test-section">
                    <h2>Forms</h2>
                    <div class="card">
                        <div class="card-body">
                            <form>
                                <div class="mb-3">
                                    <label for="exampleInputEmail1" class="form-label">Email address</label>
                                    <input type="email" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp">
                                    <div id="emailHelp" class="form-text">We'll never share your email with anyone else.</div>
                                </div>
                                <div class="mb-3">
                                    <label for="exampleInputPassword1" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="exampleInputPassword1">
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="exampleCheck1">
                                    <label class="form-check-label" for="exampleCheck1">Check me out</label>
                                </div>
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="test-section">
                    <h2>Badges & Labels</h2>
                    <div class="card">
                        <div class="card-body">
                            <h5>Status Badges</h5>
                            <span class="badge badge-project-not-started">Not Started</span>
                            <span class="badge badge-project-in-progress">In Progress</span>
                            <span class="badge badge-project-finished">Finished</span>
                            <span class="badge badge-project-cancelled">Cancelled</span>
                            
                            <h5 class="mt-3">Priority Labels</h5>
                            <div class="priority-urgent p-2 mb-2">Urgent Task</div>
                            <div class="priority-high p-2 mb-2">High Priority Task</div>
                            <div class="priority-medium p-2 mb-2">Medium Priority Task</div>
                            <div class="priority-low p-2">Low Priority Task</div>
                        </div>
                    </div>
                </div>
                
                <div class="test-section">
                    <h2>Switches</h2>
                    <div class="card">
                        <div class="card-body">
                            <div class="form-check form-switch">
                                <input class="form-check-input fq-switch" type="checkbox" id="flexSwitchCheckDefault">
                                <label class="form-check-label" for="flexSwitchCheckDefault">Default switch checkbox input</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input fq-switch" type="checkbox" id="flexSwitchCheckChecked" checked>
                                <label class="form-check-label" for="flexSwitchCheckChecked">Checked switch checkbox input</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- FlowQuest Theme JS -->
    <script src="<?= module_dir_url('flowquest_office_theme', 'assets/js/flowquest-theme.js') ?>"></script>
</body>
</html>