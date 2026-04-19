(function ($) {
    "use strict";
    $(function () {
        $(document).on("pluginModalEvent", function (e) {
            if ($("[system-plugin-downloads]").length) {
                $("[system-plugin-downloads]").prepend(`
                    <div class="col-md-6">
                        <a href="${site_url}/plugin?name=perfex-integration" target="_blank" class="btn btn-white btn-block mb-2 lift">
                            <img src="${site_url}/system/plugins/installables/perfex-integration/assets/btn.png" class="img-perfex">
                        </a>
                    </div>
                `);
            }
        });

        var linksWithRatesGateway = false;

        $("a").each(function () {
            if ($(this).attr("href") && $(this).attr("href").includes("/rates.gateway")) {
                linksWithRatesGateway = true;
                return false;
            }
        });

        if ($("[plugin-downloads-btn]").length < 1) {
            if (linksWithRatesGateway) {
                $(".header-body .col-auto").prepend(`
                    <button class="btn btn-primary lift" system-plugin-directory="perfex-integration" plugin-downloads-btn system-toggle="plugin.downloads">
                        <i class="la la-puzzle-piece la-lg"></i> ${lang_plugins_btn_pluginsdefopagegeneric}
                    </button>
                `);
            } else {
                $("[plugin-downloads-btn]").remove();
            }
        }

        document.addEventListener("pjax:complete", function () {
            var linksWithRatesGateway = false;

            $("a").each(function () {
                if ($(this).attr("href") && $(this).attr("href").includes("/rates.gateway")) {
                    linksWithRatesGateway = true;
                    return false;
                }
            });

            if ($("[plugin-downloads-btn]").length < 1) {
                if (linksWithRatesGateway) {
                    $(".header-body .col-auto").prepend(`
                        <button class="btn btn-primary lift" system-plugin-directory="perfex-integration" plugin-downloads-btn system-toggle="plugin.downloads">
                            <i class="la la-puzzle-piece la-lg"></i> ${lang_plugins_btn_pluginsdefopagegeneric}
                        </button>
                    `);
                } else {
                    $("[plugin-downloads-btn]").remove();
                }
            }
        });
    });
})(jQuery);