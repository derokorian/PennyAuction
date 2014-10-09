app.controller('userCtrl', function($scope, $timeout, userModel) {
    $scope.bIsRegistering = false;
    $scope.bForgotPassword = false;
    $scope.bIsLoggedIn = typeof $scope.user !== 'undefined';

    userModel.getCurrentUser().then(function(oRet) {
        setResults(oRet);
        if( $scope.$parent.isSuccess )
        {
            $scope.bIsLoggedIn = true;
            $scope.user = oRet.user;
        }
    })

    $scope.cancelRegister = function() {
        delete $scope.register;
        $scope.bIsRegistering = false;
    };

    $scope.cancelForgotPassword = function() {
        delete $scope.forgot;
        $scope.bForgotPassword =false;
    };

    $scope.submitRegister = function() {
        if( document.getElementById('frmRegister').checkValidity() )
        {
            userModel.saveUser($scope.register).then(function(oRet) {
                $scope.bIsRegistering = false;
                setResults(oRet);
            });
        }
    };

    $scope.submitForgotPassword = function() {
        if( document.getElementById('frmPasswordReset').checkValidity() )
        {
            userModel.forgotPassword($scope.forgot.email).then(function(oRet) {
                $scope.bForgotPassword = false;
                setResults(oRet);
            });
        }
    };

    $scope.logout = function() {
        userModel.logout().then(function(oRet) {
            setResults(oRet);
        });
        delete $scope.user;
        $scope.bIsLoggedIn = false;
        $scope.$parent.character = {};
    }

    $scope.doLogin = function() {
        if( document.getElementById('frmUserLogin').checkValidity() )
        {
            userModel.login($scope.login).then(function(oRet) {
                setResults(oRet);
                if( $scope.$parent.isSuccess )
                {
                    $scope.bIsLoggedIn = true;
                    $scope.user = oRet.user;
                }
            });
        }
    };

    function setResults(oRet) {
        if( oRet.success ) {
            $scope.$parent.results = oRet.success;
            $scope.$parent.isSuccess = true;
        }
        else
        {
            var err = oRet.error;
            if( Array.isArray(err) )
                err = err.join('<br />');
            $scope.$parent.results = err;
            $scope.$parent.isSuccess = false;
        }
        $timeout(function() {
            $scope.$parent.results = null;
        }, 3000);
    }
});