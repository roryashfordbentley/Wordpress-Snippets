<?php 

/**
 * Please use Docblock comments styles and 
 * leave 5 lines between entries.
 * I'll add categorisation and indexing
 * once this becomes large.
 */

?>


<?php
/**
 * Returns array of sub categories, if there
 * are no sub cats it returns the posts.
 * Works great with Taxonomies!
 *
 * @author Rory Ashford <rory@roikles.com>
 */
				
// First get the current term
$current_term = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );

// Set the args for get_categories

$args = array(
    'child_of' 			=> $current_term->term_id,
    'parent'	 		=>$current_term->term_id,
    'taxonomy' 			=> $current_term->taxonomy,
	'hide_empty' 		=> 0,
	'hierarchical' 		=> 1,
	'depth'  			=> 1,
	'title_li' 			=> '',
	'echo' 				=> false,
	'show_option_none' 	=> '',
    );
$cats = get_categories( $args );
?>

<?php // If $cats [object] is not empty display sub cats ?>

<?php if(!empty($cats)): ?>
	
	<ul>
	
	<?php foreach($cats as $cat): ?>
		
		<?php // get_term_link requires the cat object ?>
		
		<li><a href="<?php echo get_term_link( $cat ); ?>"><?php echo $cat->name; ?></a></li>
	
	<?php endforeach; ?>

	</ul>

<?php // Otherwise if $cats [object] is empty display posts ?>

<?php else: ?>

	<?php if ( have_posts() ): while ( have_posts() ) : the_post(); ?>
		
		<article>	
			<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		</article>
	
	<?php endwhile; endif; ?>	

<?php endif; ?>





<?php
/**
 * Create a custom Post Type
 * Much better than using a plugin
 * @author Rory Ashford <rory@roikles.com>
 */

register_post_type(
	'articles', array(	
		'label' 			 => 'Articles',
		'description' 		 => 'Add articles to the site.',
		'public' 			 => true,
		'show_ui' 			 => true,
		'show_in_menu' 		 => true,
		'capability_type' 	 => 'post',
		'hierarchical' 		 => true,
		'rewrite' 			 => array(
			'slug' 				=> '',
			'with_front' 		=> '0'
		),
		'query_var' 		 => true,
		'has_archive' 		 => true,
		'supports' 			 => array(
			'title',
			'editor',
			'revisions',
			'thumbnail',
			'author',
			'page-attributes',
		),
		'taxonomies' 		=> array(
			'post_tag',
			'sections',
		),
		'labels' 			=> array(
			'name' 				 => 'Articles',
			'singular_name' 	 => 'Article',
			'menu_name' 		 => 'Articles',
			'add_new' 			 => 'Add Article',
			'add_new_item' 		 => 'Add New Article',
			'edit' 				 => 'Edit',
			'edit_item' 		 => 'Edit Article',
			'new_item' 			 => 'New Article',
			'view' 				 => 'View Article',
			'view_item' 		 => 'View Article',
			'search_items' 		 => 'Search Articles',
			'not_found' 		 => 'No Articles Found',
			'not_found_in_trash' => 'No Articles Found in Trash',
			'parent' 			 => 'Parent Article'
		),
	) 
);
?>





<?php
/**
 * Create a custom Taxonomy
 * Much better than using a plugin
 * @author Rory Ashford <rory@roikles.com>
 */

register_taxonomy(
	'sections',array (
  		0 				=> 'articles',
	),array( 
		'hierarchical'   => true,
		'label' 		 => 'Sections',
		'show_ui' 		 => true,
		'query_var' 	 => true,
		'rewrite' 		 => array(
			'slug' 			=> '',
			'hierarchical' 	=> true
		),
		'singular_label' => 'Section'
	) 
);

?>






<?php 
/**
 * Get lowest child taxonomy that the current post 
 * belongs to and loop through the titles of any posts
 * belonging to it
 * @author  Rory Ashford <rory@roikles.com>
 * @author  Ash Davies <ash.davies@outlook.com>
 * 
 */

$taxonomy = 'sections';
$tax_terms = get_the_terms( $post->ID,$taxonomy );

foreach ($tax_terms as $tax_term){
    $args = array( 'child_of'=> $tax_term->term_id );
    //get all child of current term
    $child = get_terms( $taxonomy, $args );
    if( $tax_term->parent != '0' && count($child) =='0'){
		$parent_section = $tax_term;

		// Assign args for posts with assigned parent term_id
		$args = array( 
			'post_type' => 'articles',
			$taxonomy => $parent_section->slug
			);

		$sections = new WP_Query($args);

		// The Loop
		if ( $sections->have_posts() ) {
			while ( $sections->have_posts() ) {
				$sections->the_post();
				echo '<li>' . get_the_title() . '</li>';
			}
		} else {
			// no posts found
		}
		/* Restore original Post Data */
		wp_reset_postdata();
    }
}
?>
