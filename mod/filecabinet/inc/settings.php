<?php
  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

$settings = array('base_doc_directory'   => PHPWS_HOME_DIR . 'files/filecabinet/',
                  'max_image_dimension'  => 1400,
                  'max_image_size'       => 1000000,
                  'max_document_size'    => 5000000,
                  'max_multimedia_size'  => 10000000,
                  'max_pinned_images'    => 0,
                  'max_pinned_documents' => 0,
                  'default_mm_width'     => 320,
                  'default_mm_height'    => 240,
                  'multimedia_thumbnail' => false,
                  'auto_link_parent'     => true,
                  'classify_directory'   => PHPWS_HOME_DIR . 'files/filecabinet/incoming/',
                  'crop_threshold'       => 20,
                  'use_ffmpeg'           => 0,
                  'ffmpeg_directory'     => '',
                  'caption_images'       => 0,
                  'classify_file_type'   => 1,
                  'image_files'          => 'png,gif,jpg,jpeg',
                  'media_files'          => 'swf,flv,mov,avi,mp4,mpg,mp3,wav,wmv',
                  'document_files'       => 'txt,doc,docx,mp3,pdf,ppt,pptx,rtf,tgz,xls,xlsx,zip,jpg,png',
                  'popup_image_navigation' => 0,
                  'max_thumbnail_size'   => 100,
                  'use_jcarousel'        => true,
                  'vertical_folder'      => false,
                  'number_visible'       => 3
                  );
?>