TWP.twplan.Directives.directive('manualNukeTargetInput', ['AutocompleteBuilder', function (AutocompleteBuilder) {
	return {
		scope: true,
		require: 'ngModel',
		link: function (scope, elm, attrs, ctrl) {
			scope.manual_target = null; // A Target object
			scope.old_manual_target = null; // A Target object

			// Initialize the autocomplete widget
			AutocompleteBuilder.nuke_autocomplete(elm);

			// When focused, if there is currently a manual_target, keep a reference to it in case it is changed
			elm.bind('focus', function () {
				if (scope.manual_target) {
					scope.old_manual_target = scope.manual_target;
				}
			});

			scope.update_manual_target = function (target) {
				// Remove the manual target from the scope.targets_in_plan.nukes array
				scope.targets_in_plan.nukes.splice(scope.targets_in_plan.nukes.indexOf(target), 1);
				scope.manual_target = target;

				// If this update is replacing an old manual target, put the old one back in the scope.targets_in_plan array
				if (scope.old_manual_target) {
					scope.targets_in_plan.nukes.push(scope.old_manual_target);
					scope.old_manual_target = null;
				}
			};

			scope.restore_manual_target = function () {
				scope.manual_target = scope.old_manual_target;
				scope.manual_target.label = scope.manual_target.x_coord + '|' + scope.manual_target.y_coord; // In case erased by ng-model update
				scope.old_manual_target = null;
			};
		}
	};
}]);