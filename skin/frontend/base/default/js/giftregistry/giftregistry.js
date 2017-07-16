/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$j( document ).ready(function() {

    var deleteLinks = document.querySelectorAll('.delete-registry-button');

    for (var i = 0; i < deleteLinks.length; i++) {
      deleteLinks[i].addEventListener('click', function(event) {
          event.preventDefault();

          var choice = confirm('Are you sure?');

          if (choice) {
            window.location.href = this.getAttribute('href');
          }
      });
    } 
    

    $j("#type_id").on('change', function () {

        var thisSelect = $j(this);
        var customTypeInput = $j('.custom_event_type_id');

        if (thisSelect.val() === "custom") {
            customTypeInput.show().addClass('required-entry');
            thisSelect.removeClass('validate-select');
        } else {
            customTypeInput.hide().removeClass('required-entry');
            thisSelect.addClass('validate-select').show();
        }

    });   

    function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $j('#registry_img_display').attr('src', e.target.result);
            };

            reader.readAsDataURL(input.files[0]);
        }
    }

    $j("#registry_img_input").change(function(){
        readURL(this);
    });    
  
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    

/* 
    var registryId = $j('#registryId').val();

    
    $j( "#addToRegQty" ).change(function() {
        
      var giftRegistrySubmitUrl = $j('#addToGiftRegistryLink').attr('href');
      var addToRegQty = $j('#addToRegQty').val();      
      var substringHref = giftRegistrySubmitUrl.substring(giftRegistrySubmitUrl.lastIndexOf("addToRegQty/"),giftRegistrySubmitUrl.lastIndexOf("/")); 
      var giftRegistrySubmitUrl = giftRegistrySubmitUrl.replace(substringHref, "addToRegQty/" + addToRegQty);
      
      $j('#addToGiftRegistryLink').attr('href', giftRegistrySubmitUrl);
      

      
    });
    
    $j( "#registryId" ).change(function() {

      var giftRegistrySubmitUrl = $j('#addToGiftRegistryLink').attr('href');
      var registryId = $j('#registryId').val();      
      var substringHref = giftRegistrySubmitUrl.substring(giftRegistrySubmitUrl.lastIndexOf("registryId/"),giftRegistrySubmitUrl.lastIndexOf("/")); 
      var giftRegistrySubmitUrl = giftRegistrySubmitUrl.replace(substringHref, "addToRegQty/" + addToRegQty);
      
      $j('#addToGiftRegistryLink').attr('href', giftRegistrySubmitUrl);

      
    });
 */
    
    
    
});


