<?php if (!defined('ABSPATH')) { exit; } get_header(); ?>
<main id="main" class="content-page"><?php while (have_posts()) { the_post(); ?><h1><?php the_title(); ?></h1><div class="entry-content"><?php the_content(); ?></div><?php } ?></main><?php get_footer(); ?>
