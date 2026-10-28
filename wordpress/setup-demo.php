<?php
// Initial demo content, executed once while constructing a new Playground.
require_once '/wordpress/wp-load.php';
$data=json_decode(file_get_contents('/wordpress/wp-content/modsite-demo-data.json'),true,512,JSON_THROW_ON_ERROR);
$games=[];
foreach ($data['games'] as $game) {
    $term=term_exists($game['name'],'modsite_game');
    if (!$term) { $term=wp_insert_term($game['name'],'modsite_game',['slug'=>$game['id']]); }
    if (is_wp_error($term)) { throw new RuntimeException($term->get_error_message()); }
    $games[$game['id']]=(int)$term['term_id'];
}
$uploads=wp_upload_dir();$images=[];
foreach (array_merge(array_column($data['games'],'id'),['hero']) as $key) {
    $relative='modsite-demo/'.$key.'.jpg';$path=$uploads['basedir'].'/'.$relative;
    if (!is_file($path)) { continue; }
    $info=getimagesize($path);
    $attachment=wp_insert_attachment(['post_mime_type'=>'image/jpeg','post_title'=>'Modsite — grafika demonstracyjna '.$key,'post_status'=>'inherit','guid'=>$uploads['baseurl'].'/'.$relative],$path);
    update_post_meta($attachment,'_wp_attached_file',$relative);
    wp_update_attachment_metadata($attachment,['width'=>$info[0],'height'=>$info[1],'file'=>$relative,'filesize'=>filesize($path),'sizes'=>[]]);
    $images[$key]=$attachment;
}
foreach ($data['mods'] as $mod) {
    $existing=get_posts(['post_type'=>'modsite_mod','post_status'=>'any','meta_key'=>'_modsite_demo_id','meta_value'=>(string)$mod['id'],'fields'=>'ids','posts_per_page'=>1]);
    $content='<p><strong>Wpis demonstracyjny.</strong> Zastąp go własną modyfikacją i materiałami autora.</p><p>'.esc_html($mod['description']).'</p><h2>Instalacja</h2><ol><li>Sprawdź zgodność z wersją swojej gry.</li><li>Wykonaj kopię zapasową zapisów.</li><li>Postępuj według instrukcji dołączonej przez autora moda.</li></ol><p>Parametry i grafika w tym wpisie są przykładowe.</p>';
    $id=wp_insert_post(['ID'=>$existing?(int)$existing[0]:0,'post_type'=>'modsite_mod','post_status'=>'publish','post_title'=>$mod['title'],'post_excerpt'=>$mod['description'],'post_content'=>$content,'post_date'=>$mod['date'].' 12:00:00','comment_status'=>'closed','meta_input'=>['_modsite_demo_id'=>(string)$mod['id'],'_modsite_version'=>$mod['version'],'_modsite_file_size'=>$mod['size'],'_modsite_game_version'=>'Do uzupełnienia','_modsite_download_url'=>'']],true);
    if (is_wp_error($id)) { throw new RuntimeException($id->get_error_message()); }
    wp_set_object_terms($id,[$games[$mod['game']]],'modsite_game');
    wp_set_object_terms($id,[$mod['category']],'modsite_category');
    $image=$mod['id']===1?($images['hero']??0):($images[$mod['game']]??0);
    if ($image) { set_post_thumbnail($id,$image); }
}
if (function_exists('pmpro_generatePages')) {
    pmpro_generatePages(['account'=>'Moje konto','checkout'=>'Zakup subskrypcji','billing'=>'Dane rozliczeniowe','cancel'=>'Anulowanie subskrypcji','confirmation'=>'Potwierdzenie','invoice'=>'Historia płatności','levels'=>'Subskrypcje','member_profile_edit'=>'Profil użytkownika']);
    update_option('pmpro_login_page_id',(int)get_option('modsite_logowanie_page'));
}
update_option('pmpro_currency','PLN');
update_option('pmpro_gateway','check');
update_option('pmpro_gateway_environment','sandbox');
update_option('modsite_paid_level',0);
update_option('users_can_register',1);update_option('default_role','subscriber');
update_option('blogname','Modsite');update_option('blogdescription','Twoja gra. Więcej możliwości.');
update_option('timezone_string','Europe/Warsaw');
update_option('permalink_structure','/%postname%/');
update_option('show_on_front','posts');
update_option('blog_public',0);
$blocks=[
    2=>['content'=>'<!-- wp:heading {"level":3} --><h3 class="wp-block-heading">Z tablicy Modsite</h3><!-- /wp:heading --><!-- wp:paragraph --><p>Witaj w wersji testowej! Edytuj ten panel w Wygląd → Widżety → Prawa kolumna.</p><!-- /wp:paragraph -->'],
    3=>['content'=>'<!-- wp:paragraph --><p>MODSITE · WERSJA TESTOWA</p><!-- /wp:paragraph -->'],
    4=>['content'=>'<!-- wp:heading {"level":3} --><h3 class="wp-block-heading">Jak zacząć?</h3><!-- /wp:heading --><!-- wp:paragraph --><p>Wybierz grę i otwórz modyfikację. Wpisy oraz grafiki są przykładowe.</p><!-- /wp:paragraph -->'],
    '_multiwidget'=>1,
];
update_option('widget_block',$blocks);
update_option('sidebars_widgets',['wp_inactive_widgets'=>[],'modsite-left'=>['block-4'],'modsite-right'=>['block-2'],'modsite-top'=>['block-3'],'array_version'=>3]);
$menu=wp_create_nav_menu('Menu Modsite');
if (!is_wp_error($menu)) {
    wp_update_nav_menu_item($menu,0,['menu-item-title'=>'Modyfikacje','menu-item-url'=>home_url('/'),'menu-item-status'=>'publish']);
    if (function_exists('pmpro_url')) { wp_update_nav_menu_item($menu,0,['menu-item-title'=>'Subskrypcje','menu-item-url'=>pmpro_url('levels'),'menu-item-status'=>'publish']); }
    set_theme_mod('nav_menu_locations',['primary'=>$menu]);
}
delete_transient('_pmpro_activation_redirect');
// The prepared Modsite dashboard replaces PMPro's first-activation wizard.
delete_option('pmpro_wizard_redirect');
flush_rewrite_rules();
echo 'MODSITE_SETUP_OK: '.wp_count_posts('modsite_mod')->publish.' mods; WordPress '.get_bloginfo('version').'; PMPro '.(defined('PMPRO_VERSION')?PMPRO_VERSION:'missing');
