<?php
/**
 * Register the custom post type for magazine issues
 */

class YDN_Mag_Issue_Type {

  //store a reference to the instance
  //there's only one instance that gets shared whenever it's called
  protected static $instance = NULL;

  //constant variables for the issue type
  const type_slug = 'issue';
  const num_elts_selected = 20; //number to pull into each drop down
  const week_range = 2; //number of weeks before/after pub date to pull into drop downs
  const metadata_key = "ydn_mag_issue";

  //function to create/grab the instance of the class
  //it's a class level function
  public static function get_instance() {
    NULL  === self::$instance and self::$instance = new self;
    return self::$instance;
  }

  public static function get_content($issue_id) {
    define('SAVEQUERIES',true);
    //returns an array of the content matching $issue_ido

    //content_ids is a nested array that dictates which IDs go with which content_types
    $content_ids = get_post_meta($issue_id, self::metadata_key, true);
    //id_to_obj -- an array mapping ids to their objects
    $id_to_obj = array();
    //flat_ids -- an array of just ids
    $flat_ids = array();
    //content -- a nested array mapping content_types to their actual content objects
    $content = array();

    //first we need to build a flat array of IDs so we can query for them in one hit
    foreach ($content_ids as $ids_for_type) {
      $flat_ids = array_merge($ids_for_type, $flat_ids);
    }

    //grab the objects and then build a map of IDs to the post ojects
    $query = new WP_Query(array( 'post__in' => $flat_ids, 'posts_per_page' => 20));
    foreach($query->posts as $post) {
      $id_to_obj[$post->ID] = $post;
    }

    //finally build up content, piece by piece, from the original multidimensional array
    foreach ($content_ids as $content_type => $ids) {
      foreach ($ids as $id) {
        if(array_key_exists($id,$id_to_obj)) {
          $content[$content_type][] = $id_to_obj[$id];
        }
      }
    }

    return($content);
  }

  public function init() {
    //bind actions
    $this->register_post_type();
    add_action('add_meta_boxes', array($this, 'register_meta_boxes'));
    add_action('save_post', array($this, 'save_issue_metadata'));
    $this->content_types = array( "top_content" => array("title" => "Top Content", "num" => 4),
                                  "essays" => array("title" => "Essays", "num" => 3),
                                  "small_talk" => array("title" => "Small Talk", "num" => 3),
                                  "shorts" => array("title" => "Shorts", "num" => 4),
                                  "poetry" => array("title" => "Poetry", "num" => 4),
                                  "photo_essay" => array("title" => "Photo Essay", "num" => 1) );

    add_image_size('magazine_cover_image',390,500,true);
    add_image_size('magazine_top_long',550,153,true);
    add_image_size('magazine_bottom_long',710,225,true);
    add_image_size('magazine_span4',148,135,true);
    add_image_size('magazine_small_talk',230,100,true);

  }

  public function register_post_type() {
      $labels = array(
          'name' => _x('Issues', 'ydn'),
          'singular_name' => _x('Issue', 'ydn'),
          'add_new' => _x('Add New', 'ydn'),
          'add_new_item' => __('Add New Issue'),
          'edit_item' => __('Edit Issue'),
          'new_item' => __('New Issue'),
          'all_items' => __('All Issues'),
          'view_item' => __('View Issue'),
          'search_items' => __('Search Issues'),
          'not_found' =>  __('No issues found'),
          'not_found_in_trash' => __('No issues found in Trash'),
          'parent_item_colon' => '',
          'menu_name' => __('Issues')
        );
      $args = array(
            'labels' => $labels,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => true,
            'rewrite' => true,
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => array( 'title' )
      );
      register_post_type(self::type_slug,$args);
      flush_rewrite_rules(); //magic sauce that makes permalinks work
  }

  public function register_meta_boxes() {
    add_meta_box("cover-photo", "Cover Photo", array($this, 'draw_meta_box_cover'), self::type_slug, 'normal', 'default');

    foreach ($this->content_types as $id => $meta) {
      add_meta_box($id,$meta["title"], array($this, 'draw_meta_box'), self::type_slug, 'normal', 'default', array($meta["num"]));
    }
  }

  public function draw_meta_box_cover($post) {

  }

  public function draw_meta_box($post,$args) {
    //used to draw all of the metaboxes on the admin page

    wp_nonce_field(plugin_basename(__FILE__), 'ydnmag_issue_nonce');

    $this->post = $post;
    if (count($args['args']) != 1) {
      //this should never happen
      die('invalid number of arguments passed');
    }
    $num_elts = $args['args'][0];
    $content_type = $args['id'];
    $this->fetch_story_list();
    $this->fetch_current_meta();

    for($i = 0; $i < $num_elts; $i++) {
      $field_name = "ydn_issue_{$content_type}_{$i}";
      ?>
      <div>
        <label for="<?php echo $field_name; ?>">Element <?php echo $i + 1; ?>:</label>
        <?php
          if (count($this->current_meta) > 1) {
            //there's data already saved, extract the current type
            $current_type = $this->current_meta[$content_type][$i];
          } else {
            $current_type = NULL;
          }
          $this->create_dropdown($field_name, $current_type);
        ?>
      </div>
      <?php
    }
  }

  public function save_issue_metadata($post_id) {
    $_POST += array("{self::type_slug}_edit_nonce" => '');
    if ($_POST['post_type'] != self::type_slug) {
      return;
    }
    if (!current_user_can('edit_post', $post_id)) {
      return;
    }
    if(!wp_verify_nonce($_POST["ydnmag_issue_nonce"],plugin_basename(__FILE__))) {
      return;
    }

    $issue_vars = array(); //will build an array of IDs for the content types and then save it as a single meta

    foreach($this->content_types as $id => $type_meta) {
      $num = $type_meta["num"];
      $issue_vars[$id] = array();
      for($i = 0; $i < $num; $i++) {
        $issue_vars[$id][$i] = (int) $_REQUEST["ydn_issue_{$id}_{$i}"];
      }
    }

    update_post_meta($post_id, self::metadata_key, $issue_vars);

  }

  private function fetch_story_list() {
    if (isset($this->story_list)) { return; } //value is already chached

    //gets a content_id and fetches the stories within the week_range from pub date
    $query_args = array( 'posts_per_page' => YDN_Mag_Issue_Type::num_elts_selected );
    add_filter('posts_where', array($this, 'fetch_content_where_filter'));
    $results = new WP_Query($query_args);
    $this->story_list = $results->posts;
    remove_filter('posts_where', array($this, 'fetch_content_where_filter'));
  }

  private function fetch_iamge_list() {
    if(isset($this->image_list)) { return; }
    $query_args = array( 'posts_per_page' => YDN_Mag_Issue_Type::num_elts_selected, 'post_type' => 'attachment' );
    add_filter('posts_where', array($this, 'fetch_content_where_filter'));
    $results = new WP_Query($query_args);
    $this->image_list = $results->posts;
    remove_filter('posts_where', array($this, 'fetch_content_where_filter'));
 }

  private function fetch_current_meta() {
    if (isset($this->current_meta)) { return; }
    $this->current_meta = get_post_meta($this->post->ID,self::metadata_key,true);
  }

  public function fetch_content_where_filter($where = '') {
    //necessary to allow WP to select posts published +- week_range weeks from pub date
    $current_time = strtotime($this->post->post_date);
    $start_date = strtotime( '-' . YDN_Mag_Issue_Type::week_range . " weeks", $current_time);
    $end_date = strtotime( '+' . YDN_Mag_Issue_Type::week_range . " weeks", $current_time);
    $where .= " AND post_date between '" . date('Y-m-d', $start_date) . "' AND '" . date('Y-m-d',$end_date). "'";
    return $where;
  }


  private function create_dropdown($name, $post_id) {
    //renders a drop down, setting its value to $post_id unless nothing is passed
    $post_id = (int) $post_id;
    echo "<select id=\"{$name}\" name=\"{$name}\">";
    echo "<option value=\"-1\">--------</option>";
    foreach($this->story_list as $post) {
     if ($post->ID == $post_id) {
       $selected = " selected=\"selected\" ";
     } else {
       $selected = "";
     }
     echo "<option value=\"{$post->ID}\" {$selected} >" . $post->post_title . "</option>";
    }
    echo "</select>";
  }

}

add_action('init', array(YDN_Mag_Issue_Type::get_instance(), 'init'));

?>
