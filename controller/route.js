// LOADERS
const showLoader = () => {
  document.getElementById("loader-wrapper").style.display = "flex";
};

const hideLoader = () => {
  document.getElementById("loader-wrapper").style.display = "none";
  document.getElementById("wrapper").style.display = "flex";
};

// get user access : access_routes
const getAccess = async () => {
  return new Promise((resolve, reject) => {
    main.api_request("users/get_user_access.php", null, "GET", function (resp) {
      // Assuming resp contains the access array
      if (resp) {
        resolve(resp);
      } else {
        reject("Error fetching access data");
      }
    });
  });
};

const login_type = document.getElementById("login_type").value;

const set_active_link = (name) => {
  let ul = $("ul.accordion");

  ul.children().removeClass("active");

  ul.find('a[href="#/' + name + '"]')
    .parent()
    .addClass("active");
};

const unauthorized_action_alert = (login_type_session) => {
  Swal.fire({
    title: "Unauthorized Action Detected",
    html: "You will be logged out in <b></b> seconds.",
    icon: "error",
    timer: 5000,
    timerProgressBar: true,
    allowOutsideClick: false,
    allowEscapeKey: false,
    didOpen: () => {
      Swal.showLoading();
      const timer = Swal.getPopup().querySelector("b");
      timerInterval = setInterval(() => {
        timer.textContent = (Swal.getTimerLeft() / 1000).toFixed();
      }, 100);
    },
    willClose: () => {
      // api call for session destroy
      main.api_request("session/delete_session.php", null, "GET", function (resp) {
        // need this because php returned values become string
        const booleanResp = JSON.parse(resp);
        // if true redirect to login
        if (booleanResp) {
          localStorage.clear();
          clearInterval(timerInterval);
          login_type_session == "admin" ? (window.location.href = "../admin/login.html") : (window.location.href = "../");
        } else {
          console.log("error logging out", resp);
        }
      });
    },
  });
};

// initialize routes
const initRoute = async (login_type_session) => {
  try {
    // this data will be coming from database
    const access = await getAccess();
    // console.log({ access });

    // remove hidden
    await access.forEach((item) => {
      $(`#route_${item}`).removeAttr("hidden");
    });

    // Sammy routes
    const app = await Sammy("#app", function () {
      // this.debug = true;

      // ## Define routes
      // dashboard page
      this.get("#/dashboard", function (context) {
        if (access.includes("dashboard")) {
          set_active_link("dashboard");
          this.partial("../views/dashboard/dashboard.html");
        } else {
          this.redirect("#/404");
        }
      });

      this.get("#/manage_account", function (context) {
        if (access.includes("manage_account")) {
          set_active_link("manage_account");
          this.partial("../views/users/manage_account.html");
        } else {
          this.redirect("#/404");
        }
      });

      // Logout and Settings
      this.get("#/settings", function (context) {
        this.partial("../views/users/settings.html");
      });

      this.get("#/logout", function (context) {
        // api call for session destroy
        main.api_request("session/delete_session.php", null, "GET", function (resp) {
          // need this because php returned values become string
          const booleanResp = JSON.parse(resp);
          // if true redirect to login
          if (booleanResp) {
            localStorage.clear();
            window.location.href = "../admin/login.html";
          } else {
            console.log("error logging out", resp);
          }
        });
      });

      this.get("#/logout_client", function (context) {
        // api call for session destroy
        main.api_request("session/delete_session.php", null, "GET", function (resp) {
          // need this because php returned values become string
          const booleanResp = JSON.parse(resp);
          // if true redirect to login
          if (booleanResp) {
            localStorage.clear();
            window.location.href = "../";
          } else {
            console.log("error logging out", resp);
          }
        });
      });

      // 404 page
      this.get("#/404", function (context) {
        this.partial("../views/404.html");
      });

      // Redirect to a 404 page when the route is not found
      this.get("#/:any", function (context) {
        this.redirect("#/404");
      });
    });

    // Run the app
    // login_type == "client" ? await app.run("#/dashboard_client") : await app.run("#/dashboard");
    // login_type == "client" && login_type_session == "client" ? await app.run("#/unit_enrollment") : await app.run("#/dashboard");

    // checking for unauthorize action (changing url from client to admin vice versa)
    if (login_type == "client" && login_type_session == "client") {
      await app.run("#/unit_enrollment");
    } else if (login_type == "" && login_type_session == "admin") {
      await app.run("#/dashboard");
    } else {
      unauthorized_action_alert(login_type_session);
    }
    hideLoader();
  } catch (error) {
    console.error("Error initializing routes:", error);
  }
};

// check sessions
const checkSessionRoute = async () => {
  const { isLoggedIn, login_type_session } = await auth.isLogin();

  if (!isLoggedIn) {
    login_type == "client" ? (window.location.href = "../") : (window.location.href = "../admin/login.html");
  } else {
    await initRoute(login_type_session);
  }
};

// Initialize

// load loader
showLoader();

async function handleMaintenance() {
  try {
    const result = await auth.isMaintenance();
    if (result == 1) {
      window.location.href = "../maintenance.html";
    } else {
      // init check session route
      checkSessionRoute();
    }
  } catch (error) {
    console.error("Error occurred while checking maintenance status:", error);
  }
}

if (login_type == "client") {
  handleMaintenance();
} else {
  // init check session route
  checkSessionRoute();
}
