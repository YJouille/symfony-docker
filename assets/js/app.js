require("@assets/styles/app.scss");

const $ = require("jquery");
global.$ = global.jQuery = $;

const bootstrap = require("bootstrap");
global.bootstrap = bootstrap;

import { initFlashMessages } from "@assets/js/components/flashMessages";

$(function () {
    initFlashMessages();
});
