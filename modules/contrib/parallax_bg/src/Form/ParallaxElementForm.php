<?php

namespace Drupal\parallax_bg\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Class ParallaxElementForm. Form handler for parallax element add/edit forms.
 *
 * @package Drupal\parallax_bg\Form
 */
class ParallaxElementForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /** @var \Drupal\parallax_bg\Entity\ParallaxElementInterface $parallax_element */
    $parallax_element = $this->entity;

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Valid jQuery selector'),
      '#maxlength' => 255,
      '#default_value' => $parallax_element->label(),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $parallax_element->id(),
      '#machine_name' => [
        'exists' => '\Drupal\parallax_bg\Entity\ParallaxElement::load',
      ],
      '#disabled' => !$parallax_element->isNew(),
    ];

    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#default_value' => $parallax_element->getDescription(),
    ];

    $position = $parallax_element->getPosition();

    $form['position'] = [
      '#type' => 'select',
      '#title' => $this->t('Position'),
      '#default_value' => $position ?? '50%',
      '#options' => [
        '0' => $this->t('Left'),
        '50%' => $this->t('Center'),
        '100%' => $this->t('Right'),
      ],
    ];

    $speed = $parallax_element->getSpeed();

    $form['speed'] = [
      '#type' => 'select',
      '#title' => $this->t('Relative speed'),
      '#default_value' => $speed ?? '0.1',
      '#options' => array_combine(range(0, 3, 0.1), range(0, 3, 0.1)),
    ];

    $form['status'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Published'),
      '#default_value' => $parallax_element->status(),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);

    $actions['cancel'] = [
      '#type' => 'html_tag',
      '#tag' => 'a',
      '#value' => $this->t('Cancel'),
      '#attributes' => [
        'class' => ['button', 'button--danger'],
        'href' => Url::fromRoute('entity.parallax_element.collection')->toString(),
      ],
    ];

    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\parallax_bg\Entity\ParallaxElementInterface $parallax_element */
    $parallax_element = $this->entity;
    $status = $parallax_element->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addStatus($this->t('Created the %label Parallax element.', [
          '%label' => $parallax_element->label(),
        ]));
        break;

      default:
        $this->messenger()->addStatus($this->t('Saved the %label Parallax element.', [
          '%label' => $parallax_element->label(),
        ]));
    }

    $form_state->setRedirectUrl($parallax_element->toUrl('collection'));
    return $status;
  }

}
