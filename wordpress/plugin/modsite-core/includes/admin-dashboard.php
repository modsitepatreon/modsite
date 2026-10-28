<?php
if (!defined('ABSPATH')) { exit; }

add_action('admin_menu',function(){
    add_menu_page('Panel Modsite','Modsite','manage_options','modsite','modsite_admin_dashboard','dashicons-games',3);
    add_submenu_page('modsite','Panel Modsite','Przegląd','manage_options','modsite','modsite_admin_dashboard');
    add_submenu_page('modsite','Ustawienia Modsite','Dostęp do plików','manage_options','modsite-settings','modsite_settings_page');
});

function modsite_admin_dashboard() {
    if (!current_user_can('manage_options')) { return; }
    $counts=wp_count_posts('modsite_mod');
    $published=isset($counts->publish)?(int)$counts->publish:0;
    $membership=function_exists('pmpro_hasMembershipLevel');
    $google=class_exists('NextendSocialLogin');
    $test=defined('MODSITE_PLAYGROUND')&&MODSITE_PLAYGROUND;
    $cards=[
        ['dashicons-edit-page','Modyfikacje','Dodawaj mody, grafiki, instrukcje i prywatne linki do hostingu.','edit.php?post_type=modsite_mod','Zarządzaj modyfikacjami'],
        ['dashicons-category','Gry i kategorie','Porządkuj katalog według gry i rodzaju modyfikacji.','edit-tags.php?taxonomy=modsite_game&post_type=modsite_mod','Zarządzaj grami'],
        ['dashicons-screenoptions','Panele strony','Dodawaj bloki do lewej kolumny, prawej kolumny i stałej górnej belki.','widgets.php','Edytuj panele'],
        ['dashicons-art','Logo i wygląd','Wgraj logo, zmień dane witryny i zobacz podgląd motywu.','customize.php','Dostosuj wygląd'],
        ['dashicons-groups','Subskrypcje','Utwórz plan miesięczny, ustal cenę i zarządzaj członkami.',$membership?'admin.php?page=pmpro-membershiplevels':'plugin-install.php?s=paid-memberships-pro&tab=search&type=term',$membership?'Plany subskrypcji':'Zainstaluj PMPro'],
        ['dashicons-lock','Dostęp do plików','Wybierz płatny plan, który odblokuje linki do pobierania.','admin.php?page=modsite-settings','Ustaw dostęp'],
        ['dashicons-money-alt','Płatności','Skonfiguruj operatora i tryb testowy przed uruchomieniem sprzedaży.',$membership?'admin.php?page=pmpro-paymentsettings':'plugins.php','Ustawienia płatności'],
        ['dashicons-admin-users','Logowanie Google','Skonfiguruj aplikację Google i włącz dostawcę logowania.',$google?'admin.php?page=nextend-social-login':'plugins.php',$google?'Ustawienia Google':'Wtyczki logowania'],
    ];
    ?>
    <div class="wrap modsite-admin">
      <style>
        .modsite-admin{max-width:1220px}.modsite-admin *{box-sizing:border-box}.modsite-admin-hero{padding:30px 34px;background:#151719;border:1px solid #493f2d;border-radius:10px;color:#f1f1f1;margin:24px 0}.modsite-admin-label{color:#d7b777;font-weight:700;font-size:11px;letter-spacing:2px}.modsite-admin-hero h1{font-size:30px;color:#fff;margin:13px 0 10px;font-weight:600}.modsite-admin-hero p{color:#bdc0c7;font-size:14px;margin:0 0 20px}.modsite-admin-hero .notice{color:#1d2327}.modsite-admin-hero .notice p{color:#1d2327;font-size:13px;margin:.5em 0;padding:2px}.modsite-admin .modsite-admin-button{display:inline-block;background:#d7b777;color:#191816;padding:10px 17px;border-radius:5px;text-decoration:none;font-weight:600}.modsite-admin-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px;margin:22px 0}.modsite-admin-card{background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:24px;display:flex;flex-direction:column}.modsite-admin-card>.dashicons{color:#9a7436;font-size:25px;width:28px;height:28px}.modsite-admin-card h2{margin:14px 0 6px;font-size:17px}.modsite-admin-card p{font-size:13px;color:#646970;line-height:1.7;flex:1;margin:0 0 20px}.modsite-admin-card a{font-weight:600;text-decoration:none}.modsite-admin-summary{display:flex;gap:12px;flex-wrap:wrap}.modsite-admin-summary span{border:1px solid #dcdcde;border-radius:5px;padding:8px 12px;background:white;font-size:12px}.modsite-admin-note{padding:17px 20px;border-left:4px solid #d7b777;background:#fff;line-height:1.8;margin-bottom:20px}.modsite-admin-note h2{font-size:15px;margin:0 0 5px}.modsite-admin-note p{margin:0}.modsite-admin-links{display:flex;gap:18px;flex-wrap:wrap;margin:20px 0 35px}
      </style>
      <section class="modsite-admin-hero"><div class="modsite-admin-label">MODSITE · PANEL ADMINISTRATORA</div><h1>Twoja strona. Wszystko pod ręką.</h1><p>Zarządzaj katalogiem modów, wyglądem i dostępem do plików.</p><a class="modsite-admin-button" href="<?php echo esc_url(home_url('/')); ?>" target="_blank" rel="noopener">Zobacz stronę ↗</a> <a class="modsite-admin-button" href="<?php echo esc_url(admin_url('post-new.php?post_type=modsite_mod')); ?>">Dodaj modyfikację</a></section>
      <?php if ($test) { ?><section class="modsite-admin-note"><h2>WordPress testowy w Twojej przeglądarce</h2><p>Zmiany dotyczą tej lokalnej kopii. W WordPress Playground wybierz zapis w przeglądarce, a przed zakończeniem pracy pobierz eksport ZIP. Publiczny podgląd na GitHub Pages jest osobną stroną. Płatności i wysyłka e-maili są wyłączone.</p></section><?php } ?>
      <div class="modsite-admin-summary"><span><strong><?php echo esc_html($published); ?></strong> opublikowanych modów</span><span>Motyw: <strong><?php echo esc_html(wp_get_theme()->get('Name')); ?></strong></span><span>Subskrypcje: <strong><?php echo $membership?'PMPro zainstalowane':'do skonfigurowania'; ?></strong></span><span>Dostęp: <strong><?php echo function_exists('modsite_membership_level')&&modsite_membership_level()?'plan wybrany':'wybierz płatny plan'; ?></strong></span></div>
      <div class="modsite-admin-grid"><?php foreach ($cards as $card) { ?><section class="modsite-admin-card"><span class="dashicons <?php echo esc_attr($card[0]); ?>" aria-hidden="true"></span><h2><?php echo esc_html($card[1]); ?></h2><p><?php echo esc_html($card[2]); ?></p><a href="<?php echo esc_url(admin_url($card[3])); ?>"><?php echo esc_html($card[4]); ?> →</a></section><?php } ?></div>
      <section class="modsite-admin-note"><h2>Dodawanie modyfikacji</h2><p>Uzupełnij tytuł, opis, obrazek wyróżniający, grę i kategorię. Link do pliku umieść w polu „Link do zewnętrznego hostingu (HTTPS, prywatny)” pod edytorem. Opis i zajawka są publiczne.</p></section>
      <div class="modsite-admin-links"><a href="<?php echo esc_url(admin_url('edit-tags.php?taxonomy=modsite_category&post_type=modsite_mod')); ?>">Kategorie modów</a><a href="<?php echo esc_url(admin_url('nav-menus.php')); ?>">Menu strony</a><a href="<?php echo esc_url(admin_url('options-general.php')); ?>">Dane witryny i rejestracja</a><a href="<?php echo esc_url(admin_url('users.php')); ?>">Użytkownicy</a></div>
    </div>
    <?php
}
