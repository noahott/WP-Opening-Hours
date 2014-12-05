<?php
/**
 *  Opening Hours: Module: Widget: FieldRenderer
 *
 *  Module class with methods to render widget form fields.
 */

namespace OpeningHours\Module\Widget;

use OpeningHours\Module\AbstractModule;

use WP_Widget;
use InvalidArgumentException;

class FieldRenderer extends AbstractModule {

  /**
   *  Valid Field Types
   *  sequencial array of strings w/ valid field types
   *
   *  @access     protected
   *  @static
   *  @type       array
   */
  protected static $validFieldTypes = array( 'text', 'date', 'time', 'email', 'url', 'textarea', 'select' );

  /**
   *  Render Field
   *  renders the widget form field and returns markup as string
   *
   *  @access     public
   *  @static
   *  @param      WP_Widget   $widget
   *  @param      string      $field_name
   */
  public static function renderField ( WP_Widget $widget, $field_name ) {

    $field  = $widget->getField( $field_name );

    try {
      $field  = self::validateFuield( $field, $widget );

    } catch ( InvalidArgumentException $e ) {
      add_admin_notice( $e->getMessage(), 'error' );
      return;

    }

    extract( $field );

    ob_start();

    /** Start of Field Element */
    echo '<p>';

      /** Field Label */
      if ( isset( $caption ) and !empty( $caption ) )
        echo '<label for="'. $wp_id .'">' . $caption . '</label>';

      switch ( $type ) :

        /** Field Types: text, date, time, 'email', 'url' */
        case 'text' :
        case 'date' :
        case 'time' :
        case 'email' :
        case 'url' :
          echo '<input class="widefat" type="'. $type .'" id="'. $wp_id .'" name="'. $wp_name .'" value="'. $value .'" />';
          break;

        /** Field Type: textarea */
        case 'textarea' :
          echo '<textarea class="widefat" id="'. $wp_id .'" name="'. $wp_name .'">' . $value . '</textarea>';
          break;

        /** Field Type: select */
        case 'select' :
          echo '<select class="widefat" id="'. $wp_id .'" name="'. $wp_name .'">';

          foreach ( $options as $key => $caption ) :
            $selected   = ( $key == $value ) ? 'selected="selected"' : null;
            echo '<option value="'. $key .'" '. $selected .'>'. $caption .'</option>';
          endforeach;

          echo '</select>';
          break;

      endswitch;

    echo '</p>';

  }

  /**
   *  Validate Field
   *  validates and filters widget.
   *
   *  @access     public
   *  @static
   *  @param      array       $field
   *  @param      WP_Widget   $widget
   *  @throws     InvalidArgumentException
   *  @return     array
   */
  public static function validateField ( array $field, WP_Widget $widget ) {

    /**
     *  Validation
     */
    if ( !count( $field ) )
      throw new InvalidArgumentException( sprintf( __( 'Field configuration has to be array. %s given', self::TEXTDOMAIN ), gettype( $field ) ) );

    if ( empty( $field[ 'name' ] ) or !is_string( $field[ 'name' ] ) )
      throw new InvalidArgumentException( __( 'Field name is empty or not a string.', self::TEXTDOMAIN ) );

    if ( !isset( $field[ 'type' ] ) )
      throw new InvalidArgumentException( sprintf( __( 'No Type option set for field %s.', self::TEXTDOMAIN ), '<b>' . $field[ 'name' ] . '</b>' ) );

    if ( !in_array( $field[ 'type' ], self::getValidFieldTypes() ) )
      throw new InvalidArgumentException( sprintf( __( 'Field type %s provided for field %s is not a valid type.', '<b>' . $field[ 'type' ] . '</b>', '<b>' . $field[ 'name' ] . '</b>' ) ) );

    if ( $field[ 'type' ] == 'select' and ( !isset( $field[ 'options' ] ) or !is_array( $field[ 'options' ] ) ) )
      throw new InvalidArgumentException( sprintf( __( 'Field %s with field type select, required the options array.', self::TEXTDOMAIN ), $field[ 'name' ] ) );

    /**
     *  Filter
     */
    $instance   = $widget->getInstance();

    $field[ 'value' ]   = $instance[ $field[ 'name' ] ];
    $field[ 'wp_id' ]   = $widget->get_field_id( $field[ 'name' ] );
    $field[ 'wp_name' ] = $widget->get_field_name( $field[ 'name' ] );

    $field      = apply_filters( 'op_widget_field', $field );
    $field      = apply_filters( 'op_widget_' . $widget->getId() . '_field', $field );

    return $field;

  }

  /**
   *  Terminate
   *  adds error admin notice and throws Exception
   *
   *  @access     protected
   *  @static
   *  @param      string      $message
   *  @param      WP_Widget   $widget
   *  @throws     InvalidArgumentException
   */
  public static function terminate ( $message, WP_Widget $widget ) {

    $notice   = '<b>' . $widget->getTitle() . ':</b>' . $message;

    throw new InvalidArgumentException( $notice );

  }

  /**
   *  Getter: Valid Field Types
   *
   *  @access     public
   *  @static
   *  @return     array
   */
  public static function getValidFieldTypes () {
    return self::$validFieldTypes;
  }

}
?>
