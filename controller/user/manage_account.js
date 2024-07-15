Ladda.bind(".ladda-button");

var create_account_btn = document.getElementById("create_account_btn");
var update_account_btn = document.getElementById("update_account_btn");
var cancel_account_btn = document.getElementById("cancel_account_btn");

// let create_account_btn_label = document.getElementById("create_account_btn_label");
// let status_label = document.getElementById("status_label");
// let status_field = document.getElementById("status");

var manage_account = {
  input: function () {
    const form_data = {
      username: document.getElementById("username"),
      password: document.getElementById("password"),
      user_role_id: document.getElementById("user_role"), // TODO: please update the name
      first_name: document.getElementById("first_name"),
      last_name: document.getElementById("last_name"),
      user_barangay: document.getElementById("user_barangay"),
      // status: document.getElementById("status"),
      access_routes: manage_account.get.selected_user_access(),
    };
    return form_data;
  },
  get: {
    selected_user_access: function () {
      let selectedValues = $("#user_access").val();

      // Convert the array to a comma-separated string
      let selectedValuesString = selectedValues ? selectedValues.join(",") : "";

      return selectedValuesString;
    },
  },
  user_role_list: function () {
    main.api_request("users/user_role_list.php", null, "GET", function (resp) {
      // List all data using map
      const user_role_list_option = resp.map((v) => `<option value="${v.user_role_id}">${v.role_desc}</option>`).join("");

      // For multiple dropdowns with the same id
      document.querySelectorAll("#user_role").forEach((element) => (element.innerHTML += user_role_list_option));
    });
  },
  tbl: {
    // --------------------------------------------PARAMETERS-------------------------------------------------->generate table
    user_account_tbl: function () {
      let unfiltered_rows_count;

      const columns = [
        { data: "username", title: "Username", className: "username", sortable: false },
        { data: "full_name", title: "Full Name", className: "full_name", sortable: false },
        { data: "role_desc", title: "User Role", className: "role_desc", sortable: false },
        { data: "user_barangay", title: "Barangay", className: "user_barangay", sortable: false },
        { data: "access_routes", title: "Access", className: "access_routes", sortable: false },
        { data: "user_status", title: "Status", className: "user_status", sortable: false },
        { title: "Actions", className: "td_actions", sortable: false },
      ];

      $("#user_accounts_dt").dataTable({
        serverSide: true,
        lengthChange: false,
        searchDelay: 1000,
        searching: false,
        processing: true,
        //pageLength: 10, // default 10
        lengthChange: true,
        lengthMenu: [10, 25, 50, 100],
        paging: true,
        language: {
          infoFiltered: "", // filtered from n total entries datatables remove kasi mali bilang lagi kulang ng isa kapag nag a add.
        },
        columns: columns,
        columnDefs: [
          {
            render: function (data, type, row) {
              return row.user_id;
            },
            targets: -1,
          },
          {
            render: function (data, type, row) {
              let active_status = `<span class="badge bg-success">Active</span>`;
              let inactive_status = `<span class="badge bg-danger">Inactive</span>`;
              return (account_status = row.user_status == 0 ? active_status : inactive_status);
            },
            targets: -2,
          },
          {
            render: function (data, type, row) {
              const ar_str = row.access_routes;
              const ar_array = ar_str.split(",");

              const ar_badge = ar_array.map((v) => `<span class="badge bg-secondary mr-2">${v}</span>`);

              return ar_badge.join("");
            },
            targets: -3,
          },
        ],

        ajax: function (data, callback, settings) {
          const username = document.getElementById("search_account_text").value;
          const params = {
            _limit: data.length,
            _limit_offset: data.start,
            _username: username,
          };

          main.api_request("users/get_user_accounts_dt.php", params, "GET", function (response) {
            let resp = response.data || [];

            if (data.draw === 1) {
              // if this is the first draw, which means it is unfiltered
              unfiltered_rows_count = response._total_count;
            }

            let total_count = response._total_count;

            callback({
              draw: data.draw,
              data: resp,
              recordsTotal: unfiltered_rows_count,
              recordsFiltered: total_count,
            });
          });
        },
        createdRow: function (row, data, dataIndex) {
          $(row).find("td:eq(-1)").html(`
                    <div class="custom_action_btn_container"> 
                      <a class="user_update_btn_dt custom-action-update-btn" data-toggle="tooltip" data-placement="top" title="Update" data-original-title="Update">
                        <i class="fa fa-edit"></i>
                      </a>
                      <a class="user_delete_btn_dt custom-action-delete-btn" data-toggle="tooltip" data-placement="top" title="Delete" data-original-title="Delete">
                        <i class="fa fa-times"></i>
                      </a>
                    </div>
                  `);

          $(row).find("td:eq(-1) > div > a").attr({
            "data-user_id": data.user_id,
            "data-username": data.username,
            "data-password": data.password,
            "data-first_name": data.first_name,
            "data-last_name": data.last_name,
            "data-user_role_id": data.user_role_id,
            "data-user_barangay": data.user_barangay,
            "data-access_routes": data.access_routes,
            "data-user_status": data.user_status,
          });

          $(row).addClass("hover_cls");
        },
      });
    },
  },
  add_update_user: function (cat, user_id, callback) {
    let action = cat == 1 ? "add_account.php" : "update_account.php";

    const { username, password, user_role_id, first_name, last_name, user_barangay, access_routes } = manage_account.input();
    const params = {
      _user_id: user_id,
      _username: username.value,
      _password: password.value,
      _user_role_id: user_role_id.value,
      _first_name: first_name.value,
      _last_name: last_name.value,
      _user_barangay: user_barangay.value,
      _access_routes: access_routes,
      // _status: status,
    };

    main.api_request("users/" + action, params, "POST", function (resp) {
      callback(resp);
    });
  },
  reset: {
    show_add_hide_update_cancel_btn_user: function () {
      // create_account_btn_label.style.display = "block";
      create_account_btn.style.display = "block";
      update_account_btn.style.display = "none";
      cancel_account_btn.style.display = "none";
      // status_label.style.display = "none";
      // status_field.style.display = "none";
    },
    clear_user_field: function () {
      $(".manage-account-form").find("input").val("").end().find("select").val("").end();
      $(".form-control.is-valid, .form-select.is-valid").removeClass("is-valid");
      $(".form-control.is-invalid, .form-select.is-invalid").removeClass("is-invalid");
      $(".user_access").val(null).trigger("change");
    },
    hide_add_show_update_cancel_btn_user: function () {
      // create_account_btn_label.display = "none";
      create_account_btn.style.display = "none";
      update_account_btn.style.display = "block";
      cancel_account_btn.style.display = "block";
      // status_label.style.display = "block";
      // status_field.style.display = "block";
    },
  },
};

// initialize select2 btn
$(".user_access").select2({
  createTag: function () {
    // Disable tagging
    return null;
  },
  // theme: "bootstrap-5",
  closeOnSelect: false,
  placeholder: "Select User Access",
  dropdownAutoWidth: true,
});

// initialize validator
main.init.validator("form.manage-account-form");

// initialize methods
manage_account.tbl.user_account_tbl();
manage_account.user_role_list();
//manage_account.get.maintenance_mode(); //for future upgrade

$(document)
  // CREATE USER
  .off("click", "#create_account_btn")
  .on("click", "#create_account_btn", function (e) {
    e.preventDefault();
    const { access_routes } = manage_account.input();

    if (validator.checkAll() > 0 || access_routes.length == 0) {
      Toast.fire({
        icon: "error",
        title: "Please Fill out the required fields and select at least one (1) user access!",
      });
      Ladda.stopAll();
      return;
    }

    manage_account.add_update_user(1, "", function (resp) {
      // 1 = add, 0 = update
      if (resp.status) {
        $("#user_accounts_dt").DataTable().draw(false); // refresh with false = to retain page when draw
        manage_account.reset.clear_user_field();
        Toast.fire({
          icon: "success",
          title: resp.message,
        });
        Ladda.stopAll();
      } else {
        // error message
        alert(resp.message);
        Ladda.stopAll();
      }
    });
    Ladda.stopAll();
  })

  // UPDATE BUTTON
  .off("click", ".user_update_btn_dt")
  .on("click", ".user_update_btn_dt", function () {
    let { user_id, username, password, user_role_id, first_name, last_name, user_barangay, access_routes } = $(this).data();

    manage_account.reset.clear_user_field();
    manage_account.reset.hide_add_show_update_cancel_btn_user();

    document.getElementById("username").value = username;
    document.getElementById("user_role").value = user_role_id;
    document.getElementById("first_name").value = first_name;
    document.getElementById("last_name").value = last_name;
    document.getElementById("user_barangay").value = user_barangay;
    // document.getElementById("status").value = status;

    // convert access_routes to array before populate to select2
    let userAccessArr = access_routes.split(",");
    $(".user_access").val(userAccessArr).trigger("change");

    // STORE UPDATED DATA
    $(document)
      .off("click", "#update_account_btn")
      .on("click", "#update_account_btn", function () {
        const { access_routes } = manage_account.input();

        // remove required attribute when update
        $("#password").removeAttr("required");
        // $("#status").removeClass("is-valid");

        if (validator.checkAll() > 0 || access_routes.length == 0) {
          Toast.fire({
            icon: "error",
            title: "Please Fill out the required fields and select at least one (1) user access!",
          });
          Ladda.stopAll();
          return;
        }

        manage_account.add_update_user(0, user_id, function (resp) {
          if (resp.status) {
            Toast.fire({
              icon: "success",
              title: resp.message,
            });
            manage_account.reset.clear_user_field();
            $("#user_accounts_dt").DataTable().draw(false); // refresh with false = to retain page when draw
            manage_account.reset.show_add_hide_update_cancel_btn_user();
            Ladda.stopAll();
          } else {
            manage_account.reset.clear_user_field();
            Swal.fire("Error on Update!", resp.message, "error");
            manage_account.reset.show_add_hide_update_cancel_btn_user();
            Ladda.stopAll();
          }
        });
      });
  })

  // CANCEL UPDATE BUTTON
  .off("click", "#cancel_account_btn")
  .on("click", "#cancel_account_btn", function () {
    manage_account.reset.clear_user_field();
    manage_account.reset.show_add_hide_update_cancel_btn_user();
  })

  // SEARCH BUTTON
  .off("click", "#search_account_btn")
  .on("click", "#search_account_btn", function () {
    $("#user_accounts_dt").DataTable().draw(true); // refresh with false = to retain page when draw
  })
  // MAINTENANCE
  // .off("change", "#maintenance_mode")
  // .on("change", "#maintenance_mode", function () {
  //   // Check if the checkbox is checked
  //   let mode = $(this).prop("checked") ? 0 : 1;

  //   const params = {
  //     _mode: mode,
  //   };

  //   manage_account.maintenance_action(params, function (resp) {
  //     if (resp.is_maintenance == 0) {
  //       $("#maintenance_mode_label").html(`<span class="badge bg-success">Online</span>`);
  //       main.toast.success("Server is back online");
  //     } else {
  //       $("#maintenance_mode_label").html(`<span class="badge bg-danger">Offline</span>`);
  //       main.toast.error("Server is now in maintenance mode");
  //     }
  //   });
  // });
