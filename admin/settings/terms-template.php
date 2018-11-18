<?php

namespace NikolayS93\Queries;

$lorem = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Possimus ex voluptatum quo ducimus suscipit facere vitae beatae placeat temporibus quod libero voluptas, architecto, consectetur magnam commodi eum, vel et! Hic!
Numquam rerum, sunt rem officia similique, doloremque obcaecati. Assumenda non esse, magni inventore nulla veritatis vel, itaque eveniet qui veniam, sapiente velit voluptatum consequuntur dolorum quia animi accusamus recusandae ratione.
Deserunt architecto, totam iusto vero quas, ut molestiae, harum, odit cupiditate exercitationem reprehenderit ea soluta. Eius velit quo, cupiditate quasi, soluta, eos odio possimus molestias recusandae accusamus expedita aut libero.
Non sint nihil ea iure consectetur libero veniam at deserunt facere, dolore reiciendis quidem omnis vero, aperiam laboriosam sed voluptate, fugiat alias accusamus sunt dolorem eveniet obcaecati in tempore. Iure!
Eaque unde libero consequatur aliquam? Optio, magni id in libero corrupti nisi voluptate ex ipsum, voluptatibus accusantium esse minus. Eveniet voluptate quae, perferendis totam voluptatum sequi, omnis labore blanditiis id.';

$data = array(
    array(
        'field_id' => 'show_title',
        'id'          => $args['widget']->get_field_id( 'show_title' ),
        'name'        => $args['widget']->get_field_name( 'show_title' ),
        'type'        => 'checkbox',
        'label'       => __( 'Display Widget Title', DOMAIN ),
        ),
    array(
        'field_id' => 'hierarchical',
        'id'    => $args['widget']->get_field_id( 'hierarchical' ),
        'name'  => $args['widget']->get_field_name( 'hierarchical' ),
        'type'  => 'checkbox',
        'label' => __( 'Hierarchical', 'advanced-categories-this' ),
        ),
    array(
        'field_id' => 'show_count',
        'id'    => $args['widget']->get_field_id( 'show_count' ),
        'name'  => $args['widget']->get_field_name( 'show_count' ),
        'type'  => 'checkbox',
        'label' => __( 'Display Post Count', 'advanced-categories-this' ),
        ),
    array(
        'field_id' => 'show_desc',
        'id'    => $args['widget']->get_field_id( 'show_desc' ),
        'name'  => $args['widget']->get_field_name( 'show_desc' ),
        'type'  => 'checkbox',
        'label' => __( 'Display Term Description', 'advanced-categories-this' ),
        ),
    array(
        'field_id' => 'desc_length',
        'id'    => $args['widget']->get_field_id( 'desc_length' ),
        'name'  => $args['widget']->get_field_name( 'desc_length' ),
        'type'  => 'number',
        'label' => __( 'Excerpt Length:', 'advanced-categories-this' ),
        'input_class' => 'small-text',
        'custom_attributes' => array(
            'step' => 1,
            'min' => 0,
            ),
        ),
    array(
        'id'    => 'excerpt-preview',
        'type'  => 'html',
        'value' => '
        <div class="widget-panel-excerptsize-wrap">
            <p>
                '.__( 'Preview:', 'advanced-categories-this' ).'<br />

                <span class="widget-panel-preview-container">
                    <span class="widget-panel-excerpt-preview">
                        <span class="widget-panel-excerpt">
                            '.wp_trim_words( $lorem, 15, '&hellip;' ).'
                        </span>
                        <span class="widget-panel-excerpt-sample" aria-hidden="true" role="presentation">
                            '.$lorem.'
                        </span>
                    </span>
                </span>
            </p>
        </div>',
        ),
);

return $data;
