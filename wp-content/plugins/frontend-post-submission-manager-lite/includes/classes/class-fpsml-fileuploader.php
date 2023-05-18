<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!!' );
if ( !class_exists( 'FPSML_qqUploadedFileXhr' ) ) {

    /**
     * Handle file uploads via XMLHttpRequest
     */
    class FPSML_qqUploadedFileXhr {

        /**
         * Save the file to the specified path
         * @return boolean TRUE on success
         */
        function save( $path ) {
            $input = fopen( "php://input", "r" );
            $temp = tmpfile();
            $realSize = stream_copy_to_stream( $input, $temp );
            fclose( $input );

            if ( $realSize != $this->getSize() ) {
                return false;
            }

            $target = fopen( $path, "w" );
            fseek( $temp, 0, SEEK_SET );
            stream_copy_to_stream( $temp, $target );
            fclose( $target );

            return true;
        }

        function getName() {
            return $_GET['qqfile'];
        }

        function getSize() {
            if ( isset( $_SERVER["CONTENT_LENGTH"] ) ) {
                return (int) $_SERVER["CONTENT_LENGTH"];
            } else {
                throw new Exception( 'Getting content length is not supported.' );
            }
        }

    }

    /**
     * Handle file uploads via regular form post (uses the $_FILES array)
     */
    class FPSML_qqUploadedFileForm {

        /**
         * Save the file to the specified path
         * @return boolean TRUE on success
         */
        function save( $path ) {

            if ( !move_uploaded_file( $_FILES['qqfile']['tmp_name'], $path ) ) {
                return false;
            }
            return true;
        }

        function getName() {
            return $_FILES['qqfile']['name'];
        }

        function getSize() {
            return $_FILES['qqfile']['size'];
        }

    }

    class FPSML_qqFileUploaders {

        private $allowedExtensions = array();
        private $sizeLimit = 10485760;
        private $file;

        function __construct( array $allowedExtensions = array(), $sizeLimit = 10485760 ) {
            $allowedExtensions = array_map( "strtolower", $allowedExtensions );
            $unallowed_extensions = array( 'php', 'exe', 'ini', 'perl' );
            $exts = array_keys( get_allowed_mime_types() );
            $missed_exts = array( 'JPEG', 'JPG', 'PNG', 'GIF' );
            foreach ( $missed_exts as $m_e ) {
                $exts[] = $m_e;
            }
            $available_exts = array();
            foreach ( $exts as $ext ) {
                $array = explode( '|', $ext );
                foreach ( $array as $a ) {
                    $available_exts[] = $a;
                }
            }
            $count = 0;
            foreach ( $allowedExtensions as $ext ) {
                if ( !in_array( $ext, $available_exts ) ) {
                    unset( $allowedExtensions[$count] );
                }
                $count++;
            }
            $this->allowedExtensions = $allowedExtensions;
            $this->sizeLimit = $sizeLimit;

            $this->checkServerSettings();

            if ( isset( $_GET['qqfile'] ) ) {
                $this->file = new FPSML_qqUploadedFileXhr();
            } elseif ( isset( $_FILES['qqfile'] ) ) {
                $this->file = new FPSML_qqUploadedFileForm();
            } else {
                $this->file = false;
            }
        }

        public function getName() {
            if ( $this->file )
                return $this->file->getName();
        }

        private function checkServerSettings() {
            $postSize = $this->toBytes( ini_get( 'post_max_size' ) );
            $uploadSize = $this->toBytes( ini_get( 'upload_max_filesize' ) );

            if ( $postSize < $this->sizeLimit || $uploadSize < $this->sizeLimit ) {
                $size = max( 1, $this->sizeLimit / 1024 / 1024 ) . 'M';
                die( "{'error':'increase post_max_size and upload_max_filesize to $size'}" );
            }
        }

        private function toBytes( $str ) {
            $val = trim( $str );
            $last = strtolower( $str[strlen( $str ) - 1] );
            $val = floatval( $val );
            switch( $last ) {
                case 'g': $val *= 1024;
                case 'm': $val *= 1024;
                case 'k': $val *= 1024;
            }
            return $val;
        }

        /**
         * Returns array('success'=>true) or array('error'=>'error message')
         */
        function handleUpload( $uploadDirectory, $replaceOldFile = FALSE, $upload_url ) {
            if ( !is_writable( $uploadDirectory ) ) {
                return array( 'error' => esc_html__( "Server error. Upload directory isn't writable.", 'frontend-post-submission-manager-lite' ) );
            }

            if ( !$this->file ) {
                return array( 'error' => esc_html__( 'No files were uploaded.', 'frontend-post-submission-manager-lite' ) );
            }

            $size = $this->file->getSize();

            if ( $size == 0 ) {
                return array( 'error' => esc_html__( 'File is empty', 'frontend-post-submission-manager-lite' ) );
            }

            if ( $size > $this->sizeLimit ) {
                return array( 'error' => esc_html__( 'File is too large', 'frontend-post-submission-manager-lite' ) );
            }

            $pathinfo = pathinfo( $this->file->getName() );
            $filename = $pathinfo['filename'];
            $filename = sanitize_title( $filename );

            $ext = @$pathinfo['extension'];  // hide notices if extension is empty
            if ( !$this->allowedExtensions ) {
                die( 'No script kiddies please' );
            }
            if ( $this->allowedExtensions && !in_array( strtolower( $ext ), $this->allowedExtensions ) ) {
                $these = implode( ', ', $this->allowedExtensions );
                return array( 'error' => 'File has an invalid extension, it should be one of ' . $these . '.' );
            }

            if ( !$replaceOldFile ) {
                /// don't overwrite previous files that were uploaded
                while ( file_exists( $uploadDirectory . $filename . '.' . $ext ) ) {
                    global $fpsml_library_obj;
                    $filename .= $fpsml_library_obj->generate_random_string();
                }
            }

            if ( $this->file->save( $uploadDirectory . $filename . '.' . $ext ) ) {
                $filetype = wp_check_filetype( $filename . '.' . $ext );
                $mime_type = $filetype['type'];
                $file_url = $upload_url . '/' . $filename . '.' . $ext;
                $file_path = $uploadDirectory . $filename . '.' . $ext;
                $attachment = array(
                    'post_mime_type' => $mime_type,
                    'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $filename . '.' . $ext ) ),
                    'post_content' => '',
                    'post_status' => 'inherit',
                    'guid' => $file_url
                );
                require_once( ABSPATH . 'wp-admin/includes/admin.php' );
                $attachment_id = wp_insert_attachment( $attachment, $file_path );
                require_once(ABSPATH . 'wp-admin/includes/image.php');
                $attachment_data = wp_generate_attachment_metadata( $attachment_id, $file_path );
                $check = wp_update_attachment_metadata( $attachment_id, $attachment_data );
                $attachment_date = get_the_date( "U", $attachment_id );
                $attachment_code = md5( $attachment_date );
                $attachment_thumbnail = wp_get_attachment_image_src( $attachment_id );
                global $fpsml_library_obj;
                $attachment_size = $fpsml_library_obj->format_file_size( $size );
                if ( wp_attachment_is( 'audio', $attachment_id ) ) {
                    $media_type = 'audio';
                } else if ( wp_attachment_is( 'video', $attachment_id ) ) {
                    $media_type = 'video';
                } else if ( wp_attachment_is( 'image', $attachment_id ) ) {
                    $media_type = 'image';
                } else {
                    $media_type = 'others';
                }


                $media_details = array( 'success' => true,
                    'media_id' => $attachment_id,
                    'media_key' => $attachment_code,
                    'media_name' => $filename,
                    'media_size' => $attachment_size,
                    'media_type' => $media_type,
                    'media_full_url' => $file_url,
                    'media_extension' => $ext
                );
                if ( $attachment_thumbnail ) {
                    $media_details['media_url'] = $attachment_thumbnail[0];
                } else {
                    $media_details['media_url'] = FPSML_URL . '/assets/images/no-preview.jpg';
                }
                return $media_details;
            } else {
                return array( 'error' => esc_html__( 'Could not save uploaded file.The upload was cancelled, or server error encountered', 'frontend-post-submission-manager-lite' ) );
            }
        }

    }

}