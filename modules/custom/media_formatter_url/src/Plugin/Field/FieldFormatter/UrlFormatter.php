<?php



namespace Drupal\media_formatter_url\Plugin\Field\FieldFormatter;



use Drupal\Core\Field\FormatterBase;

use Drupal\Core\Field\FieldItemListInterface;

use Drupal\media\Entity\Media;

use Drupal\file\Entity\File;

use Drupal\Core\Url;



/**

 * Plugin implementation of the 'media_formatter_url_only_url' formatter.

 *

 * @FieldFormatter(

 *   id = "media_formatter_url_only_url",

 *   label = @Translation("Only URL"),

 *   field_types = {

 *     "entity_reference"

 *   }

 * )

 */

class UrlFormatter extends FormatterBase {



  /**

   * {@inheritdoc}

   */

  public function viewElements(FieldItemListInterface $items, $langcode) {

    $elements = [];



    foreach ($items as $delta => $item) {



      // Get the media item.

      $media_id = $item->getValue()['target_id'];

      $media_item = Media::load($media_id);



	  //var_dump($media_item->field_media_image->getValue()[0]['target_id']);



      // Get the image. /!\ path différent pour une image que pour un fichier --> White screen 

      $image_id = $media_item->field_media_image->getValue()[0]['target_id'];

      $file = File::load($image_id);

	  

      // Get the URL.

      $uri = $file->getFileUri();

      $url = Url::fromUri(\Drupal::service('file_url_generator')->generateAbsoluteString($uri))->toString();



	  //var_dump($url);

	  

      // Output sans TWIG (NIA)

      $elements[$delta] = [

        '#type'     => 'inline_template',

        '#template' => '{{ url }}',

        '#context'  => [

          'url' => $url,

        ],

      ];

    }



    return $elements;

  }



}