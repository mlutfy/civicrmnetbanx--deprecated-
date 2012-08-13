
(function ($) {
  Drupal.behaviors.cividesjardins_logos = {
    attach: function(context, settings) {
      // In donation form, show Desjardins' logo
      $('#crm-container .crm-contribution-main-form-block .credit_card_info-section').prepend('<div class="civicrmdesjardins-logodj">' + Drupal.t('Payment secured by Desjardins') + '</div>');

      // add visa/mc logos
      $('#crm-container .crm-contribution-main-form-block .credit_card_type-section .content').append('<div class="civicrmdesjardins-cclogo"><a href="#" class="civicrmdesjardins-cclogo-visa"><span>Visa</span></a> <a href="#" class="civicrmdesjardins-cclogo-mc"><span>MasterCard</span></a> <a href="#" class="civicrmdesjardins-cclogo-amex"><span>Amex</span></a></div>');

      $('#crm-container .crm-contribution-main-form-block .credit_card_type-section .civicrmdesjardins-cclogo-visa').click(function() {
        $('#crm-container .crm-contribution-main-form-block .credit_card_type-section #credit_card_type').val('Visa');
        $('#crm-container .crm-contribution-main-form-block .credit_card_type-section a').css('opacity', 0.4);
        $('#crm-container .crm-contribution-main-form-block .credit_card_type-section .civicrmdesjardins-cclogo-visa').css('opacity', 1);
        $('input#cvv2').val('000');
        return false;
      });
      $('#crm-container .crm-contribution-main-form-block .credit_card_type-section .civicrmdesjardins-cclogo-mc').click(function() {
        $('#crm-container .crm-contribution-main-form-block .credit_card_type-section #credit_card_type').val('MasterCard');
        $('#crm-container .crm-contribution-main-form-block .credit_card_type-section a').css('opacity', 0.4);
        $('#crm-container .crm-contribution-main-form-block .credit_card_type-section .civicrmdesjardins-cclogo-mc').css('opacity', 1);
        $('input#cvv2').val('000');
        return false;
      });
      $('#crm-container .crm-contribution-main-form-block .credit_card_type-section .civicrmdesjardins-cclogo-amex').click(function() {
        $('#crm-container .crm-contribution-main-form-block .credit_card_type-section #credit_card_type').val('Amex');
        $('#crm-container .crm-contribution-main-form-block .credit_card_type-section a').css('opacity', 0.4);
        $('#crm-container .crm-contribution-main-form-block .credit_card_type-section .civicrmdesjardins-cclogo-amex').css('opacity', 1);
        $('input#cvv2').val('0000');
        return false;
      });

      $('#crm-container .crm-contribution-main-form-block .credit_card_type-section select#credit_card_type').change(function() {
        var cctype = $(this).val();
        if (cctype == 'Amex') {
          $('input#cvv2').val('0000');
        } else {
          $('input#cvv2').val('000');
        }
      });

      // Hide the CC type field (redundant anyway) and select according to the number entered
      $('input#credit_card_number').change(function() {
        var ccnumtype = $(this).val().substr(0, 4);

        // Semi-hide all images, we will un-hide the right one afterwards
        $('#crm-container .crm-contribution-main-form-block .credit_card_type-section a').css('opacity', 0.4);
        $('#credit_card_type').val('');

        // https://en.wikipedia.org/wiki/Credit_card_numbers
        if (ccnumtype.substr(0, 1) == '3') {
          $('#credit_card_type').val('Amex');
          $('#crm-container .crm-contribution-main-form-block .credit_card_type-section .civicrmdesjardins-cclogo-amex').css('opacity', 1);
          $('input#cvv2').val('0000');
        }
        else if (ccnumtype.substr(0, 2) >= '51' && ccnumtype.substr(0, 2) <= '55') {
          $('#credit_card_type').val('MasterCard');
          $('#crm-container .crm-contribution-main-form-block .credit_card_type-section .civicrmdesjardins-cclogo-mc').css('opacity', 1);
          $('input#cvv2').val('000');
        }
        else if (ccnumtype.substr(0, 1) == '4') {
          $('#credit_card_type').val('Visa');
          $('#crm-container .crm-contribution-main-form-block .credit_card_type-section .civicrmdesjardins-cclogo-visa').css('opacity', 1);
          $('input#cvv2').val('000');
        }
      });
    }
  }
})(jQuery);
