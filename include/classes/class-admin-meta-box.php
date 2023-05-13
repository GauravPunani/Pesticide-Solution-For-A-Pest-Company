<?php

/**
 * Register a meta box using a class.
 */
class GamPageMetaBox
{

    public $meta_query = [];
    /**
     * Constructor.
     */
    public function __construct()
    {
        if (is_admin()) {
            add_action('load-post.php', array($this, 'gam_init_metabox'));
            add_action('load-post-new.php', array($this, 'gam_init_metabox'));
            add_action('save_post', array($this, 'gam_page_save_meta_box'));
        }
    }

    /**
     * Meta box initialization.
     */
    public function gam_init_metabox()
    {
        add_action('add_meta_boxes', array($this, 'gam_page_add_metabox'));
        add_action('save_post',      array($this, 'save_metabox'), 10, 2);
    }

    /**
     * Adds the meta box.
     */
    public function gam_page_add_metabox()
    {
        add_meta_box(
            'ads-type-meta-box',
            __('Landing page Information', 'gam'),
            array($this, 'gam_render_page_metabox'),
            'page',
            'advanced',
            'default'
        );
    }

    /**
     * Fetch page meta.
     */
    public function gam_fetch_landing_page_data($page_id)
    { 
        return [
            'branch' => esc_attr( get_post_meta( $page_id, 'ads_branch_id', true )),
            'provider' => esc_attr( get_post_meta( $page_id, 'ads_provider', true ))
        ];
    }

    /**
     * Search Query for ads.
     */
    public function gam_ads_search_query_string($query, string $key)
    { 
        if(!empty($query)){
            if($query == "all"){
                $meta = array(
                    'key'     => $key,
                    'value'   => '',
                    'compare' => '!='
                );
            }else{
                $meta = array(
                    'key'     => $key,
                    'value'   =>  $query
                );
            }
            return $meta;
        }
    }

    /**
     * Renders the meta box.
     */
    public function gam_render_page_metabox($post)
    { 
        $ads_data = self::gam_fetch_landing_page_data(get_the_ID());
        $branch_id =  $ads_data['branch'];
        $ad_provider =  $ads_data['provider'];
        ?>   
        <div class="hcf_box">
            <style scoped>
                .hcf_box {
                    display: grid;
                    grid-template-columns: max-content 1fr;
                    grid-row-gap: 10px;
                    grid-column-gap: 20px;
                }

                .hcf_field {
                    display: contents;
                }
            </style>
            <p class="meta-options hcf_field">
                <label for="hcf_branch">Select Branch</label>
                <?php
                $branches = (new Branches)->getAllBranches(); ?>
                <select name="ads_branch_id" class="form-control select2-field">
                    <option value="">Select</option>
                    <?php if (is_array($branches) && count($branches) > 0) : ?>
                        <?php foreach ($branches as $branch) : ?>
                            <option value="<?= $branch->id; ?>"
                            <?= (!empty($branch_id) && $branch_id == $branch->id) ? 'selected' : ''; ?>
                            ><?= $branch->location_name; ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <?php
                ?>
            </p>
            <p class="meta-options hcf_field">
                <label for="hcf_ads">Select Ad Type</label>
                <?php
                $provider = (new GamFunctions)->gamlandingPageAdsProvider();?>
                <select name="ads_provider" class="form-control select2-field">
                    <option value="">Select</option>
                    <?php if (is_array($provider) && count($provider) > 0) : ?>
                        <?php foreach ($provider as $k=>$item) : ?>
                            <option value="<?= $k; ?>"
                            <?= (!empty($ad_provider) && $ad_provider == $k) ? 'selected' : ''; ?>
                            ><?= $item; ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <?php
                ?>
            </p>
        </div>
    <?php
    }

    /**
     * Save meta box content.
     *
     * @param int $post_id Post ID
     */
    public function gam_page_save_meta_box( $post_id ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( $parent_id = wp_is_post_revision( $post_id ) ) {
            $post_id = $parent_id;
        }
        $fields = [
            'ads_branch_id',
            'ads_provider'
        ];
        foreach ( $fields as $field ) {
            if ( array_key_exists( $field, $_POST ) ) {
                update_post_meta( $post_id, $field, sanitize_text_field( $_POST[$field] ) );
            }
        }
    }
}

new GamPageMetaBox();
