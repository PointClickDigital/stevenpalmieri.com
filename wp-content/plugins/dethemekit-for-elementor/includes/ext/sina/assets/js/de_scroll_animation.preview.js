let animObjects = [];
let deScrollOptions = [];

class DeScrollOption {
  constructor(classes) {
    var arr_classes = classes.split(' ')

    this.active = false;
    this.animation_preview = false;
    this.editMode = false; 
    this.id = '';
    this.transform = 'translateX'; 
    this.distance = '50'; 
    this.duration = '1000', 
    this.direction = 'normal', 
    this.easing = 'linear',

    this.translateX = false,
    this.translateX_distance = '',

    this.translateY = false,
    this.translateY_distance = '',

    this.translateZ = false,
    this.translateZ_distance = '',
    this.translateZ_perspective = '',

		this.rotate = false,
		this.rotate_distance = '',
		
    this.rotateX = false,
    this.rotateX_distance = '',
		
    this.rotateY = false,
    this.rotateY_distance = '',
		
    this.rotateZ = false,
    this.rotateZ_distance = '',
		
    this.scale = false,
    this.scale_distance = '',
		
    this.scaleX = false,
    this.scaleX_distance = '',
		
    this.scaleY = false,
    this.scaleY_distance = '',
		
    this.scaleZ = false,
    this.scaleZ_distance = '',
		
    this.skew = false,
    this.skew_distance = '',

		this.skewX = false,
		this.skewX_distance = '',
		
    this.skewY = false,
    this.skewY_distance = '',

    jQuery.each(arr_classes, (index, value) => {
      if ( value.search('de_scroll_animation_yes') === 0 ) {
        this.active = true;
      }

      if ( value.search('de_scroll_animation_preview_yes') === 0 ) {
        this.animation_preview = true;
      }

      if ( value.search('elementor-element-edit-mode') === 0 ) {
        this.editMode = true;
      }

      if ( value.length === 25 && value.search('elementor-element-') === 0 ) {
        this.id = value.replace('elementor-element-','')
      }

      if ( value.search('de_scroll_transform_') === 0 ) {
        this.transform = value.replace('de_scroll_transform_','')
      }

      if ( value.search('de_scroll_distance_') === 0 ) {
        this.distance = value.replace('de_scroll_distance_','')
      }

      if ( value.search('de_scroll_duration_') === 0 ) {
        this.duration = value.replace('de_scroll_duration_','')
      }

      if ( value.search('de_scroll_easing_') === 0 ) {
        this.easing = value.replace('de_scroll_easing_','')
      }

      if ( 'de_scroll_translateX_popover_checked' === value ) { this.translateX = true }
      if ( 'de_scroll_translateY_popover_checked' === value ) { this.translateY = true }
      if ( 'de_scroll_translateZ_popover_checked' === value ) { this.translateZ = true }
      if ( 'de_scroll_rotate_popover_checked' === value ) { this.rotate = true }
      if ( 'de_scroll_rotateX_popover_checked' === value ) { this.rotateX = true }
      if ( 'de_scroll_rotateY_popover_checked' === value ) { this.rotateY = true }
      if ( 'de_scroll_rotateZ_popover_checked' === value ) { this.rotateZ = true }
      if ( 'de_scroll_scale_popover_checked' === value ) { this.scale = true }
      if ( 'de_scroll_scaleX_popover_checked' === value ) { this.scaleX = true }
      if ( 'de_scroll_scaleY_popover_checked' === value ) { this.scaleY = true }
      if ( 'de_scroll_scaleZ_popover_checked' === value ) { this.scaleZ = true }
      if ( 'de_scroll_skew_popover_checked' === value ) { this.skew = true }
      if ( 'de_scroll_skewX_popover_checked' === value ) { this.skewX = true }
      if ( 'de_scroll_skewY_popover_checked' === value ) { this.skewY = true }

      if ( value.search('de_scroll_translateX_distance_') === 0 ) {
        this.translateX_distance = value.replace('de_scroll_translateX_distance_','')
      }
      if ( value.search('de_scroll_translateY_distance_') === 0 ) {
        this.translateY_distance = value.replace('de_scroll_translateY_distance_','')
      }
      if ( value.search('de_scroll_translateZ_distance_') === 0 ) {
        this.translateZ_distance = value.replace('de_scroll_translateZ_distance_','')
      }
      if ( value.search('de_scroll_translateZ_perspective_') === 0 ) {
        this.translateZ_perspective = value.replace('de_scroll_translateZ_perspective_','')
      }
      if ( value.search('de_scroll_rotate_distance_') === 0 ) {
        this.rotate_distance = value.replace('de_scroll_rotate_distance_','')
      }
      if ( value.search('de_scroll_rotateX_distance_') === 0 ) {
        this.rotateX_distance = value.replace('de_scroll_rotateX_distance_','')
      }
      if ( value.search('de_scroll_rotateY_distance_') === 0 ) {
        this.rotateY_distance = value.replace('de_scroll_rotateY_distance_','')
      }
      if ( value.search('de_scroll_rotateZ_distance_') === 0 ) {
        this.rotateZ_distance = value.replace('de_scroll_rotateZ_distance_','')
      }
      if ( value.search('de_scroll_scale_distance_') === 0 ) {
        this.scale_distance = value.replace('de_scroll_scale_distance_','')
      }
      if ( value.search('de_scroll_scaleX_distance_') === 0 ) {
        this.scaleX_distance = value.replace('de_scroll_scaleX_distance_','')
      }
      if ( value.search('de_scroll_scaleY_distance_') === 0 ) {
        this.scaleY_distance = value.replace('de_scroll_scaleY_distance_','')
      }
      if ( value.search('de_scroll_scaleZ_distance_') === 0 ) {
        this.scaleZ_distance = value.replace('de_scroll_scaleZ_distance_','')
      }
      if ( value.search('de_scroll_skew_distance_') === 0 ) {
        this.skew_distance = value.replace('de_scroll_skew_distance_','')
      }
      if ( value.search('de_scroll_skewX_distance_') === 0 ) {
        this.skewX_distance = value.replace('de_scroll_skewX_distance_','')
      }
      if ( value.search('de_scroll_skewY_distance_') === 0 ) {
        this.skewY_distance = value.replace('de_scroll_skewY_distance_','')
      }
    })

  }
}

class DeScrollAnimationHandlerClass extends elementorModules.frontend.handlers.Base {
    getDefaultSettings() {
        return {
            selectors: {
                wrapper: '.de_scroll_animation_yes',
                widget_container: '.elementor-widget-container',
            },
            last_known_scroll_position: this.last_known_scroll_position,
        };
    }

    getDefaultElements() {
        const selectors = this.getSettings( 'selectors' );
        return {
            $wrapper: this.$element.find( selectors.wrapper ),
            $widget_container: this.$element.find( selectors.widget_container ),
            $body: this.$element.find( 'body' ),
            $document: this.$element.find( 'document' ),
        };
    }

    onInit() {    
      var observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
          if (mutation.attributeName === "class") {
            var attributeValue = $(mutation.target).prop(mutation.attributeName);
            
            var $arr_classes = attributeValue.split(' ')

            var do_animation = false
            var do_animation_preview = false
      
            jQuery.each($arr_classes, (index, value) => {
              if ( value.search('de_scroll_animation_yes') === 0 ) {
                  do_animation = true;
              }

              if ( value.search('de_scroll_animation_preview_yes') === 0 ) {
                do_animation_preview = true;
              }
            })

            if (do_animation) {
                let deScrollOption = new DeScrollOption(attributeValue);
                let animindex = animObjects.length;
                let i;
                for (i = 0; i < deScrollOptions.length; i++) {
                  if ( deScrollOptions[i].id === deScrollOption.id ) {
                    animindex = i;
                    break;
                  }
                }

                deScrollOptions[animindex] = new DeScrollOption(attributeValue);

                animObjects[animindex] = anime({
                  targets: ".elementor-element-" + deScrollOption.id + ".de_scroll_animation_yes .elementor-widget-container",
                  perspective: function() {
                    let result = 0;
                    if ( deScrollOption.translateZ ) {
                      result = deScrollOption.translateZ_perspective;
                    }
                    return result;
                  },
                  scale: function() {
                    let result = 1;
                    if ( deScrollOption.scale ) {
                      result = deScrollOption.scale_distance;
                    }
                    return result;
                  },
                  scaleX: function() {
                    let result = 1;
                    if ( deScrollOption.scaleX ) {
                      result = deScrollOption.scaleX_distance;
                    }
                    return result;
                  },
                  scaleY: function() {
                    let result = 1;
                    if ( deScrollOption.scaleY ) {
                      result = deScrollOption.scaleY_distance;
                    }
                    return result;
                  },
                  scaleZ: function() {
                    let result = 1;
                    if ( deScrollOption.scaleZ ) {
                      result = deScrollOption.scaleZ_distance;
                    }
                    return result;
                  },
                  translateX: function() {
                    let result = 0;
                    if ( deScrollOption.translateX ) {
                      result = deScrollOption.translateX_distance;
                    }
                    return result;
                  },
                  translateY: function() {
                    let result = 0;
                    if ( deScrollOption.translateY ) {
                      result = deScrollOption.translateY_distance;
                    }
                    return result;
                  },
                  translateZ: function() {
                    let result = 0;
                    if ( deScrollOption.translateZ ) {
                      result = deScrollOption.translateZ_distance;
                    }
                    return result;
                  },
                  rotate: function() {
                    let result = 0;
                    if ( deScrollOption.rotate ) {
                      result = deScrollOption.rotate_distance;
                    }
                    return result;
                  },
                  rotateX: function() {
                    let result = 0;
                    if ( deScrollOption.rotateX ) {
                      result = deScrollOption.rotateX_distance;
                    }
                    return result;
                  },
                  rotateY: function() {
                    let result = 0;
                    if ( deScrollOption.rotateY ) {
                      result = deScrollOption.rotateY_distance;
                    }
                    return result;
                  },
                  rotateZ: function() {
                    let result = 0;
                    if ( deScrollOption.rotateZ ) {
                      result = deScrollOption.rotateZ_distance;
                    }
                    return result;
                  },
                  skew: function() {
                    let result = 0;
                    if ( deScrollOption.skew ) {
                      result = deScrollOption.skew_distance;
                    }
                    return result;
                  },
                  skewX: function() {
                    let result = 0;
                    if ( deScrollOption.skewX ) {
                      result = deScrollOption.skewX_distance;
                    }
                    return result;
                  },
                  skewY: function() {
                    let result = 0;
                    if ( deScrollOption.skewY ) {
                      result = deScrollOption.skewY_distance;
                    }
                    return result;
                  },
                  delay: function(el, i) {
                    return i * 100;
                  },
                  elasticity: 200,
                  easing: deScrollOption.easing,
                  direction: "normal",
                  autoplay: false,
                });
            }
          }
        });
      });

      if (this.$element) {
          var elementId = this.$element.context.attributes['data-id'].nodeValue;
          var elementSelector = document.querySelector("[data-id='" + elementId + "']");
          if (elementSelector) {
              observer.observe(elementSelector, {
                  attributes: true
              });

              if ( this.$element.context.classList.contains('de_scroll_animation_yes') ) {
                let last_known_scroll_position = 0;
                let ticking = false;
                let animindex = animObjects.length;
                let optionindex = deScrollOptions.length;

                deScrollOptions[optionindex] = new DeScrollOption(this.$element.context.className);

                animObjects[animindex] = anime({
                  targets: ".elementor-element-" + deScrollOptions[optionindex].id + ".de_scroll_animation_yes .elementor-widget-container",
                  perspective: function() {
                    let result = 0;
                    if ( deScrollOptions[optionindex].translateZ ) {
                      result = deScrollOptions[optionindex].translateZ_perspective;
                    }
                    return result;
                  },
                  scale: function() {
                    let result = 1;
                    if ( deScrollOptions[optionindex].scale ) {
                      result = deScrollOptions[optionindex].scale_distance;
                    }
                    return result;
                  },
                  scaleX: function() {
                    let result = 1;
                    if ( deScrollOptions[optionindex].scaleX ) {
                      result = deScrollOptions[optionindex].scaleX_distance;
                    }
                    return result;
                  },
                  scaleY: function() {
                    let result = 1;
                    if ( deScrollOptions[optionindex].scaleY ) {
                      result = deScrollOptions[optionindex].scaleY_distance;
                    }
                    return result;
                  },
                  scaleZ: function() {
                    let result = 1;
                    if ( deScrollOptions[optionindex].scaleZ ) {
                      result = deScrollOptions[optionindex].scaleZ_distance;
                    }
                    return result;
                  },
                  translateX: function() {
                    let result = 0;
                    if ( deScrollOptions[optionindex].translateX ) {
                      result = deScrollOptions[optionindex].translateX_distance;
                    }
                    return result;
                  },
                  translateY: function() {
                    let result = 0;
                    if ( deScrollOptions[optionindex].translateY ) {
                      result = deScrollOptions[optionindex].translateY_distance;
                    }
                    return result;
                  },
                  translateZ: function() {
                    let result = 0;
                    if ( deScrollOptions[optionindex].translateZ ) {
                      result = deScrollOptions[optionindex].translateZ_distance;
                    }
                    return result;
                  },
                  rotate: function() {
                    let result = 0;
                    if ( deScrollOptions[optionindex].rotate ) {
                      result = deScrollOptions[optionindex].rotate_distance;
                    }
                    return result;
                  },
                  rotateX: function() {
                    let result = 0;
                    if ( deScrollOptions[optionindex].rotateX ) {
                      result = deScrollOptions[optionindex].rotateX_distance;
                    }
                    return result;
                  },
                  rotateY: function() {
                    let result = 0;
                    if ( deScrollOptions[optionindex].rotateY ) {
                      result = deScrollOptions[optionindex].rotateY_distance;
                    }
                    return result;
                  },
                  rotateZ: function() {
                    let result = 0;
                    if ( deScrollOptions[optionindex].rotateZ ) {
                      result = deScrollOptions[optionindex].rotateZ_distance;
                    }
                    return result;
                  },
                  skew: function() {
                    let result = 0;
                    if ( deScrollOptions[optionindex].skew ) {
                      result = deScrollOptions[optionindex].skew_distance;
                    }
                    return result;
                  },
                  skewX: function() {
                    let result = 0;
                    if ( deScrollOptions[optionindex].skewX ) {
                      result = deScrollOptions[optionindex].skewX_distance;
                    }
                    return result;
                  },
                  skewY: function() {
                    let result = 0;
                    if ( deScrollOptions[optionindex].skewY ) {
                      result = deScrollOptions[optionindex].skewY_distance;
                    }
                    return result;
                  },
                  delay: function(el, i) {
                    return i * 100;
                  },
                  elasticity: 200,
                  easing: deScrollOptions[optionindex].easing,
                  autoplay: false
                });

                document.onscroll = () => {
 
                  last_known_scroll_position = document.scrollingElement.scrollTop / document.scrollingElement.scrollHeight * 100;
  
                  if (!ticking) {
                    window.requestAnimationFrame(function() {
                      var i;
                      for (i = 0; i < animObjects.length; i++) {
                        if ( deScrollOptions[i].editMode ) {
                          if ( deScrollOptions[i].animation_preview ) {
                            animObjects[i].seek(animObjects[i].duration * last_known_scroll_position);
                          }
                        } else {
                          animObjects[i].seek(animObjects[i].duration * last_known_scroll_position);
                        }
                      }
                      ticking = false;
                    });
                
                    ticking = true;
                  }
                
                }    
              } //if (this.$element.context.classList.contains('de_scroll_animation_yes'))

          }
      }
    }

    bindEvents() {
    }
} //class DeScrollAnimationHandlerClass

jQuery( window ).on( 'elementor/frontend/init', () => {
  const addHandler = ( $element ) => {
    elementorFrontend.elementsHandler.addHandler( DeScrollAnimationHandlerClass, {
      $element,
    } );
  };

  elementorFrontend.hooks.addAction( 'frontend/element_ready/global', addHandler );
} );
