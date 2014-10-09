app.factory('userModel', ['$http', 'BASE_URL', function($http, BASE_URL) {
    return {
        getUsers: function (aOpts) {
            return $http({
                url: BASE_URL + 'user/getUsers',
                method: 'GET',
                data: aOpts
            }).then(function (result) {
                return result.data;
            });
        },
        login: function (oLogin) {
            return $http({
                url: BASE_URL + 'user/login',
                method: 'POST',
                data: oLogin
            }).then(function (result) {
                return result.data;
            });
        },
        logout: function () {
            return $http({
                url: BASE_URL + 'user/logout',
                method: 'POST'
            }).then(function (result) {
                return result.data;
            });
        },
        getCurrentUser: function () {
            return $http({
                url: BASE_URL + 'user/current',
                method: 'get'
            }).then(function (result) {
                return result.data;
            });
        },
        saveUser: function(oUser) {
            return $http({
                url: BASE_URL + 'user/saveUser',
                method: 'POST',
                data: { user: oUser }
            }).then(function (result) {
                return result.data;
            });
        },
        forgotPassword: function(strEmail) {
            return $http({
                url: BASE_URL + 'user/saveUser',
                method: 'POST',
                data: { email: strEmail }
            }).then(function (result) {
                return result.data;
            });
        }
    };
}]);