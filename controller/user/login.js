// console.log("login init admin");

const checkSessionLogin = async () => {
  const { isLoggedIn, login_type_session } = await auth.isLogin();

  if (isLoggedIn) {
    login_type_session == "admin" ? (window.location.href = "../admin/") : (window.location.href = "../client");
  }
};

checkSessionLogin();

const login = {
  user: function (username, password, cb) {
    const params = {
      username: username,
      password: password,
      login_type: "admin",
    };
    main.api_request("auth/login.php", params, "GET", function (resp) {
      cb(resp);
    });
  },
};

// initialize validator
main.init.validator("form.login-form");

// buttons
$(document)
  .off("submit", ".login-form")
  .on("submit", ".login-form", function (e) {
    e.preventDefault();
    main.button.loader_start("login_btn", "login_loading_btn");
    const username = $("#username").val();
    const password = $("#password").val();
    login.user(username, password, function (resp) {
      if (resp.status) {
        localStorage.setItem("BarangayData", JSON.stringify(resp.data)); //convert `userData` to `BarangayData` in all localStorage Identifiers
        window.location.href = "../admin/";
      } else {
        // error input
        validator.errorTrigger($("#username"), "");
        validator.errorTrigger($("#password"), "");

        // popup message
        Swal.fire({
          icon: "error",
          title: "Invalid",
          text: resp.message,
        });
        main.button.loader_stop("login_btn", "login_loading_btn");
      }
    });
  });
