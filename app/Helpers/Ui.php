<?php

namespace App\Helpers;

class Ui
{
  
  /**
   * @param $buttons
   * @return string
   */
  public static function actionButtons( $buttons ) : string
  {
    $wrapper = '<div class="btn-group btn-group-xs btn-actions">{buttons}</div>';
    $link = '<a data-attributes>{label}</a>';
    $output = '';
    foreach( $buttons as $button ) {
      
      $label = $button[ 'label' ];
      
      if( empty( $button[ 'class' ] ) ) {
        $button[ 'class' ] = '';
      }
      
      $button[ 'class' ] = 'btn ' . $button[ 'class' ];
      unset( $button[ 'label' ] );
      $attributes = [];
      foreach( $button as $key => $value ) {
        $attributes[] = $key . '="' . $value . '"';
      }
      $output .= str_replace( [ 'data-attributes', '{label}' ], [ implode( ' ', $attributes ), $label ], $link );
    }
    
    $output = str_replace( '{buttons}', $output, $wrapper );
    
    return $output;
  }
  
  /**
   * @return array
   */
  public static function colors() : array
  {
    return [
      '#059BFF',
      '#FF6384',
      '#FF9F40',
      '#4BC0C0',
      '#009688',
      '#ff9800',
      '#ff5722',
      '#795548',
      '#9e9e9e',
    ];
  }
}
