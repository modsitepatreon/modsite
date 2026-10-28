<?php
/** Plugin Name: Modsite — lokalny tryb testowy Playground */
if (!defined('ABSPATH')) { exit; }
define('MODSITE_PLAYGROUND',true);
// Playground uses a UTC SQLite database. PMPro's MySQL expression converts
// UTC to the database session timezone; here that conversion is an identity.
// Keep all membership conditions and timestamp comparisons in the original query.
add_filter('query',static function($sql){
    if (stripos($sql,'pmpro_')===false) { return $sql; }
    $sql=preg_replace("/CONVERT_TZ\\(\\s*((?:`?[a-z_]+`?\\.)?`?(?:startdate|enddate)`?)\\s*,\\s*'\\+00:00'\\s*,\\s*@@global\\.time_zone\\s*\\)/i",'$1',$sql);
    $sql=preg_replace('/UNIX_TIMESTAMP\\(\\s*enddate\\s*\\)/i',"(CASE WHEN enddate IS NULL OR enddate='0000-00-00 00:00:00' THEN NULL ELSE UNIX_TIMESTAMP(enddate) END)",$sql);
    if (stripos($sql,'pmpro_memberships_users AS mu')!==false && stripos($sql,'pmpro_membership_levels AS l')!==false) {
        $sql=preg_replace('/GROUP BY ID\\b/i','GROUP BY l.id',$sql);
    }
    return $sql;
});
// The development copy never sends mail, even when testing account creation.
add_filter('pre_wp_mail',static function(){return true;});
// No payment processor can be selected while this development-only MU plugin is present.
add_filter('pre_option_pmpro_gateway',static function(){return 'check';});
add_filter('pre_option_pmpro_gateway_environment',static function(){return 'sandbox';});
add_action('admin_notices',static function(){
    if (!current_user_can('manage_options')) { return; }
    echo '<div class="notice notice-info"><p><strong>Modsite — test w przeglądarce.</strong> Zmiany są lokalne. Płatności i wysyłka e-maili są wyłączone. Przed zakończeniem pracy pobierz eksport ZIP z menu WordPress Playground.</p></div>';
});
