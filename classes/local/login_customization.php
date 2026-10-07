<?php
// SPDX-License-Identifier: GPL-3.0-or-later
namespace local_coursecarousel\local;
defined('MOODLE_INTERNAL') || die();
/** Provider configuration only; the theme decides how to present it. */
final class login_customization {
    public static function context(): ?array {
        $enabled = get_config('local_coursecarousel', 'customizelogin');
        if ($enabled !== false && empty($enabled)) { return null; }
        $url = get_config('local_coursecarousel', 'identityproviderurl');
        if (empty($url)) { $url = (new \moodle_url('/auth/shibboleth/index.php'))->out(false); }
        return ['providerurl' => $url, 'label' => get_string('institutionallogin', 'local_coursecarousel')];
    }
}