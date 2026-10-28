<?php if (!defined('ABSPATH')) { exit; } get_header(); ?>
<main id="main" class="content-page"><?php while (have_posts()) { the_post();$id=get_the_ID(); ?>
<div class="intro-label"><?php echo esc_html(modsite_terms_text($id,'modsite_game')); ?></div><h1><?php the_title(); ?></h1>
<?php if (has_post_thumbnail()) { the_post_thumbnail('large',['class'=>'single-cover']); } ?>
<div class="detail-info"><?php foreach (['version'=>'Wersja moda: ','game_version'=>'Wersja gry: ','file_size'=>'Plik: '] as $key=>$label) { $value=get_post_meta($id,'_modsite_'.$key,true);if ($value) { echo '<span>'.esc_html($label.$value).'</span>'; } } ?></div>
<div class="entry-content"><?php the_content(); ?></div>
<section class="lock-box" aria-label="Pobierz modyfikację">
<?php if (function_exists('modsite_can_download')&&modsite_can_download()) { ?>
  <h2>Gotowy do gry?</h2><p>Przejdziesz na zewnętrzny hosting pliku.</p><a class="button gold" rel="nofollow noopener" referrerpolicy="no-referrer" href="<?php echo esc_url(modsite_download_link($id)); ?>">Przejdź do pobierania →</a>
<?php } else { ?>
  <h2>Odblokuj link do pobrania.</h2><p>Link jest dostępny po zalogowaniu i aktywowaniu subskrypcji Modsite.</p><a class="button gold" href="<?php echo esc_url(modsite_theme_url('levels')); ?>">Zobacz subskrypcję →</a><?php if (!is_user_logged_in()) { ?><p><a href="<?php echo esc_url(modsite_theme_url('login')); ?>">Masz konto? Zaloguj się</a></p><?php } ?>
<?php } ?>
</section><?php } ?></main><?php get_footer(); ?>
