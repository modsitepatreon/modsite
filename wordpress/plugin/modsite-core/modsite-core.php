<?php
/**
 * Plugin Name: Modsite Core
 * Description: Katalog modów, prosta rejestracja i chronione przekierowania do zewnętrznego hostingu. Integracja z Paid Memberships Pro.
 * Version: 0.2.0
 * Requires at least: 6.6
 * Requires PHP: 8.1
 * License: GPL-2.0-or-later
 * Text Domain: modsite
 */
if (!defined('ABSPATH')) { exit; }
require_once __DIR__.'/includes/admin-dashboard.php';

function modsite_register_catalog() {
    register_post_type('modsite_mod', [
        'labels' => ['name'=>'Modyfikacje','singular_name'=>'Modyfikacja','add_new_item'=>'Dodaj modyfikację','edit_item'=>'Edytuj modyfikację'],
        'public'=>true, 'show_in_rest'=>true, 'has_archive'=>true,
        'rewrite'=>['slug'=>'mody'], 'menu_icon'=>'dashicons-games',
        'supports'=>['title','editor','excerpt','thumbnail','revisions'],
    ]);
    register_taxonomy('modsite_game', 'modsite_mod', [
        'label'=>'Gry', 'public'=>true, 'hierarchical'=>true, 'show_in_rest'=>true,
        'rewrite'=>['slug'=>'gra'],
    ]);
    register_taxonomy('modsite_category', 'modsite_mod', [
        'label'=>'Kategorie modów', 'public'=>true, 'hierarchical'=>true, 'show_in_rest'=>true,
        'rewrite'=>['slug'=>'kategoria-modow'],
    ]);
    // The protected download URL deliberately has NO REST registration.
    // WordPress treats underscore-prefixed metadata as protected by default.
}
add_action('init','modsite_register_catalog');

function modsite_activate() {
    modsite_register_catalog();
    foreach (['logowanie'=>['Logowanie','[modsite_login]'],'rejestracja'=>['Rejestracja','[modsite_register]']] as $slug=>$page) {
        $existing=get_page_by_path($slug);
        if ($existing) { $id=$existing->ID; }
        else { $id=wp_insert_post(['post_type'=>'page','post_status'=>'publish','post_title'=>$page[0],'post_name'=>$slug,'post_content'=>$page[1]],true); }
        if (!is_wp_error($id)) { update_option('modsite_'.$slug.'_page',absint($id)); }
    }
    flush_rewrite_rules();
}
register_activation_hook(__FILE__,'modsite_activate');
register_deactivation_hook(__FILE__,'flush_rewrite_rules');

function modsite_membership_level() { return absint(get_option('modsite_paid_level',0)); }
function modsite_can_download($user_id=0) {
    $user_id=$user_id ? absint($user_id) : get_current_user_id();
    if (!$user_id) { return false; }
    if (user_can($user_id,'manage_options')) { return true; }
    $level=modsite_membership_level();
    // Deny access if the membership plugin or an explicit paid level is missing.
    if (!$level || !function_exists('pmpro_hasMembershipLevel') || !function_exists('pmpro_getLevel')) { return false; }
    $definition=pmpro_getLevel($level);
    if (!$definition || ((float)($definition->initial_payment??0)<=0 && (float)($definition->billing_amount??0)<=0)) { return false; }
    if (!pmpro_hasMembershipLevel($level,$user_id) || !function_exists('pmpro_getMembershipLevelsForUser')) { return false; }
    foreach (pmpro_getMembershipLevelsForUser($user_id) as $membership) {
        if ((int)$membership->id!==$level) { continue; }
        $end=$membership->enddate??null;
        if (!empty($end)) {
            $expires=is_numeric($end)?(int)$end:strtotime((string)$end.' UTC');
            if (!$expires || $expires<=time()) { return false; }
        }
        return true;
    }
    return false;
}
function modsite_page_url($kind) {
    $id=absint(get_option('modsite_'.$kind.'_page',0));
    return $id ? get_permalink($id) : ($kind==='logowanie' ? wp_login_url() : wp_registration_url());
}
function modsite_membership_url($kind='levels') {
    if (function_exists('pmpro_url')) { $url=pmpro_url($kind); if ($url) { return $url; } }
    return modsite_page_url('logowanie');
}
function modsite_download_link($post_id) {
    return wp_nonce_url(add_query_arg(['action'=>'modsite_download','mod_id'=>absint($post_id)],admin_url('admin-post.php')),'modsite_download_'.absint($post_id));
}
function modsite_valid_download_url($url) {
    $url=esc_url_raw(trim((string)$url),['https']);
    $parts=wp_parse_url($url);
    if (!$parts || ($parts['scheme']??'')!=='https' || empty($parts['host']) || isset($parts['user']) || isset($parts['pass'])) { return ''; }
    return $url;
}
function modsite_download() {
    nocache_headers();
    header('Cache-Control: private, no-store, no-cache, must-revalidate, max-age=0');
    header('Referrer-Policy: no-referrer');
    header('X-Robots-Tag: noindex, nofollow');
    $id=absint($_GET['mod_id']??0);
    $post=get_post($id);
    if (!$post || $post->post_type!=='modsite_mod' || $post->post_status!=='publish') { wp_die('Nie znaleziono modyfikacji.','Modsite',['response'=>404]); }
    if (!is_user_logged_in()) { wp_safe_redirect(wp_login_url(get_permalink($id))); exit; }
    if (!modsite_can_download()) { wp_die('Ten link wymaga aktywnej subskrypcji Modsite.','Dostęp w subskrypcji',['response'=>403,'back_link'=>true]); }
    $nonce=isset($_GET['_wpnonce'])&&is_string($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';
    if (!wp_verify_nonce($nonce,'modsite_download_'.$id)) { wp_die('Link wygasł. Odśwież stronę modyfikacji i spróbuj ponownie.','Modsite',['response'=>403,'back_link'=>true]); }
    $url=modsite_valid_download_url(get_post_meta($id,'_modsite_download_url',true));
    if (!$url) { wp_die('Autor nie dodał jeszcze prawidłowego linku.','Modsite',['response'=>404,'back_link'=>true]); }
    // Intentional external redirect to a URL saved by an authorized post editor.
    wp_redirect($url,302,'Modsite');
    exit;
}
add_action('admin_post_modsite_download','modsite_download');
add_action('admin_post_nopriv_modsite_download','modsite_download');
add_action('template_redirect',function(){
    if (is_singular('modsite_mod') || is_page([absint(get_option('modsite_logowanie_page')),absint(get_option('modsite_rejestracja_page'))])) {
        if (!defined('DONOTCACHEPAGE')) { define('DONOTCACHEPAGE',true); }
        nocache_headers();
        header('Cache-Control: private, no-store, no-cache, must-revalidate, max-age=0');
    }
});

function modsite_fields() {
    return ['download_url'=>'Link do zewnętrznego hostingu (HTTPS, prywatny)','version'=>'Wersja moda','game_version'=>'Zgodna wersja gry','file_size'=>'Rozmiar pliku, np. 245 MB'];
}
add_action('add_meta_boxes',function(){
    add_meta_box('modsite-details','Szczegóły modyfikacji',function($post){
        wp_nonce_field('modsite_save_details','modsite_details_nonce');
        echo '<p>Link do pobrania umieść wyłącznie w polu poniżej. Opis, zajawka i instrukcje są publiczne.</p>';
        foreach (modsite_fields() as $key=>$label) {
            echo '<p><label for="modsite-'.esc_attr($key).'"><strong>'.esc_html($label).'</strong></label><br><input style="width:100%" id="modsite-'.esc_attr($key).'" name="modsite_'.esc_attr($key).'" type="'.($key==='download_url'?'url':'text').'" value="'.esc_attr(get_post_meta($post->ID,'_modsite_'.$key,true)).'"></p>';
        }
    },'modsite_mod','normal','high');
});
add_action('save_post_modsite_mod',function($id){
    if (defined('DOING_AUTOSAVE')&&DOING_AUTOSAVE) { return; }
    if (wp_is_post_revision($id) || !current_user_can('edit_post',$id)) { return; }
    $nonce=$_POST['modsite_details_nonce']??'';
    if (!is_string($nonce) || !wp_verify_nonce(sanitize_text_field(wp_unslash($nonce)),'modsite_save_details')) { return; }
    foreach (modsite_fields() as $key=>$label) {
        if (!isset($_POST['modsite_'.$key]) || !is_string($_POST['modsite_'.$key])) { continue; }
        $value=wp_unslash($_POST['modsite_'.$key]);
        $value=$key==='download_url' ? modsite_valid_download_url($value) : sanitize_text_field($value);
        update_post_meta($id,'_modsite_'.$key,$value);
    }
});

add_action('admin_menu',function(){add_options_page('Modsite','Modsite','manage_options','modsite-settings','modsite_settings_page');});
add_action('admin_init',function(){register_setting('modsite_settings','modsite_paid_level',['type'=>'integer','sanitize_callback'=>'absint','default'=>0]);});
function modsite_settings_page() {
    if (!current_user_can('manage_options')) { return; }
    echo '<div class="wrap"><h1>Ustawienia Modsite</h1><p>Zainstaluj Paid Memberships Pro i jego darmowy dodatek PayPal Gateway. Utwórz płatny plan miesięczny, a następnie wpisz jego ID poniżej.</p><form method="post" action="options.php">';
    settings_fields('modsite_settings');
    echo '<label for="modsite_paid_level"><strong>Płatny plan odblokowujący pobieranie</strong></label><br><select id="modsite_paid_level" name="modsite_paid_level"><option value="0">— Nie wybrano planu —</option>';
    if (function_exists('pmpro_getAllLevels')) {
        foreach (pmpro_getAllLevels(true) as $level) {
            if ((float)$level->initial_payment<=0 && (float)$level->billing_amount<=0) { continue; }
            echo '<option value="'.esc_attr($level->id).'" '.selected(modsite_membership_level(),(int)$level->id,false).'>'.esc_html($level->name).' (ID '.esc_html($level->id).')</option>';
        }
    }
    echo '</select><p>Najpierw utwórz plan i ustal cenę w sekcji Subskrypcje. Bez wybranego płatnego planu linki pozostają zablokowane. Administrator może sprawdzać pobieranie bez subskrypcji.</p>';
    submit_button();echo '</form><h2>Konfiguracja kont</h2><p>W Ustawienia → Ogólne włącz „Każdy może się zarejestrować”. Domyślna rola: Subskrybent. Dla Google zainstaluj Nextend Social Login, skonfiguruj dostawcę Google i włącz go.</p><p>Modsite Core nie dodaje bramki BLIK. Wariant początkowy korzysta z PayPal.</p></div>';
}
add_action('admin_notices',function(){
    if (!current_user_can('manage_options')) { return; }
    if (!function_exists('pmpro_hasMembershipLevel') || !modsite_membership_level()) { echo '<div class="notice notice-warning"><p>Modsite: pobieranie dla subskrybentów jest zablokowane do czasu konfiguracji Paid Memberships Pro i ID planu w <a href="'.esc_url(admin_url('options-general.php?page=modsite-settings')).'">ustawieniach Modsite</a>.</p></div>'; }
});

function modsite_google_login() {
    return shortcode_exists('nextend_social_login') ? do_shortcode('[nextend_social_login provider="google"]') : '';
}
add_shortcode('modsite_login',function(){
    if (is_user_logged_in()) { return '<p>Jesteś już zalogowany. <a href="'.esc_url(modsite_membership_url('account')).'">Przejdź do konta</a>.</p>'; }
    return '<div class="modsite-auth">'.modsite_google_login().wp_login_form(['echo'=>false,'redirect'=>home_url('/'),'label_username'=>'Nazwa użytkownika lub e-mail','label_password'=>'Hasło','label_remember'=>'Zapamiętaj mnie','label_log_in'=>'Zaloguj się']).'<p><a href="'.esc_url(wp_lostpassword_url()).'">Nie pamiętasz hasła?</a> · <a href="'.esc_url(modsite_page_url('rejestracja')).'">Utwórz konto</a></p></div>';
});
function modsite_register_form() {
    if (is_user_logged_in()) { return '<p>Jesteś już zalogowany.</p>'; }
    if (!get_option('users_can_register')) { return '<p>Rejestracja jest obecnie wyłączona.</p>'; }
    $messages=['error'=>'Nie udało się utworzyć konta. Sprawdź dane lub spróbuj później.','invalid'=>'Użyj poprawnej nazwy użytkownika, adresu e-mail i hasła o długości co najmniej 12 znaków.','exists'=>'Nazwa lub e-mail są już używane. Zaloguj się albo odzyskaj hasło.','limit'=>'Zbyt wiele prób. Spróbuj ponownie za 15 minut.','created'=>'Konto zostało utworzone. Możesz się zalogować.'];
    $status=isset($_GET['modsite_status'])&&is_string($_GET['modsite_status'])?sanitize_key(wp_unslash($_GET['modsite_status'])):'';
    ob_start();echo '<div class="modsite-auth">'.modsite_google_login();
    if (isset($messages[$status])) { echo '<p role="status" class="modal-info">'.esc_html($messages[$status]).'</p>'; }
    ?>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="auth-form">
        <input type="hidden" name="action" value="modsite_register">
        <?php wp_nonce_field('modsite_register','modsite_nonce'); ?>
        <label class="field">Nazwa użytkownika<input name="username" required maxlength="60" autocomplete="username"></label>
        <label class="field">E-mail<input name="email" type="email" required maxlength="100" autocomplete="email"></label>
        <label class="field">Hasło<input name="password" type="password" required minlength="12" autocomplete="new-password"></label>
        <div aria-hidden="true" style="position:absolute;left:-10000px"><label>Nie wypełniaj<input name="website" tabindex="-1" autocomplete="off"></label></div>
        <button class="button gold" type="submit">Utwórz konto</button>
    </form>
    <p><a href="<?php echo esc_url(modsite_page_url('logowanie')); ?>">Masz już konto? Zaloguj się</a></p>
    <?php if (get_privacy_policy_url()) { echo '<p><a href="'.esc_url(get_privacy_policy_url()).'">Polityka prywatności</a></p>'; } ?>
    </div>
    <?php return ob_get_clean();
}
add_shortcode('modsite_register','modsite_register_form');
function modsite_registration_redirect($status) {
    wp_safe_redirect(add_query_arg('modsite_status',$status,modsite_page_url('rejestracja')));exit;
}
function modsite_process_registration() {
    nocache_headers();
    if (($_SERVER['REQUEST_METHOD']??'')!=='POST') { wp_die('Niedozwolona metoda.','Modsite',['response'=>405]); }
    if (is_user_logged_in()) { wp_safe_redirect(home_url('/'));exit; }
    if (!get_option('users_can_register')) { wp_die('Rejestracja jest wyłączona.','Modsite',['response'=>403]); }
    $nonce=$_POST['modsite_nonce']??'';
    if (!is_string($nonce)||!wp_verify_nonce(sanitize_text_field(wp_unslash($nonce)),'modsite_register')) { modsite_registration_redirect('error'); }
    foreach (['username','email','password','website'] as $field) { if (isset($_POST[$field])&&!is_string($_POST[$field])) { modsite_registration_redirect('invalid'); } }
    if (!empty($_POST['website'])) { modsite_registration_redirect('error'); }
    $key='modsite_reg_'.hash_hmac('sha256',(string)($_SERVER['REMOTE_ADDR']??'unknown'),wp_salt('auth'));
    $attempts=(int)get_transient($key);
    if ($attempts>=10) { modsite_registration_redirect('limit'); }
    set_transient($key,$attempts+1,15*MINUTE_IN_SECONDS);
    $username=sanitize_user(wp_unslash($_POST['username']??''),true);
    $email=sanitize_email(wp_unslash($_POST['email']??''));
    $password=wp_unslash($_POST['password']??'');
    if (!$username||strlen($username)>60||!validate_username($username)||!is_email($email)||strlen($email)>100||strlen($password)<12||strlen($password)>4096) { modsite_registration_redirect('invalid'); }
    if (username_exists($username)||email_exists($email)) { modsite_registration_redirect('exists'); }
    $id=wp_insert_user(['user_login'=>$username,'user_email'=>$email,'user_pass'=>$password,'role'=>'subscriber']);
    unset($password);
    if (is_wp_error($id)) { modsite_registration_redirect('error'); }
    wp_new_user_notification($id,null,'both');
    modsite_registration_redirect('created');
}
add_action('admin_post_nopriv_modsite_register','modsite_process_registration');
add_action('admin_post_modsite_register','modsite_process_registration');
