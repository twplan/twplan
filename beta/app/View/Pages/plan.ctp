<?php if ($this->Session->flash()) :?>

<h1>Plan</h1>

<?php echo $this->Session->flash(); ?>

<?php else : ?>

<div ng-app='PlanModule'>

	<!-- Dynamic content loaded by $routeProvider using the appropriate partials for each step -->
	<div ng-view></div>

</div>

<?php endif; ?>