//<loading-gif ng-if="!dataLoaded"> </loading-gif>
// TODO: добавить throttle - не показывать гифку если идет тут-же переключение туда - обратно
angular.module('admin_app')
    .directive('loadingGif', [function() {
        return {
            restrict: 'E',
            template: '<img ng-if="lgIf" src="img/ajax-loading.gif"/>',
            scope: {
                lgIf: '='
            }
        };
    }]);