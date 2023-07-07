<?php

// Every file should have GPL and copyright in the header - we skip it in tutorials but you should not skip it for real.

// This line protects the file from being accessed by a URL directly.                                                               
defined('MOODLE_INTERNAL') || die();                                                                                                
                                                                                                                                    
// This is used for performance, we don't need to know about these settings on every page in Moodle, only when                      
// we are looking at the admin settings pages.                                                                                      
if ($ADMIN->fulltree) {                                                                                                             
    $settings = new theme_boost_admin_settingspage_tabs('themesettingskema', get_string('configtitle', 'theme_skema'));
    $page = new admin_settingpage('theme_skema_general', get_string('generalsettings', 'theme_skema'));

    
    // Advanced settings.
    $page = new admin_settingpage('theme_skema_advanced', get_string('advancedsettings', 'theme_skema'));

    // Raw SCSS to include before the content.
    $setting = new admin_setting_scsscode('theme_skema/scsspre',
        get_string('rawscsspre', 'theme_skema'), get_string('rawscsspre_desc', 'theme_skema'), '', PARAM_RAW);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Raw SCSS to include after the content.
    $setting = new admin_setting_scsscode('theme_skema/scss', get_string('rawscss', 'theme_skema'),
        get_string('rawscss_desc', 'theme_skema'), '', PARAM_RAW);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);


    // Raw SCSS to include after the content.
    $setting = new admin_setting_scsscode('theme_skema/scss', get_string('rawscss', 'theme_skema'),
        get_string('rawscss_desc', 'theme_skema'), '', PARAM_RAW);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $settings->add($page);
    
    
    
    $page = new admin_settingpage('theme_skema_imagenscurso', get_string('imagenscurso', 'theme_skema'));

    $i = 1;
    for($i = 1; $i <= 10; $i++ ){
        // Program text setting.
        $name = 'theme_skema/programvalue'.$i;
        $title = get_string('programvalue'.$i, 'theme_skema');
        $description = '';
        $setting = new admin_setting_configtext($name, $title, $description, 'programvalue'.$i);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);

        // Program image setting.
        $name = 'theme_skema/skprogramimage'.$i;
        $title = get_string('skprogramimage'.$i, 'theme_skema');
        $description = '';
        $setting = new admin_setting_configstoredfile($name, $title, $description, 'skprogramimage'.$i);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);
    }

    
    $settings->add($page);
}