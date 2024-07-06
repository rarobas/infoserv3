// Authentications here thru api request
const auth = {
  isLogin: async function () {
    return new Promise((resolve) => {
      main.api_request("session/get_session.php", null, "GET", async function (resp) {
        const { username, login_type } = JSON.parse(resp);

        const isLoggedIn = username !== null || login_type !== null;

        resolve({ isLoggedIn, login_type_session: login_type });
      });
    });
  },
};
