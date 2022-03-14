/**
 * Fork of https://github.com/awran5/CMB2-conditional-logic/blob/master/cmb2-conditional-logic.js
 */

(function ($) {
    'use strict'
  
    function CMB2Conditional() {
      $('[data-conditional-id]').each((i, el) => {
        let condName = el.dataset.conditionalId,
          condValue = el.dataset.conditionalValue,
          inverted = (el.dataset.conditionalInvert) ? true : false,
          condParent = el.closest('.cmb-row'),
          inGroup = condParent.classList.contains('cmb-repeat-group-field');
          console.log(condName);
          console.log(condValue);
  
        let initAction = (inverted === true) ? 'show' : 'hide';
  
        // Check if the field is in group
        if (inGroup) {
            let groupID = condParent.closest('.cmb-repeatable-group').getAttribute('data-groupid'),
            iterator = condParent.closest('.cmb-repeatable-grouping').getAttribute('data-iterator');

            // change the select name with group ID added
            condName = `${groupID}[${iterator}][${condName}]`;
        }
  
        // Check if value is matching
        function valueMatch(value) {
  
            let checkCondition = condValue.includes(value) && value !== '';

            // Invert if needed
            if (inverted === true) {
                checkCondition = !checkCondition;
            }

            return checkCondition;
  
        }
  
        function conditionalField(field, action) {
  
            if ((action == 'hide' && inverted === false) || (action != 'hide' && inverted !== false)) {
                field.addClass('field_is_hidden');
            } else {
                field.removeClass('field_is_hidden');
            }
  
        }
  
        // Select the field by name and loop through
        $('[name="' + condName + '"]').each(function (i, field) {
            // Select field
            if ("select-one" === field.type) {

                if (!valueMatch(field.value)) {
                    conditionalField($(condParent), initAction);
                }

                // Check on change
                $(field).on('change', function (event) {

                    (!valueMatch(event.target.value)) ? conditionalField($(condParent), 'hide') : conditionalField($(condParent), 'show');

                });

            }
  
        });
  
      });
    }
  
    // Trigger the funtion
    CMB2Conditional();
  
    // Trigger again when new group added
    $('.cmb2-wrap > .cmb2-metabox').on('cmb2_add_row', function () {

        CMB2Conditional();

    });
  
  })(jQuery);