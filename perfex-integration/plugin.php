<?php
/**
 * PerfexCRM Plugin
 */

return [
    "default" => function(&$request){
        if(!$this->session->has("logged"))
            $this->header->redirect(site_url("dashboard/auth"));

        $plugin = $this->system->getPlugin($request["name"], "directory");

        if(!$plugin)
            $this->pluginError(__("plugin_generic_unabletoprocess"));

        $pluginData = json_decode($plugin["data"], true);

        $this->smarty->assign([
            "version" => $pluginData["version"],
            "prefix" => $pluginData["prefix"],
            "name" => $pluginData["name"],
            "desc" => $pluginData["desc"],
            "author" => $pluginData["author"],
            "author_uri" => $pluginData["author_uri"],
            "site_name" => system_site_name,
            "site_url" => rtrim(site_url(false, true), "/")
        ]);

        $this->file->put(__DIR__ . "/temp/system.php", $this->smarty->fetch(__DIR__ . "/views/system.tpl"));
        $this->file->put(__DIR__ . "/temp/sms_system.php", $this->smarty->fetch(__DIR__ . "/views/sms_system.tpl"));

        try {
            $zip = new \PhpZip\ZipFile();
            $zip->addDirRecursive(__DIR__ . "/resources", $pluginData["prefix"])
                ->addFile(__DIR__ . "/temp/system.php", "/{$pluginData["prefix"]}/{$pluginData["prefix"]}.php")
                ->addFile(__DIR__ . "/temp/sms_system.php", "/{$pluginData["prefix"]}/libraries/Sms_{$pluginData["prefix"]}.php")
                ->outputAsAttachment("{$pluginData["prefix"]}.zip");
        } catch(Exception $e){
            $this->pluginError(__("plugin_generic_unabletoprocess"));
        }
    },
    "widget" => function(&$request){
        if(!$this->session->has("logged"))
            response(302);

        if(!isset($request["tpl"], $request["json"]))
            response(500, __("response_went_wrong"));

        if(!$this->smarty->templateExists(__DIR__ . "/views/widgets/{$request["tpl"]}.tpl")):
            response(500, __("response_invalid"));
        endif;

        switch($request["tpl"]):
            case "plugin.downloads":
                $vars = [
                    "template" => [
                        "title" => __("plugin_generic_downloadstitle")
                    ],
                    "handler" => [
                        "tpl" => $request["tpl"],
                        "size" => "md"
                    ]
                ];

                break;
            default;
                response(500, __("response_went_wrong"));
        endswitch;

        $this->smarty->assign($vars["template"]);

        response(200, false, [
            "vars" => (isset($vars["handler"]) ? $vars["handler"] : false),
            "tpl" => $this->smarty->fetch(__DIR__ . "/views/widgets/{$request["tpl"]}.tpl")
        ]);
    }
];