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




<?php
/**
 * Fetches images from ACF Gallery
 * by passing a post ID and quantity
 * 
 * @param  [int] $post_id Current post ID
 * @param  [int] $qty     Number of images to show
 * @return [str]          Returns list of images
 * @author  Ash Davies <ash.davies@outlook.com>
 */
function get_gallery_imgs($post_id,$qty);
	$gallery = get_post($post_id);
	$gallery_id = $gallery->ID;

	if(get_post_meta($post_id)){
		$gallery_meta = get_post_meta($gallery_id);

		$prepend = '<li><img src="';	$append = '"/></li>';

		$gallery_ids = maybe_unserialize($gallery_meta['gallery'][0]);

		$i = 1;
		foreach($gallery_ids as $img){
			$img_arr = wp_get_attachment_image_src($img,'medium');
			$img_url = $img_arr[0];
			
			echo $prepend . $img_url . $append;
			if ($i < $qty) {
				$i++;
			}else{
				return false;
			}
		}
	}else{
		return false;
	}
}
?>





<?php
/**
 * Post URLs to IDs function, supports custom post types - borrowed and modified from url_to_postid() in wp-includes/rewrite.php
 *
 * @author "betterwp.net/wordpress-tips/url_to_postid-for-custom-post-types/"
 */
function convert_url_to_postid($url)
{
	global $wp_rewrite;

	$url = apply_filters('url_to_postid', $url);

	// First, check to see if there is a 'p=N' or 'page_id=N' to match against
	if ( preg_match('#[?&](p|page_id|attachment_id)=(\d+)#', $url, $values) )	{
		$id = absint($values[2]);
		if ( $id )
			return $id;
	}

	// Check to see if we are using rewrite rules
	$rewrite = $wp_rewrite->wp_rewrite_rules();

	// Not using rewrite rules, and 'p=N' and 'page_id=N' methods failed, so we're out of options
	if ( empty($rewrite) )
		return 0;

	// Get rid of the #anchor
	$url_split = explode('#', $url);
	$url = $url_split[0];

	// Get rid of URL ?query=string
	$url_split = explode('?', $url);
	$url = $url_split[0];

	// Add 'www.' if it is absent and should be there
	if ( false !== strpos(home_url(), '://www.') && false === strpos($url, '://www.') )
		$url = str_replace('://', '://www.', $url);

	// Strip 'www.' if it is present and shouldn't be
	if ( false === strpos(home_url(), '://www.') )
		$url = str_replace('://www.', '://', $url);

	// Strip 'index.php/' if we're not using path info permalinks
	if ( !$wp_rewrite->using_index_permalinks() )
		$url = str_replace('index.php/', '', $url);

	if ( false !== strpos($url, home_url()) ) {
		// Chop off http://domain.com
		$url = str_replace(home_url(), '', $url);
	} else {
		// Chop off /path/to/blog
		$home_path = parse_url(home_url());
		$home_path = isset( $home_path['path'] ) ? $home_path['path'] : '' ;
		$url = str_replace($home_path, '', $url);
	}

	// Trim leading and lagging slashes
	$url = trim($url, '/');

	$request = $url;
	// Look for matches.
	$request_match = $request;
	foreach ( (array)$rewrite as $match => $query) {
		// If the requesting file is the anchor of the match, prepend it
		// to the path info.
		if ( !empty($url) && ($url != $request) && (strpos($match, $url) === 0) )
			$request_match = $url . '/' . $request;

		if ( preg_match("!^$match!", $request_match, $matches) ) {
			// Got a match.
			// Trim the query of everything up to the '?'.
			$query = preg_replace("!^.+\?!", '', $query);

			// Substitute the substring matches into the query.
			$query = addslashes(WP_MatchesMapRegex::apply($query, $matches));

			// Filter out non-public query vars
			global $wp;
			parse_str($query, $query_vars);
			$query = array();
			foreach ( (array) $query_vars as $key => $value ) {
				if ( in_array($key, $wp->public_query_vars) )
					$query[$key] = $value;
			}

		// Taken from class-wp.php
		foreach ( $GLOBALS['wp_post_types'] as $post_type => $t )
			if ( $t->query_var )
				$post_type_query_vars[$t->query_var] = $post_type;

		foreach ( $wp->public_query_vars as $wpvar ) {
			if ( isset( $wp->extra_query_vars[$wpvar] ) )
				$query[$wpvar] = $wp->extra_query_vars[$wpvar];
			elseif ( isset( $_POST[$wpvar] ) )
				$query[$wpvar] = $_POST[$wpvar];
			elseif ( isset( $_GET[$wpvar] ) )
				$query[$wpvar] = $_GET[$wpvar];
			elseif ( isset( $query_vars[$wpvar] ) )
				$query[$wpvar] = $query_vars[$wpvar];

			if ( !empty( $query[$wpvar] ) ) {
				if ( ! is_array( $query[$wpvar] ) ) {
					$query[$wpvar] = (string) $query[$wpvar];
				} else {
					foreach ( $query[$wpvar] as $vkey => $v ) {
						if ( !is_object( $v ) ) {
							$query[$wpvar][$vkey] = (string) $v;
						}
					}
				}

				if ( isset($post_type_query_vars[$wpvar] ) ) {
					$query['post_type'] = $post_type_query_vars[$wpvar];
					$query['name'] = $query[$wpvar];
				}
			}
		}

			// Do the query
			$query = new WP_Query($query);
			if ( !empty($query->posts) && $query->is_singular )
				return $query->post->ID;
			else
				return 0;
		}
	}
	return 0;
}
?>




<?php 
/**
 * Adds custom classes to WP post links (Prev & next posts links)
 * @return [str] [class to use]
 * @author "css-tricks.com/snippets/wordpress/add-class-to-links-generated-by-next_posts_link-and-previous_posts_link/"
 */
function posts_link_attributes() {
    return 'class="btn"';
}

// ADD CLASSES TO ARCHIVE PAGINATION
add_filter('next_posts_link_attributes', 'posts_link_attributes');
add_filter('previous_posts_link_attributes', 'posts_link_attributes');
?>
