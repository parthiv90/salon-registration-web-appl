function validateForm(event) {
    event.preventDefault();
  
    var username = document.getElementById("username").value;
    var email = document.getElementById("email").value;
    var password = document.getElementById("password").value;
    var confirmPassword = document.getElementById("confirmPassword").value;
  
    if (password !== confirmPassword) {
      alert("Password and Confirm Password do not match");
      return;
    }
  
    // Additional validation logic can be added here
  
    // If all validations pass, you can submit the form or perform other actions
    alert("Registration successful!");
  }
  