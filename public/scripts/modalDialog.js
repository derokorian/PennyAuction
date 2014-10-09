app.directive('modal', function() {
    return {
        restrict: 'A',
        scope: {
            show: '=',
            title: '@',
            modal: '='
        },
        replace: true, // Replace with the template below
        transclude: true, // we want to insert custom content inside the directive
        link: function(scope, element, attrs) {
            scope.dialogStyle = {};
            if (scope.modal.width) {
                scope.dialogStyle.width = scope.modal.width;
            }
            if (scope.modal.height) {
                scope.dialogStyle.height = scope.modal.height;
            }
        },
        template: "<div class='ng-modal' ng-show='show'>"+
                      "<div class='ng-modal-overlay'></div>"+
                      "<div class='ng-modal-dialog' ng-style='dialogStyle'>"+
                          "<div class='ng-modal-dialog-title ng-show='title && title.length'>{{title}}</div>" +
                          "<div class='ng-modal-dialog-content' ng-transclude></div>"+
                      "</div>"+
                  "</div>"
    };
});