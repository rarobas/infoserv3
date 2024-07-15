var current_password_inp = document.getElementById("current_password_inp");
var new_password_inp = document.getElementById("new_password_inp");
var confirm_new_password_inp = document.getElementById("confirm_new_password_inp");
var show_password = document.getElementById("show_password");

var new_email_inp = document.getElementById("new_email_inp"),
  confirm_new_email_inp = document.getElementById("confirm_new_email_inp");

var settings = {
  init: function () {
    let BarangayData = localStorage.getItem("BarangayData");
    var { login_type } = JSON.parse(BarangayData);

    if (login_type == "client") {
      document.getElementById("nav-change-email-tab").classList.remove("invisible");
    }
  },
  update: {
    password: function (current_password, new_password, callback) {
      const params = {
        _current_password: current_password,
        _new_password: new_password,
      };
      main.api_request("settings/change_password.php", params, "POST", function (resp) {
        callback(resp);
      });
    },
    email: function (new_email, callback) {
      const params = {
        _new_email: new_email,
      };
      main.api_request("settings/change_email.php", params, "POST", function (resp) {
        callback(resp);
      });
    },
  },
  validation: {
    password: function (password) {
      const pattern = /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d!@#$%^&*()-_+=]{8,}$/;
      return pattern.test(password);
    },
  },
};

settings.init();

$("#change_password_btn").on("click", function () {
  const button = $(this);
  main.loader.start(button, "Updating..");

  if (!current_password_inp.value.trim() || !new_password_inp.value.trim() || !confirm_new_password_inp.value.trim()) {
    Swal.fire({ title: "Change Password", text: "Please fill out all fields", icon: "error" });
    main.loader.stop(button, "Update");
    return;
  }

  if (new_password_inp.value !== confirm_new_password_inp.value) {
    Swal.fire({ title: "Change Password", text: "New Password do not match!", icon: "error" });
    main.loader.stop(button, "Update");
    return;
  }

  if (!settings.validation.password(new_password_inp.value)) {
    Swal.fire({ title: "Reset Password", text: "Invalid password. Password must be at least 8 characters long and include both letters and numbers.", icon: "error" });
    main.loader.stop(button, "Update");
    return;
  }

  settings.update.password(current_password_inp.value.trim(), new_password_inp.value.trim(), function (resp) {
    if (resp.status) {
      Swal.fire({ title: "Change Password", text: resp.message, icon: "success" });
      document.getElementById("change_password_form").reset();
    } else {
      Swal.fire({ title: "Change Password", text: resp.message, icon: "error" });
    }
    main.loader.stop(button, "Update");
  });
});

$("#change_email_btn").on("click", function () {
  const button = $(this);
  const orig_btn_text = button[0].innerHTML;
  const alert_title = "Change Email Address";

  main.loader.start(button, "Updating..");

  if (!new_email_inp.value.trim() || !confirm_new_email_inp.value.trim()) {
    Swal.fire({ title: alert_title, text: "Please fill out all fields", icon: "error" });
    main.loader.stop(button, orig_btn_text);
    return;
  }

  if (new_email_inp.value !== confirm_new_email_inp.value) {
    Swal.fire({ title: alert_title, text: "New email do not match!", icon: "error" });
    main.loader.stop(button, orig_btn_text);
    return;
  }

  if (!main.validations.email_address(new_email_inp.value)) {
    Swal.fire({ title: alert_title, text: "Invalid email format.", icon: "error" });
    main.loader.stop(button, orig_btn_text);
    return;
  }

  settings.update.email(new_email_inp.value.trim(), function (resp) {
    if (resp.status) {
      Swal.fire({ title: alert_title, text: resp.message, icon: "success" });
      document.getElementById("change_email_form").reset();
    } else {
      Swal.fire({ title: alert_title, text: resp.message, icon: "error" });
    }
    main.loader.stop(button, orig_btn_text);
  });
});

$(document)
  .off("click", "#show_password")
  .on("click", "#show_password", function () {
    const type = show_password.checked ? "text" : "password";
    current_password_inp.type = type;
    new_password_inp.type = type;
    confirm_new_password_inp.type = type;
  });
