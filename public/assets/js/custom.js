
$(document).ready(function() {
	'use strict';

  function togglePassword(){
      let input = document.getElementById("inputPass");
      var eye = document.getElementById("eye");
      var eyeSlash = document.getElementById("eye-slash");

      if(input.type === "password"){
          input.type = "text"
          eye.style.display = "none";
          eyeSlash.style.display = "inline";
      } else {
          input.type = "password"
          eye.style.display = "inline";
          eyeSlash.style.display = "none";
      }
  }

  $('.pass-toggler-btn').on('click', 'i', function() {
  	togglePassword();
  })


  /*--------------------------------------------
  Owl Carousel
  --------------------------------------------*/

  $('.testimonial-carousel').owlCarousel({
    loop:true,
    items: 1,
    autoplay: true,
    autoplayHoverPause: true,
    dots: true,
    nav: false,
    autoplaySpeed: 600,
    dotsSpeed: 600,
    margin: 15
  })

  /*-- form validation --*/
  $(document).ready(function(){
    $('body').on('click', '#submit_btn', function(){
   
   if($("input[type='email']").val() == "" && $("input[type='password']").val() == "") {
       $("input[type='email'], input[type='password']").addClass("border-danger");
       $("#pop_up").show().text('Please fill the form');
   }else{
       $("input[type='email'], input[type='password']").removeClass("border-danger");
       $("#pop_up").hide();
   }
        
        if($('input[type="email"]').val() == '' && $('input[type="password"]').empty()){
         //   $("input[type='password']").removeClass('border-danger');
        }else{
            $("#pop_up").text('Password is Empty').show();
            $("input[type='password']").show().addClass('border-danger');
        }
        
        if($('input[type="password"]').val() == '' && $('input[type="email"]').empty()){
        //   $("input[type='email']").removeClass('border-danger');
        }else{
            // alert("Email is empty");
              $("#pop_up").text('Email is Empty').show();
             $("input[type='email']").addClass('border-danger');
            
        }
        if($('input[type="email"]').val() != '' && $('input[type="password"]').val() != ''){
            $("#pop_up").hide();
        }else{
           
        }

});
});


$('input[type="email"]').keyup(function () {
  var $email = this.value;
  validateEmail($email);
});

//  if( $(".form-group").first().find('input[type="email"]'));
function validateEmail(email) {
//   $(".form-group").find(".fa").hide();
//$(this).parent('.form-group').find();
var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;

if($('input[type="email"]').val() == ''){
    // $(this).parent(".form-group").find('.fa').show();

     $(this).removeClass("border-danger");
     $(this).removeClass("border-success");
}else{
if (!emailReg.test(email)) {
$(".form-group").first().find(".fa-times").show();
$(".form-group").first().find(".fa-check").hide();
$('input[type="email"]').addClass("border-danger");
$('input[type="email"]').removeClass("border-success");
$("#pop_up").show().text('Somthing went wrong check Your Email Id');
}else{
$(".form-group").first().find(".fa-check").show();
$(".form-group").first().find(".fa-times").hide();
$('input[type="email"]').removeClass("border-danger");
$('input[type="email"]').addClass("border-success");
$("#pop_up").hide();
}
}


if($('input[type="email"]').val() == "") {
$('input[type="email"]').parents('.form-group').find(".fa").hide();
$('input[type="email"]').removeClass("border-success");
$('input[type="email"]').removeClass("border-danger");
}else{}

}

})

// Function to update the inputPass strength progress bar
function updateProgressBar() {
  var inputPass = document.getElementById("inputPass").value;
  var strength = 0;
  var progress = document.getElementById("progress");

  if (inputPass.length >= 8) {
    strength += 1;
  }

  if (inputPass.match(/[A-Z]+/)) {
    strength += 1;
  }

  if (inputPass.match(/[a-z]+/)) {
    strength += 1;
  }

  if (inputPass.match(/[0-9]+/)) {
    strength += 1;
  }

  if (inputPass.match(/[~!@#$%^&*()]+/)) {
    strength += 1;
  }

  switch (strength) {
    case 0:
      progress.style.width = "0%";
      progress.style.backgroundColor = "red";
      break;
    case 1:
      progress.style.width = "20%";
      progress.style.backgroundColor = "red";
      break;
    case 2:
      progress.style.width = "40%";
      progress.style.backgroundColor = "red";
      break;
    case 3:
      progress.style.width = "60%";
      progress.style.backgroundColor = "orange";
      break;
    case 4:
      progress.style.width = "70%";
      progress.style.backgroundColor = "orange";
      break;
    case 5:
      progress.style.width = "100%";
      progress.style.backgroundColor = "#0EC36B";
      break;
  }

  // Update inputPass suggestions based on strength
  var suggestion = document.getElementById("suggestions");
  var suggestionText = document.querySelector(".suggestion");

  if (strength === 5) {
    // suggestionText.innerText = "Strong inputPass!";
    suggestionText.style.color = "red";
    suggestionText.style.color = "orange";
    suggestionText.style.color = "#0EC36B";
  } else if (strength >= 3) {
    // suggestionText.innerText = "Medium inputPass";
    suggestionText.style.color = "red";
    suggestionText.style.color = "#0EC36B";
    suggestionText.style.color = "orange";
  } else {
    // suggestionText.innerText = "Weak inputPass";
    suggestionText.style.color = "#0EC36B";
    suggestionText.style.color = "orange";
    suggestionText.style.color = "red";
  }
}


/*-- requier  js --*/
$("#commentForm").validate();