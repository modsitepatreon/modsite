<?php
if (!defined('ABSPATH')) { exit; }
add_action('after_setup_theme',function(){
    add_theme_support('title-tag');add_theme_support('post-thumbnails');add_theme_support('responsive-embeds');
    add_theme_support('html5',['search-form','comment-form','comment-list','gallery','caption','style','script']);
    add_theme_support('custom-logo',['height'=>80,'width'=>260,'flex-height'=>true,'flex-width'=>true]);
    register_nav_menus(['primary'=>'Menu w górnej belce','footer'=>'Menu w stopce']);
});
add_action('wp_enqueue_scripts',function(){
    wp_enqueue_style('modsite',get_stylesheet_uri(),[],'0.2.0');
    wp_enqueue_script('modsite',get_template_directory_uri().'/assets/theme.js',[],'0.2.0',true);
});
add_action('widgets_init',function(){
    foreach (['left'=>'Lewa kolumna','right'=>'Prawa kolumna','top'=>'Górna stała belka'] as $id=>$name) {
        register_sidebar(['name'=>$name,'id'=>'modsite-'.$id,'description'=>'Panele Modsite — dodaj blok, tekst, przyciski lub menu.','before_widget'=>'<section id="%1$s" class="custom-panel widget %2$s">','after_widget'=>'</section>','before_title'=>'<h3>','after_title'=>'</h3>']);
    }
});
function modsite_brand() {
    if (has_custom_logo()) { the_custom_logo(); }
    else { echo '<a class="brand" href="'.esc_url(home_url('/')).'" aria-label="Modsite — strona główna">MOD<span>SITE</span><i></i></a>'; }
}
function modsite_theme_url($kind) {
    if ($kind==='login') { return function_exists('modsite_page_url') ? modsite_page_url('logowanie') : wp_login_url(); }
    if ($kind==='register') { return function_exists('modsite_page_url') ? modsite_page_url('rejestracja') : wp_registration_url(); }
    return function_exists('modsite_membership_url') ? modsite_membership_url($kind) : wp_login_url();
}
function modsite_catalog_url($args=[]) { return add_query_arg($args,home_url('/')); }
function modsite_terms_text($id,$tax) {
    $terms=get_the_terms($id,$tax);
    return $terms&&!is_wp_error($terms) ? implode(', ',wp_list_pluck($terms,'name')) : '';
}
function modsite_render_sidebar($position) {
    if ($position==='left') { ?>
      <aside class="left-sidebar" id="game-sidebar" aria-label="Gry i kategorie">
        <div class="side-title">ODKRYWAJ</div><a class="side-item" href="<?php echo esc_url(home_url('/')); ?>">Wszystkie modyfikacje</a>
        <div class="sidebar-divider"></div><div class="side-title">WYBIERZ GRĘ</div>
        <?php $terms=get_terms(['taxonomy'=>'modsite_game','hide_empty'=>true]);
        if (!is_wp_error($terms)) { foreach ($terms as $term) { echo '<a class="side-item" href="'.esc_url(modsite_catalog_url(['mod_game'=>$term->slug])).'">'.esc_html($term->name).'<span class="count">'.absint($term->count).'</span></a>'; } } ?>
        <div class="sidebar-divider"></div><div class="side-title">KATEGORIE</div>
        <?php $cats=get_terms(['taxonomy'=>'modsite_category','hide_empty'=>true]);
        if (!is_wp_error($cats)) { foreach ($cats as $term) { echo '<a class="side-item" href="'.esc_url(modsite_catalog_url(['mod_category'=>$term->slug])).'">'.esc_html($term->name).'</a>'; } }
        dynamic_sidebar('modsite-left'); ?>
      </aside>
    <?php } else { ?>
      <aside class="right-sidebar" aria-label="Subskrypcja i panele">
        <section class="membership-panel"><div class="membership-top">MODSITE MEMBERSHIP</div><h2>Więcej z gry.<br><em>W jednym miejscu.</em></h2><p>Plan miesięczny z dostępem do linków do pobrania.</p><ul><li>✓ Linki do modyfikacji</li><li>✓ Aktualizacje w katalogu</li><li>✓ Instrukcje od autora</li></ul><a class="button gold wide" href="<?php echo esc_url(modsite_theme_url('levels')); ?>">Zobacz subskrypcję →</a><div class="membership-meta">Plan miesięczny · PayPal</div></section>
        <?php dynamic_sidebar('modsite-right'); ?>
      </aside>
    <?php }
}
function modsite_render_mod_card() {
    $id=get_the_ID();$game=modsite_terms_text($id,'modsite_game');$category=modsite_terms_text($id,'modsite_category'); ?>
    <article class="mod-card">
      <a class="mod-image" href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr('Zobacz: '.get_the_title()); ?>">
        <?php if (has_post_thumbnail()) { the_post_thumbnail('medium_large',['loading'=>'lazy']); } else { ?><img src="<?php echo esc_url(get_template_directory_uri().'/assets/placeholder.svg'); ?>" alt="" loading="lazy"><?php } ?>
      </a>
      <div class="mod-content"><div class="mod-game"><?php echo esc_html($game); ?></div><div class="mod-title-row"><a class="mod-title" href="<?php the_permalink(); ?>"><?php the_title(); ?></a></div><p class="mod-description"><?php echo esc_html(wp_trim_words(wp_strip_all_tags(get_the_excerpt()),24)); ?></p><div class="mod-meta"><span><?php echo esc_html($category); ?></span><?php foreach (['version','file_size'] as $field) { $v=get_post_meta($id,'_modsite_'.$field,true);if ($v) { echo '<span class="dot"></span><span>'.esc_html(($field==='version'?'v':'').$v).'</span>'; } } ?></div><div class="mod-bottom"><span class="access-label">DOSTĘP W SUBSKRYPCJI</span><a class="detail-link" href="<?php the_permalink(); ?>">Zobacz mod →</a></div></div>
    </article>
<?php }
