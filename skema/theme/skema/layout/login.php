<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

defined('MOODLE_INTERNAL') || die();

/**
 * A login page layout for the boost theme.
 *
 * @package   theme_boost
 * @copyright 2016 Damyon Wiese
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$bodyattributes = $OUTPUT->body_attributes();

$languagedata = new \core\output\language_menu($PAGE);
$languagemenu = $languagedata->export_for_action_menu($OUTPUT);

// main auth is Office 365. Secondary login mode is manual for some users who have local accounts.
// This will inject a class into the form container to hide local or alternate providers access.
// This works even if there is no continuous feeding chain from entry point down to loginform.
$authmode = optional_param('authmode', 'main', PARAM_TEXT);

$templatecontext = [
    'sitename' => format_string($SITE->shortname, true, ['context' => context_course::instance(SITEID), "escape" => false]),
    'output' => $OUTPUT,
    'bodyattributes' => $bodyattributes,
    'languagemenu' => $languagemenu,
    'authmode' => $authmode,
];

echo $OUTPUT->render_from_template('theme_skema/login', $templatecontext);

