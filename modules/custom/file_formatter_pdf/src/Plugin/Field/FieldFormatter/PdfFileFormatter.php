<?php



/**

 * @file

 * Contains Drupal\file_formatter_pdf\Plugin\Field\FieldFormatter\PdfFileFormatter.

 */

namespace Drupal\file_formatter_pdf\Plugin\Field\FieldFormatter;



use Drupal\Core\Field\FieldItemListInterface;

use Drupal\Core\Form\FormStateInterface;

use Drupal\file\Plugin\Field\FieldFormatter\FileFormatterBase;

use Drupal\Core\File;



/**

 * Plugin implementation of the 'file_formatter_pdf' formatter.

 *

 * @FieldFormatter(

 *   id = "file_formatter_pdf_embeded_pdf",

 *   label = @Translation("File Embed PDF"),

 *   field_types = {

 *     "file"

 *   }

 * )

 */

class PdfFileFormatter extends FileFormatterBase {

  /**

   * {@inheritdoc}

   */

  public static function defaultSettings() {

    return array(

      // Implement default settings.

    ) + parent::defaultSettings();

  }



  /**

   * {@inheritdoc}

   */

  public function settingsForm(array $form, FormStateInterface $form_state) {

    return array(

      // Implement settings form.

    ) + parent::settingsForm($form, $form_state);

  }



  /**

   * {@inheritdoc}

   */

  public function settingsSummary() {

    $summary = [];

    // Implement settings summary.

    return $summary;

  }



  /**

   * {@inheritdoc}

   */

  public function viewElements(FieldItemListInterface $items, $langcode) {

    $elements = array();



    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $file) {

      $entity = $this->fieldDefinition->getTargetEntityTypeId();

      $bundle = $this->fieldDefinition->getTargetBundle();

      $field_name = $this->fieldDefinition->getName();

      $field_type = $this->fieldDefinition->getType();

      $file_uri = $file->getFileUri();

      $filename = $file->getFileName();

      $uri_scheme = \Drupal\Core\StreamWrapper\StreamWrapperManager::getScheme($file_uri);



      if ($uri_scheme == 'public') {

        $url = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());

        $elements[$delta] = array(

          '#theme' => 'file_formatter_pdf',

          '#url' => $url,

          '#filename' => $filename,

          '#delta' => $delta,

          '#entity' => $entity,

          '#bundle' => $bundle,

          '#field_name' => $field_name,

          '#field_type' => $field_type,

          '#attached' => array(

            'library' => array(

              'file_formatter_pdf/file_formatter_pdf',

            ),

          ),

        );



      }

      else {

        \Drupal::messenger()->addError(t('The file (%file) is not publicly accessible. It must be publicly available in order for the Google Docs viewer to be able to access it.',

          array('%file' => $filename)

          ));

      }

    }



    return $elements;

  }



}

