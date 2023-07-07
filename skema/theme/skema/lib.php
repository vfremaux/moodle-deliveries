<?php

// Every file should have GPL and copyright in the header - we skip it in tutorials but you should not skip it for real.

// This line protects the file from being accessed by a URL directly.                                                               
defined('MOODLE_INTERNAL') || die();

// Function to return the SCSS to prepend to our main SCSS for this theme.
// Note the function name starts with the component name because this is a global function and we don't want namespace clashes.
function theme_skema_get_pre_scss($theme) {
    // Load the settings from the parent.                                                                                           
    $theme = theme_config::load('boost');                                                                                           
    // Call the parent themes get_pre_scss function.                                                                                
    return theme_boost_get_pre_scss($theme);                         
}

function theme_skema_get_extra_scss($theme) {                                                                                       
    return !empty($theme->settings->scss) ? $theme->settings->scss : '';                                                            
}

/**
 * Serves any files associated with the theme settings.
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return bool
 */
function theme_skema_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    if ($context->contextlevel == CONTEXT_SYSTEM && (
        $filearea === 'logo' || 
        $filearea === 'backgroundimage' ||
        $filearea === 'loginbackgroundimage' ||
        $filearea === 'skprogramimage1' ||
        $filearea === 'skprogramimage2' ||
        $filearea === 'skprogramimage3' ||
        $filearea === 'skprogramimage4' ||
        $filearea === 'skprogramimage5' ||
        $filearea === 'skprogramimage6' ||
        $filearea === 'skprogramimage7' ||
        $filearea === 'skprogramimage8' ||
        $filearea === 'skprogramimage9' ||
        $filearea === 'skprogramimage10' 
        
        
        )) {
        $theme = theme_config::load('skema');
        // By default, theme files must be cache-able by both browsers and proxies.
        if (!array_key_exists('cacheability', $options)) {
            $options['cacheability'] = 'public';
        }
        return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
    } else {
        send_file_not_found();
    }
}