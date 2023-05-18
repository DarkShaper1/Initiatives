<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!!' );
if ( !empty( $login_settings['login_page_url'] ) ) {
    ?>
    <script>
        window.location = '<?php echo esc_url( $login_settings['login_page_url'] ); ?>';
        exit;
    </script>
    <?php
}