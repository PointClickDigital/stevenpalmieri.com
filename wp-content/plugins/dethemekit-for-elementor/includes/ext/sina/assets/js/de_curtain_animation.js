jQuery( window ).on( 'elementor/frontend/init', () => {
  // const addHandler = ( $element ) => {
  //     elementorFrontend.elementsHandler.addHandler( WidgetHandlerClass, {
  //         $element,
  //     } );
  // };

  const addHandler = ( $element ) => {
    if ( $element.hasClass('de_curtain_animation_yes') ) {
      var $classes = $element.attr('class')
      // console.log($classes)
      var $arr_classes = $classes.split(' ')

      var $id, $duration, $color, $direction, $easing

      $id = 'elementor-element-' + $element.data('id')

      jQuery.each($arr_classes, (index, value) => {
        if ( value.search('de_curtain_duration_') === 0 ) {
          $duration = value.replace('de_curtain_duration_','')
        }

        if ( value.search('de_curtain_color_') === 0 ) {
          $color = value.replace('de_curtain_color_','')
        }

        if ( value.search('de_curtain_direction_') === 0 ) {
          $direction = value.replace('de_curtain_direction_','')
        }

        if ( value.search('de_curtain_easing_') === 0 ) {
          $easing = value.replace('de_curtain_easing_','')
        }
      })

      // console.log( 'disini' )
      var $widget_container = $element.find('.elementor-widget-container')

      if ( $widget_container.length > 0 ) {
        var $selector = document.querySelector( classesToDot( $id ) + ' ' + classesToDot( $widget_container.attr('class') ) )

        console.log( $id )
        // console.log( classesToDot( $element.attr('class') ) + ' ' + classesToDot( $widget_container.attr('class') ) )

        var rev12 = new RevealFx($selector);
      
        rev12.reveal({
            bgcolor: $color,
            duration: $duration,
            direction: $direction,
            easing: $easing,
            onStart: function(contentEl, revealerEl) { contentEl.style.opacity = 0; },
            onCover: function(contentEl, revealerEl) { contentEl.style.opacity = 1; },
            onComplete: function(contentEl, revealerEl) { contentEl.style.opacity = 1; },
        });  
      }

    }

    // if ( $element.data('id') === '7ca7f1a') {
    //   var rev12 = new RevealFx(document.querySelector('.elementor-element-7ca7f1a.de_curtain_animation_yes .elementor-image'));
    
    //   rev12.reveal({
    //       bgcolor: '#0000ff',
    //       duration: 700,
    //       direction: 'bt',
    //       onStart: function(contentEl, revealerEl) { contentEl.style.opacity = 0; },
    //       onCover: function(contentEl, revealerEl) { contentEl.style.opacity = 1; }
    //   });  
    // }

    // if ( $element.data('id') === 'a46f6d8') {
    //   var rev12 = new RevealFx(document.querySelector('.elementor-element-a46f6d8.de_curtain_animation_yes .elementor-widget-container'));
    
    //   rev12.reveal({
    //       bgcolor: '#ff0000',
    //       duration: 700,
    //       onStart: function(contentEl, revealerEl) { contentEl.style.opacity = 0; },
    //       onCover: function(contentEl, revealerEl) { contentEl.style.opacity = 1; }
    //   });  
    // }

    // jQuery('.de_curtain_animation_yes').each(
    //   () => {
    //     console.log( 'disini' );
    //     console.log( jQuery(this).hasClass('.de_curtain_animation_yes') );
    //   }
    // )

 };

  elementorFrontend.hooks.addAction( 'frontend/element_ready/global', addHandler );
} );


function classesToDot($classes) {
  var classes = $classes.split(' ');
  var textList = "";
  for (var i = 0; i < classes.length; i++)
  {
      if (classes[i].length > 0) {
          textList += "."+classes[i];
      }
  }

  return textList;
}