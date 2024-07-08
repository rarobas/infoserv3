var currentYear = new Date().getFullYear();
var Toast = Swal.mixin({
  toast: true,
  position: "top-right",
  iconColor: "white",
  customClass: {
    popup: "colored-toast",
  },
  showConfirmButton: false,
  timer: 3000,
  timerProgressBar: true,
  didOpen: (toast) => {
    toast.onmouseenter = Swal.stopTimer;
    toast.onmouseleave = Swal.resumeTimer;
  },
});

const main = {
  api_request: function (api, params, type, cb) {
    $.ajax({
      type: type, // GET, POST, PUT, DELETE
      url: server_url + api,
      data: params,
      success: function (response) {
        return cb(response);
      },
      error: function (jqXHR, textStatus, errorThrown) {
        console.error("AJAX Text Status:", textStatus);
        console.error("AJAX Error:", errorThrown);

        if (textStatus === "timeout") {
          alert("Request timed out. Please check your internet connection.");
        } else if (textStatus === "error") {
          if (jqXHR.status === 0) {
            alert("Network error. Please check your internet connection.");
          } else if (jqXHR.status === 401) {
            alert("Unauthorized Request. Please re-login to your account.");
            location.reload(true);
          } else {
            // Handle other HTTP errors as needed
            alert("An error occurred. Please try again later.");
          }
        }
      },
    });
  },
  form_api_request: function (api, params, type, cb) {
    $.ajax({
      type: type, // GET, POST, PUT, DELETE
      url: server_url + api,
      data: params,
      contentType: false,
      processData: false,
      success: function (response) {
        return cb(response);
      },
      error: function (jqXHR, textStatus, errorThrown) {
        console.error("AJAX Text Status:", textStatus);
        console.error("AJAX Error:", errorThrown);

        if (textStatus === "timeout") {
          alert("Request timed out. Please check your internet connection.");
        } else if (textStatus === "error") {
          if (jqXHR.status === 0) {
            alert("Network error. Please check your internet connection.");
          } else if (jqXHR.status === 401) {
            alert("Unauthorized Request. Please re-login to your account.");
            location.reload(true);
          } else {
            // Handle other HTTP errors as needed
            alert("An error occurred. Please try again later.");
          }
        }
      },
    });
  },
  init: {
    validator: function (form) {
      return (validator = $(form).jbvalidator({
        errorMessage: true,
        successClass: true,
        language: "../assets/dist/lang/en.json",
      }));
    },
  },
  button: {
    loader_start: function (btn, loader_btn) {
      // Hide the Sign Up button and show the Loading button
      document.getElementById(btn).style.display = "none";
      document.getElementById(loader_btn).style.display = "inline-block";
    },
    loader_stop: function (btn, loader_btn) {
      // Hide the Loading button and show the Sign Up button when the request is done
      document.getElementById(btn).style.display = "inline-block";
      document.getElementById(loader_btn).style.display = "none";
    },
  },

  toast: {
    error: function (message) {
      Toast.fire({ icon: "error", title: message });
    },
    success: function (message) {
      Toast.fire({ icon: "success", title: message });
    },
    info: function (message) {
      Toast.fire({ icon: "info", title: message });
    },
  },
  // loader v2
  loader: {
    start: function (button, text) {
      button.html(`<div class="spinner-border" role="status" style="height: 20px; width: 20px;">
                    <span class="visually-hidden">Loading...</span> 
                  </div> ${text}`);
      button.prop("disabled", true);
    },
    stop: function (button, text, disable = false) {
      button.html(text);
      button.prop("disabled", disable);
    },
  },
};

    
// Display the current year in the HTML document
$("#page-footer").text(`Copyright &copy; BSIT 3-2N BARANGAY TEAM `+currentYear);
