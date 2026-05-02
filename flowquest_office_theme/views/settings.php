<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="<?= get_option('active_language'); ?>">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= _l('flowquest_office_theme'); ?></title>
    <?= app_style('assets/css/style.css'); ?>
</head>
<body class="flowquest-theme">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <h1><?= _l('flowquest_office_theme'); ?></h1>
                <p class="text-muted"><?= _l('flowquest_office_theme_description'); ?></p>
                
                <div class="card">
                    <div class="card-body">
                        <h3><?= _l('flowquest_office_theme_settings'); ?></h3>
                        
                        <form method="post" action="<?= admin_url('flowquest_office_theme/settings/save'); ?>">
                            <div class="form-group">
                                <label for="primary_color">Kolor podstawowy:</label>
                                <input type="color" class="form-control" id="primary_color" name="primary_color" value="#2563eb">
                            </div>
                            
                            <div class="form-group">
                                <label for="accent_color">Kolor akcentu:</label>
                                <input type="color" class="form-control" id="accent_color" name="accent_color" value="#10b981">
                            </div>
                            
                            <div class="form-group">
                                <label for="font_family">Czcionka:</label>
                                <select class="form-control" id="font_family" name="font_family">
                                    <option value="DM Sans">DM Sans</option>
                                    <option value="Inter">Inter</option>
                                    <option value="Roboto">Roboto</option>
                                </select>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Zapisz zmiany</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>