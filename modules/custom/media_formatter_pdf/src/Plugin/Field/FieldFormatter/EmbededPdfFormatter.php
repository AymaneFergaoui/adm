<?php



namespace Drupal\media_formatter_pdf\Plugin\Field\FieldFormatter;



use Drupal\Core\Field\FormatterBase;

use Drupal\Core\Field\FieldItemListInterface;

use Drupal\media\Entity\Media;

use Drupal\file\Entity\File;

use Drupal\Core\Url;



/**

 * Plugin implementation of the 'media_formatter_pdf_embeded_pdf' formatter.

 *

 * @FieldFormatter(

 *   id = "media_formatter_pdf_embeded_pdf",

 *   label = @Translation("Embed PDF"),

 *   field_types = {

 *     "entity_reference"

 *   }

 * )

 */

class EmbededPdfFormatter extends FormatterBase {



  /**

   * {@inheritdoc}

   */

  public function viewElements(FieldItemListInterface $items, $langcode) {

    $elements = [];



    foreach ($items as $delta => $item) {



      // Get the media item.

      $media_id = $item->getValue()['target_id'];

      $media_item = Media::load($media_id);



      // Get the file.

      $file_id = $media_item->field_media_file->getValue()[0]['target_id'];

      $file = File::load($file_id);



      // Get the URL.

      $uri = $file->getFileUri();

      $url = Url::fromUri(\Drupal::service('file_url_generator')->generateAbsoluteString($uri))->toString();



      // Output sans TWIG (NIA)

      $elements[$delta] = [

        '#type'     => 'inline_template',

        '#template' => '<embed height="600" src="{{ url }}" type="application/pdf" width="100%"></embed>',

        '#context'  => [

          'url' => $url,

        ],

      ];



	  // Version avec Twig (NIA)

      //$elements[$delta] = [

      //  '#theme' => 'media_formatter_pdf_embeded_pdf',

      //  '#url'   => $url,

      //];

    }



    return $elements;

  }



}