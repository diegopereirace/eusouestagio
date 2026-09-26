<?php

/**
 * @file
 * Setup estrutural feature 020 — content type depoimento + View + placement.
 *
 * Uso: docker compose exec -T drupal vendor/bin/drush php:script
 *   modules/custom/custom_configs/scripts/setup_depoimentos_020.php
 *
 * Depois: drush cex -y
 */

use Drupal\block\Entity\Block;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\NodeType;
use Drupal\views\Entity\View;

$messages = [];

// --- Node type ---
if (!NodeType::load('depoimento')) {
  $type = NodeType::create([
    'type' => 'depoimento',
    'name' => 'Depoimento',
    'description' => 'Depoimentos de clientes/empresas para prova social B2B',
    'new_revision' => FALSE,
    'preview_mode' => DRUPAL_OPTIONAL,
    'display_submitted' => FALSE,
  ]);
  $type->setThirdPartySetting('menu_ui', 'available_menus', []);
  $type->setThirdPartySetting('menu_ui', 'parent', '');
  $type->save();
  $messages[] = 'Created node type depoimento.';
}
else {
  $messages[] = 'Node type depoimento already exists.';
}

// --- Field instances (reuse storages) ---
$field_defs = [
  'field_text_simple' => [
    'label' => 'Cargo / Empresa',
    'description' => 'Ex.: Head de Talentos - TechCorp',
    'settings' => [],
  ],
  'field_text_simple_long' => [
    'label' => 'Texto do depoimento',
    'description' => 'Corpo do depoimento exibido no card.',
    'settings' => [],
  ],
  'field_imagem' => [
    'label' => 'Foto do autor',
    'description' => 'Avatar do autor (exibido circular no carrossel).',
    'settings' => [
      'handler' => 'default:file',
      'handler_settings' => [],
      'file_directory' => 'depoimentos/[date:custom:Y]-[date:custom:m]',
      'file_extensions' => 'png gif jpg jpeg webp',
      'max_filesize' => '',
      'max_resolution' => '',
      'min_resolution' => '',
      'alt_field' => TRUE,
      'alt_field_required' => FALSE,
      'title_field' => TRUE,
      'title_field_required' => FALSE,
      'default_image' => [
        'uuid' => '',
        'alt' => '',
        'title' => '',
        'width' => NULL,
        'height' => NULL,
      ],
    ],
  ],
];

foreach ($field_defs as $field_name => $def) {
  if (FieldConfig::loadByName('node', 'depoimento', $field_name)) {
    $messages[] = "Field $field_name already on depoimento.";
    continue;
  }
  $storage = FieldStorageConfig::loadByName('node', $field_name);
  if (!$storage) {
    $messages[] = "MISSING storage $field_name — run cim first.";
    continue;
  }
  FieldConfig::create([
    'field_storage' => $storage,
    'bundle' => 'depoimento',
    'label' => $def['label'],
    'description' => $def['description'],
    'required' => FALSE,
    'translatable' => FALSE,
    'settings' => $def['settings'],
  ])->save();
  $messages[] = "Created field instance $field_name on depoimento.";
}

// --- Form display default ---
$form = EntityFormDisplay::load('node.depoimento.default');
if (!$form) {
  $form = EntityFormDisplay::create([
    'targetEntityType' => 'node',
    'bundle' => 'depoimento',
    'mode' => 'default',
    'status' => TRUE,
  ]);
}
$form->setComponent('title', [
  'type' => 'string_textfield',
  'weight' => 0,
  'region' => 'content',
  'settings' => ['size' => 60, 'placeholder' => 'Nome do autor'],
])->setComponent('field_imagem', [
  'type' => 'image_image',
  'weight' => 1,
  'region' => 'content',
  'settings' => [
    'progress_indicator' => 'throbber',
    'preview_image_style' => 'thumbnail',
  ],
])->setComponent('field_text_simple', [
  'type' => 'string_textfield',
  'weight' => 2,
  'region' => 'content',
  'settings' => ['size' => 60, 'placeholder' => ''],
])->setComponent('field_text_simple_long', [
  'type' => 'string_textarea',
  'weight' => 3,
  'region' => 'content',
  'settings' => ['rows' => 5, 'placeholder' => ''],
])->setComponent('status', [
  'type' => 'boolean_checkbox',
  'weight' => 10,
  'region' => 'content',
  'settings' => ['display_label' => TRUE],
])->setComponent('created', [
  'type' => 'datetime_timestamp',
  'weight' => 8,
  'region' => 'content',
])->setComponent('uid', [
  'type' => 'entity_reference_autocomplete',
  'weight' => 11,
  'region' => 'content',
  'settings' => [
    'match_operator' => 'CONTAINS',
    'size' => 60,
    'placeholder' => '',
  ],
])->setComponent('path', [
  'type' => 'path',
  'weight' => 9,
  'region' => 'content',
])->setComponent('langcode', [
  'type' => 'language_select',
  'weight' => 7,
  'region' => 'content',
  'settings' => ['include_locked' => TRUE],
]);
$form->removeComponent('promote');
$form->removeComponent('sticky');
$form->save();
$messages[] = 'Form display node.depoimento.default saved.';

// --- View display default ---
$view_default = EntityViewDisplay::load('node.depoimento.default');
if (!$view_default) {
  $view_default = EntityViewDisplay::create([
    'targetEntityType' => 'node',
    'bundle' => 'depoimento',
    'mode' => 'default',
    'status' => TRUE,
  ]);
}
$view_default->setComponent('field_imagem', [
  'type' => 'image',
  'label' => 'hidden',
  'weight' => 0,
  'region' => 'content',
  'settings' => [
    'image_link' => '',
    'image_style' => 'thumbnail',
    'image_loading' => ['attribute' => 'lazy'],
  ],
])->setComponent('field_text_simple', [
  'type' => 'string',
  'label' => 'hidden',
  'weight' => 1,
  'region' => 'content',
  'settings' => ['link_to_entity' => FALSE],
])->setComponent('field_text_simple_long', [
  'type' => 'basic_string',
  'label' => 'hidden',
  'weight' => 2,
  'region' => 'content',
]);
$view_default->removeComponent('links');
$view_default->removeComponent('langcode');
$view_default->save();
$messages[] = 'View display node.depoimento.default saved.';

// --- View display teaser ---
$view_teaser = EntityViewDisplay::load('node.depoimento.teaser');
if (!$view_teaser) {
  $view_teaser = EntityViewDisplay::create([
    'targetEntityType' => 'node',
    'bundle' => 'depoimento',
    'mode' => 'teaser',
    'status' => TRUE,
  ]);
}
$view_teaser->setComponent('field_imagem', [
  'type' => 'image',
  'label' => 'hidden',
  'weight' => 0,
  'region' => 'content',
  'settings' => [
    'image_link' => '',
    'image_style' => 'thumbnail',
    'image_loading' => ['attribute' => 'lazy'],
  ],
])->setComponent('field_text_simple', [
  'type' => 'string',
  'label' => 'hidden',
  'weight' => 1,
  'region' => 'content',
  'settings' => ['link_to_entity' => FALSE],
])->setComponent('field_text_simple_long', [
  'type' => 'basic_string',
  'label' => 'hidden',
  'weight' => 2,
  'region' => 'content',
]);
$view_teaser->removeComponent('links');
$view_teaser->removeComponent('langcode');
$view_teaser->save();
$messages[] = 'View display node.depoimento.teaser saved.';

// --- View depoimentos_carousel ---
$view = View::load('depoimentos_carousel');
if (!$view) {
  $view = View::create([
    'id' => 'depoimentos_carousel',
    'label' => 'Depoimentos Carousel',
    'module' => 'views',
    'description' => 'Carrossel de depoimentos para /para-empresas',
    'tag' => '',
    'base_table' => 'node_field_data',
    'base_field' => 'nid',
    'display' => [
      'default' => [
        'id' => 'default',
        'display_title' => 'Default',
        'display_plugin' => 'default',
        'position' => 0,
        'display_options' => [
          'title' => 'Depoimentos',
          'fields' => [],
          'pager' => [
            'type' => 'none',
            'options' => ['offset' => 0],
          ],
          'exposed_form' => [
            'type' => 'basic',
            'options' => [
              'submit_button' => 'Aplicar',
              'reset_button' => FALSE,
              'reset_button_label' => 'Redefinir',
              'exposed_sorts_label' => 'Ordenar por',
              'expose_sort_order' => TRUE,
              'sort_asc_label' => 'Asc',
              'sort_desc_label' => 'Desc',
            ],
          ],
          'access' => [
            'type' => 'perm',
            'options' => ['perm' => 'access content'],
          ],
          'cache' => [
            'type' => 'tag',
            'options' => [],
          ],
          'empty' => [],
          'sorts' => [
            'created' => [
              'id' => 'created',
              'table' => 'node_field_data',
              'field' => 'created',
              'relationship' => 'none',
              'group_type' => 'group',
              'admin_label' => '',
              'entity_type' => 'node',
              'entity_field' => 'created',
              'plugin_id' => 'date',
              'order' => 'DESC',
              'expose' => [
                'label' => '',
                'field_identifier' => '',
              ],
              'exposed' => FALSE,
              'granularity' => 'second',
            ],
          ],
          'arguments' => [],
          'filters' => [
            'status' => [
              'id' => 'status',
              'table' => 'node_field_data',
              'field' => 'status',
              'entity_type' => 'node',
              'entity_field' => 'status',
              'plugin_id' => 'boolean',
              'value' => '1',
              'group' => 1,
              'expose' => ['operator' => ''],
            ],
            'type' => [
              'id' => 'type',
              'table' => 'node_field_data',
              'field' => 'type',
              'entity_type' => 'node',
              'entity_field' => 'type',
              'plugin_id' => 'bundle',
              'value' => ['depoimento' => 'depoimento'],
              'group' => 1,
            ],
          ],
          'filter_groups' => [
            'operator' => 'AND',
            'groups' => [1 => 'AND'],
          ],
          'style' => [
            'type' => 'default',
            'options' => [
              'row_class' => '',
              'default_row_class' => FALSE,
              'uses_fields' => FALSE,
            ],
          ],
          'row' => [
            'type' => 'entity:node',
            'options' => [
              'relationship' => 'none',
              'view_mode' => 'teaser',
            ],
          ],
          'query' => [
            'type' => 'views_query',
            'options' => [
              'query_comment' => '',
              'disable_sql_rewrite' => FALSE,
              'distinct' => FALSE,
              'replica' => FALSE,
              'query_tags' => [],
            ],
          ],
          'relationships' => [],
          'header' => [],
          'footer' => [],
          'display_extenders' => [],
          'css_class' => 'depoimentos-empresas-view',
        ],
      ],
      'block_depoimentos_empresas' => [
        'id' => 'block_depoimentos_empresas',
        'display_title' => 'Depoimentos Para Empresas',
        'display_plugin' => 'block',
        'position' => 1,
        'display_options' => [
          'display_description' => 'Carrossel de depoimentos em /para-empresas',
          'block_description' => 'Depoimentos Para Empresas',
          'defaults' => [
            'css_class' => FALSE,
            'pager' => FALSE,
            'style' => FALSE,
            'row' => FALSE,
            'filters' => FALSE,
            'filter_groups' => FALSE,
            'sorts' => FALSE,
            'empty' => FALSE,
          ],
          'css_class' => 'depoimentos-empresas-view',
          'pager' => [
            'type' => 'none',
            'options' => ['offset' => 0],
          ],
          'empty' => [],
          'style' => [
            'type' => 'default',
            'options' => [
              'row_class' => '',
              'default_row_class' => FALSE,
              'uses_fields' => FALSE,
            ],
          ],
          'row' => [
            'type' => 'entity:node',
            'options' => [
              'relationship' => 'none',
              'view_mode' => 'teaser',
            ],
          ],
          'filters' => [
            'status' => [
              'id' => 'status',
              'table' => 'node_field_data',
              'field' => 'status',
              'entity_type' => 'node',
              'entity_field' => 'status',
              'plugin_id' => 'boolean',
              'value' => '1',
              'group' => 1,
              'expose' => ['operator' => ''],
            ],
            'type' => [
              'id' => 'type',
              'table' => 'node_field_data',
              'field' => 'type',
              'entity_type' => 'node',
              'entity_field' => 'type',
              'plugin_id' => 'bundle',
              'value' => ['depoimento' => 'depoimento'],
              'group' => 1,
            ],
          ],
          'filter_groups' => [
            'operator' => 'AND',
            'groups' => [1 => 'AND'],
          ],
          'sorts' => [
            'created' => [
              'id' => 'created',
              'table' => 'node_field_data',
              'field' => 'created',
              'relationship' => 'none',
              'group_type' => 'group',
              'admin_label' => '',
              'entity_type' => 'node',
              'entity_field' => 'created',
              'plugin_id' => 'date',
              'order' => 'DESC',
              'expose' => [
                'label' => '',
                'field_identifier' => '',
              ],
              'exposed' => FALSE,
              'granularity' => 'second',
            ],
          ],
          'display_extenders' => [],
        ],
      ],
    ],
  ]);
  $view->save();
  $messages[] = 'Created view depoimentos_carousel.';
}
else {
  $messages[] = 'View depoimentos_carousel already exists.';
}

// --- Block placement ---
$block_id = 'default_views_block__depoimentos_carousel_block_depoimentos_empresas';
$plugin = 'views_block:depoimentos_carousel-block_depoimentos_empresas';
$block = Block::load($block_id);
if (!$block) {
  $block = Block::create([
    'id' => $block_id,
    'theme' => 'default',
    'region' => 'content_full',
    'weight' => 4,
    'provider' => NULL,
    'plugin' => $plugin,
    'settings' => [
      'id' => $plugin,
      'label' => 'Depoimentos Para Empresas',
      'label_display' => '0',
      'provider' => 'views',
      'views_label' => '',
      'items_per_page' => NULL,
    ],
    'visibility' => [
      'request_path' => [
        'id' => 'request_path',
        'negate' => FALSE,
        'pages' => '/para-empresas',
      ],
    ],
  ]);
  $block->save();
  $messages[] = 'Created block placement ' . $block_id;
}
else {
  $changed = FALSE;
  if ((int) $block->getWeight() !== 4) {
    $block->setWeight(4);
    $changed = TRUE;
  }
  if ($block->getRegion() !== 'content_full') {
    $block->setRegion('content_full');
    $changed = TRUE;
  }
  $visibility = $block->getVisibility();
  $pages = (string) ($visibility['request_path']['pages'] ?? '');
  if (trim($pages) !== '/para-empresas') {
    $block->setVisibilityConfig('request_path', [
      'id' => 'request_path',
      'negate' => FALSE,
      'pages' => '/para-empresas',
    ]);
    $changed = TRUE;
  }
  if ($changed) {
    $block->save();
    $messages[] = 'Updated block placement ' . $block_id;
  }
  else {
    $messages[] = 'Block placement already ok.';
  }
}

// --- CTA PE weight 4 → 5 ---
$cta = Block::load('default_ctav1paraempresas');
if ($cta) {
  if ((int) $cta->getWeight() !== 5) {
    $cta->setWeight(5);
    $cta->save();
    $messages[] = 'CTA PE weight set to 5.';
  }
  else {
    $messages[] = 'CTA PE weight already 5.';
  }
}
else {
  $messages[] = 'WARNING: default_ctav1paraempresas not found.';
}

echo implode("\n", $messages) . "\n";
